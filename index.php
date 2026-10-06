<?php
require __DIR__ . '/includes/bootstrap.php';
redirect(empty($_SESSION['user_id']) ? 'login.php' : 'dashboard.php');
