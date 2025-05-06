<?php
session_start(); // Always call session_start() first in auth_check.php
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
