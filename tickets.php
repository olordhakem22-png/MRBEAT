<?php
require __DIR__ . '/../app/bootstrap.php';
mr_require_admin(['admin','support']);

$pdo = mr_db();
$is_ar = (mr_lang() === 'ar');
$ticket_id = (int)($_GET['ticket_id'] ?? 0);

function ticket_owner_column_admin(PDO $pdo): string {
static $ownerColumn = null;
if ($ownerColumn !== null) return $ownerColumn;

try {
$st = $pdo->query("SHOW COLUMNS FROM tickets");
$cols = $st->fetchAll(PDO::FETCH_ASSOC);
$names = array_map(fn($c) => (string)$c['Field'], $cols);

if (in_array('owner_id', $names, true)) return $ownerColumn = 'owner_id';
if (in_array('user_id', $names, true)) return $ownerColumn = 'user_id';
} catch (Throwable $e) {
}

return $ownerColumn = 'user_id';
}

function ticket_has_owner_role_admin(PDO $pdo): bool {
static $hasRole = null;
if ($hasRole !== null) return $hasRole;

try {
$st = $pdo->query("SHOW COLUMNS FROM tickets LIKE 'owner_role'");
$hasRole = (bool)$st->fetch();
} catch (Throwable $e) {
$hasRole = false;
}

return $hasRole;
}

function upload_admin_ticket_attachment(?array $file): ?array {
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

$dir = __DIR__ . '/../uploads/tickets';
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

$owner_col = ticket_owner_column_admin($pdo);
$has_owner_role = ticket_has_owner_role_admin($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
mr_csrf_check();

$action = trim((string)($_POST['action'] ?? ''));
$tid = (int)($_POST['ticket_id'] ?? 0);

if ($action === 'reply' && $tid) {
$message = trim((string)($_POST['message'] ?? ''));
$fileData = upload_admin_ticket_attachment($_FILES['attachment'] ?? null);

if ($message !== '' || $fileData) {
$pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender, sender_id, message, attachment_path, attachment_name, attachment_type) VALUES (?,?,?,?,?,?,?)")
->execute([
$tid,
'admin',
(int)($_SESSION['admin_id'] ?? 0),
$message,
$fileData['path'] ?? null,
$fileData['name'] ?? null,
$fileData['type'] ?? null,
]);

$pdo->prepare("UPDATE tickets SET status='pending' WHERE id=?")->execute([$tid]);
}

header('Location: tickets.php?ticket_id=' . $tid);
exit;
}

if ($action === 'close' && $tid) {
$pdo->prepare("UPDATE tickets SET status='closed', closed_at=NOW() WHERE id=?")->execute([$tid]);
header('Location: tickets.php?ticket_id=' . $tid);
exit;
}

if ($action === 'open' && $tid) {
$pdo->prepare("UPDATE tickets SET status='open', closed_at=NULL WHERE id=?")->execute([$tid]);
header('Location: tickets.php?ticket_id=' . $tid);
exit;
}
}

