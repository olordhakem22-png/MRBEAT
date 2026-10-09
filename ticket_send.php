<?php
require __DIR__ . '/app/bootstrap.php';

mr_csrf_check();
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
$sender_type = 'user';

if ($is_admin_session) {
$actor_type = 'admin';
$actor_id = (int)$_SESSION['admin_id'];
$actor_role = (string)($_SESSION['admin_role'] ?? 'seller');
$sender_type = 'admin';

if (!in_array($actor_role, ['seller', 'support', 'admin'], true)) {
echo json_encode(['ok' => false, 'error' => 'Access denied'], JSON_UNESCAPED_UNICODE);
exit;
}
} else {
$actor_type = 'user';
$actor_id = (int)$_SESSION['user_id'];
$actor_role = (string)($_SESSION['role'] ?? 'user');
$sender_type = 'user';
}

$action = trim((string)($_POST['action'] ?? 'reply'));
$ticket_id = (int)($_POST['ticket_id'] ?? 0);
$message = trim((string)($_POST['message'] ?? ''));

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

function upload_ticket_attachment(?array $file): ?array {
if (!$file || empty($file['name']) || empty($file['tmp_name'])) return null;
if (!is_uploaded_file($file['tmp_name'])) return null;

$max = 10 * 1024 * 1024;
if (($file['size'] ?? 0) > $max) return null;

$allowed = [
'jpg','jpeg','png','webp','gif',
'pdf','zip','rar','txt','doc','docx','xls','xlsx'
];

$ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) return null;

$dir = __DIR__ . '/uploads/tickets';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$filename = 'ticket_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
$dest = $dir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) return null;

$mime = mime_content_type($dest);
return [
'path' => 'uploads/tickets/' . $filename,
'name' => (string)$file['name'],
'type' => $mime ?: $ext,
];
}

$owner_col = support_ticket_owner_column($pdo);
$has_owner_role = support_ticket_has_role($pdo);

if ($action !== 'reply') {
echo json_encode(['ok' => false, 'error' => 'Invalid action'], JSON_UNESCAPED_UNICODE);
exit;
}

if ($ticket_id <= 0) {
echo json_encode(['ok' => false, 'error' => 'Invalid ticket'], JSON_UNESCAPED_UNICODE);
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

if (($ticket['status'] ?? '') === 'closed') {
echo json_encode(['ok' => false, 'error' => 'Ticket is closed'], JSON_UNESCAPED_UNICODE);
exit;
}

$fileData = upload_ticket_attachment($_FILES['attachment'] ?? null);

if ($message === '' && !$fileData) {
echo json_encode(['ok' => false, 'error' => 'Empty message'], JSON_UNESCAPED_UNICODE);
exit;
}

try {
$pdo->beginTransaction();

$pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender, sender_id, message, attachment_path, attachment_name, attachment_type) VALUES (?,?,?,?,?,?,?)")
->execute([
$ticket_id,
$sender_type,
$actor_id,
$message,
$fileData['path'] ?? null,
$fileData['name'] ?? null,
$fileData['type'] ?? null
]);

$message_id = (int)$pdo->lastInsertId();

$pdo->prepare("UPDATE tickets SET status='open' WHERE id=?")->execute([$ticket_id]);

$pdo->commit();

echo json_encode([
'ok' => true,
'message' => [
'id' => $message_id,
'ticket_id' => $ticket_id,
'sender' => $sender_type,
'sender_id' => $actor_id,
'message' => $message,
'attachment_path' => $fileData['path'] ?? null,
'attachment_name' => $fileData['name'] ?? null,
'attachment_type' => $fileData['type'] ?? null,
'attachment_url' => !empty($fileData['path']) ? mr_asset_url($fileData['path']) : '',
'created_at' => date('Y-m-d H:i:s'),
]
], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
if ($pdo->inTransaction()) $pdo->rollBack();

echo json_encode([
'ok' => false,
'error' => 'Send failed',
'debug' => $e->getMessage()
], JSON_UNESCAPED_UNICODE);
}