<?php
require __DIR__ . '/../app/bootstrap.php';

mr_require_active_user(true);
$pdo = mr_db();

$ticket_id = (int)($_GET['ticket_id'] ?? 0);
if ($ticket_id <= 0) {
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => false, 'html' => '']);
exit;
}

$admin_id = (int)($_SESSION['admin_id'] ?? 0);
$isSeller = mr_is_seller();
$isAdminSide = mr_can_manage_tickets();

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$ticket_owner_col = 'user_id';
$ticket_has_owner_role = false;

try {
$st = $pdo->query("SHOW COLUMNS FROM tickets");
$cols = $st->fetchAll(PDO::FETCH_ASSOC);
$names = array_map(fn($c) => (string)$c['Field'], $cols);

if (in_array('owner_id', $names, true)) {
$ticket_owner_col = 'owner_id';
} elseif (in_array('user_id', $names, true)) {
$ticket_owner_col = 'user_id';
}

$ticket_has_owner_role = in_array('owner_role', $names, true);
} catch (Throwable $e) {}

$allowed = false;

if ($isSeller) {
if ($ticket_has_owner_role) {
$st = $pdo->prepare("SELECT id FROM tickets WHERE id=? AND {$ticket_owner_col}=? AND owner_role='admin' LIMIT 1");
$st->execute([$ticket_id, $admin_id]);
} else {
$st = $pdo->prepare("SELECT id FROM tickets WHERE id=? AND {$ticket_owner_col}=? LIMIT 1");
$st->execute([$ticket_id, $admin_id]);
}
$allowed = (bool)$st->fetch();
} elseif ($isAdminSide) {
$st = $pdo->prepare("SELECT id FROM tickets WHERE id=? LIMIT 1");
$st->execute([$ticket_id]);
$allowed = (bool)$st->fetch();
}

header('Content-Type: application/json; charset=utf-8');

if (!$allowed) {
echo json_encode(['ok' => false, 'html' => '']);
exit;
}

$m = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY id ASC");
$m->execute([$ticket_id]);
$rows = $m->fetchAll();

ob_start();
foreach ($rows as $msg) {
if ($isSeller) {
$cls = $msg['sender'] === 'user' ? 'me' : 'them';
} else {
$cls = $msg['sender'] === 'admin' ? 'them' : 'me';
}
?>
<div class="bubble <?php echo $cls; ?>">
<?php echo nl2br(e($msg['message'])); ?>
<div class="timeTxt"><?php echo e($msg['created_at']); ?></div>
</div>
<?php
}
$html = ob_get_clean();

echo json_encode([
'ok' => true,
'html' => $html
]);