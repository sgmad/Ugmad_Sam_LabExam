<?php
require __DIR__ . '/includes/bootstrap.php';

if (empty($_SESSION['user_id'])) {
    flash('error', 'Please log in to continue.');
    redirect('login.php');
}

$stmt = db()->prepare('SELECT phone, email, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) redirect('logout.php');

$pageTitle = 'Dashboard';
$bodyClass = 'auth-page';
require __DIR__ . '/includes/header.php';
?>
<main class="auth">
    <section class="dash">
        <img src="assets/img/GreenWallet_lightLogo.png" alt="" width="90" height="90">
        <h2>Welcome back</h2>
        <p>You're logged in to your GreenWallet account.</p>
        <dl>
            <div><dt>Account number</dt><dd><?= e(mask_phone($user['phone'])) ?></dd></div>
            <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
            <div><dt>Member since</dt><dd><?= e(date('F j, Y', strtotime($user['created_at'] . ' UTC'))) ?></dd></div>
        </dl>
        <a class="btn btn-light" href="logout.php">Log Out</a>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
