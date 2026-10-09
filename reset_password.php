<?php
require __DIR__ . '/../app/bootstrap.php';
mr_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
mr_csrf_check();

$uid = (int)($_POST['user_id'] ?? 0);
$newpass = trim($_POST['newpass'] ?? '');

if ($uid && $newpass !== '') {
  $hash = password_hash($newpass, PASSWORD_DEFAULT);
  mr_db()->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash,$uid]);
}

header('Location: users.php');
exit;