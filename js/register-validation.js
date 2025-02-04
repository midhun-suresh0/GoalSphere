document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const inputs = {
        firstName: document.getElementById('first-name'),
        lastName: document.getElementById('last-name'),
        email: document.getElementById('email'),
        password: document.getElementById('password'),
        confirmPassword: document.getElementById('confirm-password'),
        terms: document.getElementById('terms')
    };

    // Create error message elements for each field
    Object.keys(inputs).forEach(field => {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-500 text-sm mt-1 hidden';
        errorDiv.id = `${field}-error`;
        inputs[field].parentNode.appendChild(errorDiv);
    });

    // Add input event listeners
    inputs.firstName.addEventListener('input', () => validateField('firstName'));
    inputs.lastName.addEventListener('input', () => validateField('lastName'));
    inputs.email.addEventListener('input', () => validateField('email'));
    inputs.password.addEventListener('input', () => {
        validateField('password');
        if(inputs.confirmPassword.value) validateField('confirmPassword');
    });
    inputs.confirmPassword.addEventListener('input', () => validateField('confirmPassword'));
    inputs.terms.addEventListener('change', () => validateField('terms'));

    function validateField(field) {
        const value = inputs[field].value.trim();
        const errorElement = document.getElementById(`${field}-error`);

        switch(field) {
            case 'firstName':
                if(!value) {
                    showError(errorElement, 'First name is required');
                } else if(value.length < 2) {
                    showError(errorElement, 'First name must be at least 2 characters');
                } else {
                    hideError(errorElement);
                }
                break;

            case 'lastName':
                if(!value) {
                    showError(errorElement, 'Last name is required');
                } else if(value.length < 2) {
                    showError(errorElement, 'Last name must be at least 2 characters');
                } else {
                    hideError(errorElement);
                }
                break;

            case 'email':
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if(!value) {
                    showError(errorElement, 'Email is required');
                } else if(!emailPattern.test(value)) {
                    showError(errorElement, 'Please enter a valid email address');
                } else {
                    hideError(errorElement);
                }
                break;

            case 'password':
                if(!value) {
                    showError(errorElement, 'Password is required');
                } else if(value.length < 8) {
                    showError(errorElement, 'Password must be at least 8 characters');
                } else if(!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(value)) {
                    showError(errorElement, 'Password must include uppercase, lowercase, and numbers');
                } else {
                    hideError(errorElement);
                }
                break;

            case 'confirmPassword':
                if(!value) {
                    showError(errorElement, 'Please confirm your password');
                } else if(value !== inputs.password.value) {
                    showError(errorElement, 'Passwords do not match');
                } else {
                    hideError(errorElement);
                }
                break;

            case 'terms':
                if(!inputs.terms.checked) {
                    showError(errorElement, 'You must accept the terms and conditions');
                } else {
                    hideError(errorElement);
                }
                break;
        }
    }

    function showError(element, message) {
        element.textContent = message;
        element.classList.remove('hidden');
        element.classList.add('text-red-500');
    }

    function hideError(element) {
        element.classList.add('hidden');
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all fields
        Object.keys(inputs).forEach(field => validateField(field));

        // Check for any errors
        const hasErrors = Object.keys(inputs).some(field => 
            !document.getElementById(`${field}-error`).classList.contains('hidden')
        );

        if(!hasErrors) {
            form.submit();
        }
    });
}); 