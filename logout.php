<?php
require __DIR__ . '/includes/bootstrap.php';
$_SESSION = [];
session_destroy();
session_start();
flash('success', 'You have been logged out.');
redirect('login.php');
