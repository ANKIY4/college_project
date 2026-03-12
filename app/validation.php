<?php

function clean_input($value)
{
    return trim((string) $value);
}

function validate_signup($input)
{
    $errors = [];

    $name = clean_input(isset($input['name']) ? $input['name'] : '');
    $email = clean_input(isset($input['email']) ? $input['email'] : '');
    $password = isset($input['password']) ? (string) $input['password'] : '';
    $confirmPassword = isset($input['confirm_password']) ? (string) $input['confirm_password'] : '';

    if ($name === '' || strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please provide a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Password must include uppercase, lowercase, and a number.';
    }

    if ($confirmPassword !== $password) {
        $errors['confirm_password'] = 'Password confirmation does not match.';
    }

    return $errors;
}

function validate_login($input)
{
    $errors = [];
    $email = clean_input(isset($input['email']) ? $input['email'] : '');
    $password = isset($input['password']) ? (string) $input['password'] : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please provide a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    return $errors;
}

function validate_event($input)
{
    $errors = [];

    $title = clean_input(isset($input['title']) ? $input['title'] : '');
    $venue = clean_input(isset($input['venue']) ? $input['venue'] : '');
    $eventDate = clean_input(isset($input['event_date']) ? $input['event_date'] : '');
    $openAt = clean_input(isset($input['registration_open_at']) ? $input['registration_open_at'] : '');
    $closeAt = clean_input(isset($input['registration_close_at']) ? $input['registration_close_at'] : '');

    if ($title === '') {
        $errors['title'] = 'Event title is required.';
    }

    if ($venue === '') {
        $errors['venue'] = 'Venue is required.';
    }

    $eventDateObject = DateTimeImmutable::createFromFormat('Y-m-d', $eventDate);
    if (!$eventDateObject || $eventDateObject->format('Y-m-d') !== $eventDate) {
        $errors['event_date'] = 'Please provide a valid event date.';
    }

    $openAtObject = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $openAt);
    if (!$openAtObject || $openAtObject->format('Y-m-d\TH:i') !== $openAt) {
        $errors['registration_open_at'] = 'Please provide a valid registration open datetime.';
    }

    if ($closeAt !== '') {
        $closeAtObject = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $closeAt);
        if (!$closeAtObject || $closeAtObject->format('Y-m-d\TH:i') !== $closeAt) {
            $errors['registration_close_at'] = 'Please provide a valid registration close datetime.';
        } elseif ($openAtObject && $closeAtObject <= $openAtObject) {
            $errors['registration_close_at'] = 'Registration close datetime must be after open datetime.';
        }
    }

    return $errors;
}

function validate_registration($input)
{
    $errors = [];

    $fullName = clean_input(isset($input['full_name']) ? $input['full_name'] : '');
    $email = clean_input(isset($input['email']) ? $input['email'] : '');
    $phone = clean_input(isset($input['phone']) ? $input['phone'] : '');
    $college = clean_input(isset($input['college']) ? $input['college'] : '');

    if ($fullName === '' || strlen($fullName) < 2) {
        $errors['full_name'] = 'Full name must be at least 2 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please provide a valid email address.';
    }

    if (!preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
        $errors['phone'] = 'Please provide a valid phone number.';
    }

    if ($college === '') {
        $errors['college'] = 'College name is required.';
    }

    return $errors;
}
