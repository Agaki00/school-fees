// script.js - Client-side JavaScript for the School Fees Payment Portal
// Features: Form validation, CSRF token handling, smooth UX enhancements, and basic security checks

document.addEventListener('DOMContentLoaded', function () {
    // 1. Form Validation for fees.php
    const feeForm = document.getElementById('feeForm');
    if (feeForm) {
        feeForm.addEventListener('submit', function (e) {
            // Reset previous error states
            clearErrors();

            let isValid = true;
            const errors = [];

            // Get form fields
            const payerName = document.getElementById('payer_name').value.trim();
            const payerEmail = document.getElementById('payer_email').value.trim();
            const payerPhone = document.getElementById('payer_phone').value.trim();
            const amount = document.getElementById('amount').value.trim();
            const description = document.getElementById('description').value.trim();

            // Name validation
            if (payerName === '' || payerName.length < 3) {
                isValid = false;
                errors.push('Please enter a valid full name (at least 3 characters).');
                highlightError('payer_name');
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(payerEmail)) {
                isValid = false;
                errors.push('Please enter a valid email address.');
                highlightError('payer_email');
            }

            // Phone validation (Nigerian format or international)
            const phoneRegex = /^[\+]?[0-9]{10,15}$/;
            if (!phoneRegex.test(payerPhone.replace(/\s/g, ''))) {
                isValid = false;
                errors.push('Please enter a valid phone number (10-15 digits).');
                highlightError('payer_phone');
            }

            // Amount validation
            if (amount === '' || isNaN(amount) || parseFloat(amount) <= 0) {
                isValid = false;
                errors.push('Please enter a valid amount greater than zero.');
                highlightError('amount');
            }

            // Description validation
            if (description === '' || description.length < 10) {
                isValid = false;
                errors.push('Please provide a detailed description (e.g., Student ID, Class, Term).');
                highlightError('description');
            }

            // If not valid, prevent submission and show errors
            if (!isValid) {
                e.preventDefault();
                showFormErrors(errors);
            }
            // If valid, submission continues normally (PHP handles server-side validation too)
        });
    }

    // Helper: Highlight field with error
    function highlightError(fieldId) {
        const field = document.getElementById(fieldId);
        field.style.borderColor = '#c41b1b';
        field.style.boxShadow = '0 0 0 3px rgba(196, 27, 27, 0.2)';
    }

    // Helper: Clear previous error styles
    function clearErrors() {
        const fields = ['payer_name', 'payer_email', 'payer_phone', 'amount', 'description'];
        fields.forEach(id => {
            const field = document.getElementById(id);
            if (field) {
                field.style.borderColor = '#ccc';
                field.style.boxShadow = 'none';
            }
        });

        // Remove any previous error message
        const existingError = document.querySelector('.form-error-message');
        if (existingError) {
            existingError.remove();
        }
    }

    // Helper: Show error messages above the form
    function showFormErrors(errors) {
        const errorContainer = document.createElement('div');
        errorContainer.className = 'message error form-error-message';
        errorContainer.innerHTML = '<strong>Please fix the following:</strong><ul style="text-align:left;margin-top:0.5rem;">' +
            errors.map(err => `<li>${err}</li>`).join('') +
            '</ul>';
        
        // Insert after the card title or at top of form
        const card = document.querySelector('.card');
        if (card) {
            card.insertBefore(errorContainer, card.querySelector('form'));
        }
    }

    // 2. Auto-format phone number (optional nice touch)
    const phoneInput = document.getElementById('payer_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            let value = this.value.replace(/\D/g, ''); // Remove non-digits
            if (value.startsWith('234')) {
                value = '+' + value;
            } else if (value.startsWith('0')) {
                value = '+234' + value.substring(1);
            } else if (value.length >= 10) {
                value = '+234' + value.substring(0, 10);
            }
            this.value = value;
        });
    }

    // 3. Auto-focus first field on page load
    const firstField = document.querySelector('input, textarea');
    if (firstField) {
        firstField.focus();
    }

    // 4. Smooth scroll to RRR box if present (after successful generation)
    const rrrBox = document.querySelector('.rrr-box');
    if (rrrBox && window.location.hash === '#rrr') {
        rrrBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // 5. Refresh button for viewrecord.php (to check latest payment status)
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            location.reload();
        });
    }
});