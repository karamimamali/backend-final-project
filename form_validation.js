/**
 * Form Validation and Enhancement Script
 * Handles client-side validation and user experience improvements
 */

document.addEventListener('DOMContentLoaded', function() {
    const sessionForm = document.getElementById('sessionForm');
    const dateField = document.getElementById('sessionDate');
    const startTimeField = document.getElementById('startTime');
    const endTimeField = document.getElementById('endTime');
    const distanceField = document.getElementById('distance');
    
    // Set maximum date to today
    const currentDate = new Date().toISOString().split('T')[0];
    dateField.setAttribute('max', currentDate);
    
    // Set default date to today only for new entries (not edit mode)
    if (!dateField.value) {
        dateField.value = currentDate;
    }
    
    /**
     * Validate form submission
     */
    sessionForm.addEventListener('submit', function(event) {
        const distanceValue = parseFloat(distanceField.value);
        
        // Validate distance is positive
        if (distanceValue <= 0 || isNaN(distanceValue)) {
            event.preventDefault();
            showValidationError('Please enter a valid distance greater than 0 kilometers.');
            distanceField.focus();
            return false;
        }
        
        // Validate date is not in the future
        const selectedDate = new Date(dateField.value);
        const todayDate = new Date(currentDate);
        
        if (selectedDate > todayDate) {
            event.preventDefault();
            showValidationError('The session date cannot be in the future.');
            dateField.focus();
            return false;
        }

        // Validate time range
        if (startTimeField.value && endTimeField.value) {
            if (startTimeField.value >= endTimeField.value) {
                event.preventDefault();
                showValidationError('End time must be after start time.');
                endTimeField.focus();
                return false;
            }
        }
        
        // Show loading state
        const submitButton = sessionForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span>Saving...</span>';
        }
    });
    
    /**
     * Display validation error message
     */
    function showValidationError(message) {
        alert(message);
    }
    
    /**
     * Add error styling on invalid inputs
     */
    const formInputs = sessionForm.querySelectorAll('input, select');
    formInputs.forEach(input => {
        input.addEventListener('invalid', function(event) {
            event.preventDefault();
            this.classList.add('field-error');
        });
        
        input.addEventListener('input', function() {
            this.classList.remove('field-error');
        });
    });
    
    /**
     * Real-time distance validation
     */
    if (distanceField) {
        distanceField.addEventListener('blur', function() {
            const value = parseFloat(this.value);
            if (value < 0) {
                this.value = 0;
            }
        });
    }
    
    /**
     * Auto-focus first empty required field
     */
    const firstEmptyField = sessionForm.querySelector('input[required]:not([value]), select[required]:not([value])');
    if (firstEmptyField && !dateField.value) {
        // Skip date field as it gets auto-filled
        const nextField = sessionForm.querySelector('input[required]:not(#sessionDate)');
        if (nextField) {
            setTimeout(() => nextField.focus(), 100);
        }
    }
});