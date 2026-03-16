<?php
// admin/logout.php - Logout with redirect to login
session_start();
session_destroy();
header('Location: login.php');
exit;
?>
