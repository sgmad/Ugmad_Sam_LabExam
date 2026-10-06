<?php
require __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['user_id'])) redirect('dashboard.php');

$errors = [];
$phoneInput = '';
$emailInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneInput = trim($_POST['phone'] ?? '');
    $emailInput = trim($_POST['email'] ?? '');
    $pin = (string)($_POST['pin'] ?? '');
    $confirm = (string)($_POST['confirm_pin'] ?? '');
    $phone = normalize_phone($phoneInput);
    $email = strtolower($emailInput);

    if (!csrf_check()) $errors['form'] = 'Your session expired. Please try again.';

    if ($phoneInput === '') $errors['phone'] = 'Phone number is required.';
    elseif (!$phone) $errors['phone'] = 'Enter a valid PH mobile number, like 0917 123 4567.';

    if ($emailInput === '') $errors['email'] = 'Email is required.';
    elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address, like name@email.com.';

    if ($err = validate_new_pin($pin)) $errors['pin'] = $err;

    if ($confirm === '') $errors['confirm_pin'] = 'Please confirm your PIN.';
    elseif (!isset($errors['pin']) && $pin !== $confirm) $errors['confirm_pin'] = 'PINs do not match.';

    if (empty($_POST['human'])) $errors['human'] = 'Please confirm you are human.';

    if (!$errors) {
        $dup = db()->prepare('SELECT phone, email FROM users WHERE phone = ? OR email = ?');
        $dup->execute([$phone, $email]);
        foreach ($dup->fetchAll() as $row) {
            if ($row['phone'] === $phone) $errors['phone'] = 'This number is already registered. Log in instead.';
            if ($row['email'] === $email) $errors['email'] = 'This email is already registered.';
        }
    }

    if (!$errors) {
        try {
            db()->prepare('INSERT INTO users (phone, email, pin_hash) VALUES (?, ?, ?)')
                ->execute([$phone, $email, password_hash($pin, PASSWORD_DEFAULT)]);
            flash('success', 'Account created. Log in to start tracking your impact.');
            redirect('login.php');
        } catch (PDOException $ex) {
            $errors['form'] = 'We could not create your account. Please try again.';
        }
    }
}

$flash = null;
$pageTitle = 'Create Account';
$bodyClass = 'auth-page';
$biometric = false;
require __DIR__ . '/includes/header.php';
?>
<main class="auth">
    <?php require __DIR__ . '/includes/brand.php'; ?>

    <div class="welcome">
        <h2>Create your Account</h2>
        <p>Set up your eco-friendly wallet in under two minutes to start sending funds and tracking your positive impact.</p>
    </div>

    <?php require __DIR__ . '/includes/alerts.php'; ?>

    <form method="post" action="register.php" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="field">
            <div class="field-head">
                <label for="phone">Phone Number</label>
                <a href="login.php">Already have an account? Login</a>
            </div>
            <div class="control"><input type="tel" id="phone" name="phone" value="<?= e($phoneInput) ?>" placeholder="09** *** ****" autocomplete="tel" aria-invalid="<?= isset($errors['phone']) ? 'true' : 'false' ?>"></div>
            <?= field_error($errors, 'phone') ?>
        </div>

        <div class="field">
            <div class="field-head"><label for="email">Email</label></div>
            <div class="control"><input type="email" id="email" name="email" class="email-input" value="<?= e($emailInput) ?>" placeholder="@email.com" autocomplete="email" aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>"></div>
            <?= field_error($errors, 'email') ?>
        </div>

        <div class="field field-gap">
            <div class="field-head"><label for="pin">Enter 6-Digit Numerical Pin</label></div>
            <div class="control">
                <input type="password" id="pin" name="pin" class="pin-input" maxlength="6" inputmode="numeric" autocomplete="new-password" aria-invalid="<?= isset($errors['pin']) ? 'true' : 'false' ?>">
                <button type="button" class="toggle" data-toggle="pin" aria-label="Show PIN"><?= icon('eye') ?></button>
            </div>
            <?= field_error($errors, 'pin') ?>
        </div>

        <div class="field">
            <div class="control">
                <input type="password" id="confirm_pin" name="confirm_pin" class="pin-input" maxlength="6" inputmode="numeric" autocomplete="new-password" placeholder="Confirm PIN" aria-label="Confirm PIN" aria-invalid="<?= isset($errors['confirm_pin']) ? 'true' : 'false' ?>">
                <button type="button" class="toggle" data-toggle="confirm_pin" aria-label="Show confirm PIN"><?= icon('eye') ?></button>
            </div>
            <?= field_error($errors, 'confirm_pin') ?>
        </div>

        <?php require __DIR__ . '/includes/keypad.php'; ?>

        <div class="actions">
            <label class="captcha">
                <input type="checkbox" name="human" value="1" <?= !empty($_POST['human']) ? 'checked' : '' ?>>
                <span>I am human</span>
                <small>hCaptcha<br>Privacy - Terms</small>
            </label>
            <button type="submit" class="btn">Continue</button>
        </div>
        <?= field_error($errors, 'human') ?>
    </form>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