if ($has_owner_role) {
$tickets = $pdo->query("
SELECT t.*,
COALESCE(u.username, a.username, 'Unknown') AS username
FROM tickets t
LEFT JOIN users u ON (t.{$owner_col} = u.id AND t.owner_role = 'user')
LEFT JOIN admins a ON (t.{$owner_col} = a.id AND t.owner_role = 'admin')
ORDER BY t.id DESC
LIMIT 50
")->fetchAll();
} else {
$tickets = $pdo->query("
SELECT t.*, u.username
FROM tickets t
LEFT JOIN users u ON u.id=t.{$owner_col}
ORDER BY t.id DESC
LIMIT 50
")->fetchAll();
}

$active = null;
$messages = [];

if ($ticket_id) {
if ($has_owner_role) {
$t = $pdo->prepare("
SELECT t.*,
COALESCE(u.username, a.username, 'Unknown') AS username
FROM tickets t
LEFT JOIN users u ON (t.{$owner_col} = u.id AND t.owner_role = 'user')
LEFT JOIN admins a ON (t.{$owner_col} = a.id AND t.owner_role = 'admin')
WHERE t.id=?
LIMIT 1
");
$t->execute([$ticket_id]);
} else {
$t = $pdo->prepare("SELECT t.*, u.username FROM tickets t LEFT JOIN users u ON u.id=t.{$owner_col} WHERE t.id=? LIMIT 1");
$t->execute([$ticket_id]);
}

$active = $t->fetch();

if ($active) {
$m = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY id ASC");
$m->execute([$ticket_id]);
$messages = $m->fetchAll();
}
}
?>
<!doctype html>
<html lang="<?php echo $is_ar ? 'ar' : 'en'; ?>" dir="<?php echo $is_ar ? 'rtl' : 'ltr'; ?>">
<head>
<meta charset="utf-8">
<?php echo mr_head_tags(); ?>
<title><?php echo $is_ar ? 'التذاكر' : 'Tickets'; ?></title>
<style>
body{font-family:Arial;background:#0f141b;color:#fff;padding:20px;margin:0}
a{color:#4da3ff;text-decoration:none}
.wrap{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:320px 1fr;gap:14px}
.box{background:#161d26;border:1px solid #222;border-radius:14px;padding:12px}
.item{padding:10px;border-radius:10px;border:1px solid #222;background:#0b0f14;margin-bottom:8px}
.msg{
margin-top:10px;
height:500px;
overflow:auto;
padding-right:4px;
}
.bubble{
max-width:75%;
padding:10px;
border-radius:12px;
margin:8px 0;
line-height:1.5;
white-space:pre-wrap;
word-break:break-word;
overflow-wrap:anywhere;
}
.me{background:#222}
.them{background:#2f6bff;margin-left:auto}
textarea,input[type="file"]{
width:100%;
padding:10px;
border-radius:10px;
border:1px solid #222;
background:#0b0f14;
color:#fff;
margin-top:8px;
box-sizing:border-box;
}
button{padding:10px 12px;border-radius:10px;border:0;background:#2f6bff;color:#fff;cursor:pointer;margin-top:8px}
.small{font-size:12px;opacity:.8}
.attPreview{display:block;max-width:220px;border-radius:10px;margin-top:8px;border:1px solid #333}
.fileLink{display:inline-block;margin-top:8px;padding:8px 10px;border-radius:10px;background:#111827;border:1px solid #333;color:#fff}
.topbar{max-width:1100px;margin:0 auto 14px auto}
@media (max-width: 900px){
.wrap{grid-template-columns:1fr}
.msg{height:420px}
.bubble{max-width:88%}
}
</style>
</head>
<body>

<div class="topbar">
<h1 style="margin:0 0 12px 0"><?php echo $is_ar ? 'تذاكر الدعم' : 'Support Tickets'; ?></h1>
<div style="margin-bottom:12px">
<a href="dashboard.php">← <?php echo $is_ar ? 'العودة للوحة' : 'Dashboard'; ?></a>
</div>
</div>

<div class="wrap">

<div class="box">
<div style="margin-bottom:10px;font-weight:bold"><?php echo $is_ar ? 'الوارد' : 'Inbox'; ?></div>
<?php foreach ($tickets as $t): ?>
<div class="item">
<a href="tickets.php?ticket_id=<?php echo (int)$t['id']; ?>">
#<?php echo (int)$t['id']; ?> - <?php echo htmlspecialchars($t['username'] ?? 'Unknown'); ?>
<div class="small"><?php echo htmlspecialchars($t['subject']); ?></div>
<div class="small"><?php echo $is_ar ? 'الحالة' : 'Status'; ?>: <?php echo htmlspecialchars($t['status']); ?></div>
<?php if (!empty($t['owner_role'])): ?>
<div class="small"><?php echo $is_ar ? 'النوع' : 'Type'; ?>: <?php echo htmlspecialchars($t['owner_role']); ?></div>
<?php endif; ?>
</a>
</div>
<?php endforeach; ?>
</div>

<div class="box">
<?php if (!$active): ?>
<div class="small"><?php echo $is_ar ? 'اختر تذكرة لعرض المحادثة.' : 'Select a ticket to view chat.'; ?></div>
<?php else: ?>
<div style="font-weight:bold">
<?php echo $is_ar ? 'تذكرة' : 'Ticket'; ?> #<?php echo (int)$active['id']; ?> - <?php echo htmlspecialchars($active['username'] ?? 'Unknown'); ?>
</div>
<div class="small">
<?php echo htmlspecialchars($active['subject']); ?> | <?php echo $is_ar ? 'الحالة' : 'Status'; ?>: <?php echo htmlspecialchars($active['status']); ?>
</div>

<div class="msg" id="msgBox">
<?php foreach ($messages as $m): ?>
<div class="bubble <?php echo $m['sender']==='admin' ? 'them':'me'; ?>">
<?php if (!empty($m['message'])): ?>
<div><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>
<?php endif; ?>

<?php if (!empty($m['attachment_path'])): ?>
<?php
$attUrl = mr_asset_url($m['attachment_path']);
$attType = strtolower((string)($m['attachment_type'] ?? ''));
$attName = (string)($m['attachment_name'] ?? 'Attachment');
$isImage = strpos($attType, 'image/') === 0;
?>
<?php if ($isImage): ?>
<a href="<?php echo htmlspecialchars($attUrl); ?>" target="_blank">
<img class="attPreview" src="<?php echo htmlspecialchars($attUrl); ?>" alt="">
</a>
<?php else: ?>
<a class="fileLink" href="<?php echo htmlspecialchars($attUrl); ?>" target="_blank"><?php echo htmlspecialchars($attName); ?></a>
<?php endif; ?>
<?php endif; ?>

<div class="small" style="margin-top:6px"><?php echo htmlspecialchars($m['created_at']); ?></div>
</div>
<?php endforeach; ?>
</div>

<?php if (($active['status'] ?? '') !== 'closed'): ?>
<form method="post" id="adminReplyForm" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="reply">
<input type="hidden" name="ticket_id" value="<?php echo (int)$active['id']; ?>">
<textarea name="message" rows="3" placeholder="<?php echo $is_ar ? 'اكتب الرد...' : 'Type reply...'; ?>"></textarea>
<input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.zip,.rar,.txt,.doc,.docx,.xls,.xlsx">
<button type="submit"><?php echo $is_ar ? 'إرسال' : 'Send'; ?></button>
</form>
<?php else: ?>
<div class="small" style="margin-top:12px;color:#fca5a5"><?php echo $is_ar ? 'التذكرة مغلقة.' : 'This ticket is closed.'; ?></div>
<?php endif; ?>

<form method="post" style="display:flex;gap:8px;flex-wrap:wrap">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="ticket_id" value="<?php echo (int)$active['id']; ?>">
<?php if (($active['status'] ?? '') !== 'closed'): ?>
<button name="action" value="close" style="background:#444"><?php echo $is_ar ? 'إغلاق' : 'Close'; ?></button>
<?php else: ?>
<button name="action" value="open" style="background:#444"><?php echo $is_ar ? 'إعادة فتح' : 'Reopen'; ?></button>
<?php endif; ?>
</form>
<?php endif; ?>
</div>

</div>

<script>
function escapeHtml(s){
return (s || '').replace(/[&<>"']/g, function(m){
return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
});
}
function nl2brJs(s){
return escapeHtml(s).replace(/\n/g,'<br>');
}
function isImageType(type, name){
type = (type || '').toLowerCase();
name = (name || '').toLowerCase();
if (type.startsWith('image/')) return true;
return /\.(jpg|jpeg|png|webp|gif)$/.test(name);
}

const msgBox = document.getElementById('msgBox');
const replyForm = document.getElementById('adminReplyForm');
const currentTicketId = <?php echo $active ? (int)$active['id'] : 0; ?>;
const csrfToken = <?php echo json_encode((string)mr_csrf_token()); ?>;
const isArabic = <?php echo $is_ar ? 'true' : 'false'; ?>;

function renderMessages(messages){
if (!msgBox) return;

let html = '';
messages.forEach(function(m){
const cls = (m.sender === 'admin') ? 'them' : 'me';
html += '<div class="bubble ' + cls + '">';

if (m.message) {
html += '<div>' + nl2brJs(m.message) + '</div>';
}

if (m.attachment_url) {
if (isImageType(m.attachment_type, m.attachment_name)) {
html += '<a href="' + encodeURI(m.attachment_url) + '" target="_blank">';
html += '<img class="attPreview" src="' + encodeURI(m.attachment_url) + '" alt="">';
html += '</a>';
} else {
html += '<a class="fileLink" href="' + encodeURI(m.attachment_url) + '" target="_blank">' + escapeHtml(m.attachment_name || 'Attachment') + '</a>';
}
}

html += '<div class="small" style="margin-top:6px">' + escapeHtml(m.created_at || '') + '</div>';
html += '</div>';
});

msgBox.innerHTML = html || '<div class="small">' + (isArabic ? 'لا توجد رسائل بعد.' : 'No messages yet.') + '</div>';
msgBox.scrollTop = msgBox.scrollHeight;
}

async function pollMessages(){
if (!currentTicketId || !msgBox) return;

try {
const res = await fetch('ticket_poll.php?ticket_id=' + currentTicketId, {
headers: { 'X-Requested-With': 'XMLHttpRequest' }
});
const data = await res.json();
if (data.ok) {
renderMessages(data.messages || []);
}
} catch (e) {}
}

if (replyForm && currentTicketId) {
replyForm.addEventListener('submit', async function(ev){
ev.preventDefault();

const fd = new FormData(replyForm);
fd.append('_csrf', csrfToken);
fd.append('ticket_id', currentTicketId);

try {
const res = await fetch('ticket_send.php', {
method: 'POST',
body: fd
});
const data = await res.json();
if (data.ok) {
replyForm.reset();
pollMessages();
}
} catch (e) {}
});
}

if (msgBox) msgBox.scrollTop = msgBox.scrollHeight;
if (currentTicketId && msgBox) {
pollMessages();
setInterval(pollMessages, 2500);
}
</script>

</body>
</html>