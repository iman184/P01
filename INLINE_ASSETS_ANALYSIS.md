# Inline CSS & JavaScript Analysis Report

## Executive Summary
Found **extensive inline CSS and JavaScript** across admin, student, and teacher pages. Major consolidation opportunities exist, especially for repeated patterns like grade calculations, color codes, and status styling.

---

## ADMIN FOLDER

### admin/students.php
**File Path:** `admin/students.php`

#### Inline CSS
```css
/* Search input styling */
width: 100%; 
max-width: 400px; 
padding: 8px 12px; 
border: 1px solid #cbd5e1; 
border-radius: 4px; 
font-size: 14px;

/* Table and cells */
font-size: 13px;
padding: 8px 6px;    /* table header */
padding: 6px;        /* table cells */
text-align: center;  /* status column */

/* Status badge - offline */
background: #cbd5e1; 
color: #475569;

/* Online indicator text */
color: #10b981; 
font-weight: bold;

/* Never logged in text */
color: #94a3b8; 
font-size: 10px;
```

#### Inline JavaScript Functions

**1. `updateOnlineStatus()`**
- **Purpose:** Fetches student online status via AJAX and updates page every 3 seconds
- **Flow:**
  - Calls `fetch('get_student_status.php')`
  - Updates `.status-badge` element with green (🟢) or gray (🔴) indicator
  - Updates 5th column (last login) with formatted date or "En ligne"
  - Auto-runs every 3000ms via `setInterval()`
  - Also runs on page load (`DOMContentLoaded`)
- **DOM Selectors:**
  - `[data-student-id="${student.id}"]` - identifies rows by data attribute
  - `.status-badge` - badge container
  - `td` elements by index

**2. `filterStudents()`**
- **Purpose:** Real-time search filtering of student table
- **Flow:**
  - Gets search term from `#searchInput` (lowercase)
  - Searches column 0 (matricule) and column 1 (full name)
  - Shows/hides rows: `row.style.display = ''` or `'none'`
  - Triggered on `keyup` event
- **DOM Selectors:**
  - `#searchInput` - search input field
  - `table tbody` - table body
  - `tr` rows and `td` cells

#### Data Attributes Used
- `data-student-id` - on each table row to identify student

#### DOM Structure Requirements
```html
<input id="searchInput" type="text">
<table>
  <tbody>
    <tr data-student-id="123">
      <td>Matricule</td>
      <td>Full Name</td>
      <td>Email</td>
      <td><span class="status-badge">Badge</span></td>
      <td>Last Login</td>
      <!-- more columns -->
    </tr>
  </tbody>
</table>
```

---

### admin/dashboard.php
**File Path:** `admin/dashboard.php`

#### Inline CSS
```css
color: #94a3b8;  /* for "Jamais" text */
```

#### Inline JavaScript
None

---

### admin/notes.php
**File Path:** `admin/notes.php`

#### Inline CSS
```css
/* Average box container - styling handled by external classes */
.average-box  /* pass or fail variant */
.average-left
.average-score
.average-badge

/* Inline colors only */
color: #94a3b8;  /* "Jamais" text */
```

#### Inline JavaScript
None (calculation in add_note.php)

#### Helper Functions (PHP)
- `mention($grade)` - converts grade to badge HTML (16+: Très bien, 14+: Bien, 12+: Assez bien, 10+: Passable, <10: Insuffisant)

---

### admin/add_note.php
**File Path:** `admin/add_note.php`

#### Inline CSS
```css
.hidden { display: none; }  /* for coefficient display initially hidden */
background: #cbd5e1;       /* disabled input background */
```

#### Inline JavaScript Functions

**1. `updateMention()`**
- **Purpose:** Live grade-to-mention conversion
- **Logic:**
  ```
  16+  → 🏆 Très bien
  14+  → ✅ Bien
  12+  → 📘 Assez bien
  10+  → 🟡 Passable
  <10  → ❌ Insuffisant
  ```
- **Triggers:** On module select change AND grade input change
- **Updates:** `#mention_display` field

**2. Event Listeners**
- `#module_select` change - shows coefficient in `#coef_display`
- `#grade_input` input - updates mention display

