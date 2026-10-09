<?php
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = mr_db();

$is_user_session = !empty($_SESSION['user_id']);
$is_admin_session = !empty($_SESSION['admin_id']);

if (!$is_user_session && !$is_admin_session) {
echo json_encode(['ok' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
exit;
}

$actor_type = 'user';
$actor_id = 0;
$actor_role = 'user';

if ($is_admin_session) {
$actor_type = 'admin';
$actor_id = (int)$_SESSION['admin_id'];
$actor_role = (string)($_SESSION['admin_role'] ?? 'seller');

if (!in_array($actor_role, ['seller', 'support', 'admin'], true)) {
echo json_encode(['ok' => false, 'error' => 'Access denied'], JSON_UNESCAPED_UNICODE);
exit;
}
} else {
$actor_type = 'user';
$actor_id = (int)$_SESSION['user_id'];
$actor_role = (string)($_SESSION['role'] ?? 'user');
}

$ticket_id = (int)($_GET['ticket_id'] ?? 0);

function support_ticket_owner_column(PDO $pdo): string {
try {
$st = $pdo->query("SHOW COLUMNS FROM tickets");
$cols = $st->fetchAll(PDO::FETCH_ASSOC);
$names = array_map(fn($c) => (string)$c['Field'], $cols);

if (in_array('owner_id', $names, true)) return 'owner_id';
if (in_array('user_id', $names, true)) return 'user_id';
} catch (Throwable $e) {
}

return 'user_id';
}

function support_ticket_has_role(PDO $pdo): bool {
try {
$st = $pdo->query("SHOW COLUMNS FROM tickets LIKE 'owner_role'");
return (bool)$st->fetch();
} catch (Throwable $e) {
return false;
}
}

$owner_col = support_ticket_owner_column($pdo);
$has_owner_role = support_ticket_has_role($pdo);

if ($ticket_id <= 0) {
echo json_encode(['ok' => false, 'error' => 'Invalid ticket id'], JSON_UNESCAPED_UNICODE);
exit;
}

if ($has_owner_role) {
$st = $pdo->prepare("SELECT * FROM tickets WHERE id=? AND {$owner_col}=? AND owner_role=? LIMIT 1");
$st->execute([$ticket_id, $actor_id, $actor_type]);
} else {
$st = $pdo->prepare("SELECT * FROM tickets WHERE id=? AND {$owner_col}=? LIMIT 1");
$st->execute([$ticket_id, $actor_id]);
}
$ticket = $st->fetch();

if (!$ticket) {
echo json_encode(['ok' => false, 'error' => 'Ticket not found'], JSON_UNESCAPED_UNICODE);
exit;
}

$st = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY id ASC");
$st->execute([$ticket_id]);
$messages = $st->fetchAll(PDO::FETCH_ASSOC);

$out = [];
foreach ($messages as $m) {
$attachment_url = '';
if (!empty($m['attachment_path'])) {
$attachment_url = mr_asset_url($m['attachment_path']);
}

$out[] = [
'id' => (int)$m['id'],
'sender' => (string)($m['sender'] ?? ''),
'sender_id' => (int)($m['sender_id'] ?? 0),
'message' => (string)($m['message'] ?? ''),
'attachment_path' => (string)($m['attachment_path'] ?? ''),
'attachment_name' => (string)($m['attachment_name'] ?? ''),
'attachment_type' => (string)($m['attachment_type'] ?? ''),
'attachment_url' => (string)$attachment_url,
'created_at' => (string)($m['created_at'] ?? ''),
];
}

echo json_encode([
'ok' => true,
'ticket_status' => (string)($ticket['status'] ?? ''),
'messages' => $out,
], JSON_UNESCAPED_UNICODE);