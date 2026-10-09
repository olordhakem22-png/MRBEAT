<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

if (!empty($_SESSION['admin_id'])) {
header('Location: dashboard.php');
exit;
}

header('Location: login.php');
exit;