#### DOM Elements Required
```html
<select id="module_select" name="module_id">
  <option data-coef="2.5">MODULE (Coef. 2.5)</option>
</select>

<div id="coef_display" class="hidden">
  <input type="text" id="coef_value" disabled>
</div>

<input type="number" id="grade_input" name="grade">

<input type="text" id="mention_display" disabled>
```

---

### admin/edit_note.php
**File Path:** `admin/edit_note.php`

#### Inline CSS
```css
background: #cbd5e1;  /* disabled input background */
```

#### Inline JavaScript Functions

**1. `updateMention()`** (Same as add_note.php)
- Called on page load and grade input change
- Updates `#mention_display` with calculated mention

#### DOM Elements
```html
<input type="number" id="grade_input" name="grade" value="current_grade">
<input type="text" id="mention_display" disabled>
```

---

### admin/add_student.php, add_teacher.php, add_module.php
**Inline CSS & JS:** None (form-only pages)

---

### admin/edit_student.php, edit_teacher.php, edit_module.php
**Inline CSS & JS:** None (form-only pages with field validation messages)

---

## STUDENT FOLDER

### student/dashboard.php
**File Path:** `student/dashboard.php`

#### Inline CSS - EXTENSIVE

**1. Profile Image Container**
```css
#profile-image-container {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  background: #f0f4f8;
  border: 3px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
}

#profile-image-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
```

**2. Profile Section Layout**
```css
/* Parent flex container */
display: flex;
gap: 24px;
align-items: start;

/* Image column */
flex-shrink: 0;

/* Info column */
flex: 1;

/* Info labels */
margin: 0 0 4px;
color: #64748b;

/* Heading */
margin: 0 0 8px;
font-size: 20px;

/* Button row */
display: flex;
gap: 8px;
align-items: center;
```

**3. Upload Status Message**
```css
#upload-status {
  display: none;  /* initially hidden */
  font-size: 13px;
  color: #64748b;
}

.upload-success { 
  color: #16a34a !important; 
}

.upload-error { 
  color: #dc2626 !important; 
}
```

#### Inline JavaScript Functions

**1. Profile Image Upload Handler**
- **Trigger:** `#profile-image-input` change event
- **File Validation:**
  - Max size: 5MB
  - Allowed types: image/jpeg, image/png, image/gif, image/webp
  - Allowed extensions: jpg, jpeg, png, gif, webp
- **Process:**
  - Shows "⏳ Téléchargement en cours..." status
  - Uploads to `./upload_profile_image.php` via FormData
  - Updates profile image: `#profile-image-container img` src with cache buster `?t=' + Date.now()`
  - Updates sidebar avatar: `.sidebar-user-avatar img` if present
  - Shows success/error message with auto-hide after 3000ms
- **Error Handling:** Catches and displays fetch errors
- **Resets:** Input value after upload

**2. File Input Click Handler**
```javascript
onclick="document.getElementById('profile-image-input').click();"
```

#### DOM Structure Required
```html
<input type="file" id="profile-image-input" accept="image/*" style="display: none;">
<button onclick="document.getElementById('profile-image-input').click();">
  📤 Changer la photo
</button>

<div id="profile-image-container">
  <!-- img or emoji inside -->
</div>

<span id="upload-status"></span>

<!-- Sidebar (optional but updated) -->
<div class="sidebar-user-avatar">
  <!-- img inside if exists -->
</div>
```

---

### student/releve.php
**File Path:** `student/releve.php`

#### Inline CSS - VERY EXTENSIVE

**1. Actions Bar**
```css
.releve-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 18px;
}
```

**2. Main Sheet Container**
```css
.releve-sheet {
  background: #fff;
  border: 1px solid #0f172a;
  padding: 18px;
  color: #0f172a;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}
```

**3. Sheet Header (3-column layout)**
```css
.sheet-head {
  display: grid;
  grid-template-columns: 1fr 1.1fr 1fr;
  gap: 14px;
  align-items: start;
  border-bottom: 1px solid #0f172a;
  padding-bottom: 12px;
  margin-bottom: 12px;
}

.sheet-head p {
  margin: 2px 0;
  font-size: 12px;
  line-height: 1.25;
}

.sheet-head .arabic {
  direction: rtl;
  text-align: right;
}
```

