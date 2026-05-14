<?php
// Initialize the session
session_start();

// Clear the bucket
unset($_SESSION['bucket']);

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Clear any sensitive data from memory
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['is_admin']);

// Redirect to homepage
header("Location: home.php");
exit();
?>