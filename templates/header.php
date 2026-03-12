<?php
$pageTitle = isset($pageTitle) ? $pageTitle : config('app.name', 'Event Management Portal');
$currentUser = current_user();
$flashMessages = pull_flashes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo e(base_url('public/css/styles.css')); ?>">
</head>
<body>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="<?php echo e(base_url('index.php')); ?>"><?php echo e(config('app.name', 'Event Management Portal')); ?></a>
            <nav class="site-nav">
                <a href="<?php echo e(base_url('index.php')); ?>">Events</a>
                <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                    <a href="<?php echo e(base_url('admin/dashboard.php')); ?>">Admin Dashboard</a>
                <?php endif; ?>
            </nav>
            <div class="auth-links">
                <?php if ($currentUser): ?>
                    <span class="welcome">Hi, <?php echo e($currentUser['name']); ?></span>
                    <form method="POST" action="<?php echo e(base_url('logout.php')); ?>" class="inline-form">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-outline">Logout</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-outline" href="<?php echo e(base_url('login.php')); ?>">Login</a>
                    <a class="btn btn-primary" href="<?php echo e(base_url('signup.php')); ?>">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="site-main">
        <div class="container">
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
            <?php endforeach; ?>
