<?php
// admin/check_session.php - Protection Layer
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect if partner tries to access admin panel
if (isset($require_admin) && $require_admin && $_SESSION['rol'] !== 'admin') {
    header('Location: ../partner/panel_socio.php');
    exit;
}
?>
