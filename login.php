<?php
require_once __DIR__ . '/app/bootstrap.php';

if (is_logged_in()) {
    $user = current_user();
    if ($user && $user['role'] === 'admin') {
        redirect('admin/dashboard.php');
    }

    redirect('index.php');
}

$formData = [
    'email' => '',
    'password' => '',
];
$errors = [];

if (is_post_request()) {
    require_valid_csrf('login.php');

    $formData['email'] = clean_input(isset($_POST['email']) ? $_POST['email'] : '');
    $formData['password'] = isset($_POST['password']) ? (string) $_POST['password'] : '';

    $errors = validate_login($formData);

    if (empty($errors)) {
        $user = attempt_login($formData['email'], $formData['password']);

        if (!$user) {
            $errors['password'] = 'Invalid email or password.';
        } else {
            login_user($user);
            flash('success', 'Welcome back, ' . $user['name'] . '!');

            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            }

            redirect('index.php');
        }
    }
}

$pageTitle = 'Login';
$extraScripts = ['public/js/form-validation.js'];
require __DIR__ . '/templates/header.php';
?>

<section class="card form-card">
    <h1>Log In</h1>
    <p class="meta">Log in to register for events or manage events as admin.</p>

    <form method="POST" action="<?php echo e(base_url('login.php')); ?>" data-validate-type="login" novalidate>
        <?php echo csrf_field(); ?>

        <div class="form-row">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?php echo e($formData['email']); ?>" class="<?php echo isset($errors['email']) ? 'input-error' : ''; ?>" required>
            <small class="field-error" data-error-for="email"><?php echo isset($errors['email']) ? e($errors['email']) : ''; ?></small>
        </div>

        <div class="form-row">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" class="<?php echo isset($errors['password']) ? 'input-error' : ''; ?>" required>
            <small class="field-error" data-error-for="password"><?php echo isset($errors['password']) ? e($errors['password']) : ''; ?></small>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">Log In</button>
            <a class="btn btn-muted" href="<?php echo e(base_url('signup.php')); ?>">Create account</a>
        </div>
    </form>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>
