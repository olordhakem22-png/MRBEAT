<?php
require __DIR__ . '/app/bootstrap.php';

$pdo = mr_db();
$is_ar = (mr_lang() === 'ar');

$is_user_session = !empty($_SESSION['user_id']);
$is_admin_session = !empty($_SESSION['admin_id']);

if (!$is_user_session && !$is_admin_session) {
header('Location: ' . mr_config('app.base_url') . '/login.php');
exit;
}

$actor_type = 'user';
$actor_id = 0;
$actor_role = 'user';
$account_status = 'active';
$back_url = 'account.php';

if ($is_admin_session) {
$actor_type = 'admin';
$actor_id = (int)$_SESSION['admin_id'];
$actor_role = (string)($_SESSION['admin_role'] ?? 'seller');
$account_status = function_exists('mr_admin_status') ? (string)mr_admin_status($actor_id) : 'active';
$back_url = 'mr-beat-admin/dashboard.php?section=overview';

if (!in_array($actor_role, ['seller', 'support', 'admin'], true)) {
header('Location: ' . mr_config('app.base_url') . '/mr-beat-admin/dashboard.php?section=overview');
exit;
}
} else {
$actor_type = 'user';
$actor_id = (int)$_SESSION['user_id'];
$actor_role = (string)($_SESSION['role'] ?? 'user');
$account_status = (string)mr_user_status($actor_id);
$back_url = 'account.php';
}

$is_blocked = ($account_status === 'blocked');

$inline_start = $is_ar ? 'right' : 'left';
$inline_end = $is_ar ? 'left' : 'right';
$opp_start = $is_ar ? 'left' : 'right';
$opp_end = $is_ar ? 'right' : 'left';

$ticket_id = (int)($_GET['ticket_id'] ?? 0);
$msg = '';

function support_ticket_owner_column(PDO $pdo): string {
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

function support_ticket_has_role(PDO $pdo): bool {
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

function upload_support_attachment(?array $file): ?array {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
mr_csrf_check();

$action = trim((string)($_POST['action'] ?? ''));

if ($action === 'new_ticket') {
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$fileData = upload_support_attachment($_FILES['attachment'] ?? null);

if ($subject !== '' && ($message !== '' || $fileData)) {
try {
$pdo->beginTransaction();

if ($has_owner_role) {
$sql = "INSERT INTO tickets ({$owner_col}, owner_role, subject, status) VALUES (?,?,?, 'open')";
$pdo->prepare($sql)->execute([$actor_id, $actor_type, $subject]);
} else {
$sql = "INSERT INTO tickets ({$owner_col}, subject, status) VALUES (?,?, 'open')";
$pdo->prepare($sql)->execute([$actor_id, $subject]);
}

$tid = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender, sender_id, message, attachment_path, attachment_name, attachment_type) VALUES (?,?,?,?,?,?,?)")
->execute([
$tid,
'user',
$actor_id,
$message,
$fileData['path'] ?? null,
$fileData['name'] ?? null,
$fileData['type'] ?? null
]);

$pdo->commit();

header('Location: support.php?ticket_id=' . $tid);
exit;
} catch (Throwable $e) {
if ($pdo->inTransaction()) $pdo->rollBack();
$msg = $is_ar ? 'تعذر فتح التيكت' : 'Could not open ticket';
}
} else {
$msg = $is_ar ? 'اكتب عنوان ورسالة أو أرفق ملفًا' : 'Write subject and message or attach a file';
}
}

if ($action === 'reply' && $ticket_id) {
$message = trim((string)($_POST['message'] ?? ''));
$fileData = upload_support_attachment($_FILES['attachment'] ?? null);

if ($has_owner_role) {
$ticketStmt = $pdo->prepare("SELECT id, status FROM tickets WHERE id=? AND {$owner_col}=? AND owner_role=? LIMIT 1");
$ticketStmt->execute([$ticket_id, $actor_id, $actor_type]);
} else {
$ticketStmt = $pdo->prepare("SELECT id, status FROM tickets WHERE id=? AND {$owner_col}=? LIMIT 1");
$ticketStmt->execute([$ticket_id, $actor_id]);
}

$ticketRow = $ticketStmt->fetch();

if (!$ticketRow) {
$msg = $is_ar ? 'التذكرة غير موجودة' : 'Ticket not found';
} elseif (($ticketRow['status'] ?? '') === 'closed') {
$msg = $is_ar ? 'تم إغلاق هذه التذكرة ولا يمكنك إرسال رسائل جديدة' : 'This ticket is closed and you cannot send new messages';
} elseif ($message !== '' || $fileData) {
$pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender, sender_id, message, attachment_path, attachment_name, attachment_type) VALUES (?,?,?,?,?,?,?)")
->execute([
$ticket_id,
'user',
$actor_id,
$message,
$fileData['path'] ?? null,
$fileData['name'] ?? null,
$fileData['type'] ?? null
]);

$pdo->prepare("UPDATE tickets SET status='open' WHERE id=?")->execute([$ticket_id]);

header('Location: support.php?ticket_id=' . $ticket_id);
exit;
}
}
}

