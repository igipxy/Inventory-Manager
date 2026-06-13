<?php
session_start();

// Clear all session variables
$_SESSION = [];
session_unset();

// Destroy the session completely
session_destroy();

// Redirect to login
header('Location: login.php');
exit;