**4. Center Column (Logo & Title)**
```css
.sheet-center {
  text-align: center;
}

.sheet-center img {
  width: 72px;
  height: auto;
  margin-bottom: 4px;
}

.sheet-center h2 {
  margin: 4px 0;
  letter-spacing: 1px;
  font-size: 27px;
}

.sheet-center .sub {
  font-size: 12px;
  margin: 0;
}
```

**5. Table Styling**
```css
.identity-table,
.grades-table-usthb {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 10px;
}

.identity-table td,
.grades-table-usthb th,
.grades-table-usthb td {
  border: 1px solid #0f172a;
  padding: 6px 8px;
  font-size: 12px;
}

.identity-table .label {
  width: 20%;
  font-weight: 700;
  background: #f8fafc;
}

.grades-table-usthb th {
  font-weight: 700;
  text-align: center;
  background: #eef2ff;
}

.grades-table-usthb td {
  text-align: center;
}

.grades-table-usthb td.module {
  text-align: left;
}
```

**6. Grade Indicators**
```css
.grade-ok {
  color: #166534;
  font-weight: 700;
}

.grade-bad {
  color: #991b1b;
  font-weight: 700;
}
```

**7. Summary Row**
```css
.summary-row td {
  font-weight: 700;
  background: #f8fafc;
}
```

**8. Bottom Section**
```css
.sheet-bottom {
  margin-top: 12px;
  border-top: 1px solid #0f172a;
  padding-top: 8px;
  display: flex;
  justify-content: space-between;
  gap: 12px;
  font-size: 12px;
}
```

**9. Responsive Design**
```css
@media (max-width: 900px) {
  .sheet-head {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .sheet-head .arabic {
    text-align: center;
    direction: ltr;
  }

  .sheet-bottom {
    flex-direction: column;
  }
}
```

#### Inline JavaScript
None

#### DOM Structure
```html
<div class="releve-sheet">
  <div class="sheet-head">
    <div><!-- French header --></div>
    <div class="sheet-center">
      <img src="../assets/images/USTHB.png" alt="Logo USTHB">
      <h2>RELEVE DE NOTES</h2>
      <p class="sub">Annee universitaire 2024/2025</p>
    </div>
    <div class="arabic"><!-- Arabic header --></div>
  </div>

  <table class="identity-table">
    <tr>
      <td class="label">Nom et prenom</td>
      <td>Student Name</td>
      <td class="label">Matricule</td>
      <td>Student Number</td>
    </tr>
    <!-- more rows -->
  </table>

  <table class="grades-table-usthb">
    <thead>
      <tr>
        <th>Code UE</th>
        <th>Matiere</th>
        <th>Coef</th>
        <th>Moy /20</th>
        <th>Credits</th>
        <th>Sess</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>CODE</td>
        <td class="module">Module Name</td>
        <td>Coef</td>
        <td class="grade-ok">Grade</td>
        <td>Credits</td>
        <td>N</td>
      </tr>
    </tbody>
    <tfoot>
      <tr class="summary-row">
        <td colspan="3">Moyenne generale</td>
        <td>Average</td>
        <td>Total Credits</td>
        <td>Status</td>
      </tr>
    </tfoot>
  </table>

  <div class="sheet-bottom">
    <div><strong>Decision:</strong> ADMIS</div>
    <div><strong>Date:</strong> 15/04/2026</div>
    <div><strong>Total des credits valides:</strong> 45.5</div>
  </div>
</div>
```

---

### student/upload_profile_image.php
**Type:** JSON API Endpoint (no inline CSS/JS)

**Returns:**
```json
{
  "success": true,
  "message": "Image de profil mise à jour avec succès",
  "image_url": "../assets/uploads/student_profiles/student_123_1702395600.jpg"
}
```

---

### student/notes.php
**File Path:** `student/notes.php`

#### Inline CSS
```css
color: #94a3b8;  /* "Jamais" text */
```

#### Inline JavaScript
None

---

### student/change_password.php
**File Path:** `student/change_password.php`

#### Inline CSS & JS
None (form-only page)

---

### student/dowload_releve.php
**Type:** PDF Generation (uses DomPDF)

