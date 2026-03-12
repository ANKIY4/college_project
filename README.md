# Event Management Website (PHP + MySQL)

A complete event management website with separate admin and user capabilities.

## Core features
- User signup/login with real-time + server-side validation.
- Admin-only event management (create/edit event dates and registration windows).
- Automatic registration open/close behavior based on date/time (no manual toggle needed).
- Current-month event listing on the public homepage.
- Logged-in users can submit event registration forms.
- Admin can download registrations as CSV (per event or all events).
- Admin dashboard includes a live registrations panel that auto-refreshes every 5 seconds.
- Validation coverage across login, signup, event registration, and admin event forms.
- Timezone policy: input/display in `Asia/Kathmandu`, stored and compared in UTC.

## Setup (XAMPP + phpMyAdmin)
1. Open phpMyAdmin.
2. Import `sql/schema.sql`.
3. Import `sql/seed_admin.sql`.
4. Ensure MySQL credentials in `app/config.php` match your local setup.
5. Open the project in browser:
   - `http://localhost/event_management_collegeProject/index.php`

## Default admin credentials
- Email: `admin@example.com`
- Password: `Admin@123`

## Project structure
- `app/` shared config, auth, DB, validation, CSRF, event utilities
- `admin/` admin dashboard, event form, CSV export
- `templates/` shared header/footer
- `public/` CSS and JS assets
- `sql/` database schema and seed scripts
