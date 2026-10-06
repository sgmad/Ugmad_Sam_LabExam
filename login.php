<?php
require __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['user_id'])) redirect('dashboard.php');

// "Switch Account" forgets the remembered account number.
if (isset($_GET['switch'])) {
    setcookie('gw_account', '', time() - 3600, '/');
    redirect('login.php');
}

$remembered = normalize_phone($_COOKIE['gw_account'] ?? '');
$errors = [];
$phoneInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneInput = trim($_POST['phone'] ?? '');
    $pin = (string)($_POST['pin'] ?? '');
    $phone = $remembered ?? normalize_phone($phoneInput);

    if (!csrf_check()) {
        $errors['form'] = 'Your session expired. Please try again.';
    } elseif (($_SESSION['lock_until'] ?? 0) > time()) {
        $errors['form'] = 'Too many failed attempts. Try again in ' . ($_SESSION['lock_until'] - time()) . ' seconds.';
    } else {
        if (!$remembered) {
            if ($phoneInput === '') $errors['phone'] = 'Account number is required.';
            elseif (!$phone) $errors['phone'] = 'Enter a valid PH mobile number, like 0917 123 4567.';
        }
        if ($pin === '') $errors['pin'] = 'PIN is required.';
        elseif (!preg_match('/^\d{6}$/', $pin)) $errors['pin'] = 'PIN must be exactly 6 digits.';

        if (!$errors) {
            $stmt = db()->prepare('SELECT * FROM users WHERE phone = ?');
            $stmt->execute([$phone]);
            $user = $stmt->fetch();

            if ($user && password_verify($pin, $user['pin_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                unset($_SESSION['fails'], $_SESSION['lock_until']);
                setcookie('gw_account', $user['phone'], ['expires' => time() + 60 * 60 * 24 * 30, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
                redirect('dashboard.php');
            }

            $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
            if ($_SESSION['fails'] >= 5) {
                $_SESSION['lock_until'] = time() + 30;
                $_SESSION['fails'] = 0;
            }
            $errors['form'] = 'Account number or PIN is incorrect.';
        }
    }
}

$flash = take_flash();
$pageTitle = 'Log In';
$bodyClass = 'login-page';
$biometric = true;
require __DIR__ . '/includes/header.php';
?>
<main class="login">
    <aside class="info-card" aria-label="About GreenWallet">
        <div class="info-top">
            <figure>
                <img src="assets/img/GreenWallet_lightLogo.png" alt="" width="100" height="100">
                <figcaption>GreenWallet</figcaption>
            </figure>
            <div>
                <p>Manage your daily transactions with an e-wallet built around environmental impact.</p>
                <p>Every transfer, bill payment, and merchant checkout helps fund verified conservation programs worldwide.</p>
            </div>
        </div>
        <hr>
        <div class="feature"><h3>Everyday Efficiency</h3><p>Send and receive funds instantly with zero transfer fees between GreenWallet users.</p></div>
        <div class="feature"><h3>Automated Micro-Contributions</h3><p>Round up spare change on card purchases to fund verified reforestation and carbon-reduction projects.</p></div>
        <div class="feature"><h3>Transparent Footprint Tracking</h3><p>View real-time estimates of the carbon footprint associated with your purchasing habits, paired with practical suggestions to reduce it.</p></div>
        <hr>
        <div class="stat"><span class="tile"><?= icon('dollar') ?></span><p><b>$4.2M</b>Generated for certified conservation programs through automated round-ups.</p></div>
        <div class="stat"><span class="tile"><?= icon('tree') ?></span><p><b>1.8M</b>Trees planted across monitored restoration zones since launch.</p></div>
        <div class="stat"><span class="tile"><?= icon('zero') ?></span><p><b>Zero</b>Paper waste, physical plastic cards, or unnecessary administrative overhead.</p></div>
        <hr>
        <p class="fine">Protected by AES-256 bank-grade encryption and biometric verification.</p>
    </aside>

    <section class="panel">
        <?php require __DIR__ . '/includes/brand.php'; ?>

        <div class="welcome">
            <h2>Welcome to GreenWallet</h2>
            <p>Log in to manage your balance, track your environmental contributions, and access your virtual cards.</p>
        </div>

        <?php require __DIR__ . '/includes/alerts.php'; ?>

        <form method="post" action="login.php" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div class="field">
                <div class="field-head">
                    <label for="phone">Account Number</label>
                    <?php if ($remembered): ?>
                        <a href="login.php?switch=1">Switch Account</a>
                    <?php else: ?>
                        <a href="register.php" class="mobile-only">Create an Account</a>
                    <?php endif; ?>
                </div>
                <div class="control">
                    <?php if ($remembered): ?>
                        <input type="text" id="phone" value="<?= e(mask_phone($remembered)) ?>" readonly>
                    <?php else: ?>
                        <input type="tel" id="phone" name="phone" value="<?= e($phoneInput) ?>" placeholder="09** *** ****" autocomplete="tel" aria-invalid="<?= isset($errors['phone']) ? 'true' : 'false' ?>">
                    <?php endif; ?>
                </div>
                <?= field_error($errors, 'phone') ?>
            </div>

            <div class="field">
                <div class="field-head">
                    <label for="pin">Enter PIN</label>
                    <a href="#" data-soon="PIN reset isn't available in this demo.">Forgot PIN? Reset</a>
                </div>
                <div class="control">
                    <input type="password" id="pin" name="pin" class="pin-input" maxlength="6" inputmode="numeric" autocomplete="current-password" placeholder="6-digit PIN" aria-invalid="<?= isset($errors['pin']) ? 'true' : 'false' ?>">
                    <button type="button" class="toggle" data-toggle="pin" aria-label="Show PIN"><?= icon('eye') ?></button>
                </div>
                <?= field_error($errors, 'pin') ?>
            </div>

            <?php require __DIR__ . '/includes/keypad.php'; ?>

            <div class="actions">
                <button type="button" class="link-btn scan" data-soon="QR login isn't available in this demo."><?= icon('scan') ?> Scan QR to Login</button>
                <button type="submit" class="btn">Log In</button>
            </div>
        </form>

        <p class="switch-note">New to GreenWallet? <a href="register.php">Create an account</a> in under two minutes.</p>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
