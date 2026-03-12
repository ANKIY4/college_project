<?php

function csrf_token()
{
    if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function require_valid_csrf($fallbackPath = 'index.php')
{
    $submittedToken = isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : '';

    if ($submittedToken === '' || !hash_equals(csrf_token(), $submittedToken)) {
        flash('error', 'Invalid or expired request token. Please submit the form again.');
        redirect($fallbackPath);
    }
}
