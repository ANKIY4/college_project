<?php
require_once __DIR__ . '/app/bootstrap.php';

if (is_logged_in()) {
    redirect('index.php');
}

$formData = [
    'name' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => '',
];
$errors = [];

if (is_post_request()) {
    require_valid_csrf('signup.php');

    $formData['name'] = clean_input(isset($_POST['name']) ? $_POST['name'] : '');
    $formData['email'] = clean_input(isset($_POST['email']) ? $_POST['email'] : '');
    $formData['password'] = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $formData['confirm_password'] = isset($_POST['confirm_password']) ? (string) $_POST['confirm_password'] : '';

    $errors = validate_signup($formData);

    if (empty($errors)) {
        $emailCheck = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $emailCheck->execute(['email' => $formData['email']]);

        if ($emailCheck->fetch()) {
            $errors['email'] = 'This email is already registered.';
        }
    }

    if (empty($errors)) {
        $insert = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
        $insert->execute([
            'name' => $formData['name'],
            'email' => $formData['email'],
            'password_hash' => password_hash($formData['password'], PASSWORD_DEFAULT),
            'role' => 'user',
        ]);

        flash('success', 'Account created successfully. Please log in.');
        redirect('login.php');
    }
}

$pageTitle = 'Sign Up';
$extraScripts = ['public/js/form-validation.js'];
require __DIR__ . '/templates/header.php';
?>

<section class="card form-card">
    <h1>Create Account</h1>
    <p class="meta">User registration is required before event form submission.</p>

    <form method="POST" action="<?php echo e(base_url('signup.php')); ?>" data-validate-type="signup" novalidate>
        <?php echo csrf_field(); ?>

        <div class="form-row">
            <label for="name">Full Name</label>
            <input id="name" name="name" type="text" value="<?php echo e($formData['name']); ?>" class="<?php echo isset($errors['name']) ? 'input-error' : ''; ?>" required>
            <small class="field-error" data-error-for="name"><?php echo isset($errors['name']) ? e($errors['name']) : ''; ?></small>
        </div>

        <div class="form-row">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?php echo e($formData['email']); ?>" class="<?php echo isset($errors['email']) ? 'input-error' : ''; ?>" required>
            <small class="field-error" data-error-for="email"><?php echo isset($errors['email']) ? e($errors['email']) : ''; ?></small>
        </div>

        <div class="form-row">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" class="<?php echo isset($errors['password']) ? 'input-error' : ''; ?>" required>
            <small class="field-error" data-error-for="password"><?php echo isset($errors['password']) ? e($errors['password']) : ''; ?></small>
            <small class="help-text">Use at least 8 characters with uppercase, lowercase, and a number.</small>
        </div>

        <div class="form-row">
            <label for="confirm_password">Confirm Password</label>
            <input id="confirm_password" name="confirm_password" type="password" class="<?php echo isset($errors['confirm_password']) ? 'input-error' : ''; ?>" required>
            <small class="field-error" data-error-for="confirm_password"><?php echo isset($errors['confirm_password']) ? e($errors['confirm_password']) : ''; ?></small>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">Create Account</button>
            <a class="btn btn-muted" href="<?php echo e(base_url('login.php')); ?>">Already have an account?</a>
        </div>
    </form>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>
