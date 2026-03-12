<?php

function current_user()
{
    static $cachedUser = null;
    static $cachedUserId = null;

    $sessionUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
    if ($sessionUserId <= 0) {
        return null;
    }

    if (is_array($cachedUser) && $cachedUserId === $sessionUserId) {
        return $cachedUser;
    }

    $statement = db()->prepare('SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $sessionUserId]);
    $user = $statement->fetch();

    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }

    $cachedUser = $user;
    $cachedUserId = $sessionUserId;

    return $cachedUser;
}

function is_logged_in()
{
    return current_user() !== null;
}

function login_user($user)
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
}

function attempt_login($email, $password)
{
    $statement = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return null;
    }

    unset($user['password_hash']);

    return $user;
}

function logout_user()
{
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
}

function require_login()
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_admin()
{
    require_login();

    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        flash('error', 'Admin access is required.');
        redirect('index.php');
    }
}
