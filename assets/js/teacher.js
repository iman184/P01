/* ──────────────────────────────────────────
   TEACHER PAGES - JAVASCRIPT
   ────────────────────────────────────────── */

/**
 * Calculate weighted average of grades
 * Used when adding/editing notes for students
 */
function calculateWeightedAverage() {
    const rows = document.querySelectorAll('[data-grade][data-coef]');
    let totalScore = 0;
    let totalWeight = 0;
    let count = 0;
    
    rows.forEach(row => {
        const grade = parseFloat(row.getAttribute('data-grade'));
        const coef = parseFloat(row.getAttribute('data-coef'));
        
        if (!isNaN(grade) && !isNaN(coef) && grade >= 0) {
            totalScore += grade * coef;
            totalWeight += coef;
            count++;
        }
    });
    
    if (count > 0 && totalWeight > 0) {
        const average = totalScore / totalWeight;
        const averageEl = document.getElementById('weightedAverage');
        if (averageEl) {
            averageEl.textContent = average.toFixed(2);
            averageEl.className = average >= 10 ? 'grade-ok' : 'grade-bad';
        }
        return average;
    }
    return null;
}

/**
 * Update grade input and recalculate average
 * Called when grade value changes
 */
function updateGradeInput(input) {
    const value = parseFloat(input.value);
    
    // Validate grade is between 0 and 20
    if (!isNaN(value)) {
        if (value < 0) input.value = 0;
        if (value > 20) input.value = 20;
    }
    
    calculateWeightedAverage();
}

/**
 * Format student name with first letter capitalized
 */
function formatStudentName(name) {
    return name.trim().split(' ').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
    ).join(' ');
}

/**
 * Initialize event listeners for teacher pages
 */
document.addEventListener('DOMContentLoaded', function() {
    // Grade input validation
    const gradeInputs = document.querySelectorAll('.grade-input');
    if (gradeInputs.length > 0) {
        gradeInputs.forEach(input => {
            input.addEventListener('blur', function() {
                updateGradeInput(this);
            });
            input.addEventListener('keyup', function() {
                updateGradeInput(this);
            });
        });
        calculateWeightedAverage(); // Calculate on page load
    }
});
