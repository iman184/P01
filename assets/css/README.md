# CSS Architecture Documentation

## Overview
The CSS has been reorganized into a clean, modular architecture with proper separation of concerns.

## File Structure

### `main.css` ⭐ **Main Entry Point**
- Imports all CSS modules in order
- Contains architecture notes and usage guidelines
- **Link this file in all HTML pages**

### `variables.css`
**Purpose:** Design tokens and CSS custom properties

**Contains:**
- Color system (primary, text, backgrounds, borders, semantic colors)
- Spacing system (xs, sm, md, lg, xl, 2xl, 3xl)
- Typography variables (font sizes, font family)
- Shadow definitions (sm, md, lg)
- Border radius values (md, lg)
- Layout dimensions (sidebar width, navbar height)
- Transition timing

**Usage:** Reference variables throughout all other CSS files
```css
color: var(--color-primary);
padding: var(--spacing-md);
```

---

### `global.css`
**Purpose:** Global styles and base elements

**Contains:**
- CSS reset and box model defaults
- Body and base element styling
- Typography defaults (headings, paragraphs, links)
- Navbar component (sticky positioning, navigation links)
- Table base styles
- Responsive adjustments for mobile

**Usage:** Applied to all pages automatically

---

### `components.css`
**Purpose:** Reusable UI components

**Contains:**
- Buttons (primary, secondary, danger, outline, navigation)
- Badges (blue, green, amber, red)
- Alerts (success, danger, info, warning)
- Forms (groups, inputs, textareas, selects, validation)
- Cards (generic cards with headers)
- Stat cards (with icons and metrics)
- Average/grade display boxes
- Progress bars
- Info lists
- Responsive utilities

**Usage:** Apply component classes to HTML elements
```html
<button class="btn-primary">Submit</button>
<div class="badge blue">Active</div>
<form class="form-group">...</form>
```

---

### `pages.css`
**Purpose:** Page-specific layouts and styles

**Contains:**

#### Homepage Page
- Hero section (title, subtitle, call-to-action)
- Features grid and cards
- Roles section with role cards
- About section with statistics
- Footer

#### Authentication Pages
- Auth wrapper and card layout
- Role selector buttons
- Form styling specific to login pages

#### Dashboard Page
- Sidebar navigation and layout
- Main content area
- Dashboard grid layouts
- Navigation items and sections
- Page headers and statistics grids

**Responsive Breakpoints:**
- 1024px - Desktop to tablet
- 768px - Tablet to mobile
- 480px - Mobile optimization

---

## Color System

### Primary Colors
```
--color-primary: #0A2E8A (Blue)
--color-text: #1e293b (Dark slate)
--color-bg-white: #fff (White)
--color-border-light: #e2e8f0 (Light gray)
```

### Semantic Colors
```
--color-green: #10b981 (Success)
--color-amber: #f59e0b (Warning)
--color-red: #ef4444 (Error/Danger)
```

### Light Variants (For backgrounds)
```
--color-primary-light: #e8f0ff
--color-green-light: #e6f7ee
--color-amber-light: #fef3e0
--color-red-light: #fee2e2
```

---

## Spacing System

| Size | Value | Variable |
|------|-------|----------|
| XS | 4px | `--spacing-xs` |
| SM | 8px | `--spacing-sm` |
| MD | 12px | `--spacing-md` |
| LG | 16px | `--spacing-lg` |
| XL | 20px | `--spacing-xl` |
| 2XL | 24px | `--spacing-2xl` |
| 3XL | 32px | `--spacing-3xl` |

---

## Typography

```
--font-size-xs: 11px
--font-size-sm: 12px
--font-size-base: 13px
--font-size-md: 14px
--font-size-lg: 16px
--font-size-xl: 18px
--font-size-2xl: 24px
--font-size-3xl: 28px
```

---

## Best Practices

✅ **DO:**
- Use CSS variables for all colors, spacing, shadows
- Use semantic class names (.role-card, .auth-wrap, etc)
- Keep component styles organized in components.css
- Keep page layouts in pages.css
- Use utility classes for common patterns
- Maintain mobile-first responsive design
- Use transitions for interactive elements

❌ **DON'T:**
- Hardcode colors or spacing values
- Use inline styles in HTML
- Create new CSS files without updating main.css
- Use non-semantic class names (.red-box, .big-text, etc)
- Mix component styles with page styles

---

## How to Add New Styles

### New Component
1. Add component class to `components.css`
2. Use CSS variables for all properties
3. Include responsive styles at the end

### New Page-Specific Style
1. Add to `pages.css` under the appropriate page section
2. Use existing component classes where possible
3. Include responsive breakpoints

### New Design Token
1. Add to `variables.css` in the appropriate section
2. Use meaningful variable names (e.g., `--spacing-lg`, not `--m-16`)
3. Document the variable in this README

---

## File Import Order

The import order in `main.css` is important:

1. **variables.css** - Define all tokens first
2. **global.css** - Base styles and reset
3. **components.css** - Reusable components
4. **pages.css** - Page-specific layouts

This ensures proper cascade and specificity.

---

## Responsive Design

All styles include responsive breakpoints at:

- **1024px** - Changes from desktop 3-column to 2-column layouts
- **768px** - Changes to single-column mobile layout, hides sidebar
- **480px** - Further optimizations for small screens

Example:
```css
/* Desktop (default) */
.roles-grid {
    grid-template-columns: repeat(3, 1fr);
}

/* Tablet */
@media (max-width: 1024px) {
    .roles-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Mobile */
@media (max-width: 768px) {
    .roles-grid {
        grid-template-columns: 1fr;
    }
}
```

---

## Maintenance

- Review and update variables.css when design tokens change
- Keep components.css focused on reusable components
- Update pages.css when adding new pages or layouts
- Always test responsive design at all breakpoints
- Avoid creating duplicate styles across files

---

Last updated: April 21, 2026
