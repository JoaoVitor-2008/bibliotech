<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: modules/dashboard/index.php');
    exit;
}

header('Location: login.php');
exit;