#### Inline CSS (in HTML string)
```css
body { 
  font-family: DejaVu Sans, sans-serif; 
  font-size: 11px; 
  color: #0f172a; 
}

/* Table styling for PDF */
.sheet { border: 1px solid #0f172a; padding: 12px; }
.head { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.head td { vertical-align: top; width: 33.33%; font-size: 10px; line-height: 1.4; }
.head .center { text-align: center; }
.head .center h1 { font-size: 24px; margin: 3px 0; letter-spacing: 1px; }

/* Same styling as releve.php but embedded in PHP string */
```

#### Inline JavaScript
None (all processing in PHP)

---

## TEACHER FOLDER

### teacher/dashboard.php
**File Path:** `teacher/dashboard.php`

#### Inline CSS
Uses external classes mostly:
- `.stat-card`, `.stat-icon`, `.stat-info`
- `.dashboard-grid`
- `.info-list`, `.info-row`, `.info-label`, `.info-value`
- `.flex`, `.gap-md`, `.mt-lg`

Minimal inline CSS:
```css
gap: 24px;  /* flex gap */
```

#### Inline JavaScript
None

---

### teacher/notes.php
**File Path:** `teacher/notes.php`

#### Inline CSS
```css
badge blue      /* badge color class */
text-secondary  /* text color class */
text-xs         /* text size class */
px-md py-sm     /* padding classes */
flex gap-md     /* flex utilities */
```

#### Inline JavaScript Functions

**1. `updateMention()`** (Same pattern as admin files)
- Grade conversion logic:
  ```
  16+  → 🏆 Très bien
  14+  → ✅ Bien
  12+  → 📘 Assez bien
  10+  → 🟡 Passable
  <10  → ❌ Insuffisant
  ```
- Called on module select and grade input events

**2. Event Listeners**
- Module select change
- Grade input change

#### DOM Elements
```html
<input type="number" id="grade_input" name="grade">
<input type="text" id="mention_display" disabled>
```

---

### teacher/students.php
**File Path:** `teacher/students.php`

#### Inline CSS
```css
.badge, .badge-amber, .badge-green, .badge-red  /* badge variants */
.text-muted                                      /* text styling */
.grade-pass, .grade-fail                        /* grade colors */
```

#### Inline JavaScript
None

---

### teacher/change_password.php
**File Path:** `teacher/change_password.php`

#### Inline CSS
```css
margin-bottom: 14px;  /* on match message */
```

#### Inline JavaScript Functions

