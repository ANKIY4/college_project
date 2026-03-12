<?php
require_once __DIR__ . '/app/bootstrap.php';

if (!is_post_request()) {
    redirect('index.php');
}

require_valid_csrf('index.php');

logout_user();
flash('success', 'You have logged out successfully.');
redirect('login.php');
