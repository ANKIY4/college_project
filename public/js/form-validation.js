(function () {
    var MODE_FIELDS = {
        login: ['email', 'password'],
        signup: ['name', 'email', 'password', 'confirm_password'],
        registration: ['full_name', 'email', 'phone', 'college'],
        event: ['title', 'venue', 'event_date', 'registration_open_at', 'registration_close_at']
    };

    function findField(form, fieldName) {
        return form.querySelector('[name="' + fieldName + '"]');
    }

    function findErrorElement(form, fieldName) {
        return form.querySelector('[data-error-for="' + fieldName + '"]');
    }

    function setError(form, fieldName, message) {
        var field = findField(form, fieldName);
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

    function getFieldValue(form, fieldName) {
        var field = findField(form, fieldName);
        return field ? field.value : '';
    }

    function getTrimmedFieldValue(form, fieldName) {
        return getFieldValue(form, fieldName).trim();
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function parseDateValue(value) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return null;
        }

        var parts = value.split('-');
        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);
        var day = parseInt(parts[2], 10);

        var date = new Date(year, month - 1, day);
        if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
            return null;
        }

        return date;
    }

    function parseDateTimeLocalValue(value) {
        if (!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(value)) {
            return null;
        }

        var pieces = value.split('T');
        var baseDate = parseDateValue(pieces[0]);
        if (!baseDate) {
            return null;
        }

        var timeParts = pieces[1].split(':');
        var hour = parseInt(timeParts[0], 10);
        var minute = parseInt(timeParts[1], 10);

        if (hour < 0 || hour > 23 || minute < 0 || minute > 59) {
            return null;
        }

        var datetime = new Date(
            baseDate.getFullYear(),
            baseDate.getMonth(),
            baseDate.getDate(),
            hour,
            minute,
            0,
            0
        );

        if (
            datetime.getFullYear() !== baseDate.getFullYear() ||
            datetime.getMonth() !== baseDate.getMonth() ||
            datetime.getDate() !== baseDate.getDate() ||
            datetime.getHours() !== hour ||
            datetime.getMinutes() !== minute
        ) {
            return null;
        }

        return datetime;
    }

    function getLoginFieldError(form, fieldName) {
        var email = getTrimmedFieldValue(form, 'email');
        var password = getFieldValue(form, 'password');

        if (fieldName === 'email' && !isValidEmail(email)) {
            return 'Please provide a valid email address.';
        }

        if (fieldName === 'password' && password === '') {
            return 'Password is required.';
        }

        return '';
    }

    function getSignupFieldError(form, fieldName) {
        var name = getTrimmedFieldValue(form, 'name');
        var email = getTrimmedFieldValue(form, 'email');
        var password = getFieldValue(form, 'password');
        var confirmPassword = getFieldValue(form, 'confirm_password');

        if (fieldName === 'name' && (name === '' || name.length < 2)) {
            return 'Name must be at least 2 characters.';
        }

        if (fieldName === 'email' && !isValidEmail(email)) {
            return 'Please provide a valid email address.';
        }

        if (fieldName === 'password') {
            if (password.length < 8) {
                return 'Password must be at least 8 characters.';
            }

            if (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/\d/.test(password)) {
                return 'Password must include uppercase, lowercase, and a number.';
            }
        }

        if (fieldName === 'confirm_password' && confirmPassword !== password) {
            return 'Password confirmation does not match.';
        }

        return '';
    }

    function getRegistrationFieldError(form, fieldName) {
        var fullName = getTrimmedFieldValue(form, 'full_name');
        var email = getTrimmedFieldValue(form, 'email');
        var phone = getTrimmedFieldValue(form, 'phone');
        var college = getTrimmedFieldValue(form, 'college');

        if (fieldName === 'full_name' && (fullName === '' || fullName.length < 2)) {
            return 'Full name must be at least 2 characters.';
        }

        if (fieldName === 'email' && !isValidEmail(email)) {
            return 'Please provide a valid email address.';
        }

        if (fieldName === 'phone' && !/^[0-9+\-\s]{7,20}$/.test(phone)) {
            return 'Please provide a valid phone number.';
        }

        if (fieldName === 'college' && college === '') {
            return 'College name is required.';
        }

        return '';
    }

    function getEventFieldError(form, fieldName) {
        var title = getTrimmedFieldValue(form, 'title');
        var venue = getTrimmedFieldValue(form, 'venue');
        var eventDate = getTrimmedFieldValue(form, 'event_date');
        var openAt = getTrimmedFieldValue(form, 'registration_open_at');
        var closeAt = getTrimmedFieldValue(form, 'registration_close_at');
        var openAtDate = parseDateTimeLocalValue(openAt);
        var closeAtDate = parseDateTimeLocalValue(closeAt);

        if (fieldName === 'title' && title === '') {
            return 'Event title is required.';
        }

        if (fieldName === 'venue' && venue === '') {
            return 'Venue is required.';
        }

        if (fieldName === 'event_date' && !parseDateValue(eventDate)) {
            return 'Please provide a valid event date.';
        }

        if (fieldName === 'registration_open_at' && !openAtDate) {
            return 'Please provide a valid registration open datetime.';
        }

        if (fieldName === 'registration_close_at') {
            if (closeAt !== '' && !closeAtDate) {
                return 'Please provide a valid registration close datetime.';
            }

            if (openAtDate && closeAtDate && closeAtDate <= openAtDate) {
                return 'Registration close datetime must be after open datetime.';
            }
        }

        return '';
    }

    function getFieldError(form, mode, fieldName) {
        if (mode === 'login') {
            return getLoginFieldError(form, fieldName);
        }

        if (mode === 'signup') {
            return getSignupFieldError(form, fieldName);
        }

        if (mode === 'registration') {
            return getRegistrationFieldError(form, fieldName);
        }

        if (mode === 'event') {
            return getEventFieldError(form, fieldName);
        }

        return '';
    }

    function validateField(form, mode, fieldName) {
        var message = getFieldError(form, mode, fieldName);
        setError(form, fieldName, message);
        return !message;
    }

    function validateForm(form, mode) {
        var fields = MODE_FIELDS[mode] || [];
        var valid = true;
        var i;

        for (i = 0; i < fields.length; i += 1) {
            valid = validateField(form, mode, fields[i]) && valid;
        }

        return valid;
    }

    function modeHasField(mode, fieldName) {
        var fields = MODE_FIELDS[mode] || [];
        return fields.indexOf(fieldName) !== -1;
    }

    function validateFieldWithDependencies(form, mode, fieldName) {
        validateField(form, mode, fieldName);

        if (mode === 'signup' && fieldName === 'password') {
            validateField(form, mode, 'confirm_password');
        }

        if (mode === 'event' && (fieldName === 'registration_open_at' || fieldName === 'registration_close_at')) {
            validateField(form, mode, 'registration_open_at');
            validateField(form, mode, 'registration_close_at');
        }
    }

    function initValidation(form) {
        var mode = form.getAttribute('data-validate-type');
        if (!MODE_FIELDS[mode]) {
            return;
        }

        form.addEventListener('input', function (event) {
            var target = event.target;
            if (!target || !target.name || !modeHasField(mode, target.name)) {
                return;
            }

            validateFieldWithDependencies(form, mode, target.name);
        });

        form.addEventListener('blur', function (event) {
            var target = event.target;
            if (!target || !target.name || !modeHasField(mode, target.name)) {
                return;
            }

            validateFieldWithDependencies(form, mode, target.name);
        }, true);

        form.addEventListener('submit', function (event) {
            if (!validateForm(form, mode)) {
                event.preventDefault();
            }
        });
    }

    var forms = document.querySelectorAll('form[data-validate-type]');
    var i;
    for (i = 0; i < forms.length; i += 1) {
        initValidation(forms[i]);
    }
})();