if ($has_owner_role) {
$ticketsStmt = $pdo->prepare("SELECT * FROM tickets WHERE {$owner_col}=? AND owner_role=? ORDER BY id DESC LIMIT 30");
$ticketsStmt->execute([$actor_id, $actor_type]);
} else {
$ticketsStmt = $pdo->prepare("SELECT * FROM tickets WHERE {$owner_col}=? ORDER BY id DESC LIMIT 30");
$ticketsStmt->execute([$actor_id]);
}
$tickets = $ticketsStmt->fetchAll();

$active = null;
$messages = [];

if ($ticket_id) {
if ($has_owner_role) {
$t = $pdo->prepare("SELECT * FROM tickets WHERE id=? AND {$owner_col}=? AND owner_role=? LIMIT 1");
$t->execute([$ticket_id, $actor_id, $actor_type]);
} else {
$t = $pdo->prepare("SELECT * FROM tickets WHERE id=? AND {$owner_col}=? LIMIT 1");
$t->execute([$ticket_id, $actor_id]);
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
<title><?php echo htmlspecialchars(mr_site_name()); ?> - Support</title>
<style><?php echo mr_theme_css(); ?></style>
<style>
body{padding:20px;margin:0}
a{text-decoration:none}
.wrap{max-width:1150px;margin:0 auto}
.topShell{
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
border-radius:28px;
background:
radial-gradient(circle at top <?php echo $inline_end; ?>, color-mix(in srgb, var(--primary) 16%, transparent), transparent 26%),
linear-gradient(180deg,var(--card),color-mix(in srgb, var(--card) 84%, #000 16%));
padding:16px;
margin-bottom:18px;
box-shadow:0 12px 30px rgba(0,0,0,.18);
}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.brandBox{min-width:180px}
.nav{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.pill{
display:inline-block;
background:color-mix(in srgb, var(--card) 92%, #000 8%);
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
padding:9px 13px;
border-radius:999px;
text-decoration:none;
color:var(--text);
transition:.2s ease;
}
.pill:hover{transform:translateY(-1px);border-color:var(--primary)}
.alertBanner{
padding:15px;
border-radius:22px;
border:1px solid color-mix(in srgb, var(--primary) 30%, transparent);
background:color-mix(in srgb, var(--primary) 12%, var(--card));
color:var(--text);
margin:14px 0;
animation:fadeUp .25s ease;
}
.mainGrid{display:grid;grid-template-columns:minmax(0,330px) minmax(0,1fr);gap:16px;align-items:start}
.card{
background:linear-gradient(180deg,var(--card),color-mix(in srgb, var(--card) 84%, #000 16%));
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
border-radius:26px;
padding:16px;
min-width:0;
animation:fadeUp .3s ease;
box-shadow:0 10px 24px rgba(0,0,0,.12);
}
.cardTitle{font-size:16px;font-weight:800;margin-bottom:10px}
.ticketScroll{max-height:420px;overflow:auto;padding-<?php echo $inline_end; ?>:4px;scrollbar-width:thin}
.ticketItem{
display:block;
padding:13px;
border-radius:18px;
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
background:color-mix(in srgb, var(--card) 82%, #000 18%);
margin-bottom:10px;
color:var(--text);
transition:.22s ease;
overflow-wrap:anywhere;
word-break:break-word;
}
.ticketItem:hover{transform:translateY(-2px);border-color:var(--primary)}
.ticketItem.active{
background:linear-gradient(180deg,color-mix(in srgb, var(--primary) 20%, transparent),color-mix(in srgb, var(--primary) 8%, transparent));
border-color:var(--primary);
}
.ticketSubject{font-weight:700}
.ticketMeta{font-size:12px;opacity:.8;margin-top:6px}
.badge{
display:inline-block;
padding:4px 10px;
border-radius:999px;
font-size:12px;
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
background:color-mix(in srgb, var(--card) 78%, #000 22%);
color:var(--text);
margin-top:8px;
}
.formBox{margin-top:14px;padding-top:14px;border-top:1px solid color-mix(in srgb, var(--text) 10%, transparent)}
.chatHead{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px}

.chatBox{
height:500px;
overflow:auto;
padding:16px;
border-radius:22px;
background:
linear-gradient(180deg, color-mix(in srgb, var(--card) 88%, #000 12%), color-mix(in srgb, var(--card) 78%, #000 22%));
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
display:flex;
flex-direction:column;
gap:10px;
scrollbar-width:thin;
}

.bubble{
max-width:78%;
padding:10px 12px 8px;
border-radius:18px;
line-height:1.6;
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
overflow-wrap:anywhere;
word-break:break-word;
white-space:pre-wrap;
animation:fadeUp .18s ease;
box-shadow:0 8px 18px rgba(0,0,0,.08);
}

.me{
background:linear-gradient(180deg, color-mix(in srgb, var(--primary) 78%, #000 22%), var(--primary));
color:#fff;
border-color:transparent;
margin-<?php echo $opp_start; ?>:auto;
border-bottom-<?php echo $opp_start; ?>-radius:8px;
}

.them{
background:color-mix(in srgb, var(--card) 72%, #000 28%);
margin-<?php echo $opp_start; ?>:0;
margin-<?php echo $opp_end; ?>:auto;
border-bottom-<?php echo $opp_end; ?>-radius:8px;
}

.msgText{
font-size:14px;
line-height:1.7;
}

.timeTxt{
font-size:11px;
opacity:.78;
margin-top:7px;
text-align:right;
direction:ltr;
}

input,textarea{
width:100%;
padding:12px 13px;
border-radius:16px;
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
background:color-mix(in srgb, var(--card) 82%, #000 18%);
color:var(--text);
margin-top:8px;
box-sizing:border-box;
transition:.2s ease;
}
input:focus,textarea:focus{outline:none;border-color:var(--primary);transform:translateY(-1px)}
textarea{max-width:100%;resize:vertical;overflow:auto}

button{
padding:11px 15px;
border-radius:14px;
border:0;
background:linear-gradient(180deg, color-mix(in srgb, var(--primary) 78%, #000 22%), var(--primary));
color:#fff;
cursor:pointer;
margin-top:10px;
font-weight:700;
transition:.2s ease;
}
button:hover{transform:translateY(-1px)}

.small{font-size:12px;opacity:.8}
.errTxt{color:#ffb4b4;margin-top:10px}
.emptyState{opacity:.8;text-align:center;padding:34px 14px}
.closedNote{
margin-top:12px;
padding:12px 14px;
border-radius:16px;
background:color-mix(in srgb, #ef4444 10%, var(--card));
border:1px solid color-mix(in srgb, #ef4444 35%, transparent);
color:var(--text);
}

.attPreview{
margin-top:10px;
display:block;
width:100%;
max-width:260px;
max-height:320px;
object-fit:cover;
border-radius:14px;
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
transition:.2s ease;
}
.attPreview:hover{
transform:scale(1.02);
opacity:.96;
}

.fileLink{
display:inline-block;
margin-top:10px;
padding:9px 12px;
border-radius:12px;
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
background:color-mix(in srgb, var(--card) 82%, #000 18%);
color:var(--text);
max-width:100%;
overflow-wrap:anywhere;
word-break:break-word;
}

.uploadHint{font-size:12px;opacity:.75;margin-top:8px}

.msgForm{
margin-top:12px;
padding:12px;
border-radius:18px;
background:color-mix(in srgb, var(--card) 84%, #000 16%);
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
}
.msgForm textarea{
min-height:90px;
margin-top:0;
}

.fileUploadRow{
display:flex;
gap:10px;
align-items:center;
flex-wrap:wrap;
margin-top:10px;
}

.fileUploadBox{
position:relative;
display:inline-flex;
align-items:center;
justify-content:center;
padding:10px 14px;
border-radius:14px;
background:color-mix(in srgb, var(--card) 80%, #000 20%);
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
cursor:pointer;
transition:.2s ease;
min-width:160px;
font-size:13px;
font-weight:700;
color:var(--text);
}

.fileUploadBox:hover{
transform:translateY(-1px);
border-color:var(--primary);
}

.fileUploadBox input[type="file"]{
position:absolute;
inset:0;
opacity:0;
cursor:pointer;
width:100%;
height:100%;
margin:0;
padding:0;
}

.fileUploadName{
font-size:12px;
opacity:.8;
word-break:break-word;
}

.msgActions{
display:flex;
gap:10px;
align-items:center;
justify-content:space-between;
flex-wrap:wrap;
margin-top:10px;
}

.msgActions button{
margin-top:0;
min-width:140px;
}

@keyframes fadeUp{
from{opacity:0;transform:translateY(10px)}
to{opacity:1;transform:translateY(0)}
}

@media (max-width: 920px){
.mainGrid{grid-template-columns:1fr}
.chatBox{height:420px}
.bubble{max-width:88%}
.attPreview{max-width:100%}
}
</style>
</head>
<body>
<div class="wrap">

<div class="topShell">
<div class="topbar">
<div class="brandBox">
<div style="font-size:20px;font-weight:800"><?php echo htmlspecialchars(mr_site_name()); ?></div>
<div style="opacity:.8;font-size:13px;margin-top:4px">
<?php echo $is_ar ? 'تواصل مع الدعم بسهولة وسرعة' : 'Reach support quickly and easily'; ?>
</div>
</div>

<div class="nav">
    
<a class="pill" href="index.php"><?php echo $is_ar ? 'الرئيسية' : 'Home'; ?></a>
<a class="pill" href="<?php echo htmlspecialchars($back_url); ?>"><?php echo $is_ar ? 'رجوع' : 'Back'; ?></a>

<a class="pill" href="?lang=<?php echo $is_ar ? 'en' : 'ar'; ?>"><?php echo $is_ar ? 'English' : 'عربي'; ?></a>
<a class="pill" href="logout.php"><?php echo $is_ar ? 'خروج' : 'Logout'; ?></a>
</div>
</div>
</div>

<?php if ($is_blocked): ?>
<div class="alertBanner">
<?php echo $is_ar
? 'حسابك محظور حاليًا. يمكنك فتح تيكت وطلب تفعيل الحساب من هنا.'
: 'Your account is currently blocked. You can open a support ticket here and request reactivation.'; ?>
</div>
<?php endif; ?>

<div class="mainGrid">

<div class="card">
<div class="cardTitle"><?php echo $is_ar ? 'تيكتاتك' : 'Your Tickets'; ?></div>

<div class="ticketScroll">
<?php if (!$tickets): ?>
<div class="emptyState"><?php echo $is_ar ? 'لا توجد تيكتات حتى الآن.' : 'No tickets yet.'; ?></div>
<?php else: ?>
<?php foreach ($tickets as $t): ?>
<?php $is_open = (($t['status'] ?? '') === 'open'); ?>
<a class="ticketItem <?php echo ($ticket_id === (int)$t['id']) ? 'active' : ''; ?>" href="support.php?ticket_id=<?php echo (int)$t['id']; ?>">
<div class="ticketSubject">#<?php echo (int)$t['id']; ?> - <?php echo htmlspecialchars($t['subject']); ?></div>
<div class="ticketMeta"><?php echo htmlspecialchars($t['created_at'] ?? ''); ?></div>
<div class="badge" style="background:<?php echo $is_open ? '#102318' : '#231616'; ?>;border-color:<?php echo $is_open ? '#1f5c38' : '#5a2a2a'; ?>;color:<?php echo $is_open ? '#86efac' : '#fca5a5'; ?>;">
<?php echo ($is_ar ? 'الحالة: ' : 'Status: '), htmlspecialchars($t['status']); ?>
</div>
</a>
<?php endforeach; ?>
<?php endif; ?>
</div>

<div class="formBox">
<div class="cardTitle" style="font-size:15px"><?php echo $is_ar ? 'فتح تيكت جديد' : 'Open New Ticket'; ?></div>

<?php if ($msg && !$ticket_id): ?><div class="errTxt"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="msgForm">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="new_ticket">

<input
name="subject"
placeholder="<?php echo $is_ar ? 'العنوان' : 'Subject'; ?>"
value="<?php echo $is_blocked ? ($is_ar ? 'طلب تفعيل الحساب' : 'Unblock Request') : ''; ?>"
required
>

<textarea name="message" rows="4" placeholder="<?php echo $is_ar ? 'اكتب رسالتك...' : 'Write your message...'; ?>"></textarea>

<div class="msgActions">
<div class="fileUploadRow">
<label class="fileUploadBox">
<span><?php echo $is_ar ? 'رفع ملف' : 'Upload File'; ?></span>
<input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.zip,.rar,.txt,.doc,.docx,.xls,.xlsx" onchange="showFileName(this)">
</label>
<span class="fileUploadName"><?php echo $is_ar ? 'لم يتم اختيار ملف' : 'No file selected'; ?></span>
</div>

<button type="submit"><?php echo $is_ar ? 'فتح تيكت' : 'Open Ticket'; ?></button>
</div>

<div class="uploadHint"><?php echo $is_ar ? 'يمكنك إرفاق صورة أو ملف' : 'You can attach an image or file'; ?></div>
</form>
</div>
</div>

<div class="card">
<?php if (!$active): ?>
<div class="emptyState"><?php echo $is_ar ? 'اختر تيكت من القائمة لعرض المحادثة.' : 'Select a ticket from the list to view the conversation.'; ?></div>
<?php else: ?>
<div class="chatHead">
<div>
<div class="cardTitle" style="margin-bottom:4px">
<?php echo $is_ar ? 'تيكت' : 'Ticket'; ?> #<?php echo (int)$active['id']; ?> - <?php echo htmlspecialchars($active['subject']); ?>
</div>
<div class="small"><?php echo $is_ar ? 'الحالة' : 'Status'; ?>: <?php echo htmlspecialchars($active['status']); ?></div>
</div>
<?php $active_open = (($active['status'] ?? '') === 'open'); ?>
<div class="badge" style="background:<?php echo $active_open ? '#102318' : '#231616'; ?>;border-color:<?php echo $active_open ? '#1f5c38' : '#5a2a2a'; ?>;color:<?php echo $active_open ? '#86efac' : '#fca5a5'; ?>;">
<?php echo htmlspecialchars($active['status']); ?>
</div>
</div>

<div class="chatBox" id="msgBox">
<?php if (!$messages): ?>
<div class="emptyState"><?php echo $is_ar ? 'لا توجد رسائل بعد.' : 'No messages yet.'; ?></div>
<?php else: ?>
<?php foreach ($messages as $m): ?>
<div class="bubble <?php echo $m['sender'] === 'user' ? 'me' : 'them'; ?>">
<?php if (!empty($m['message'])): ?>
<div class="msgText"><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>
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

<div class="timeTxt"><?php echo htmlspecialchars($m['created_at']); ?></div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<?php if (($active['status'] ?? '') === 'closed'): ?>
<div class="closedNote">
<?php echo $is_ar ? 'تم إغلاق هذه التذكرة. لا يمكنك إرسال رسائل جديدة إلا إذا أعاد الأدمن فتحها.' : 'This ticket is closed. You cannot send new messages unless the admin reopens it.'; ?>
</div>
<?php if ($msg && $ticket_id): ?><div class="errTxt"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<?php else: ?>
<?php if ($msg && $ticket_id): ?><div class="errTxt"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<form method="post" id="supportReplyForm" enctype="multipart/form-data" class="msgForm">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="reply">
<input type="hidden" name="ticket_id" value="<?php echo (int)$active['id']; ?>">

<textarea name="message" rows="3" placeholder="<?php echo $is_ar ? 'اكتب رسالة...' : 'Type your message...'; ?>"></textarea>

<div class="msgActions">
<div class="fileUploadRow">
<label class="fileUploadBox">
<span><?php echo $is_ar ? 'رفع ملف' : 'Upload File'; ?></span>
<input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.zip,.rar,.txt,.doc,.docx,.xls,.xlsx" onchange="showFileName(this)">
</label>
<span class="fileUploadName"><?php echo $is_ar ? 'لم يتم اختيار ملف' : 'No file selected'; ?></span>
</div>

<button type="submit"><?php echo $is_ar ? 'إرسال' : 'Send'; ?></button>
</div>

<div class="uploadHint"><?php echo $is_ar ? 'يمكنك إرسال نص أو ملف أو الاثنين معًا' : 'You can send text, a file, or both'; ?></div>
</form>
<?php endif; ?>
<?php endif; ?>
</div>

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

function showFileName(input){
const row = input.closest('.fileUploadRow');
if(!row) return;

const nameBox = row.querySelector('.fileUploadName');
if(!nameBox) return;

if(input.files && input.files[0]){
nameBox.textContent = input.files[0].name;
} else {
nameBox.textContent = <?php echo json_encode($is_ar ? 'لم يتم اختيار ملف' : 'No file selected'); ?>;
}
}

const msgBox = document.getElementById('msgBox');
const replyForm = document.getElementById('supportReplyForm');
const currentTicketId = <?php echo $active ? (int)$active['id'] : 0; ?>;
const csrfToken = <?php echo json_encode((string)mr_csrf_token()); ?>;
const isArabic = <?php echo $is_ar ? 'true' : 'false'; ?>;

function renderMessages(messages){
if (!msgBox) return;

let html = '';
messages.forEach(function(m){
const cls = (m.sender === 'user') ? 'me' : 'them';
html += '<div class="bubble ' + cls + '">';

if (m.message) {
html += '<div class="msgText">' + nl2brJs(m.message) + '</div>';
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

html += '<div class="timeTxt">' + escapeHtml(m.created_at || '') + '</div>';
html += '</div>';
});

msgBox.innerHTML = html || '<div class="emptyState">' + (isArabic ? 'لا توجد رسائل بعد.' : 'No messages yet.') + '</div>';
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
fd.set('_csrf', csrfToken);
fd.set('action', 'reply');
fd.set('ticket_id', currentTicketId);

try {
const res = await fetch('ticket_send.php', {
method: 'POST',
body: fd
});
const data = await res.json();
if (data.ok) {
replyForm.reset();

const allNames = replyForm.querySelectorAll('.fileUploadName');
allNames.forEach(function(el){
el.textContent = isArabic ? 'لم يتم اختيار ملف' : 'No file selected';
});

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