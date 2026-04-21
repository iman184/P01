/* ──────────────────────────────────────────
   ADMIN PAGES - JAVASCRIPT
   ────────────────────────────────────────── */

/**
 * Update student online status via AJAX
 * Called every 3 seconds to refresh status badges and login times
 */
function updateOnlineStatus() {
    fetch('get_student_status.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(student => {
                const row = document.querySelector(`[data-student-id="${student.id}"]`);
                if (row) {
                    // Update status badge
                    const badgeEl = row.querySelector('.status-badge');
                    if (badgeEl) {
                        if (student.online) {
                            badgeEl.innerHTML = '<span class="badge green">🟢</span>';
                        } else {
                            badgeEl.innerHTML = '<span class="badge" style="background: var(--offline); color: var(--offline-text);">🔴</span>';
                        }
                    }
                    
                    // Update last login column
                    const loginCells = row.querySelectorAll('td');
                    if (loginCells.length >= 5) {
                        const loginCell = loginCells[4]; // 5th column is last login
                        if (student.online) {
                            loginCell.innerHTML = '<span class="status-online">En ligne</span>';
                        } else if (student.last_login) {
                            const date = new Date(student.last_login);
                            const formatted = date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
                            loginCell.textContent = formatted;
                        } else {
                            loginCell.innerHTML = '<span class="status-never">Jamais</span>';
                        }
                    }
                }
            });
        })
        .catch(err => console.error('Status update error:', err));
}

/**
 * Filter students table by matricule or name
 * Live search with instant results
 */
function filterStudents() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    const filter = searchInput.value.toLowerCase();
    const table = document.querySelector('table tbody');
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            const matricule = cells[0].textContent.toLowerCase(); // Matricule
            const fullName = cells[1].textContent.toLowerCase();   // Nom complet
            
            if (matricule.includes(filter) || fullName.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
}

/**
 * Calculate average of grades mentioned
 * Used in note management pages
 */
function calculateGradeAverage() {
    const inputs = document.querySelectorAll('[data-coef]');
    let totalScore = 0;
    let totalWeight = 0;
    let count = 0;
    
    inputs.forEach(input => {
        const grade = parseFloat(input.value);
        const coef = parseFloat(input.getAttribute('data-coef'));
        
        if (!isNaN(grade) && !isNaN(coef)) {
            totalScore += grade * coef;
            totalWeight += coef;
            count++;
        }
    });
    
    if (totalWeight > 0) {
        const average = totalScore / totalWeight;
        const averageEl = document.getElementById('averageGrade');
        if (averageEl) {
            averageEl.textContent = average.toFixed(2);
            averageEl.className = average >= 10 ? 'grade-ok' : 'grade-bad';
        }
    }
}

/**
 * Initialize event listeners for admin pages
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh student status every 3 seconds
    updateOnlineStatus();
    setInterval(updateOnlineStatus, 3000);
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', filterStudents);
    }
    
    // Grade average calculator
    const coefInputs = document.querySelectorAll('[data-coef]');
    if (coefInputs.length > 0) {
        coefInputs.forEach(input => {
            input.addEventListener('input', calculateGradeAverage);
        });
        calculateGradeAverage(); // Calculate on page load
    }
});