**1. `check()`** - Password match validation
- Listens to `#new_pw` and `#confirm_pw` inputs
- Shows:
  - "✅ Les mots de passe correspondent." (color: #16a34a) if match
  - "❌ Ne correspondent pas." (color: #dc2626) if not match
- Clears message if confirm password is empty

#### DOM Elements
```html
<input type="password" id="new_pw" name="new_password">
<input type="password" id="confirm_pw" name="confirm_password">
<p id="match_msg"></p>
```

---

### admin/get_student_status.php
**Type:** JSON API Endpoint

**Returns:**
```json
[
  { "id": 1, "online": true, "last_login": "2026-04-22 10:30:00" },
  { "id": 2, "online": false, "last_login": "2026-04-21 15:45:00" }
]
```

---

## CONSOLIDATION OPPORTUNITIES

### 🔴 HIGH PRIORITY - Repeated Patterns

#### 1. Grade Mention Calculation
**Appears in:** 3 files
- `admin/add_note.php`
- `admin/edit_note.php`
- `teacher/notes.php`

**Current Inline:** 20+ lines of similar code in each file

**Recommended Consolidation:**
```javascript
// assets/js/grade-mention.js
function getGradeMention(grade) {
  if (grade >= 16) return '🏆 Très bien';
  if (grade >= 14) return '✅ Bien';
  if (grade >= 12) return '📘 Assez bien';
  if (grade >= 10) return '🟡 Passable';
  return '❌ Insuffisant';
}
```

#### 2. Color Code Constants
**Repeated throughout:**
- #10b981 (online/success green)
- #16a34a (success green)
- #94a3b8 (gray text)
- #cbd5e1 (light gray)
- #475569 (dark gray)
- #0f172a (dark)
- #dc2626 (error red)
- #f0f4f8 (very light blue)
- #e2e8f0 (light blue)
- #64748b (medium gray)

**Recommended:** Move to CSS variables
```css
:root {
  --color-online: #10b981;
  --color-success: #16a34a;
  --color-error: #dc2626;
  --color-gray-light: #94a3b8;
  --color-gray-lighter: #cbd5e1;
  --color-gray-base: #475569;
  --color-dark: #0f172a;
  --color-bg-light: #f0f4f8;
  --color-bg-lighter: #e2e8f0;
  --color-text-secondary: #64748b;
}
```

#### 3. Status Badge Styling
**Used in:**
- `admin/students.php` (online/offline indicators)
- `admin/notes.php` (pass/fail)
- `student/notes.php` (mention badges)
- `teacher/students.php` (grade status)

**Current Inline:** Mixed inline styles and classes

**Recommended:** Create utility classes in CSS

#### 4. Profile Image Upload Handler
**Location:** `student/dashboard.php`

**Size:** ~60 lines of inline JavaScript

**Recommendation:** Move to `assets/js/profile-upload.js`

#### 5. Table Search/Filter
**Location:** `admin/students.php`

**Size:** ~25 lines of inline JavaScript

**Recommendation:** Move to `assets/js/table-filter.js`

#### 6. Online Status Auto-Refresh
**Location:** `admin/students.php` - `updateOnlineStatus()`

**Size:** ~35 lines of inline JavaScript

**Recommendation:** Move to `assets/js/status-refresh.js`

---

### 📊 COMPLEXITY BREAKDOWN

| Type | Count | Files | Priority |
|------|-------|-------|----------|
| Inline CSS | 15+ sections | 7 files | HIGH |
| Inline JS Functions | 8+ functions | 5 files | HIGH |
| Repeated Grade Logic | 3 copies | admin & teacher | CRITICAL |
| Inline Colors | 30+ instances | 8 files | HIGH |
| DOM Event Handlers | 10+ | 5 files | MEDIUM |
| Form Validation | 4 instances | teacher & student | MEDIUM |

---

### 📋 DATA ATTRIBUTES USED

- `data-student-id` - student identification on table rows
- `data-coef` - module coefficient on dropdown options

---

### 🎯 RECOMMENDED ACTION PLAN

1. **Move JavaScript Functions to Separate Files:**
   - `assets/js/grade-mention.js` - grade calculation
   - `assets/js/profile-upload.js` - image upload handler
   - `assets/js/table-filter.js` - search filtering
   - `assets/js/status-refresh.js` - online status polling
   - `assets/js/password-validator.js` - password matching

2. **Extract CSS to Variables & Classes:**
   - `assets/css/colors.css` - color definitions
   - `assets/css/badges.css` - badge styling
   - `assets/css/transcript.css` - releve sheet styling

3. **Create Helper Functions in Shared Files:**
   - `assets/js/dom-helpers.js` - common DOM queries
   - `assets/js/fetch-utils.js` - standardized API calls

4. **Move Inline Styles to External CSS:**
   - Profile card layout styles
   - Table responsive styles
   - Badge and status indicator styles

---

## FILES TO REFACTOR (By Priority)

1. ⭐⭐⭐ `student/dashboard.php` - 100+ lines inline CSS & JS for profile upload
2. ⭐⭐⭐ `admin/students.php` - Complex table filtering & auto-refresh logic
3. ⭐⭐ `admin/add_note.php` & `admin/edit_note.php` - Duplicate grade logic
4. ⭐⭐ `student/releve.php` - 80+ lines inline CSS for transcript
5. ⭐ `teacher/change_password.php` - Simple password validator logic
6. ⭐ `teacher/notes.php` - Duplicate grade logic

---

## NOTHING TO REFACTOR

These files have minimal/no inline CSS/JS and are good as-is:
- admin/dashboard.php
- admin/modules.php
- admin/teachers.php
- admin/add_student.php
- admin/add_teacher.php
- admin/add_module.php
- admin/edit_student.php
- admin/edit_teacher.php
- admin/edit_module.php
- student/notes.php
- student/change_password.php
- teacher/dashboard.php
- teacher/students.php
