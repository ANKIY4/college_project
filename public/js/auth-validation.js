(function () {
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function findErrorElement(form, fieldName) {
        return form.querySelector('[data-error-for="' + fieldName + '"]');
    }

    function setError(form, fieldName, message) {
        var field = form.querySelector('[name="' + fieldName + '"]');
        var errorElement = findErrorElement(form, fieldName);

        if (field) {
            if (message) {
                field.classList.add('input-error');
            } else {
                field.classList.remove('input-error');
            }
        }

        if (errorElement) {
            errorElement.textContent = message || '';
        }
    }

    function clearErrors(form) {
        var fields = form.querySelectorAll('input');
        fields.forEach(function (field) {
            field.classList.remove('input-error');
        });

        var errorLabels = form.querySelectorAll('[data-error-for]');
        errorLabels.forEach(function (label) {
            label.textContent = '';
        });
    }

    function validateLogin(form) {
        var valid = true;
        var email = form.email ? form.email.value.trim() : '';
        var password = form.password ? form.password.value : '';

        if (!isValidEmail(email)) {
            setError(form, 'email', 'Enter a valid email address.');
            valid = false;
        }

        if (!password) {
            setError(form, 'password', 'Password is required.');
            valid = false;
        }

        return valid;
    }

    function validateSignup(form) {
        var valid = true;
        var name = form.name ? form.name.value.trim() : '';
        var email = form.email ? form.email.value.trim() : '';
        var password = form.password ? form.password.value : '';
        var confirmPassword = form.confirm_password ? form.confirm_password.value : '';

        if (name.length < 2) {
            setError(form, 'name', 'Name must be at least 2 characters.');
            valid = false;
        }

        if (!isValidEmail(email)) {
            setError(form, 'email', 'Enter a valid email address.');
            valid = false;
        }

        if (password.length < 8 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/\d/.test(password)) {
            setError(form, 'password', 'Use 8+ chars with uppercase, lowercase, and number.');
            valid = false;
        }

        if (confirmPassword !== password) {
            setError(form, 'confirm_password', 'Passwords do not match.');
            valid = false;
        }

        return valid;
    }

    document.querySelectorAll('form[data-auth-type]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            clearErrors(form);

            var authType = form.getAttribute('data-auth-type');
            var formIsValid = authType === 'signup' ? validateSignup(form) : validateLogin(form);

            if (!formIsValid) {
                event.preventDefault();
            }
        });
    });
})();
