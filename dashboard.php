<?php

require __DIR__ . '/../app/bootstrap.php';
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
if (isset($_GET['ajax_ticket_messages'])) {
mr_require_active_user(true);
$pdo = mr_db();

$ticket_id = (int)($_GET['ticket_id'] ?? 0);

header('Content-Type: application/json; charset=utf-8');

if ($ticket_id <= 0) {
echo json_encode(['ok' => false, 'html' => '']);
exit;
}

$admin_id = (int)($_SESSION['admin_id'] ?? 0);
$isSeller = mr_is_seller();
$isAdminSide = mr_can_manage_tickets();

function e_ajax_ticket($v){
return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

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
} catch (Throwable $e) {
}

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

if (!$allowed) {
echo json_encode(['ok' => false, 'html' => '']);
exit;
}

if ($isSeller) {
$sql = "
SELECT tm.*
FROM ticket_messages tm
JOIN tickets t ON t.id = tm.ticket_id
WHERE tm.ticket_id=?
AND t.{$ticket_owner_col}=?
" . ($ticket_has_owner_role ? " AND t.owner_role='admin' " : "") . "
ORDER BY tm.id ASC
";
$m = $pdo->prepare($sql);
$m->execute([$ticket_id, $admin_id]);
} else {
$m = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY id ASC");
$m->execute([$ticket_id]);
}
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
<?php if (!empty($msg['message'])): ?>
<div class="msgText"><?php echo nl2br(e_ajax_ticket($msg['message'])); ?></div>
<?php endif; ?>

<?php if (!empty($msg['image_url'])): ?>
<div class="msgImageWrap">
<a href="../<?php echo e_ajax_ticket($msg['image_url']); ?>" target="_blank">
<img class="msgImage" src="../<?php echo e_ajax_ticket($msg['image_url']); ?>">
</a>
</div>
<?php endif; ?>

<div class="timeTxt"><?php echo e_ajax_ticket($msg['created_at']); ?></div>
</div>
<?php
}
$html = ob_get_clean();

echo json_encode([
'ok' => true,
'html' => $html
]);
exit;
}

$section = trim((string)($_GET['section'] ?? 'overview'));
mr_require_active_user($section === 'tickets' || $section === 'support');

$allowed_sections = [
'overview','profile','users','tickets','support','products','add_product',
'deposits','withdraws','settings','my_deposits','my_withdraws'
];
if (!in_array($section, $allowed_sections, true)) $section = 'overview';

mr_require_admin(['admin','seller']);
mr_require_admin_section($section);

$pdo = mr_db();
$is_ar = (mr_lang() === 'ar');
$admin_id = (int)($_SESSION['admin_id'] ?? 0);
$admin_role = mr_admin_role();

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function n2($n){ return number_format((float)$n, 2, '.', ''); }
function can_adjust_balance(): bool { return mr_can_adjust_balance(); }
function is_super_admin(): bool { return mr_admin_role() === 'admin'; }
function admin_can_change_roles(): bool { return is_super_admin(); }
function admin_can_view_own_finance_tabs(): bool { return mr_is_seller(); }

function dashboard_sections_for_role(): array {
$role = mr_admin_role();
if (function_exists('mr_admin_allowed_sections')) {
return mr_admin_allowed_sections();
}
if (function_exists('mr_admin_sections_map')) {
$map = mr_admin_sections_map();
return $map[$role] ?? ['overview','profile'];
}
return ['overview','profile'];
}

function section_allowed(string $section): bool {
return in_array($section, dashboard_sections_for_role(), true);
}

function setting_get(PDO $pdo, string $k, string $default=''): string {
$st = $pdo->prepare("SELECT v FROM settings WHERE k=? LIMIT 1");
$st->execute([$k]);
$v = $st->fetchColumn();
return $v === false ? $default : (string)$v;
}

function setting_set(PDO $pdo, string $k, string $v): void {
$st = $pdo->prepare("INSERT INTO settings (`k`,`v`) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v), updated_at=CURRENT_TIMESTAMP");
$st->execute([$k,$v]);
}

function setting_get_int(PDO $pdo, string $k, int $default = 0): int {
return (int)setting_get($pdo, $k, (string)$default);
}

function status_class($status) {
if (in_array($status, ['active','approved','open'], true)) return 'statusGood';
if (in_array($status, ['blocked','rejected','closed','disabled'], true)) return 'statusBad';
return 'statusWarn';
}

function upload_file_path(?array $file, string $dirName, string $prefix, array $allowedExt): ?string {
if (!$file || empty($file['name'])) return null;
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) return null;
$allowedMime = [
'jpg' => ['image/jpeg'],
'jpeg' => ['image/jpeg'],
'png' => ['image/png'],
'webp' => ['image/webp'],
'gif' => ['image/gif'],
'ico' => ['image/x-icon','image/vnd.microsoft.icon'],
'svg' => ['image/svg+xml'],
];

$tmp = (string)($file['tmp_name'] ?? '');
$maxSize = 5 * 1024 * 1024; // 5MB
if (!empty($file['size']) && (int)$file['size'] > $maxSize) return null;

if ($tmp !== '' && is_file($tmp)) {
$mime = function_exists('mime_content_type') ? (string)mime_content_type($tmp) : '';
if (isset($allowedMime[$ext]) && $mime !== '' && !in_array($mime, $allowedMime[$ext], true)) {
return null;
}

if (in_array($ext, ['jpg','jpeg','png','webp','gif','ico'], true)) {
$imgInfo = @getimagesize($tmp);
if ($imgInfo === false) return null;
}
}
$cleanDir = trim($dirName, '/');
$baseDir = __DIR__ . '/../uploads';
$dir = $cleanDir !== '' ? ($baseDir . '/' . $cleanDir) : $baseDir;

if (!is_dir($dir)) mkdir($dir, 0755, true);

$filename = $prefix . '_' . time() . '_' . rand(1000,9999) . '.' . $ext;
$dest = $dir . '/' . $filename;

if (!empty($file['tmp_name']) && move_uploaded_file($file['tmp_name'], $dest)) {
return $cleanDir !== '' ? ('uploads/' . $cleanDir . '/' . $filename) : ('uploads/' . $filename);
}
return null;
}

function table_exists(PDO $pdo, string $table): bool {
try {
$st = $pdo->prepare("SHOW TABLES LIKE ?");
$st->execute([$table]);
return (bool)$st->fetch();
} catch (Throwable $e) {
return false;
}
}

function column_exists(PDO $pdo, string $table, string $column): bool {
$allowedTables = [
'tickets','ticket_messages','admins','users','wallets','transactions',
'products','product_stock','orders','order_items',
'settings','payment_methods','deposit_requests','withdraw_requests',
'banners','announcements','contact_methods','reviews'
];
if (!in_array($table, $allowedTables, true)) return false;

try {
$st = $pdo->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
$st->execute([$column]);
return (bool)$st->fetch();
} catch (Throwable $e) {
return false;
}
}

function reviews_table_ready(PDO $pdo): bool {
return table_exists($pdo, 'reviews')
&& column_exists($pdo, 'reviews', 'user_id')
&& column_exists($pdo, 'reviews', 'is_like');
}

function mr_hex_to_rgb(string $hex): array {
$hex = trim($hex);
$hex = ltrim($hex, '#');

if (strlen($hex) === 3) {
$hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
}

if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
return [59, 130, 246];
}

return [
hexdec(substr($hex, 0, 2)),
hexdec(substr($hex, 2, 2)),
hexdec(substr($hex, 4, 2)),
];
}

function mr_rgba(string $hex, float $alpha): string {
[$r, $g, $b] = mr_hex_to_rgb($hex);
$alpha = max(0, min(1, $alpha));
return "rgba({$r},{$g},{$b},{$alpha})";
}

function mr_theme_presets(): array {
return [
'account_modern' => [
'mode' => 'dark',
'bg_dark' => '#0b1020',
'card_dark' => '#121a2b',
'primary_dark' => '#6d5efc',
'text_dark' => '#eef2ff',
'g1_dark' => '#8b5cf6',
'g2_dark' => '#06b6d4',
'bg_light' => '#f6f8ff',
'card_light' => '#ffffff',
'primary_light' => '#6d5efc',
'text_light' => '#111827',
'g1_light' => '#8b5cf6',
'g2_light' => '#06b6d4',
'radius' => 22,
'shadow' => 30,
'border' => 1,
'glass' => 0,
],
'clean_light' => [
'mode' => 'light',
'bg_dark' => '#051424',
'card_dark' => '#0b1b2b',
'primary_dark' => '#3b82f6',
'text_dark' => '#ffffff',
'g1_dark' => '#0f172a',
'g2_dark' => '#1d4ed8',
'bg_light' => '#f7f8fb',
'card_light' => '#ffffff',
'primary_light' => '#2563eb',
'text_light' => '#111827',
'g1_light' => '#dbeafe',
'g2_light' => '#f8fafc',
'radius' => 14,
'shadow' => 18,
'border' => 1,
'glass' => 0,
],
'glass_light' => [
'mode' => 'light',
'bg_dark' => '#07111f',
'card_dark' => '#0d1726',
'primary_dark' => '#60a5fa',
'text_dark' => '#eef6ff',
'g1_dark' => '#0f172a',
'g2_dark' => '#2563eb',
'bg_light' => '#eef4ff',
'card_light' => '#ffffff',
'primary_light' => '#3b82f6',
'text_light' => '#0f172a',
'g1_light' => '#bfdbfe',
'g2_light' => '#eff6ff',
'radius' => 20,
'shadow' => 24,
'border' => 1,
'glass' => 1,
],
'violet_modern' => [
'mode' => 'dark',
'bg_dark' => '#120b1d',
'card_dark' => '#1c1330',
'primary_dark' => '#a855f7',
'text_dark' => '#f5f3ff',
'g1_dark' => '#7c3aed',
'g2_dark' => '#c084fc',
'bg_light' => '#faf5ff',
'card_light' => '#ffffff',
'primary_light' => '#7c3aed',
'text_light' => '#2e1065',
'g1_light' => '#ede9fe',
'g2_light' => '#ffffff',
'radius' => 22,
'shadow' => 24,
'border' => 1,
'glass' => 0,
],
'emerald_modern' => [
'mode' => 'dark',
'bg_dark' => '#081510',
'card_dark' => '#10241b',
'primary_dark' => '#10b981',
'text_dark' => '#ecfdf5',
'g1_dark' => '#059669',
'g2_dark' => '#34d399',
'bg_light' => '#ecfdf5',
'card_light' => '#ffffff',
'primary_light' => '#059669',
'text_light' => '#064e3b',
'g1_light' => '#a7f3d0',
'g2_light' => '#ffffff',
'radius' => 20,
'shadow' => 22,
'border' => 1,
'glass' => 0,
],
'sunset_modern' => [
'mode' => 'dark',
'bg_dark' => '#1a0d0b',
'card_dark' => '#2a1612',
'primary_dark' => '#f97316',
'text_dark' => '#fff7ed',
'g1_dark' => '#ef4444',
'g2_dark' => '#f59e0b',
'bg_light' => '#fff7ed',
'card_light' => '#ffffff',
'primary_light' => '#ea580c',
'text_light' => '#7c2d12',
'g1_light' => '#fdba74',
'g2_light' => '#ffffff',
'radius' => 20,
'shadow' => 22,
'border' => 1,
'glass' => 0,
],
];
}

function mr_theme_tokens(PDO $pdo): array {
$preset_key = setting_get($pdo, 'theme_preset', 'account_modern');
$presets = mr_theme_presets();
$preset = $presets[$preset_key] ?? $presets['account_modern'];

$theme_mode = setting_get($pdo, 'theme_mode', $preset['mode']);
if ($theme_mode !== 'light' && $theme_mode !== 'dark') {
$theme_mode = $preset['mode'];
}

$ui_bg = $theme_mode === 'light' ? $preset['bg_light'] : $preset['bg_dark'];
$ui_card = $theme_mode === 'light' ? $preset['card_light'] : $preset['card_dark'];
$ui_primary = $theme_mode === 'light' ? $preset['primary_light'] : $preset['primary_dark'];
$ui_text = $theme_mode === 'light' ? $preset['text_light'] : $preset['text_dark'];
$ui_g1 = $theme_mode === 'light' ? $preset['g1_light'] : $preset['g1_dark'];
$ui_g2 = $theme_mode === 'light' ? $preset['g2_light'] : $preset['g2_dark'];

$theme_radius = (int)$preset['radius'];
$theme_shadow = (int)$preset['shadow'];
$theme_border = (int)$preset['border'];
$theme_glass = (int)$preset['glass'];

return [
'preset_key' => $preset_key,
'theme_mode' => $theme_mode,
'theme_bg' => $preset['bg_dark'],
'theme_card' => $preset['card_dark'],
'theme_primary' => $preset['primary_dark'],
'theme_text' => $preset['text_dark'],
'theme_bg_light' => $preset['bg_light'],
'theme_card_light' => $preset['card_light'],
'theme_primary_light' => $preset['primary_light'],
'theme_text_light' => $preset['text_light'],
'theme_gradient_from' => $preset['g1_dark'],
'theme_gradient_to' => $preset['g2_dark'],
'theme_gradient_from_light' => $preset['g1_light'],
'theme_gradient_to_light' => $preset['g2_light'],
'theme_radius' => $theme_radius,
'theme_shadow' => $theme_shadow,
'theme_border' => $theme_border,
'theme_glass' => $theme_glass,
'ui_bg' => $ui_bg,
'ui_card' => $ui_card,
'ui_primary' => $ui_primary,
'ui_text' => $ui_text,
'ui_g1' => $ui_g1,
'ui_g2' => $ui_g2,
'primary_08' => mr_rgba($ui_primary, .08),
'primary_12' => mr_rgba($ui_primary, .12),
'primary_18' => mr_rgba($ui_primary, .18),
'primary_24' => mr_rgba($ui_primary, .24),
'text_08' => mr_rgba($ui_text, .08),
'text_12' => mr_rgba($ui_text, .12),
'text_18' => mr_rgba($ui_text, .18),
'text_72' => mr_rgba($ui_text, .72),
'shadow_lg' => '0 12px '.$theme_shadow.'px rgba(0,0,0,.18)',
'shadow_md' => '0 10px '.max(12, $theme_shadow - 4).'px rgba(0,0,0,.14)',
'blur' => $theme_glass ? 'blur(14px)' : 'blur(0px)',
'surface_mix' => $theme_glass
? 'linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.04))'
: 'linear-gradient(180deg, color-mix(in srgb, var(--card) 96%, #fff 4%), color-mix(in srgb, var(--card) 86%, #000 14%))',
];
}

$has_reviews = reviews_table_ready($pdo);

$admin = null;
$adminSourceTable = 'admins';

if (table_exists($pdo, 'admins')) {
try {
$hasRole = column_exists($pdo, 'admins', 'role');
$hasAvatar = column_exists($pdo, 'admins', 'avatar_url');
$hasBio = column_exists($pdo, 'admins', 'bio');
$hasStatus = column_exists($pdo, 'admins', 'status');

$select = "SELECT id, username, email";
$select .= $hasRole ? ", role" : ", 'seller' as role";
$select .= $hasAvatar ? ", avatar_url" : ", NULL as avatar_url";
$select .= $hasBio ? ", bio" : ", NULL as bio";
$select .= $hasStatus ? ", status" : ", 'active' as status";
$select .= " FROM admins WHERE id=? LIMIT 1";

$st = $pdo->prepare($select);
$st->execute([$admin_id]);
$admin = $st->fetch();
} catch (Throwable $e) {
$admin = null;
}
}

if (!$admin) {
$adminSourceTable = 'users';
$st = $pdo->prepare("SELECT id, username, email, role, avatar_url, bio, status FROM users WHERE id=? LIMIT 1");
$st->execute([$admin_id]);
$admin = $st->fetch();
}

if (!$admin) die('Admin not found');

$adminWalletStmt = $pdo->prepare("SELECT balance_usdt, hold_usdt FROM wallets WHERE user_id=? LIMIT 1");
$adminWalletStmt->execute([$admin_id]);
$adminWallet = $adminWalletStmt->fetch() ?: ['balance_usdt'=>0,'hold_usdt'=>0];
$admin_wallet_usdt = (float)($adminWallet['balance_usdt'] ?? 0);
$admin_hold_usdt = (float)($adminWallet['hold_usdt'] ?? 0);

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
} catch (Throwable $e) {
$ticket_owner_col = 'user_id';
$ticket_has_owner_role = false;
}

$err = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
mr_csrf_check();
$action = trim((string)($_POST['action'] ?? ''));

if ($action === 'save_admin_profile') {
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$bio = trim($_POST['bio'] ?? '');
if (mb_strlen($bio) > 255) $bio = mb_substr($bio, 0, 255);

if ($username === '') {
$err = $is_ar ? 'اسم المستخدم مطلوب' : 'Username is required';
$section = 'profile';
} else {
$avatar_path = upload_file_path($_FILES['avatar'] ?? null, 'avatars', 'admin_' . $admin_id, ['jpg','jpeg','png','webp']);

if ($adminSourceTable === 'admins') {
$hasBio = column_exists($pdo, 'admins', 'bio');
$hasAvatar = column_exists($pdo, 'admins', 'avatar_url');

$fields = ["username=?", "email=?"];
$vals = [$username, $email ?: null];

if ($hasBio) {
$fields[] = "bio=?";
$vals[] = $bio ?: null;
}
if ($hasAvatar && $avatar_path) {
$fields[] = "avatar_url=?";
$vals[] = $avatar_path;
}

$vals[] = $admin_id;
$sql = "UPDATE admins SET " . implode(',', $fields) . " WHERE id=?";
$pdo->prepare($sql)->execute($vals);
} else {
if ($avatar_path) {
$pdo->prepare("UPDATE users SET username=?, email=?, bio=?, avatar_url=? WHERE id=?")
->execute([$username, $email ?: null, $bio ?: null, $avatar_path, $admin_id]);
} else {
$pdo->prepare("UPDATE users SET username=?, email=?, bio=? WHERE id=?")
->execute([$username, $email ?: null, $bio ?: null, $admin_id]);
}
}

$_SESSION['username'] = $username;
header('Location: dashboard.php?section=profile');
exit;
}
}

if ($action === 'change_admin_password') {
$old = (string)($_POST['old_password'] ?? '');
$new = (string)($_POST['new_password'] ?? '');
$new2 = (string)($_POST['new_password2'] ?? '');

if ($new === '' || strlen($new) < 10) {
$err = $is_ar ? 'كلمة المرور الجديدة قصيرة' : 'New password too short';
$section = 'profile';
} elseif ($new !== $new2) {
$err = $is_ar ? 'تأكيد كلمة المرور غير مطابق' : 'Password confirmation mismatch';
$section = 'profile';
} else {
$passTable = table_exists($pdo, 'admins') ? 'admins' : 'users';
$ph = $pdo->prepare("SELECT password_hash FROM {$passTable} WHERE id=? LIMIT 1");
$ph->execute([$admin_id]);
$row = $ph->fetch();

if (!$row || !password_verify($old, (string)$row['password_hash'])) {
$err = $is_ar ? 'كلمة المرور القديمة غير صحيحة' : 'Old password incorrect';
$section = 'profile';
} else {
$hash = password_hash($new, PASSWORD_DEFAULT);
$pdo->prepare("UPDATE {$passTable} SET password_hash=? WHERE id=?")->execute([$hash, $admin_id]);
header('Location: dashboard.php?section=profile');
exit;
}
}
}

if ($action === 'create_user') {
if (!mr_can_manage_users()) {
http_response_code(403);
exit('Access denied');
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');
$role = $_POST['role'] ?? 'user';

if ($username === '' || $password === '') {
$err = $is_ar ? 'اكتب اسم المستخدم وكلمة المرور' : 'Enter username and password';
$section = 'users';
} else {
$hash = password_hash($password, PASSWORD_DEFAULT);
$role = in_array($role, ['user','admin','seller'], true) ? $role : 'user';

$stmt = $pdo->prepare("INSERT INTO users (username,email,password_hash,role,status) VALUES (?,?,?,?, 'active')");
$stmt->execute([$username, $email ?: null, $hash, $role]);
$uid = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO wallets (user_id,balance_mb,balance_usdt,hold_usdt) VALUES (?,?,?,?)")
->execute([$uid,0,0,0]);

if ($role === 'user') {
$bonus_enabled = setting_get($pdo, 'welcome_bonus_enabled', '0') === '1';
$bonus_amount = (float)setting_get($pdo, 'welcome_bonus_amount', '0');

if ($bonus_enabled && $bonus_amount > 0) {
$pdo->prepare("UPDATE wallets SET balance_usdt = balance_usdt + ? WHERE user_id=?")
->execute([$bonus_amount, $uid]);

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")
->execute([$uid, $admin_id, 'credit', 'usdt', $bonus_amount, 'Welcome bonus (admin created)']);
}
}

if (in_array($role, ['seller','admin'], true) && table_exists($pdo, 'admins')) {
$hasStatus = column_exists($pdo, 'admins', 'status');
$hasAllowed = column_exists($pdo, 'admins', 'allowed_sections');
$hasAvatar = column_exists($pdo, 'admins', 'avatar_url');
$hasBio = column_exists($pdo, 'admins', 'bio');

$fields = ['id','username','email','password_hash'];
$values = [$uid, $username, $email ?: null, $hash];

if (column_exists($pdo,'admins','role')) {
$fields[] = 'role';
$values[] = $role;
}
if ($hasStatus) {
$fields[] = 'status';
$values[] = 'active';
}
if ($hasAllowed && $role === 'seller') {
$fields[] = 'allowed_sections';
$values[] = 'overview,profile,products,add_product,my_deposits,my_withdraws,support';
}
if ($hasAvatar) {
$fields[] = 'avatar_url';
$values[] = null;
}
if ($hasBio) {
$fields[] = 'bio';
$values[] = null;
}

$placeholders = implode(',', array_fill(0, count($fields), '?'));
$pdo->prepare("INSERT INTO admins (" . implode(',', $fields) . ") VALUES ($placeholders)")
->execute($values);
}

header('Location: dashboard.php?section=users');
exit;
}
}
if ($action === 'update_user_access') {
if (!admin_can_change_roles()) {
http_response_code(403);
exit('Access denied');
}

$uid = (int)($_POST['user_id'] ?? 0);
$role = trim((string)($_POST['role'] ?? 'user'));
$status = trim((string)($_POST['status'] ?? 'active'));

$allowed_roles = ['user','seller','admin'];
$allowed_statuses = ['active','blocked'];

if ($uid > 0) {
if (!in_array($role, $allowed_roles, true)) $role = 'user';
if (!in_array($status, $allowed_statuses, true)) $status = 'active';

$pdo->prepare("UPDATE users SET role=?, status=? WHERE id=?")->execute([$role, $status, $uid]);

if (table_exists($pdo, 'admins')) {
$existsStmt = $pdo->prepare("SELECT id FROM admins WHERE id=? LIMIT 1");
$existsStmt->execute([$uid]);
$exists = $existsStmt->fetch();

if (in_array($role, ['admin','seller'], true)) {
$userRowStmt = $pdo->prepare("SELECT username,email,password_hash,avatar_url,bio FROM users WHERE id=? LIMIT 1");
$userRowStmt->execute([$uid]);
$urow = $userRowStmt->fetch();

$defaultSellerSections = 'overview,profile,products,add_product,my_deposits,my_withdraws,support';

if ($exists) {
$sets = [];
$vals = [];

if (column_exists($pdo,'admins','role')) {
$sets[] = "role=?";
$vals[] = $role;
}
if (column_exists($pdo,'admins','status')) {
$sets[] = "status=?";
$vals[] = $status;
}
$sets[] = "username=?";
$vals[] = $urow['username'] ?? '';
$sets[] = "email=?";
$vals[] = $urow['email'] ?? null;
if (column_exists($pdo,'admins','avatar_url')) {
$sets[] = "avatar_url=?";
$vals[] = $urow['avatar_url'] ?? null;
}
if (column_exists($pdo,'admins','bio')) {
$sets[] = "bio=?";
$vals[] = $urow['bio'] ?? null;
}
$sets[] = "password_hash=?";
$vals[] = $urow['password_hash'] ?? '';

if (column_exists($pdo,'admins','allowed_sections')) {
if ($role === 'seller') {
$chk = $pdo->prepare("SELECT allowed_sections FROM admins WHERE id=? LIMIT 1");
$chk->execute([$uid]);
$existingAllowed = trim((string)$chk->fetchColumn());
if ($existingAllowed === '') {
$sets[] = "allowed_sections=?";
$vals[] = $defaultSellerSections;
}
} else {
$sets[] = "allowed_sections=?";
$vals[] = null;
}
}

$vals[] = $uid;
$pdo->prepare("UPDATE admins SET " . implode(',', $sets) . " WHERE id=?")->execute($vals);
} else {
$fields = ['id','username','email','password_hash'];
$vals = [$uid, $urow['username'] ?? '', $urow['email'] ?? null, $urow['password_hash'] ?? ''];

if (column_exists($pdo,'admins','role')) {
$fields[] = 'role';
$vals[] = $role;
}
if (column_exists($pdo,'admins','status')) {
$fields[] = 'status';
$vals[] = $status;
}
if (column_exists($pdo,'admins','avatar_url')) {
$fields[] = 'avatar_url';
$vals[] = $urow['avatar_url'] ?? null;
}
if (column_exists($pdo,'admins','bio')) {
$fields[] = 'bio';
$vals[] = $urow['bio'] ?? null;
}
if (column_exists($pdo,'admins','allowed_sections') && $role === 'seller') {
$fields[] = 'allowed_sections';
$vals[] = $defaultSellerSections;
}

$placeholders = implode(',', array_fill(0, count($fields), '?'));
$pdo->prepare("INSERT INTO admins (" . implode(',', $fields) . ") VALUES ($placeholders)")
->execute($vals);
}
} else {
$pdo->prepare("DELETE FROM admins WHERE id=?")->execute([$uid]);
}
}
}

header('Location: dashboard.php?section=users');
exit;
}

if ($action === 'toggle_block') {
if (!mr_can_manage_users()) {
http_response_code(403);
exit('Access denied');
}

$uid = (int)($_POST['user_id'] ?? 0);
$status = trim((string)($_POST['status'] ?? 'active'));
$new = $status === 'active' ? 'blocked' : 'active';

if ($uid > 0) {
$st = $pdo->prepare("SELECT id, role FROM users WHERE id=? LIMIT 1");
$st->execute([$uid]);
$targetUser = $st->fetch();

if ($targetUser) {
$pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new, $uid]);

if (table_exists($pdo, 'admins') && column_exists($pdo, 'admins', 'status')) {
$pdo->prepare("UPDATE admins SET status=? WHERE id=?")->execute([$new, $uid]);
}
}
}

header('Location: dashboard.php?section=users');
exit;
}

if ($action === 'delete_user') {
if (!mr_can_manage_users()) {
http_response_code(403);
exit('Access denied');
}

$uid = (int)($_POST['user_id'] ?? 0);

if ($uid > 0 && $uid !== $admin_id) {
try {
$pdo->beginTransaction();

try { $pdo->prepare("DELETE FROM wallets WHERE user_id=?")->execute([$uid]); } catch (Throwable $e) {}
try { $pdo->prepare("DELETE FROM transactions WHERE user_id=?")->execute([$uid]); } catch (Throwable $e) {}
try { $pdo->prepare("DELETE FROM deposit_requests WHERE user_id=?")->execute([$uid]); } catch (Throwable $e) {}
try { $pdo->prepare("DELETE FROM withdraw_requests WHERE user_id=?")->execute([$uid]); } catch (Throwable $e) {}
try { $pdo->prepare("DELETE FROM ticket_messages WHERE sender_id=? AND sender='user'")->execute([$uid]); } catch (Throwable $e) {}
try { $pdo->prepare("DELETE FROM tickets WHERE {$ticket_owner_col}=?")->execute([$uid]); } catch (Throwable $e) {}
try { $pdo->prepare("DELETE FROM admins WHERE id=?")->execute([$uid]); } catch (Throwable $e) {}
try { $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]); } catch (Throwable $e) {}

$pdo->commit();
} catch (Throwable $e) {
if ($pdo->inTransaction()) $pdo->rollBack();
}
}

header('Location: dashboard.php?section=users');
exit;
}

if ($action === 'adjust_balance') {
if (!can_adjust_balance()) {
http_response_code(403);
exit('Access denied');
}

$uid = (int)($_POST['user_id'] ?? 0);
$mode = trim((string)($_POST['mode'] ?? 'add'));
$amount = (float)($_POST['amount'] ?? 0);
$reason = trim((string)($_POST['reason'] ?? ''));

if ($uid <= 0 || $amount <= 0) {
header('Location: dashboard.php?section=users');
exit;
}

$mode = $mode === 'deduct' ? 'deduct' : 'add';

try {
$pdo->beginTransaction();

$walletStmt = $pdo->prepare("SELECT balance_usdt FROM wallets WHERE user_id=? FOR UPDATE");
$walletStmt->execute([$uid]);
$wallet = $walletStmt->fetch();

if (!$wallet) {
$pdo->prepare("INSERT INTO wallets (user_id,balance_mb,balance_usdt,hold_usdt) VALUES (?,?,?,?)")
->execute([$uid, 0, 0, 0]);
$currentBalance = 0;
} else {
$currentBalance = (float)($wallet['balance_usdt'] ?? 0);
}

if ($mode === 'deduct' && $currentBalance < $amount) {
throw new Exception('Insufficient balance');
}

if ($mode === 'add') {
$pdo->prepare("UPDATE wallets SET balance_usdt = balance_usdt + ? WHERE user_id=?")
->execute([$amount, $uid]);

$txReason = $reason !== '' ? $reason : 'Admin balance added';

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")
->execute([$uid, $admin_id, 'credit', 'usdt', $amount, $txReason]);
}

if ($mode === 'deduct') {
$upd = $pdo->prepare("UPDATE wallets SET balance_usdt = balance_usdt - ? WHERE user_id=? AND balance_usdt >= ?");
$upd->execute([$amount, $uid, $amount]);

if ($upd->rowCount() !== 1) {
throw new Exception('Insufficient balance');
}

$txReason = $reason !== '' ? $reason : 'Admin balance deducted';

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")
->execute([$uid, $admin_id, 'debit', 'usdt', $amount, $txReason]);
}

$pdo->commit();
} catch (Throwable $e) {
if ($pdo->inTransaction()) $pdo->rollBack();
}

header('Location: dashboard.php?section=users');
exit;
}

if ($action === 'reset_password') {
if (!mr_can_manage_users()) {
http_response_code(403);
exit('Access denied');
}

$uid = (int)($_POST['user_id'] ?? 0);
$newpass = trim($_POST['newpass'] ?? '');
if ($uid && $newpass !== '') {
$hash = password_hash($newpass, PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash,$uid]);
if (table_exists($pdo,'admins')) {
try { $pdo->prepare("UPDATE admins SET password_hash=? WHERE id=?")->execute([$hash,$uid]); } catch (Throwable $e) {}
}
}
header('Location: dashboard.php?section=users');
exit;
}

if ($action === 'reply_ticket') {
if (!mr_can_manage_tickets()) {
http_response_code(403);
exit('Access denied');
}

$tid = (int)($_POST['ticket_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
$image_path = upload_file_path($_FILES['image'] ?? null, 'tickets', 'ticket_' . $tid . '_admin_' . $admin_id, ['jpg','jpeg','png','webp','gif']);
if ($tid && ($message !== '' || $image_path)) {
$chk = $pdo->prepare("SELECT status FROM tickets WHERE id=? LIMIT 1");
$chk->execute([$tid]);
$st = (string)($chk->fetchColumn() ?: '');
if ($st !== 'closed') {
$pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender, sender_id, message, image_url) VALUES (?,?,?,?,?)")
->execute([$tid, 'admin', $admin_id, $message, $image_path]);
$pdo->prepare("UPDATE tickets SET status='pending' WHERE id=?")->execute([$tid]);
}
}
header('Location: dashboard.php?section=tickets&ticket_id=' . $tid);
exit;
}

if ($action === 'close_ticket') {
if (!mr_can_manage_tickets()) {
http_response_code(403);
exit('Access denied');
}

$tid = (int)($_POST['ticket_id'] ?? 0);
if ($tid) $pdo->prepare("UPDATE tickets SET status='closed', closed_at=NOW() WHERE id=?")->execute([$tid]);
header('Location: dashboard.php?section=tickets&ticket_id=' . $tid);
exit;
}

if ($action === 'open_ticket') {
if (!mr_can_manage_tickets()) {
http_response_code(403);
exit('Access denied');
}

$tid = (int)($_POST['ticket_id'] ?? 0);
if ($tid) $pdo->prepare("UPDATE tickets SET status='open', closed_at=NULL WHERE id=?")->execute([$tid]);
header('Location: dashboard.php?section=tickets&ticket_id=' . $tid);
exit;
}

if ($action === 'seller_new_ticket' && mr_is_seller()) {
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$image_path = upload_file_path($_FILES['image'] ?? null, 'tickets', 'ticket_new_user_' . $admin_id, ['jpg','jpeg','png','webp','gif']);

if ($subject !== '' && ($message !== '' || $image_path)) {
try {
$pdo->beginTransaction();

if ($ticket_has_owner_role) {
$pdo->prepare("INSERT INTO tickets ({$ticket_owner_col}, owner_role, subject, status) VALUES (?,?,?, 'open')")
->execute([$admin_id, 'admin', $subject]);
} else {
$pdo->prepare("INSERT INTO tickets ({$ticket_owner_col}, subject, status) VALUES (?,?, 'open')")
->execute([$admin_id, $subject]);
}

$newTid = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender, sender_id, message, image_url) VALUES (?,?,?,?,?)")
->execute([$newTid, 'user', $admin_id, $message, $image_path]);

$pdo->commit();

header('Location: dashboard.php?section=support&ticket_id=' . $newTid);
exit;
} catch (Throwable $e) {
if ($pdo->inTransaction()) $pdo->rollBack();
$err = $is_ar ? 'تعذر فتح التذكرة' : 'Could not open ticket';
$section = 'support';
}
} else {
$err = $is_ar ? 'اكتب عنوان ورسالة أو اختر صورة' : 'Write subject and message or choose an image';
$section = 'support';
}
}

if ($action === 'seller_reply_ticket' && mr_is_seller()) {
$seller_support_ticket_id = (int)($_GET['ticket_id'] ?? $_POST['ticket_id'] ?? 0);
$message = trim((string)($_POST['message'] ?? ''));
$image_path = upload_file_path($_FILES['image'] ?? null, 'tickets', 'ticket_' . $seller_support_ticket_id . '_user_' . $admin_id, ['jpg','jpeg','png','webp','gif']);

if ($seller_support_ticket_id > 0 && ($message !== '' || $image_path)) {
if ($ticket_has_owner_role) {
$st = $pdo->prepare("SELECT * FROM tickets WHERE id=? AND {$ticket_owner_col}=? AND owner_role='admin' LIMIT 1");
$st->execute([$seller_support_ticket_id, $admin_id]);
} else {
$st = $pdo->prepare("SELECT * FROM tickets WHERE id=? AND {$ticket_owner_col}=? LIMIT 1");
$st->execute([$seller_support_ticket_id, $admin_id]);
}

$seller_active_ticket = $st->fetch();

if (!$seller_active_ticket) {
$err = $is_ar ? 'التذكرة غير موجودة' : 'Ticket not found';
$section = 'support';
} elseif (($seller_active_ticket['status'] ?? '') === 'closed') {
$err = $is_ar ? 'تم إغلاق هذه التذكرة' : 'This ticket is closed';
$section = 'support';
} else {
$pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender, sender_id, message, image_url) VALUES (?,?,?,?,?)")
->execute([$seller_support_ticket_id, 'user', $admin_id, $message, $image_path]);

$pdo->prepare("UPDATE tickets SET status='open' WHERE id=?")->execute([$seller_support_ticket_id]);

header('Location: dashboard.php?section=support&ticket_id=' . $seller_support_ticket_id);
exit;
}
}
}

if ($action === 'add_product') {
if (!mr_can_manage_products()) {
http_response_code(403);
exit('Access denied');
}

$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$price_usdt = (float)($_POST['price_usdt'] ?? 0);
$stock_type = trim((string)($_POST['stock_type'] ?? 'email'));

$allowed_stock_types = [
'email',
'email_2fa',
'email_access_mail',
'email_2fa_access_mail',
'rdp'
];

if (!in_array($stock_type, $allowed_stock_types, true)) {
$stock_type = 'email';
}
$description = trim($_POST['description'] ?? '');
$bulk = trim($_POST['bulk'] ?? '');
$image_path = upload_file_path($_FILES['image'] ?? null, '', 'prod', ['jpg','jpeg','png','webp']);

if ($name !== '') {
$current_admin_id = (int)($_SESSION['admin_id'] ?? 0);
$seller_id = $current_admin_id;

$stmt = $pdo->prepare("INSERT INTO products
(seller_id, name, category, price_mb, price_usdt, currency_mode, stock_type, image_url, description, is_active)
VALUES (?,?,?,?,?,?,?,?,?,1)");
$stmt->execute([$seller_id, $name, $category ?: null, 0, $price_usdt, 'usdt', $stock_type, $image_path, $description ?: null]);
$product_id = (int)$pdo->lastInsertId();

if ($bulk !== '') {
$lines = preg_split('/\r\n|\r|\n/', $bulk);
foreach ($lines as $line) {
$line = trim($line);
if ($line === '') continue;

if ($stock_type === 'rdp') {
$parts = array_map('trim', explode('|', $line));
if (count($parts) === 3) {
$pdo->prepare("INSERT INTO product_stock (product_id,type,host,user,pass) VALUES (?,?,?,?,?)")
->execute([$product_id, 'rdp', $parts[0], $parts[1], $parts[2]]);
}
}

elseif ($stock_type === 'email') {
$parts = array_map('trim', explode(':', $line));
if (count($parts) === 2) {
$pdo->prepare("INSERT INTO product_stock (product_id,type,email,pass) VALUES (?,?,?,?)")
->execute([$product_id, 'email', $parts[0], $parts[1]]);
}
}

elseif ($stock_type === 'email_2fa') {
$parts = array_map('trim', explode(':', $line));
if (count($parts) === 3) {
$pdo->prepare("INSERT INTO product_stock (product_id,type,email,pass,twofa_code) VALUES (?,?,?,?,?)")
->execute([$product_id, 'email_2fa', $parts[0], $parts[1], $parts[2]]);
}
}

elseif ($stock_type === 'email_access_mail') {
$parts = array_map('trim', explode(':', $line));
if (count($parts) === 4) {
$pdo->prepare("INSERT INTO product_stock (product_id,type,email,pass,access_email,access_pass) VALUES (?,?,?,?,?,?)")
->execute([$product_id, 'email_access_mail', $parts[0], $parts[1], $parts[2], $parts[3]]);
}
}

elseif ($stock_type === 'email_2fa_access_mail') {
$parts = array_map('trim', explode(':', $line));
if (count($parts) === 5) {
$pdo->prepare("INSERT INTO product_stock (product_id,type,email,pass,twofa_code,access_email,access_pass) VALUES (?,?,?,?,?,?,?)")
->execute([$product_id, 'email_2fa_access_mail', $parts[0], $parts[1], $parts[2], $parts[3], $parts[4]]);
}
}
}
}
}
header('Location: dashboard.php?section=products');
exit;
}

if ($action === 'update_product') {
if (!mr_can_manage_products()) {
http_response_code(403);
exit('Access denied');
}

$pid = (int)($_POST['product_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$price_usdt = (float)($_POST['price_usdt'] ?? 0);
$stock_type = trim((string)($_POST['stock_type'] ?? 'email'));

$allowed_stock_types = [
'email',
'email_2fa',
'email_access_mail',
'email_2fa_access_mail',
'rdp'
];

if (!in_array($stock_type, $allowed_stock_types, true)) {
$stock_type = 'email';
}
$description = trim($_POST['description'] ?? '');
$is_active = !empty($_POST['is_active']) ? 1 : 0;

if ($pid && $name !== '') {
if (mr_is_seller()) {
$own = $pdo->prepare("SELECT id FROM products WHERE id=? AND seller_id=? LIMIT 1");
$own->execute([$pid, $admin_id]);
if (!$own->fetch()) {
http_response_code(403);
exit('Access denied');
}
}

$new_img = upload_file_path($_FILES['image'] ?? null, '', 'prod_edit_' . $pid, ['jpg','jpeg','png','webp']);

if ($new_img) {
$pdo->prepare("UPDATE products
SET name=?, category=?, price_mb=0, price_usdt=?, currency_mode='usdt', stock_type=?, description=?, is_active=?, image_url=?
WHERE id=?")
->execute([$name, $category ?: null, $price_usdt, $stock_type, $description ?: null, $is_active, $new_img, $pid]);
} else {
if ($new_img) {
$stmt = $pdo->prepare("UPDATE products
SET name=?, category=?, price_mb=0, price_usdt=?, currency_mode='usdt', stock_type=?, description=?, is_active=?, image_url=?
WHERE id=?");
$ok = $stmt->execute([$name, $category ?: null, $price_usdt, $stock_type, $description ?: null, $is_active, $new_img, $pid]);
} else {
$stmt = $pdo->prepare("UPDATE products
SET name=?, category=?, price_mb=0, price_usdt=?, currency_mode='usdt', stock_type=?, description=?, is_active=?
WHERE id=?");
$ok = $stmt->execute([$name, $category ?: null, $price_usdt, $stock_type, $description ?: null, $is_active, $pid]);
}

if (!$ok) {
die('update failed');
}
}
}
header('Location: dashboard.php?section=products&edit_product_id=' . $pid);
exit;
}

if ($action === 'enable_product') {
if (!mr_can_manage_products()) {
http_response_code(403);
exit('Access denied');
}

$pid = (int)($_POST['product_id'] ?? 0);
if ($pid > 0) {
if (mr_is_seller()) {
$own = $pdo->prepare("SELECT id FROM products WHERE id=? AND seller_id=? LIMIT 1");
$own->execute([$pid, $admin_id]);
if (!$own->fetch()) {
http_response_code(403);
exit('Access denied');
}
}
$pdo->prepare("UPDATE products SET is_active=1 WHERE id=?")->execute([$pid]);
}
header('Location: dashboard.php?section=products');
exit;
}

if ($action === 'disable_product') {
if (!mr_can_manage_products()) {
http_response_code(403);
exit('Access denied');
}

$pid = (int)($_POST['product_id'] ?? 0);
if ($pid > 0) {
if (mr_is_seller()) {
$own = $pdo->prepare("SELECT id FROM products WHERE id=? AND seller_id=? LIMIT 1");
$own->execute([$pid, $admin_id]);
if (!$own->fetch()) {
http_response_code(403);
exit('Access denied');
}
}
$pdo->prepare("UPDATE products SET is_active=0 WHERE id=?")->execute([$pid]);
}
header('Location: dashboard.php?section=products');
exit;
}

if ($action === 'delete_product' || $action === 'force_delete_product') {
if (!mr_can_manage_products()) {
http_response_code(403);
exit('Access denied');
}

$pid = (int)($_POST['product_id'] ?? 0);

if ($pid > 0) {
try {
$pdo->beginTransaction();

if (mr_is_seller()) {
$own = $pdo->prepare("SELECT id FROM products WHERE id=? AND seller_id=? LIMIT 1");
$own->execute([$pid, $admin_id]);
if (!$own->fetch()) {
if ($pdo->inTransaction()) $pdo->rollBack();
http_response_code(403);
exit('Access denied');
}
}

$check = $pdo->prepare("SELECT id, image_url FROM products WHERE id=? LIMIT 1");
$check->execute([$pid]);
$productRow = $check->fetch();

if ($productRow) {
$pdo->prepare("DELETE FROM order_items WHERE product_id=?")->execute([$pid]);
$pdo->prepare("DELETE FROM product_stock WHERE product_id=?")->execute([$pid]);
$pdo->prepare("DELETE FROM products WHERE id=? LIMIT 1")->execute([$pid]);

if (!empty($productRow['image_url'])) {
$imgFile = __DIR__ . '/../' . ltrim((string)$productRow['image_url'], '/');
if (is_file($imgFile)) @unlink($imgFile);
}

$pdo->commit();
$msg = $action === 'force_delete_product'
? ($is_ar ? 'تم حذف المنتج نهائيًا' : 'Product deleted permanently')
: ($is_ar ? 'تم حذف المنتج' : 'Product deleted successfully');
} else {
if ($pdo->inTransaction()) $pdo->rollBack();
$err = $is_ar ? 'المنتج غير موجود' : 'Product not found';
}
} catch (Throwable $e) {
if ($pdo->inTransaction()) $pdo->rollBack();
$err = $is_ar ? 'فشل حذف المنتج: ' . $e->getMessage() : 'Delete failed: ' . $e->getMessage();
}
}

$section = 'products';
}

if ($action === 'add_stock') {
if (!mr_can_manage_products()) {
http_response_code(403);
exit('Access denied');
}

$pid = (int)($_POST['product_id'] ?? 0);
$bulk = trim($_POST['bulk'] ?? '');
$stock_type = trim((string)($_POST['stock_type'] ?? 'email'));

$allowed_stock_types = [
'email',
'email_2fa',
'email_access_mail',
'email_2fa_access_mail',
'rdp'
];

if (!in_array($stock_type, $allowed_stock_types, true)) {
$stock_type = 'email';
}

if ($pid && $bulk !== '') {
if (mr_is_seller()) {
$own = $pdo->prepare("SELECT id FROM products WHERE id=? AND seller_id=? LIMIT 1");
$own->execute([$pid, $admin_id]);
if (!$own->fetch()) {
http_response_code(403);
exit('Access denied');
}
}

$added = 0;
$failed = 0;

$lines = preg_split('/\r\n|\r|\n/', $bulk);
foreach ($lines as $line) {
$line = trim($line);
if ($line === '') continue;

$inserted = false;

if ($stock_type === 'rdp') {
$parts = array_map('trim', explode('|', $line));
if (count($parts) === 3) {
$inserted = $pdo->prepare("INSERT INTO product_stock (product_id,type,host,user,pass) VALUES (?,?,?,?,?)")
->execute([$pid, 'rdp', $parts[0], $parts[1], $parts[2]]);
}
} elseif ($stock_type === 'email') {
$parts = array_map('trim', explode(':', $line));
if (count($parts) === 2) {
$inserted = $pdo->prepare("INSERT INTO product_stock (product_id,type,email,pass) VALUES (?,?,?,?)")
->execute([$pid, 'email', $parts[0], $parts[1]]);
}
} elseif ($stock_type === 'email_2fa') {
$parts = array_map('trim', explode(':', $line));
if (count($parts) === 3) {
$inserted = $pdo->prepare("INSERT INTO product_stock (product_id,type,email,pass,twofa_code) VALUES (?,?,?,?,?)")
->execute([$pid, 'email_2fa', $parts[0], $parts[1], $parts[2]]);
}
} elseif ($stock_type === 'email_access_mail') {
$parts = array_map('trim', explode(':', $line));
if (count($parts) === 4) {
$inserted = $pdo->prepare("INSERT INTO product_stock (product_id,type,email,pass,access_email,access_pass) VALUES (?,?,?,?,?,?)")
->execute([$pid, 'email_access_mail', $parts[0], $parts[1], $parts[2], $parts[3]]);
}
} elseif ($stock_type === 'email_2fa_access_mail') {
$parts = array_map('trim', explode(':', $line));
if (count($parts) === 5) {
$inserted = $pdo->prepare("INSERT INTO product_stock (product_id,type,email,pass,twofa_code,access_email,access_pass) VALUES (?,?,?,?,?,?,?)")
->execute([$pid, 'email_2fa_access_mail', $parts[0], $parts[1], $parts[2], $parts[3], $parts[4]]);
}
}

if ($inserted) {
$added++;
} else {
$failed++;
}
}
}

header('Location: dashboard.php?section=products&stock_product_id=' . $pid);
exit;
}
if ($action === 'delete_stock') {
if (!mr_can_manage_products()) {
http_response_code(403);
exit('Access denied');
}

$stock_id = (int)($_POST['stock_id'] ?? 0);
$pid = (int)($_POST['product_id'] ?? 0);

if ($pid && mr_is_seller()) {
$own = $pdo->prepare("SELECT id FROM products WHERE id=? AND seller_id=? LIMIT 1");
$own->execute([$pid, $admin_id]);
if (!$own->fetch()) {
http_response_code(403);
exit('Access denied');
}
}

if ($stock_id) {
$pdo->prepare("DELETE FROM product_stock WHERE id=? AND product_id=? AND status='available'")->execute([$stock_id, $pid]);
}
header('Location: dashboard.php?section=products&stock_product_id=' . $pid);
exit;
}

if ($action === 'create_my_deposit') {
if (!admin_can_view_own_finance_tabs()) {
http_response_code(403);
exit('Access denied');
}

$payment_method_id = (int)($_POST['payment_method_id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);
$txid = trim((string)($_POST['txid'] ?? ''));

$proof_image = upload_file_path($_FILES['proof_image'] ?? null, 'deposits', 'dep_' . $admin_id, ['jpg','jpeg','png','webp']);

$network = '';
$currency = 'usdt';

if ($payment_method_id > 0) {
$pmStmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id=? AND is_active=1 LIMIT 1");
$pmStmt->execute([$payment_method_id]);
$pm = $pmStmt->fetch();

if ($pm) {
$network = trim((string)($pm['network'] ?? ''));
$currency = trim((string)($pm['currency'] ?? 'usdt'));
if ($currency === '') $currency = 'usdt';
}
}

if ($payment_method_id <= 0 || $network === '' || $amount <= 0) {
$err = $is_ar ? 'اختر وسيلة الدفع وأدخل المبلغ' : 'Select payment method and amount';
$section = 'my_deposits';
} else {
if (column_exists($pdo, 'deposit_requests', 'payment_method_id') && column_exists($pdo, 'deposit_requests', 'currency')) {
$pdo->prepare("INSERT INTO deposit_requests (user_id, payment_method_id, currency, network, amount, txid, proof_image, status, created_at) VALUES (?,?,?,?,?,?,?,'pending',NOW())")
->execute([$admin_id, $payment_method_id, $currency, $network, $amount, $txid ?: null, $proof_image]);
} elseif (column_exists($pdo, 'deposit_requests', 'currency')) {
$pdo->prepare("INSERT INTO deposit_requests (user_id, currency, network, amount, txid, proof_image, status, created_at) VALUES (?,?,?,?,?,?,'pending',NOW())")
->execute([$admin_id, $currency, $network, $amount, $txid ?: null, $proof_image]);
} else {
$pdo->prepare("INSERT INTO deposit_requests (user_id, network, amount, txid, proof_image, status, created_at) VALUES (?,?,?,?,?,'pending',NOW())")
->execute([$admin_id, $network, $amount, $txid ?: null, $proof_image]);
}

header('Location: dashboard.php?section=my_deposits');
exit;
}
}

if ($action === 'create_my_withdraw') {
if (!admin_can_view_own_finance_tabs()) {
http_response_code(403);
exit('Access denied');
}

$payment_method_id = (int)($_POST['payment_method_id'] ?? 0);
$withdraw_value = trim((string)($_POST['withdraw_value'] ?? ''));
$amount = (float)($_POST['amount'] ?? 0);
$note = trim((string)($_POST['note'] ?? ''));

$network = '';

if ($payment_method_id > 0) {
$pmStmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id=? AND is_active=1 LIMIT 1");
$pmStmt->execute([$payment_method_id]);
$pm = $pmStmt->fetch();

if ($pm) {
$network = trim((string)($pm['network'] ?? ''));
}
}

$walletStmt = $pdo->prepare("SELECT balance_usdt FROM wallets WHERE user_id=? LIMIT 1");
$walletStmt->execute([$admin_id]);
$w = $walletStmt->fetch();
$balance = (float)($w['balance_usdt'] ?? 0);

if ($payment_method_id <= 0 || $network === '' || $withdraw_value === '' || $amount <= 0) {
$err = $is_ar ? 'اختر وسيلة السحب وأدخل البيانات كاملة' : 'Select withdraw method and fill all fields';
$section = 'my_withdraws';
} elseif ($balance < $amount) {
$err = $is_ar ? 'الرصيد غير كافٍ' : 'Insufficient balance';
$section = 'my_withdraws';
} else {
$pdo->beginTransaction();

try {
$stmt = $pdo->prepare("UPDATE wallets SET balance_usdt = balance_usdt - ?, hold_usdt = hold_usdt + ? WHERE user_id=? AND balance_usdt >= ?");
$stmt->execute([$amount, $amount, $admin_id, $amount]);

if ($stmt->rowCount() !== 1) {
throw new Exception('Wallet update failed');
}

if (column_exists($pdo, 'withdraw_requests', 'payment_method_id')) {
$pdo->prepare("INSERT INTO withdraw_requests (user_id, payment_method_id, network, address, amount, note, status, created_at) VALUES (?,?,?,?,?,?,'pending',NOW())")
->execute([$admin_id, $payment_method_id, $network, $withdraw_value, $amount, $note ?: null]);
} else {
$pdo->prepare("INSERT INTO withdraw_requests (user_id, network, address, amount, note, status, created_at) VALUES (?,?,?,?,?,'pending',NOW())")
->execute([$admin_id, $network, $withdraw_value, $amount, $note ?: null]);
}

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")->execute([
$admin_id,
null,
'debit',
'usdt',
$amount,
'Seller withdraw request pending'
]);

$pdo->commit();
header('Location: dashboard.php?section=my_withdraws');
exit;
} catch (Throwable $e) {
if ($pdo->inTransaction()) $pdo->rollBack();
$err = $is_ar ? 'فشل إنشاء طلب السحب' : 'Could not create withdraw request';
$section = 'my_withdraws';
}
}
}

if ($action === 'approve_deposit' || $action === 'reject_deposit') {
if (!mr_can_manage_deposits()) {
http_response_code(403);
exit('Access denied');
}

$id = (int)($_POST['id'] ?? 0);
$note = trim($_POST['note'] ?? '');

$reqStmt = $pdo->prepare("SELECT * FROM deposit_requests WHERE id=? LIMIT 1");
$reqStmt->execute([$id]);
$r = $reqStmt->fetch();

if ($r && ($r['status'] ?? '') === 'pending') {
if ($action === 'approve_deposit') {
$pdo->beginTransaction();

$pdo->prepare("UPDATE deposit_requests
SET status='approved', admin_note=?, reviewed_at=NOW(), reviewed_by=?
WHERE id=?")->execute([$note ?: null, $admin_id, $id]);

$pdo->prepare("UPDATE wallets SET balance_usdt = balance_usdt + ? WHERE user_id=?")
->execute([(float)$r['amount'], (int)$r['user_id']]);

$reason = 'Deposit approved';
if (!empty($r['network'])) $reason .= ' • ' . $r['network'];
if (!empty($r['txid'])) $reason .= ' • TXID: ' . $r['txid'];

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")->execute([
(int)$r['user_id'],
$admin_id,
'credit',
'usdt',
(float)$r['amount'],
$reason
]);

$pdo->commit();
}

if ($action === 'reject_deposit') {
$pdo->prepare("UPDATE deposit_requests
SET status='rejected', admin_note=?, reviewed_at=NOW(), reviewed_by=?
WHERE id=?")->execute([$note ?: null, $admin_id, $id]);

$reason = 'Deposit rejected';
if (!empty($r['network'])) $reason .= ' • ' . $r['network'];
if (!empty($r['txid'])) $reason .= ' • TXID: ' . $r['txid'];
if ($note) $reason .= ' • ' . $note;

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")->execute([
(int)$r['user_id'],
$admin_id,
'note',
'usdt',
0,
$reason
]);
}
}
header('Location: dashboard.php?section=deposits');
exit;
}

if ($action === 'approve_withdraw' || $action === 'reject_withdraw') {
if (!mr_can_manage_withdraws()) {
http_response_code(403);
exit('Access denied');
}

$id = (int)($_POST['id'] ?? 0);
$note = trim($_POST['note'] ?? '');

$reqStmt = $pdo->prepare("SELECT * FROM withdraw_requests WHERE id=? LIMIT 1");
$reqStmt->execute([$id]);
$r = $reqStmt->fetch();

if ($r && ($r['status'] ?? '') === 'pending') {
$userId = (int)$r['user_id'];
$amount = (float)$r['amount'];

$pdo->beginTransaction();

try {
$walletStmt = $pdo->prepare("SELECT balance_usdt, hold_usdt FROM wallets WHERE user_id=? FOR UPDATE");
$walletStmt->execute([$userId]);
$wallet = $walletStmt->fetch();

if (!$wallet) throw new Exception('Wallet not found');

$currentHold = (float)($wallet['hold_usdt'] ?? 0);
$deductHold = min($currentHold, $amount);

if ($action === 'approve_withdraw') {
$pdo->prepare("UPDATE withdraw_requests
SET status='approved', admin_note=?, reviewed_at=NOW(), reviewed_by=?
WHERE id=?")->execute([$note ?: null, $admin_id, $id]);

$pdo->prepare("UPDATE wallets
SET hold_usdt = GREATEST(hold_usdt - ?, 0)
WHERE user_id=?")->execute([$deductHold, $userId]);

$reason = 'Withdraw approved';
if (!empty($r['network'])) $reason .= ' • ' . $r['network'];
if (!empty($r['address'])) $reason .= ' • ' . $r['address'];
if ($note !== '') $reason .= ' • ' . $note;

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")->execute([
$userId,
$admin_id,
'note',
'usdt',
0,
$reason
]);
}

if ($action === 'reject_withdraw') {
$pdo->prepare("UPDATE withdraw_requests
SET status='rejected', admin_note=?, reviewed_at=NOW(), reviewed_by=?
WHERE id=?")->execute([$note ?: null, $admin_id, $id]);

$pdo->prepare("UPDATE wallets
SET hold_usdt = GREATEST(hold_usdt - ?, 0),
balance_usdt = balance_usdt + ?
WHERE user_id=?")->execute([$deductHold, $deductHold, $userId]);

$reason = 'Withdraw rejected refund';
if (!empty($r['network'])) $reason .= ' • ' . $r['network'];
if (!empty($r['address'])) $reason .= ' • ' . $r['address'];
if ($note !== '') $reason .= ' • ' . $note;

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")->execute([
$userId,
$admin_id,
'credit',
'usdt',
$deductHold,
$reason
]);
}

$pdo->commit();
} catch (Throwable $e) {
if ($pdo->inTransaction()) $pdo->rollBack();
}
}
header('Location: dashboard.php?section=withdraws');
exit;
}

if ($action === 'save_general_settings') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}

setting_set($pdo, 'site_name', trim($_POST['site_name'] ?? 'Mr Beat Store'));
setting_set($pdo, 'site_lang_default', trim($_POST['site_lang_default'] ?? 'en'));
setting_set($pdo, 'payment_enabled', !empty($_POST['payment_enabled']) ? '1' : '0');
setting_set($pdo, 'announce_enabled', !empty($_POST['announce_enabled']) ? '1' : '0');
setting_set($pdo, 'announce_text', trim($_POST['announce_text'] ?? ''));
setting_set($pdo, 'theme_mode', trim($_POST['theme_mode'] ?? 'dark'));
setting_set($pdo, 'theme_preset', trim($_POST['theme_preset'] ?? 'account_modern'));
setting_set($pdo, 'welcome_bonus_enabled', !empty($_POST['welcome_bonus_enabled']) ? '1' : '0');
setting_set($pdo, 'welcome_bonus_amount', trim((string)($_POST['welcome_bonus_amount'] ?? '0')));
setting_set($pdo, 'seller_commission_enabled', !empty($_POST['seller_commission_enabled']) ? '1' : '0');
setting_set($pdo, 'seller_commission_percent', trim((string)($_POST['seller_commission_percent'] ?? '20')));

$icon_path = upload_file_path($_FILES['site_icon'] ?? null, 'site', 'site_icon', ['ico','png','jpg','jpeg','webp']);
if ($icon_path) {
setting_set($pdo, 'site_icon', $icon_path);
}
if ($icon_path) setting_set($pdo, 'site_icon', $icon_path);

header('Location: dashboard.php?section=settings');
exit;
}

if ($action === 'add_banner') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}

$title = trim($_POST['title'] ?? '');
$body = trim($_POST['body'] ?? '');
$sort_order = (int)($_POST['sort_order'] ?? 0);
if ($title !== '') {
$pdo->prepare("INSERT INTO banners (title, body, is_active, sort_order) VALUES (?,?,?,?)")
->execute([$title, $body ?: null, !empty($_POST['is_active']) ? 1 : 0, $sort_order]);
}
header('Location: dashboard.php?section=settings#banners');
exit;
}

if ($action === 'toggle_banner') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}
$id = (int)($_POST['id'] ?? 0);
if ($id) $pdo->prepare("UPDATE banners SET is_active = IF(is_active=1,0,1) WHERE id=?")->execute([$id]);
header('Location: dashboard.php?section=settings#banners');
exit;
}

if ($action === 'delete_banner') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}
$id = (int)($_POST['id'] ?? 0);
if ($id) $pdo->prepare("DELETE FROM banners WHERE id=?")->execute([$id]);
header('Location: dashboard.php?section=settings#banners');
exit;
}

if ($action === 'add_announcement') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}

$title = trim($_POST['title'] ?? '');
$body = trim($_POST['body'] ?? '');
$sort_order = (int)($_POST['sort_order'] ?? 0);
if ($title !== '') {
$pdo->prepare("INSERT INTO announcements (title, body, is_active, sort_order) VALUES (?,?,?,?)")
->execute([$title, $body ?: null, !empty($_POST['is_active']) ? 1 : 0, $sort_order]);
}
header('Location: dashboard.php?section=settings#announcements');
exit;
}

if ($action === 'toggle_announcement') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}
$id = (int)($_POST['id'] ?? 0);
if ($id) $pdo->prepare("UPDATE announcements SET is_active = IF(is_active=1,0,1) WHERE id=?")->execute([$id]);
header('Location: dashboard.php?section=settings#announcements');
exit;
}

if ($action === 'delete_announcement') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}
$id = (int)($_POST['id'] ?? 0);
if ($id) $pdo->prepare("DELETE FROM announcements WHERE id=?")->execute([$id]);
header('Location: dashboard.php?section=settings#announcements');
exit;
}

if ($action === 'add_contact_method') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}

$label = trim($_POST['label'] ?? '');
$value = trim($_POST['value'] ?? '');
$type = trim($_POST['type'] ?? 'link');
$sort_order = (int)($_POST['sort_order'] ?? 0);

if ($label !== '' && $value !== '') {
$pdo->prepare("INSERT INTO contact_methods (label, value, type, is_active, is_required, sort_order) VALUES (?,?,?,?,?,?)")
->execute([$label, $value, $type ?: 'link', !empty($_POST['is_active']) ? 1 : 0, !empty($_POST['is_required']) ? 1 : 0, $sort_order]);
}
header('Location: dashboard.php?section=settings#contacts');
exit;
}

if ($action === 'toggle_contact_method') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}
$id = (int)($_POST['id'] ?? 0);
if ($id) $pdo->prepare("UPDATE contact_methods SET is_active = IF(is_active=1,0,1) WHERE id=?")->execute([$id]);
header('Location: dashboard.php?section=settings#contacts');
exit;
}

if ($action === 'delete_contact_method') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}
$id = (int)($_POST['id'] ?? 0);
if ($id) $pdo->prepare("DELETE FROM contact_methods WHERE id=?")->execute([$id]);
header('Location: dashboard.php?section=settings#contacts');
exit;
}

if ($action === 'add_payment_method') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}

$method_type = trim((string)($_POST['method_type'] ?? 'crypto'));
$title = trim((string)($_POST['title'] ?? ''));
$currency = trim((string)($_POST['currency'] ?? 'USDT'));
$network = trim((string)($_POST['network'] ?? ''));
$address = trim((string)($_POST['address'] ?? ''));
$account_name = trim((string)($_POST['account_name'] ?? ''));
$sort_order = (int)($_POST['sort_order'] ?? 0);

$allowedTypes = ['crypto', 'binance_id', 'manual'];
if (!in_array($method_type, $allowedTypes, true)) {
$method_type = 'crypto';
}

if ($currency === '') {
$currency = 'USDT';
}

if ($network === '') {
if ($method_type === 'binance_id') {
$network = 'BINANCE';
} elseif ($method_type === 'manual') {
$network = 'MANUAL';
} else {
$network = 'TRC20';
}
}

if ($title === '') {
if ($method_type === 'binance_id') {
$title = 'Binance ID';
} elseif ($method_type === 'manual') {
$title = 'Manual Payment';
} else {
$title = $currency . ' - ' . $network;
}
}

if ($address !== '') {
$extra_fields = json_encode([
'note' => trim((string)($_POST['note'] ?? '')),
'label1' => trim((string)($_POST['label1'] ?? '')),
'value1' => trim((string)($_POST['value1'] ?? '')),
'label2' => trim((string)($_POST['label2'] ?? '')),
'value2' => trim((string)($_POST['value2'] ?? '')),
], JSON_UNESCAPED_UNICODE);

$hasMethodType = column_exists($pdo, 'payment_methods', 'method_type');
$hasTitle = column_exists($pdo, 'payment_methods', 'title');
$hasAccountName = column_exists($pdo, 'payment_methods', 'account_name');
$hasExtraFields = column_exists($pdo, 'payment_methods', 'extra_fields');

if ($hasMethodType && $hasTitle && $hasAccountName && $hasExtraFields) {
$pdo->prepare("INSERT INTO payment_methods (method_type, title, currency, network, address, account_name, extra_fields, is_active, sort_order)
VALUES (?,?,?,?,?,?,?,?,?)")
->execute([
$method_type,
$title,
$currency,
$network,
$address,
($account_name !== '' ? $account_name : ''),
$extra_fields,
!empty($_POST['is_active']) ? 1 : 0,
$sort_order
]);
} else {
$pdo->prepare("INSERT INTO payment_methods (currency,network,address,is_active,sort_order) VALUES (?,?,?,?,?)")
->execute([
$currency,
$network,
$address,
!empty($_POST['is_active']) ? 1 : 0,
$sort_order
]);
}
}

header('Location: dashboard.php?section=settings#payments');
exit;
}

if ($action === 'toggle_payment_method') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}
$id = (int)($_POST['id'] ?? 0);
if ($id) $pdo->prepare("UPDATE payment_methods SET is_active = IF(is_active=1,0,1) WHERE id=?")->execute([$id]);
header('Location: dashboard.php?section=settings#payments');
exit;
}

if ($action === 'delete_payment_method') {
if (!mr_can_manage_settings()) {
http_response_code(403);
exit('Access denied');
}
$id = (int)($_POST['id'] ?? 0);
if ($id) $pdo->prepare("DELETE FROM payment_methods WHERE id=?")->execute([$id]);
header('Location: dashboard.php?section=settings#payments');
exit;
}
}
$users_count = mr_can_manage_users()
? (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn()
: 0;

$products_count = mr_is_seller()
? (function() use ($pdo, $admin_id) {
$st = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id=?");
$st->execute([$admin_id]);
return (int)$st->fetchColumn();
})()
: (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

if (mr_is_seller()) {
$st = $pdo->prepare("
SELECT COUNT(*)
FROM product_stock ps
INNER JOIN products p ON p.id = ps.product_id
WHERE ps.status='available' AND p.seller_id=?
");
$st->execute([$admin_id]);
$stock_available = (int)$st->fetchColumn();
} else {
$stock_available = (int)$pdo->query("SELECT COUNT(*) FROM product_stock WHERE status='available'")->fetchColumn();
}

if (mr_is_seller()) {
$st = $pdo->prepare("
SELECT COUNT(DISTINCT oi.order_id)
FROM order_items oi
JOIN products p ON p.id = oi.product_id
WHERE p.seller_id = ?
");
$st->execute([$admin_id]);
$orders_count = (int)$st->fetchColumn();
} else {
$orders_count = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
}

$site_sales_usdt = (float)$pdo->query("SELECT COALESCE(SUM(total_usdt),0) FROM orders WHERE status='paid'")->fetchColumn();

$st = $pdo->prepare("
SELECT COALESCE(SUM(oi.qty * oi.unit_price_usdt),0)
FROM order_items oi
JOIN products p ON p.id = oi.product_id
JOIN orders o ON o.id = oi.order_id
WHERE o.status='paid' AND p.seller_id = ?
");
$st->execute([$admin_id]);
$my_sales_usdt = (float)$st->fetchColumn();

$total_usdt = mr_can_manage_users()
? (float)$pdo->query("SELECT COALESCE(SUM(balance_usdt),0) FROM wallets")->fetchColumn()
: $admin_wallet_usdt;

$total_hold = 0;
try {
$total_hold = mr_can_manage_users()
? (float)$pdo->query("SELECT COALESCE(SUM(hold_usdt),0) FROM wallets")->fetchColumn()
: $admin_hold_usdt;
} catch (Throwable $e) {
$total_hold = 0;
}

$pending_deposits = mr_can_manage_deposits()
? (int)$pdo->query("SELECT COUNT(*) FROM deposit_requests WHERE status='pending'")->fetchColumn()
: 0;

$pending_withdraws = 0;
try {
$pending_withdraws = mr_can_manage_withdraws()
? (int)$pdo->query("SELECT COUNT(*) FROM withdraw_requests WHERE status='pending'")->fetchColumn()
: 0;
} catch (Throwable $e) {}

$open_tickets = mr_can_manage_tickets()
? (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('open','pending')")->fetchColumn()
: 0;

$q = trim($_GET['q'] ?? '');
$users = [];
if (mr_can_manage_users()) {
if ($q !== '') {
if ($has_reviews) {
$stmt = $pdo->prepare("SELECT u.*, w.balance_usdt, w.hold_usdt,
COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.user_id = u.id AND r.is_like = 1),0) AS likes_count,
COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.user_id = u.id AND r.is_like = 0),0) AS dislikes_count
FROM users u
LEFT JOIN wallets w ON w.user_id=u.id
WHERE u.username LIKE ? OR u.email LIKE ?
ORDER BY u.id DESC LIMIT 100");
} else {
$stmt = $pdo->prepare("SELECT u.*, w.balance_usdt, w.hold_usdt, 0 AS likes_count, 0 AS dislikes_count
FROM users u
LEFT JOIN wallets w ON w.user_id=u.id
WHERE u.username LIKE ? OR u.email LIKE ?
ORDER BY u.id DESC LIMIT 100");
}
$like = '%' . $q . '%';
$stmt->execute([$like,$like]);
$users = $stmt->fetchAll();
} else {
if ($has_reviews) {
$users = $pdo->query("SELECT u.*, w.balance_usdt, w.hold_usdt,
COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.user_id = u.id AND r.is_like = 1),0) AS likes_count,
COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.user_id = u.id AND r.is_like = 0),0) AS dislikes_count
FROM users u
LEFT JOIN wallets w ON w.user_id=u.id
ORDER BY u.id DESC LIMIT 50")->fetchAll();
} else {
$users = $pdo->query("SELECT u.*, w.balance_usdt, w.hold_usdt, 0 AS likes_count, 0 AS dislikes_count
FROM users u
LEFT JOIN wallets w ON w.user_id=u.id
ORDER BY u.id DESC LIMIT 50")->fetchAll();
}
}
}

$ticket_id = (int)($_GET['ticket_id'] ?? 0);
$tickets = [];
if (mr_can_manage_tickets()) {
if ($ticket_has_owner_role) {
$sql = "
SELECT t.*,
COALESCE(u.username, a.username, 'Unknown') AS username
FROM tickets t
LEFT JOIN users u ON (t.{$ticket_owner_col} = u.id AND t.owner_role = 'user')
LEFT JOIN admins a ON (t.{$ticket_owner_col} = a.id AND t.owner_role = 'admin')
ORDER BY t.id DESC
LIMIT 80
";
$tickets = $pdo->query($sql)->fetchAll();
} else {
$tickets = $pdo->query("
SELECT t.*, u.username
FROM tickets t
LEFT JOIN users u ON u.id=t.{$ticket_owner_col}
ORDER BY t.id DESC
LIMIT 80
")->fetchAll();
}
}

$active_ticket = null;
$ticket_messages = [];
if ($ticket_id && mr_can_manage_tickets()) {
if ($ticket_has_owner_role) {
$sql = "
SELECT t.*,
COALESCE(u.username, a.username, 'Unknown') AS username
FROM tickets t
LEFT JOIN users u ON (t.{$ticket_owner_col} = u.id AND t.owner_role = 'user')
LEFT JOIN admins a ON (t.{$ticket_owner_col} = a.id AND t.owner_role = 'admin')
WHERE t.id=?
LIMIT 1
";
$t = $pdo->prepare($sql);
$t->execute([$ticket_id]);
} else {
$t = $pdo->prepare("SELECT t.*, u.username FROM tickets t LEFT JOIN users u ON u.id=t.{$ticket_owner_col} WHERE t.id=? LIMIT 1");
$t->execute([$ticket_id]);
}

$active_ticket = $t->fetch();

if ($active_ticket) {
$m = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY id ASC");
$m->execute([$ticket_id]);
$ticket_messages = $m->fetchAll();
}
}

$seller_support_ticket_id = (int)($_GET['ticket_id'] ?? 0);
$seller_tickets = [];
$seller_active_ticket = null;
$seller_ticket_messages = [];

if ($section === 'support' && mr_is_seller()) {
if ($ticket_has_owner_role) {
$st = $pdo->prepare("SELECT * FROM tickets WHERE {$ticket_owner_col}=? AND owner_role='admin' ORDER BY id DESC LIMIT 50");
$st->execute([$admin_id]);
} else {
$st = $pdo->prepare("SELECT * FROM tickets WHERE {$ticket_owner_col}=? ORDER BY id DESC LIMIT 50");
$st->execute([$admin_id]);
}
$seller_tickets = $st->fetchAll();

if ($seller_support_ticket_id > 0) {
if ($ticket_has_owner_role) {
$st = $pdo->prepare("SELECT * FROM tickets WHERE id=? AND {$ticket_owner_col}=? AND owner_role='admin' LIMIT 1");
$st->execute([$seller_support_ticket_id, $admin_id]);
} else {
$st = $pdo->prepare("SELECT * FROM tickets WHERE id=? AND {$ticket_owner_col}=? LIMIT 1");
$st->execute([$seller_support_ticket_id, $admin_id]);
}
$seller_active_ticket = $st->fetch();

if ($seller_active_ticket) {
$st = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY id ASC");
$st->execute([$seller_support_ticket_id]);
$seller_ticket_messages = $st->fetchAll();
}
}
}

if (mr_is_seller()) {
$st = $pdo->prepare("
SELECT p.*,
(SELECT COUNT(*) FROM product_stock WHERE product_id=p.id AND status='available') as available_stock,
(SELECT COUNT(*) FROM product_stock WHERE product_id=p.id AND status='sold') as sold_stock
FROM products p
WHERE p.seller_id = ?
ORDER BY p.id DESC
");
$st->execute([$admin_id]);
$products = $st->fetchAll();
} elseif (mr_can_manage_products()) {
$products = $pdo->query("
SELECT p.*,
(SELECT COUNT(*) FROM product_stock WHERE product_id=p.id AND status='available') as available_stock,
(SELECT COUNT(*) FROM product_stock WHERE product_id=p.id AND status='sold') as sold_stock
FROM products p
ORDER BY p.id DESC
")->fetchAll();
} else {
$products = [];
}

$edit_product_id = (int)($_GET['edit_product_id'] ?? 0);
$stock_product_id = (int)($_GET['stock_product_id'] ?? 0);
if (!$stock_product_id && isset($pid) && !empty($pid)) {
$stock_product_id = (int)$pid;
}
$edit_product = null;
if ($edit_product_id && mr_can_manage_products()) {
if (mr_is_seller()) {
$st = $pdo->prepare("SELECT * FROM products WHERE id=? AND seller_id=? LIMIT 1");
$st->execute([$edit_product_id, $admin_id]);
} else {
$st = $pdo->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
$st->execute([$edit_product_id]);
}
$edit_product = $st->fetch();
}

$stock_product = null;
$stock_rows = [];
if ($stock_product_id && mr_can_manage_products()) {
if (mr_is_seller()) {
$st = $pdo->prepare("SELECT * FROM products WHERE id=? AND seller_id=? LIMIT 1");
$st->execute([$stock_product_id, $admin_id]);
} else {
$st = $pdo->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
$st->execute([$stock_product_id]);
}
$stock_product = $st->fetch();

if ($stock_product) {
$ss = $pdo->prepare("SELECT * FROM product_stock WHERE product_id=? ORDER BY id DESC LIMIT 200");
$ss->execute([$stock_product_id]);
$stock_rows = $ss->fetchAll();
}
}

$deposit_rows = mr_can_manage_deposits()
? $pdo->query("
SELECT d.*, u.username
FROM deposit_requests d
JOIN users u ON u.id = d.user_id
ORDER BY d.id DESC
LIMIT 80
")->fetchAll()
: [];

$withdraw_rows = [];
if (mr_can_manage_withdraws()) {
try {
$withdraw_rows = $pdo->query("
SELECT w.*, u.username
FROM withdraw_requests w
JOIN users u ON u.id = w.user_id
ORDER BY w.id DESC
LIMIT 80
")->fetchAll();
} catch (Throwable $e) {}
}

$my_dep_page = max(1, (int)($_GET['dep_page'] ?? 1));
$my_wd_page = max(1, (int)($_GET['wd_page'] ?? 1));
$my_per_page = 5;

$my_dep_total = 0;
$my_wd_total = 0;
$my_dep_pages = 1;
$my_wd_pages = 1;
$my_deposit_rows = [];
$my_withdraw_rows = [];

if (admin_can_view_own_finance_tabs()) {
try {
$st = $pdo->prepare("SELECT COUNT(*) FROM deposit_requests WHERE user_id=?");
$st->execute([$admin_id]);
$my_dep_total = (int)$st->fetchColumn();
$my_dep_pages = max(1, (int)ceil($my_dep_total / $my_per_page));

if ($my_dep_page > $my_dep_pages) {
$my_dep_page = $my_dep_pages;
}

$my_dep_offset = ($my_dep_page - 1) * $my_per_page;

$st = $pdo->prepare("SELECT * FROM deposit_requests WHERE user_id=? ORDER BY id DESC LIMIT {$my_per_page} OFFSET {$my_dep_offset}");
$st->execute([$admin_id]);
$my_deposit_rows = $st->fetchAll();
} catch (Throwable $e) {}

try {
$st = $pdo->prepare("SELECT COUNT(*) FROM withdraw_requests WHERE user_id=?");
$st->execute([$admin_id]);
$my_wd_total = (int)$st->fetchColumn();
$my_wd_pages = max(1, (int)ceil($my_wd_total / $my_per_page));

if ($my_wd_page > $my_wd_pages) {
$my_wd_page = $my_wd_pages;
}

$my_wd_offset = ($my_wd_page - 1) * $my_per_page;

$st = $pdo->prepare("SELECT * FROM withdraw_requests WHERE user_id=? ORDER BY id DESC LIMIT {$my_per_page} OFFSET {$my_wd_offset}");
$st->execute([$admin_id]);
$my_withdraw_rows = $st->fetchAll();
} catch (Throwable $e) {}
}

$admin_txs_stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id=? ORDER BY id DESC LIMIT 20");
$admin_txs_stmt->execute([$admin_id]);
$admin_txs = $admin_txs_stmt->fetchAll();

$banners = [];
$announcements = [];
$contacts = [];
$payment_methods = [];
if (mr_can_manage_settings()) {
try { $banners = $pdo->query("SELECT * FROM banners ORDER BY sort_order ASC, id DESC")->fetchAll(); } catch (Throwable $e) {}
try { $announcements = $pdo->query("SELECT * FROM announcements ORDER BY sort_order ASC, id DESC")->fetchAll(); } catch (Throwable $e) {}
try { $contacts = $pdo->query("SELECT * FROM contact_methods ORDER BY sort_order ASC, id DESC")->fetchAll(); } catch (Throwable $e) {}
try { $payment_methods = $pdo->query("SELECT * FROM payment_methods ORDER BY sort_order ASC, id DESC")->fetchAll(); } catch (Throwable $e) { $payment_methods = []; }
}
$active_payment_methods = [];
if (admin_can_view_own_finance_tabs()) {
try {
$active_payment_methods = $pdo->query("SELECT * FROM payment_methods WHERE is_active=1 ORDER BY sort_order ASC, id DESC")->fetchAll();
} catch (Throwable $e) {
$active_payment_methods = [];
}
}
$avatar_src = '';
if (!empty($admin['avatar_url'])) {
$avatar_src = '../' . ltrim((string)$admin['avatar_url'], '/');
}

$site_name = setting_get($pdo, 'site_name', 'Mr Beat Store');
$site_lang_default = setting_get($pdo, 'site_lang_default', 'en');
$payment_enabled = setting_get($pdo, 'payment_enabled', '1');
$announce_enabled = setting_get($pdo, 'announce_enabled', '0');
$announce_text = setting_get($pdo, 'announce_text', '');

$theme = mr_theme_tokens($pdo);

$theme_mode = $theme['theme_mode'];

$theme_bg = $theme['theme_bg'];
$theme_card = $theme['theme_card'];
$theme_primary = $theme['theme_primary'];
$theme_text = $theme['theme_text'];

$theme_bg_light = $theme['theme_bg_light'];
$theme_card_light = $theme['theme_card_light'];
$theme_primary_light = $theme['theme_primary_light'];
$theme_text_light = $theme['theme_text_light'];

$theme_gradient_from = $theme['theme_gradient_from'];
$theme_gradient_to = $theme['theme_gradient_to'];
$theme_gradient_from_light = $theme['theme_gradient_from_light'];
$theme_gradient_to_light = $theme['theme_gradient_to_light'];


$theme_radius = (string)$theme['theme_radius'];
$theme_shadow = (string)$theme['theme_shadow'];
$theme_border = (string)$theme['theme_border'];
$theme_glass = (string)$theme['theme_glass'];

$site_icon = setting_get($pdo, 'site_icon', '');
$welcome_bonus_enabled = setting_get($pdo, 'welcome_bonus_enabled', '0');
$welcome_bonus_amount = setting_get($pdo, 'welcome_bonus_amount', '0');
$seller_commission_enabled = setting_get($pdo, 'seller_commission_enabled', '0');
$seller_commission_percent = setting_get($pdo, 'seller_commission_percent', '20');
$seller_commission_balance = (float)setting_get($pdo, 'seller_commission_balance', '0'); 

$ui_bg = $theme['ui_bg'];
$ui_card = $theme['ui_card'];
$ui_primary = $theme['ui_primary'];
$ui_text = $theme['ui_text'];
$ui_g1 = $theme['ui_g1'];
$ui_g2 = $theme['ui_g2'];
?>
<!doctype html>
<html lang="<?php echo $is_ar ? 'ar' : 'en'; ?>" dir="<?php echo $is_ar ? 'rtl' : 'ltr'; ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php echo mr_head_tags(); ?>
<title><?php echo $is_ar ? 'لوحة التحكم' : 'Admin Dashboard'; ?></title>
<style>
:root{
--bg: <?php echo e($ui_bg); ?>;
--card: <?php echo e($ui_card); ?>;
--primary: <?php echo e($ui_primary); ?>;
--text: <?php echo e($ui_text); ?>;
--g1: <?php echo e($ui_g1); ?>;
--g2: <?php echo e($ui_g2); ?>;
--radius: <?php echo (int)$theme_radius; ?>px;
--border-size: <?php echo (int)$theme_border; ?>px;
--shadow-lg: <?php echo e($theme['shadow_lg']); ?>;
--shadow-md: <?php echo e($theme['shadow_md']); ?>;
--primary-08: <?php echo e($theme['primary_08']); ?>;
--primary-12: <?php echo e($theme['primary_12']); ?>;
--primary-18: <?php echo e($theme['primary_18']); ?>;
--primary-24: <?php echo e($theme['primary_24']); ?>;
--text-08: <?php echo e($theme['text_08']); ?>;
--text-12: <?php echo e($theme['text_12']); ?>;
--text-18: <?php echo e($theme['text_18']); ?>;
--text-72: <?php echo e($theme['text_72']); ?>;
--glass-blur: <?php echo e($theme['blur']); ?>;
--surface: <?php echo $theme['surface_mix']; ?>;
}

*{box-sizing:border-box}

body{
padding:20px;
margin:0;
background:
radial-gradient(circle at top <?php echo $is_ar ? 'left' : 'right'; ?>, color-mix(in srgb, var(--g2) 18%, transparent), transparent 28%),
linear-gradient(180deg, var(--bg), color-mix(in srgb, var(--bg) 82%, #000 18%));
color:var(--text);
font-family:Tahoma,Arial,sans-serif;
}

a{text-decoration:none}

.wrap{max-width:1480px;margin:0 auto}

.topShell{
border:var(--border-size) solid var(--text-08);
border-radius:28px;
background:
radial-gradient(circle at top <?php echo $is_ar ? 'left' : 'right'; ?>, color-mix(in srgb, var(--g2) 22%, transparent), transparent 28%),
linear-gradient(135deg, color-mix(in srgb, var(--g1) 18%, var(--card) 82%), color-mix(in srgb, var(--g2) 10%, #0d121a 90%));
padding:16px;
margin-bottom:18px;
box-shadow:var(--shadow-lg);
backdrop-filter:var(--glass-blur);
}

.topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.brandBox{min-width:220px}
.nav{display:flex;gap:10px;align-items:center;flex-wrap:wrap}

.pill{
display:inline-block;
background:var(--text-08);
border:var(--border-size) solid var(--text-08);
padding:9px 13px;
border-radius:999px;
color:var(--text);
transition:.2s ease;
}

.pill:hover{transform:translateY(-1px);border-color:var(--primary)}

.hero{margin-top:16px;display:grid;grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr);gap:16px}

.heroCard,.profileCard,.panelCard,.statCard{
border:var(--border-size) solid var(--text-08);
border-radius:26px;
background:var(--surface);
padding:16px;
box-shadow:var(--shadow-md);
animation:fadeUp .3s ease;
min-height:0;
backdrop-filter:var(--glass-blur);
}

.heroTitle{font-size:28px;font-weight:900}
.heroSub{opacity:.82;font-size:13px;margin-top:6px;line-height:1.6}
.quickStats{margin-top:14px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}

.miniBox{
background:var(--text-08);
border:var(--border-size) solid var(--text-08);
border-radius:18px;
padding:12px;
}

.miniNum{font-size:22px;font-weight:900}
.miniLabel{opacity:.8;font-size:12px;margin-top:4px}
.profileHead{display:flex;gap:12px;align-items:center;flex-wrap:wrap}

.avatarBig{
width:74px;
height:74px;
border-radius:22px;
overflow:hidden;
border:var(--border-size) solid var(--text-08);
background:#0b0f14;
display:flex;
align-items:center;
justify-content:center;
font-size:26px;
font-weight:800
}

.avatarBig img{width:100%;height:100%;object-fit:cover}
.nameTxt{font-size:20px;font-weight:800}
.subTxt{opacity:.8;font-size:13px;margin-top:4px}
.walletStrip{margin-top:14px;display:grid;grid-template-columns:1fr 1fr;gap:10px}

.walletBox{
background:var(--text-08);
border:var(--border-size) solid var(--text-08);
border-radius:18px;
padding:12px;
}

.walletVal{font-size:20px;font-weight:900}
.statsGrid{display:flex;gap:14px;margin-bottom:16px;flex-wrap:wrap}
.statNum{font-size:28px;font-weight:900}
.statLabel{opacity:.82;font-size:13px;margin-top:6px}
.sectionTabs{display:flex;gap:10px;overflow:auto;padding:2px 0;margin:18px 0 16px 0;scrollbar-width:none}
.sectionTabs::-webkit-scrollbar{width:0;height:0}

.tabBtn{
border:var(--border-size) solid var(--text-08);
background:var(--card);
color:var(--text);
padding:10px 14px;
border-radius:999px;
white-space:nowrap;
transition:.2s ease;
}

.tabBtn.active{
background:linear-gradient(135deg,var(--g1),var(--g2));
color:#fff;
transform:translateY(-1px);
}

.grid2{
display:grid;
grid-template-columns:minmax(0,1fr) minmax(0,1fr);
gap:16px;
align-items:start;
}

.settingsGrid{
display:grid;
grid-template-columns:repeat(2,minmax(0,1fr));
gap:12px;
align-items:start;
}

.settingsSubGrid{
display:grid;
grid-template-columns:repeat(2,minmax(0,1fr));
gap:12px;
align-items:start;
margin-top:12px;
}

.settingsSubGrid .fullRow{
grid-column:1 / -1;
}

.gridTickets{display:grid;grid-template-columns:340px minmax(0,1fr);gap:16px}

.cardTitle{font-size:16px;font-weight:800;margin-bottom:10px}
.muted{opacity:.8;font-size:13px}
.emptyState{opacity:.8;text-align:center;padding:30px 14px}

.grid3{
display:flex;
grid-template-columns:minmax(420px,.9fr) minmax(0,1.6fr);
gap:16px;
align-items:start;
}

.tableWrap{
overflow:auto;
border-radius:18px;
border:var(--border-size) solid var(--text-08);
max-height:640px;
scrollbar-width:none;
-ms-overflow-style:none;
}

.tableWrap::-webkit-scrollbar{width:0;height:0}
.tableWrap.usersScroll{max-height:720px}
.tableWrap.stockScroll{max-height:580px}

.productsScroll{
max-height:760px;
overflow:auto;
padding-right:4px;
scrollbar-width:none;
-ms-overflow-style:none;
}

.productsScroll::-webkit-scrollbar{width:0;height:0}
table{width:100%;border-collapse:collapse;background:transparent}

td,th{
border-bottom:var(--border-size) solid var(--text-08);
padding:12px 14px;
text-align:<?php echo $is_ar ? 'right' : 'left'; ?>;
font-size:13px;
vertical-align:top;
}

th{background:rgba(17,24,38,.94);position:sticky;top:0;z-index:1}
tr:last-child td{border-bottom:0}

.badge{
display:inline-block;
padding:4px 10px;
border-radius:999px;
font-size:12px;
border:var(--border-size) solid #2f3a4a;
background:#121923;
color:#d7e3f4;
}

.statusGood{background:#102318;border-color:#1f5c38;color:#86efac}
.statusBad{background:#231616;border-color:#5a2a2a;color:#fca5a5}
.statusWarn{background:#1e1b10;border-color:#6b4f1d;color:#fcd34d}

input,select,textarea{
width:100%;
padding:11px 12px;
margin-top:8px;
background:rgba(0,0,0,.18);
border:var(--border-size) solid var(--text-08);
color:var(--text);
border-radius:14px;
box-sizing:border-box;
}

textarea{max-width:100%;resize:vertical;overflow:auto}

button{
padding:10px 12px;
border-radius:12px;
border:0;
background:linear-gradient(135deg,var(--g1),var(--g2));
color:#fff;
cursor:pointer;
font-weight:700;
}

.btnGhost{background:#374151}
.btnDanger{background:#7f1d1d}
.btnOk{background:#166534}

.row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.row > *{flex:1}

.formCard{
background:var(--text-08);
border:var(--border-size) solid var(--text-08);
border-radius:18px;
padding:14px;
backdrop-filter:var(--glass-blur);
}

.txList{
margin-top:12px;
max-height:420px;
overflow:auto;
padding-right:6px;
scrollbar-width:none;
-ms-overflow-style:none;
scroll-behavior:smooth;
overscroll-behavior:contain;
}

.txList::-webkit-scrollbar{width:0;height:0}

.txRow{
display:flex;
justify-content:space-between;
gap:10px;
padding:12px 0;
border-bottom:var(--border-size) solid var(--text-08);
animation:fadeUp .22s ease;
will-change:transform,opacity;
}

.txRow:last-child{border-bottom:0}
.txName{font-weight:700}
.txMeta{font-size:12px;opacity:.8;margin-top:3px}
.txAmt{font-weight:800}
.neg{color:#fca5a5}
.pos{color:#86efac}

.ticketList{
max-height:680px;
overflow:auto;
padding-<?php echo $is_ar ? 'left' : 'right'; ?>:4px;
scrollbar-width:none;
-ms-overflow-style:none;
}

.supportStack{
display:flex;
flex-direction:column;
gap:16px;
}

.ticketComposer{
position:sticky;
top:0;
z-index:3;
}

.ticketHistoryBox{
max-height:420px;
overflow:auto;
padding-<?php echo $is_ar ? 'left' : 'right'; ?>:4px;
scrollbar-width:none;
-ms-overflow-style:none;
}

.ticketHistoryBox::-webkit-scrollbar{width:0;height:0}

.ticketHistoryItem,
.ticketItem{
display:block;
padding:13px;
border-radius:18px;
border:var(--border-size) solid var(--text-08);
background:var(--text-08);
margin-bottom:10px;
color:var(--text);
transition:.22s ease;
backdrop-filter:var(--glass-blur);
}

.ticketHistoryItem:hover,
.ticketItem:hover{transform:translateY(-2px);border-color:var(--primary)}

.ticketHistoryItem.active,
.ticketItem.active{
background:linear-gradient(180deg, color-mix(in srgb, var(--g2) 18%, transparent), var(--primary-08));
border-color:var(--primary);
}

.ticketList::-webkit-scrollbar{width:0;height:0}

.chatBox{
height:540px;
overflow:auto;
padding:16px;
border-radius:22px;
background:
linear-gradient(180deg, rgba(255,255,255,.02), rgba(0,0,0,.08)),
rgba(0,0,0,.14);
border:var(--border-size) solid var(--text-08);
scrollbar-width:none;
-ms-overflow-style:none;
display:flex;
flex-direction:column;
gap:10px;
}

.chatBox::-webkit-scrollbar{width:0;height:0}

.bubble{
max-width:78%;
padding:10px 12px 8px;
border-radius:18px;
line-height:1.6;
border:var(--border-size) solid var(--text-08);
overflow-wrap:anywhere;
word-break:break-word;
white-space:pre-wrap;
animation:fadeUp .18s ease;
box-shadow:var(--shadow-md);
position:relative;
}

.me{
background:#111826;
margin-left:auto;
margin-right:0;
border-bottom-right-radius:8px;
}

.them{
background:linear-gradient(135deg,var(--g1),var(--g2));
color:#fff;
border-color:transparent;
margin-right:auto;
margin-left:0;
border-bottom-left-radius:8px;
}

.msgText{font-size:14px;line-height:1.7}

.msgImageWrap{
margin-top:10px;
border-radius:14px;
overflow:hidden;
background:rgba(255,255,255,.05);
border:var(--border-size) solid rgba(255,255,255,.10);
}

.msgImage{
display:block;
width:100%;
max-width:280px;
max-height:340px;
object-fit:cover;
border-radius:14px;
transition:transform .2s ease, opacity .2s ease;
}

.msgImage:hover{
transform:scale(1.02);
opacity:.96;
}

.timeTxt{
font-size:11px;
opacity:.72;
margin-top:7px;
text-align:right;
direction:ltr;
}

.msgForm{
margin-top:12px;
padding:12px;
border-radius:18px;
background:var(--text-08);
border:var(--border-size) solid var(--text-08);
backdrop-filter:var(--glass-blur);
}

.msgForm textarea{
min-height:88px;
border-radius:16px;
margin-top:0;
}

.msgForm input[type="file"]{
margin-top:10px;
padding:10px;
border-radius:14px;
background:rgba(255,255,255,.03);
}

.msgForm button{
margin-top:10px;
min-width:140px;
}

@media (max-width: 920px){
.bubble{max-width:88%}
.msgImage{max-width:100%}
.chatBox{height:420px}
}
@media (max-width: 768px){
body{
padding:12px;
}

.wrap{
max-width:100%;
}

.topShell{
padding:12px;
border-radius:20px;
margin-bottom:14px;
}

.topbar{
flex-direction:column;
align-items:stretch;
}

.brandBox{
min-width:0;
width:100%;
}

.nav{
width:100%;
display:grid;
grid-template-columns:1fr;
gap:8px;
}

.pill,
.tabBtn{
width:100%;
text-align:center;
justify-content:center;
padding:11px 14px;
min-height:44px;
}

.hero{
grid-template-columns:1fr;
gap:12px;
margin-top:12px;
}

.heroCard,
.profileCard,
.panelCard,
.statCard{
padding:14px;
border-radius:18px;
}

.heroTitle{
font-size:24px;
line-height:1.25;
}

.heroSub{
font-size:13px;
line-height:1.7;
}

.quickStats{
grid-template-columns:1fr;
gap:10px;
}

.miniBox{
border-radius:14px;
padding:12px;
}

.miniNum{
font-size:20px;
}

.profileHead{
align-items:center;
gap:10px;
}

.avatarBig{
width:62px;
height:62px;
border-radius:18px;
font-size:22px;
}

.nameTxt{
font-size:18px;
line-height:1.3;
word-break:break-word;
}

.subTxt{
font-size:12px;
line-height:1.6;
word-break:break-word;
}

.walletStrip{
grid-template-columns:1fr;
gap:10px;
}

.walletBox{
border-radius:14px;
}

.statsGrid{
display:grid;
grid-template-columns:1fr;
gap:10px;
}

.statNum{
font-size:24px;
}

.statLabel{
font-size:12px;
}

.sectionTabs{
gap:8px;
margin:14px 0 14px 0;
padding-bottom:4px;
}

.grid2,
.grid3,
.gridTickets,
.settingsGrid,
.settingsSubGrid,
.walletStrip,
.colorGrid,
.financeGrid,
.productsGrid{
display:grid;
grid-template-columns:1fr;
gap:12px;
}

.row{
flex-direction:column;
align-items:stretch;
}

.row > *{
flex:unset;
width:100%;
}

.formCard{
padding:12px;
border-radius:16px;
}

input,
select,
textarea,
button{
font-size:16px;
}

input,
select,
textarea{
padding:12px 14px;
border-radius:14px;
}

button{
min-height:46px;
border-radius:14px;
}

.tableWrap{
overflow-x:auto;
overflow-y:visible;
max-height:none;
border-radius:16px;
}

table{
min-width:760px;
}

th,
td{
padding:10px 12px;
font-size:13px;
}

.userCell,
.actionsCol{
min-width:unset;
max-width:none;
}

.userActionBox{
padding:10px;
border-radius:14px;
}

.productsScroll,
.ticketList,
.ticketHistoryBox,
.chatBox,
.txList{
max-height:none;
height:auto;
}

.productsGrid{
grid-template-columns:1fr;
}

.pCard{
border-radius:18px;
}

.pCard img{
height:180px;
}

.pBody{
padding:12px;
}

.productActions{
flex-direction:column;
}

.productActions a,
.productActions form{
flex:unset;
width:100%;
min-width:0;
}

.chatBox{
padding:12px;
border-radius:18px;
gap:8px;
}

.bubble{
max-width:92%;
padding:10px 12px;
border-radius:16px;
}

.msgText{
font-size:13px;
line-height:1.7;
}

.msgImage{
max-width:100%;
height:auto;
max-height:260px;
}

.msgForm{
padding:12px;
border-radius:16px;
}

.msgForm textarea{
min-height:84px;
}

.ticketComposer{
position:static;
top:auto;
}

.fileUploadRow,
.msgActions{
flex-direction:column;
align-items:stretch;
}

.fileUploadBox{
width:100%;
min-width:0;
}

.fileUploadName{
display:block;
width:100%;
}

.notice,
.banner,
.announceBox,
.contactSection{
border-radius:14px;
}

.proofThumb{
max-width:88px;
}

.previewIcon{
width:42px;
height:42px;
}

.emptyState{
padding:22px 12px;
}
}

@media (max-width: 480px){
body{
padding:10px;
}

.topShell{
padding:10px;
border-radius:18px;
}

.heroCard,
.profileCard,
.panelCard,
.statCard{
padding:12px;
border-radius:16px;
}

.heroTitle{
font-size:21px;
}

.nameTxt{
font-size:16px;
}

.avatarBig{
width:56px;
height:56px;
border-radius:16px;
font-size:20px;
}

.miniNum,
.walletVal,
.financeValue,
.statNum{
font-size:20px;
}

.pCard img{
height:160px;
}

.chatBox{
padding:10px;
}

.bubble{
max-width:96%;
font-size:13px;
}

th,
td{
padding:9px 10px;
}

body::before,
body::after{
opacity:.25;
}
}
.me{
background:#111826;
margin-<?php echo $is_ar ? 'right' : 'left'; ?>:0;
margin-<?php echo $is_ar ? 'left' : 'right'; ?>:auto;
}

.them{
background:linear-gradient(135deg,var(--g1),var(--g2));
color:#fff;
border-color:transparent;
margin-<?php echo $is_ar ? 'left' : 'right'; ?>:0;
margin-<?php echo $is_ar ? 'right' : 'left'; ?>:auto;
}

.timeTxt{font-size:11px;opacity:.82;margin-top:6px}

.productsGrid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}

.pCard{
border:var(--border-size) solid var(--text-08);
border-radius:22px;
background:var(--text-08);
overflow:hidden;
transition:.2s ease;
min-height:0;
backdrop-filter:var(--glass-blur);
}

.pCard:hover{transform:translateY(-3px);border-color:var(--primary)}
.pCard img{width:100%;height:180px;object-fit:cover;background:#111}
.pBody{padding:14px}
.pName{font-size:16px;font-weight:800;word-break:break-word}
.pMeta{opacity:.82;font-size:13px;margin-top:6px;line-height:1.6;word-break:break-word}

.notice{
margin-bottom:12px;
padding:12px 14px;
border-radius:16px;
border:var(--border-size) solid #3b4454;
background:#151b24;
color:#dbe7ff;
}

.errNotice{border-color:#5a2a2a;background:#231616;color:#fecaca}

.previewIcon{
width:48px;
height:48px;
border-radius:12px;
border:var(--border-size) solid var(--text-08);
background:#0b0f14;
display:flex;
align-items:center;
justify-content:center;
overflow:hidden
}

.previewIcon img{width:100%;height:100%;object-fit:cover}

.userCell{
min-width:320px;
max-width:420px;
overflow:hidden;
padding-right:8px;
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
background:rgba(255,255,255,.05);
border:var(--border-size) solid rgba(255,255,255,.10);
cursor:pointer;
transition:.2s ease;
min-width:150px;
font-size:13px;
font-weight:700;
}

.fileUploadBox:hover{
transform:translateY(-1px);
border-color:var(--primary);
background:rgba(255,255,255,.08);
}

.fileUploadBox input[type="file"]{
position:absolute;
inset:0;
opacity:0;
cursor:pointer;
width:100%;
height:100%;
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

.msgActions button{margin-top:0}

.userName{
font-weight:800;
white-space:normal;
overflow-wrap:anywhere;
line-height:1.5;
}

.userMail{
opacity:.8;
font-size:12px;
margin-top:4px;
word-break:break-word;
line-height:1.5;
}

.actionsCol{min-width:520px;}

.userActionBox{
background:var(--text-08);
border:var(--border-size) solid var(--text-08);
border-radius:16px;
padding:10px;
backdrop-filter:var(--glass-blur);
}

.productActions{
display:flex;
gap:8px;
flex-wrap:wrap;
margin-top:10px;
}

.productActions a,
.productActions form{
flex:1 1 calc(50% - 8px);
min-width:120px;
}

.productActions a,
.productActions button{
width:100%;
text-align:center;
}

.colorGrid{
display:grid;
grid-template-columns:repeat(2,minmax(0,1fr));
gap:12px;
margin-top:10px;
}

.colorItem{
padding:12px;
border:var(--border-size) solid var(--text-08);
border-radius:16px;
background:var(--text-08);
backdrop-filter:var(--glass-blur);
}

.colorItem input[type="color"]{
height:48px;
padding:6px;
cursor:pointer;
}

.modeTabs{
display:flex;
gap:10px;
flex-wrap:wrap;
margin-top:10px;
}

.modeChip{
display:flex;
align-items:center;
gap:8px;
padding:10px 12px;
border:var(--border-size) solid var(--text-08);
border-radius:999px;
background:var(--text-08);
}

.modeChip input{
width:auto;
margin:0;
}

.financeGrid{
display:grid;
grid-template-columns:repeat(2,minmax(0,1fr));
gap:16px;
margin-bottom:14px;
}

.financeStat{
padding:14px;
border-radius:18px;
border:var(--border-size) solid var(--text-08);
background:var(--text-08);
backdrop-filter:var(--glass-blur);
}

.financeTitle{
font-size:13px;
opacity:.82;
margin-bottom:8px;
}

.financeValue{
font-size:24px;
font-weight:900;
}

.proofThumb{
max-width:120px;
border-radius:12px;
border:var(--border-size) solid var(--text-08);
display:block;
}

@keyframes fadeUp{
from{opacity:0;transform:translateY(10px)}
to{opacity:1;transform:translateY(0)}
}

@media (max-width: 1200px){
.productsGrid{grid-template-columns:repeat(2,minmax(0,1fr))}
}

@media (max-width: 920px){
.hero,
.grid2,
.gridTickets,
.grid3,
.settingsGrid,
.settingsSubGrid,
.walletStrip,
.colorGrid,
.financeGrid,
.productsGrid{
grid-template-columns:1fr;
}

.settingsSubGrid .fullRow{
grid-column:auto;
}

.tableWrap,
.productsScroll,
.ticketList,
.chatBox{
max-height:none;
height:auto;
}

.actionsCol{min-width:320px}
.userCell{min-width:220px;max-width:none}
.productActions a,
.productActions form{flex:1 1 100%}
}
</style>
</head>
<body>
<div class="wrap">
<div class="topShell">
<div class="topbar">
<div class="brandBox">
<div style="font-size:22px;font-weight:900"><?php echo e($site_name); ?> Admin</div>
<div style="opacity:.8;font-size:13px;margin-top:4px">
<?php
if ($admin_role === 'seller') {
echo $is_ar ? 'لوحة البائع لإدارة المنتجات والمخزون وطلب الإيداع والسحب والدعم.' : 'Seller dashboard to manage products, stock, deposits, withdraws and support.';
} else {
echo $is_ar ? 'لوحة تحكم شاملة لإدارة المتجر بالكامل' : 'Unified dashboard to manage your whole store';
}
?>
</div>
</div>
<div class="nav">
<a class="pill" href="dashboard.php?section=overview"><?php echo $is_ar ? 'الرئيسية' : 'Overview'; ?></a>
<a class="pill" href="dashboard.php?section=profile"><?php echo $is_ar ? 'الحساب' : 'Profile'; ?></a>
<?php if (section_allowed('users') && mr_can_manage_users()): ?>
<a class="pill" href="dashboard.php?section=users"><?php echo $is_ar ? 'المستخدمين' : 'Users'; ?></a>
<?php endif; ?>
<?php if (section_allowed('products') && mr_can_manage_products()): ?>
<a class="pill" href="dashboard.php?section=products"><?php echo $is_ar ? 'المنتجات' : 'Products'; ?></a>
<?php endif; ?>
<?php if (section_allowed('add_product') && mr_can_manage_products()): ?>
<a class="pill" href="dashboard.php?section=add_product"><?php echo $is_ar ? 'إضافة منتج' : 'Add Product'; ?></a>
<?php endif; ?>
<?php if (section_allowed('my_deposits') && admin_can_view_own_finance_tabs()): ?>
<a class="pill" href="dashboard.php?section=my_deposits"><?php echo $is_ar ? 'إيداعاتي' : 'My Deposits'; ?></a>
<?php endif; ?>
<?php if (section_allowed('my_withdraws') && admin_can_view_own_finance_tabs()): ?>
<a class="pill" href="dashboard.php?section=my_withdraws"><?php echo $is_ar ? 'سحبي' : 'My Withdraws'; ?></a>
<?php endif; ?>
<?php if (section_allowed('support') && mr_is_seller()): ?>
<a class="pill" href="dashboard.php?section=support"><?php echo $is_ar ? 'الدعم' : 'Support'; ?></a>
<?php endif; ?>
<?php if (!mr_is_seller() && section_allowed('tickets') && mr_can_manage_tickets()): ?>
<a class="pill" href="dashboard.php?section=tickets"><?php echo $is_ar ? 'الدعم' : 'Tickets'; ?></a>
<?php endif; ?>
<?php if (section_allowed('deposits') && mr_can_manage_deposits()): ?>
<a class="pill" href="dashboard.php?section=deposits"><?php echo $is_ar ? 'الإيداعات' : 'Deposits'; ?></a>
<?php endif; ?>
<?php if (section_allowed('withdraws') && mr_can_manage_withdraws()): ?>
<a class="pill" href="dashboard.php?section=withdraws"><?php echo $is_ar ? 'السحب' : 'Withdraws'; ?></a>
<?php endif; ?>
<?php if (section_allowed('settings') && mr_can_manage_settings()): ?>
<a class="pill" href="dashboard.php?section=settings"><?php echo $is_ar ? 'الإعدادات' : 'Settings'; ?></a>
<?php endif; ?>

<a class="pill" href="../index.php"><?php echo $is_ar ? 'الذهاب إلى المتجر' : 'Go to Store'; ?></a>

<a class="pill" href="logout.php"><?php echo $is_ar ? 'خروج' : 'Logout'; ?></a>
</div>
</div>

<div class="hero">
<div class="heroCard">
<div class="heroTitle"><?php echo $is_ar ? 'أهلاً، ' : 'Welcome, '; ?><?php echo e($admin['username']); ?></div>
<div class="heroSub">
<?php
if ($admin_role === 'seller') {
echo $is_ar ? 'من هنا تقدر تدير منتجاتك والمخزون الخاص بك فقط، وتقدم طلبات إيداع وسحب، وتتواصل مع الدعم.' : 'From here you can manage your own products and stock, submit deposit/withdraw requests, and contact support.';
} else {
echo $is_ar ? 'كل الإدارة بقت من مكان واحد: مستخدمين، منتجات، مخزون، دعم، إيداعات، سحب، وإعدادات.' : 'Everything is now in one place: users, products, stock, support, deposits, withdraws and settings.';
}
?>
</div>

<div class="quickStats">
<?php if (mr_can_manage_deposits()): ?>
<div class="miniBox">
<div class="miniNum"><?php echo $pending_deposits; ?></div>
<div class="miniLabel"><?php echo $is_ar ? 'إيداعات معلقة' : 'Pending deposits'; ?></div>
</div>
<?php endif; ?>

<?php if (mr_can_manage_withdraws()): ?>
<div class="miniBox">
<div class="miniNum"><?php echo $pending_withdraws; ?></div>
<div class="miniLabel"><?php echo $is_ar ? 'طلبات سحب معلقة' : 'Pending withdraws'; ?></div>
</div>
<?php endif; ?>

<?php if (mr_can_manage_tickets()): ?>
<div class="miniBox">
<div class="miniNum"><?php echo $open_tickets; ?></div>
<div class="miniLabel"><?php echo $is_ar ? 'تذاكر مفتوحة' : 'Open tickets'; ?></div>
</div>
<?php endif; ?>

<?php if (mr_can_manage_products()): ?>
<div class="miniBox">
<div class="miniNum"><?php echo $stock_available; ?></div>
<div class="miniLabel"><?php echo $is_ar ? 'مخزون متاح' : 'Available stock'; ?></div>
</div>
<?php endif; ?>
</div>
</div>

<div class="profileCard">
<div class="profileHead">
<div class="avatarBig">
<?php if ($avatar_src): ?>
<img src="<?php echo e($avatar_src); ?>">
<?php else: ?>
<div><?php echo strtoupper(substr((string)$admin['username'], 0, 1)); ?></div>
<?php endif; ?>
</div>
<div>
<div class="nameTxt"><?php echo e($admin['username']); ?></div>
<div class="subTxt"><?php echo e(ucfirst((string)$admin['role'])); ?> • <?php echo e($admin['email'] ?? ''); ?></div>
<div class="subTxt"><?php echo $is_ar ? 'الحالة' : 'Status'; ?>: <?php echo e($admin['status'] ?? 'active'); ?></div>
</div>
</div>

<div class="walletStrip">
<div class="walletBox">
<div class="muted"><?php echo $is_ar ? 'رصيدي الحالي' : 'My balance'; ?></div>
<div class="walletVal"><?php echo n2($admin_wallet_usdt); ?></div>
</div>

<div class="walletBox">
<div class="muted"><?php echo $is_ar ? 'إجمالي مبيعات الموقع' : 'Site sales'; ?></div>
<div class="walletVal"><?php echo n2($site_sales_usdt); ?></div>
</div>

<div class="walletBox">
<div class="muted"><?php echo $is_ar ? 'مبيعاتي أنا' : 'My sales'; ?></div>
<div class="walletVal"><?php echo n2($my_sales_usdt); ?></div>
</div>

<div class="walletBox">
<div class="muted"><?php echo $is_ar ? 'محجوز' : 'On Hold'; ?></div>
<div class="walletVal"><?php echo n2($admin_hold_usdt); ?></div>
</div>
</div>
</div>
</div>
</div>

<?php if ($msg): ?><div class="notice"><?php echo e($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="notice errNotice"><?php echo e($err); ?></div><?php endif; ?>

<div class="statsGrid">
<?php if (mr_can_manage_users()): ?>
<div class="statCard">
<div class="statNum"><?php echo $users_count; ?></div>
<div class="statLabel"><?php echo $is_ar ? 'المستخدمون' : 'Users'; ?></div>
</div>
<?php endif; ?>

<?php if (mr_can_manage_products()): ?>
<div class="statCard">
<div class="statNum"><?php echo $products_count; ?></div>
<div class="statLabel"><?php echo $is_ar ? 'المنتجات' : 'Products'; ?></div>
</div>
<?php endif; ?>

<div class="statCard">
<div class="statNum"><?php echo n2($admin_wallet_usdt); ?></div>
<div class="statLabel"><?php echo $is_ar ? 'رصيدي الحالي' : 'My balance'; ?></div>
</div>

<div class="statCard">
<div class="statNum"><?php echo n2($site_sales_usdt); ?></div>
<div class="statLabel"><?php echo $is_ar ? 'مبيعات الموقع' : 'Site sales'; ?></div>
</div>

<div class="statCard">
<div class="statNum"><?php echo n2($my_sales_usdt); ?></div>
<div class="statLabel"><?php echo $is_ar ? 'مبيعاتي' : 'My sales'; ?></div>
</div>

<div class="statCard">
<div class="statNum"><?php echo n2($total_hold); ?></div>
<div class="statLabel"><?php echo $is_ar ? 'إجمالي المحجوز' : 'Total hold'; ?></div>
</div>
<?php if (!mr_is_seller()): ?>
<div class="statCard">
<div class="statNum"><?php echo n2($seller_commission_balance); ?></div>
<div class="statLabel"><?php echo $is_ar ? 'رصيد مخصوم من السيلرات' : 'Seller Commission Balance'; ?></div>
</div>
<?php endif; ?>
<?php if ($orders_count > 0): ?>
<div class="statCard">
<div class="statNum"><?php echo $orders_count; ?></div>
<div class="statLabel"><?php echo $is_ar ? 'الطلبات' : 'Orders'; ?></div>
</div>
<?php endif; ?>

<?php if (mr_can_manage_products()): ?>
<div class="statCard">
<div class="statNum"><?php echo $stock_available; ?></div>
<div class="statLabel"><?php echo $is_ar ? 'المتاح' : 'Available'; ?></div>
</div>
<?php endif; ?>
</div>

<div class="sectionTabs">
<a class="tabBtn <?php echo $section==='overview'?'active':''; ?>" href="dashboard.php?section=overview"><?php echo $is_ar ? 'نظرة عامة' : 'Overview'; ?></a>
<a class="tabBtn <?php echo $section==='profile'?'active':''; ?>" href="dashboard.php?section=profile"><?php echo $is_ar ? 'بروفايلي' : 'Profile'; ?></a>
<?php if (section_allowed('users') && mr_can_manage_users()): ?>
<a class="tabBtn <?php echo $section==='users'?'active':''; ?>" href="dashboard.php?section=users"><?php echo $is_ar ? 'المستخدمون' : 'Users'; ?></a>
<?php endif; ?>
<?php if (section_allowed('products') && mr_can_manage_products()): ?>
<a class="tabBtn <?php echo $section==='products'?'active':''; ?>" href="dashboard.php?section=products"><?php echo $is_ar ? 'المنتجات' : 'Products'; ?></a>
<?php endif; ?>
<?php if (section_allowed('add_product') && mr_can_manage_products()): ?>
<a class="tabBtn <?php echo $section==='add_product'?'active':''; ?>" href="dashboard.php?section=add_product"><?php echo $is_ar ? 'إضافة منتج' : 'Add Product'; ?></a>
<?php endif; ?>
<?php if (section_allowed('my_deposits') && admin_can_view_own_finance_tabs()): ?>
<a class="tabBtn <?php echo $section==='my_deposits'?'active':''; ?>" href="dashboard.php?section=my_deposits"><?php echo $is_ar ? 'إيداعاتي' : 'My Deposits'; ?></a>
<?php endif; ?>
<?php if (section_allowed('my_withdraws') && admin_can_view_own_finance_tabs()): ?>
<a class="tabBtn <?php echo $section==='my_withdraws'?'active':''; ?>" href="dashboard.php?section=my_withdraws"><?php echo $is_ar ? 'سحبي' : 'My Withdraws'; ?></a>
<?php endif; ?>
<?php if (section_allowed('support') && mr_is_seller()): ?>
<a class="tabBtn <?php echo $section==='support'?'active':''; ?>" href="dashboard.php?section=support"><?php echo $is_ar ? 'الدعم' : 'Support'; ?></a>
<?php endif; ?>
<?php if (!mr_is_seller() && section_allowed('tickets') && mr_can_manage_tickets()): ?>
<a class="tabBtn <?php echo $section==='tickets'?'active':''; ?>" href="dashboard.php?section=tickets"><?php echo $is_ar ? 'التذاكر' : 'Tickets'; ?></a>
<?php endif; ?>
<?php if (section_allowed('deposits') && mr_can_manage_deposits()): ?>
<a class="tabBtn <?php echo $section==='deposits'?'active':''; ?>" href="dashboard.php?section=deposits"><?php echo $is_ar ? 'الإيداعات' : 'Deposits'; ?></a>
<?php endif; ?>
<?php if (section_allowed('withdraws') && mr_can_manage_withdraws()): ?>
<a class="tabBtn <?php echo $section==='withdraws'?'active':''; ?>" href="dashboard.php?section=withdraws"><?php echo $is_ar ? 'السحب' : 'Withdraws'; ?></a>
<?php endif; ?>
<?php if (section_allowed('settings') && mr_can_manage_settings()): ?>
<a class="tabBtn <?php echo $section==='settings'?'active':''; ?>" href="dashboard.php?section=settings"><?php echo $is_ar ? 'الإعدادات' : 'Settings'; ?></a>
<?php endif; ?>
</div>

<?php if ($section === 'overview'): ?>
<div class="grid2">
<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'اختصارات سريعة' : 'Quick Actions'; ?></div>
<div class="row">
<?php if (section_allowed('users') && mr_can_manage_users()): ?>
<a class="pill" href="dashboard.php?section=users"><?php echo $is_ar ? 'إدارة المستخدمين' : 'Manage Users'; ?></a>
<?php endif; ?>
<?php if (section_allowed('products') && mr_can_manage_products()): ?>
<a class="pill" href="dashboard.php?section=products"><?php echo $is_ar ? 'إدارة المنتجات' : 'Manage Products'; ?></a>
<?php endif; ?>
<?php if (section_allowed('add_product') && mr_can_manage_products()): ?>
<a class="pill" href="dashboard.php?section=add_product"><?php echo $is_ar ? 'إضافة منتج' : 'Add Product'; ?></a>
<?php endif; ?>
<?php if (section_allowed('my_deposits') && admin_can_view_own_finance_tabs()): ?>
<a class="pill" href="dashboard.php?section=my_deposits"><?php echo $is_ar ? 'إيداع جديد' : 'New Deposit'; ?></a>
<?php endif; ?>
<?php if (section_allowed('my_withdraws') && admin_can_view_own_finance_tabs()): ?>
<a class="pill" href="dashboard.php?section=my_withdraws"><?php echo $is_ar ? 'طلب سحب' : 'Request Withdraw'; ?></a>
<?php endif; ?>
<?php if (section_allowed('support') && mr_is_seller()): ?>
<a class="pill" href="dashboard.php?section=support"><?php echo $is_ar ? 'الدعم' : 'Support'; ?></a>
<?php endif; ?>
<?php if (!mr_is_seller() && section_allowed('tickets') && mr_can_manage_tickets()): ?>
<a class="pill" href="dashboard.php?section=tickets"><?php echo $is_ar ? 'الدعم' : 'Support'; ?></a>
<?php endif; ?>
<?php if (section_allowed('settings') && mr_can_manage_settings()): ?>
<a class="pill" href="dashboard.php?section=settings"><?php echo $is_ar ? 'الإعدادات' : 'Settings'; ?></a>
<?php endif; ?>
</div>
</div>

<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'آخر معاملات الحساب' : 'Recent Transactions'; ?></div>
<div class="txList">
<?php if (!$admin_txs): ?>
<div class="emptyState"><?php echo $is_ar ? 'لا توجد معاملات بعد.' : 'No transactions yet.'; ?></div>
<?php else: ?>
<?php foreach ($admin_txs as $t): ?>
<?php $amt = (float)$t['amount']; $isNeg = $t['kind'] === 'debit'; ?>
<div class="txRow">
<div>
<div class="txName"><?php echo e($t['reason'] ?: $t['kind']); ?></div>
<div class="txMeta"><?php echo e($t['created_at']); ?> • <?php echo e($t['kind']); ?></div>
</div>
<div class="txAmt <?php echo $isNeg ? 'neg' : 'pos'; ?>">
<?php echo $isNeg ? '-' : '+'; ?><?php echo n2($amt); ?> USDT
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>
</div>
<?php endif; ?>

<?php if ($section === 'profile'): ?>
<div class="grid2">
<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'تعديل البروفايل' : 'Edit Profile'; ?></div>
<div class="formCard">
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="save_admin_profile">

<label><?php echo $is_ar ? 'اسم المستخدم' : 'Username'; ?></label>
<input name="username" value="<?php echo e($admin['username']); ?>" required>

<label><?php echo $is_ar ? 'البريد الإلكتروني' : 'Email'; ?></label>
<input name="email" value="<?php echo e($admin['email'] ?? ''); ?>">

<label><?php echo $is_ar ? 'نبذة' : 'Bio'; ?></label>
<textarea name="bio" rows="4"><?php echo e($admin['bio'] ?? ''); ?></textarea>

<label><?php echo $is_ar ? 'الصورة' : 'Avatar'; ?></label>
<input type="file" name="avatar" accept="image/*">

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'حفظ التعديلات' : 'Save Changes'; ?></button>
</form>
</div>
</div>

<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'تغيير كلمة المرور' : 'Change Password'; ?></div>
<div class="formCard">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="change_admin_password">

<label><?php echo $is_ar ? 'كلمة المرور القديمة' : 'Old Password'; ?></label>
<input type="password" name="old_password" required>

<label><?php echo $is_ar ? 'كلمة المرور الجديدة' : 'New Password'; ?></label>
<input type="password" name="new_password" required>

<label><?php echo $is_ar ? 'تأكيد كلمة المرور' : 'Confirm Password'; ?></label>
<input type="password" name="new_password2" required>

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'تحديث' : 'Update'; ?></button>
</form>
</div>
</div>
</div>
<?php endif; ?>

<?php if ($section === 'users' && mr_can_manage_users()): ?>
<div class="grid3">
<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'بحث وإنشاء مستخدم' : 'Search & Create User'; ?></div>

<div class="formCard" style="margin-bottom:14px">
<form method="get">
<input type="hidden" name="section" value="users">
<label><?php echo $is_ar ? 'بحث' : 'Search'; ?></label>
<input name="q" placeholder="<?php echo $is_ar ? 'ابحث بالاسم أو البريد' : 'Search by username or email'; ?>" value="<?php echo e($q); ?>">
<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'بحث' : 'Search'; ?></button>
</form>
</div>

<div class="formCard">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="create_user">

<label><?php echo $is_ar ? 'اسم المستخدم' : 'Username'; ?></label>
<input name="username" required>

<label><?php echo $is_ar ? 'البريد الإلكتروني' : 'Email'; ?></label>
<input name="email">

<label><?php echo $is_ar ? 'كلمة المرور' : 'Password'; ?></label>
<input name="password" required>

<label><?php echo $is_ar ? 'الدور' : 'Role'; ?></label>
<select name="role">
<option value="user">user</option>
<option value="seller">seller</option>
<option value="admin">admin</option>
</select>

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'إنشاء مستخدم' : 'Create User'; ?></button>
</form>
</div>
</div>

<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'قائمة المستخدمين' : 'Users List'; ?></div>
<div class="tableWrap usersScroll">
<table>
<tr>
<th style="min-width:80px">ID</th>
<th style="min-width:360px"><?php echo $is_ar ? 'المستخدم' : 'User'; ?></th>
<th style="min-width:110px"><?php echo $is_ar ? 'الدور' : 'Role'; ?></th>
<th style="min-width:110px"><?php echo $is_ar ? 'الحالة' : 'Status'; ?></th>
<th style="min-width:100px"><?php echo $is_ar ? 'المتاح' : 'Available'; ?></th>
<th style="min-width:100px"><?php echo $is_ar ? 'المحجوز' : 'Hold'; ?></th>
<th style="min-width:520px"><?php echo $is_ar ? 'إجراءات' : 'Actions'; ?></th>
</tr>
<?php foreach ($users as $u): ?>
<tr>
<td>#<?php echo (int)$u['id']; ?></td>
<td class="userCell">
<div style="display:flex;gap:10px;align-items:flex-start">
<div style="width:52px;height:52px;border-radius:14px;overflow:hidden;background:#0b0f14;border:1px solid rgba(255,255,255,.08);flex:0 0 52px;display:flex;align-items:center;justify-content:center;font-weight:800">
<?php if (!empty($u['avatar_url'])): ?>
<img src="../<?php echo e(ltrim((string)$u['avatar_url'], '/')); ?>" style="width:100%;height:100%;object-fit:cover">
<?php else: ?>
<?php echo strtoupper(substr((string)$u['username'], 0, 1)); ?>
<?php endif; ?>
</div>
<div style="min-width:0;flex:1">
<div class="userName"><?php echo e($u['username']); ?></div>
<div class="userMail"><?php echo e($u['email'] ?? ''); ?></div>
<div class="userMail">ID: #<?php echo (int)$u['id']; ?></div>
<div class="userMail">👍 <?php echo (int)($u['likes_count'] ?? 0); ?> | 👎 <?php echo (int)($u['dislikes_count'] ?? 0); ?></div>
<div class="userMail"><?php echo $is_ar ? 'الدور' : 'Role'; ?>: <?php echo e($u['role'] ?? 'user'); ?></div>
<?php if (!empty($u['bio'])): ?>
<div class="userMail"><?php echo e($u['bio']); ?></div>
<?php endif; ?>
</div>
</div>
</td>
<td><span class="badge"><?php echo e($u['role']); ?></span></td>
<td><span class="badge <?php echo status_class($u['status'] ?? ''); ?>"><?php echo e($u['status']); ?></span></td>
<td><?php echo n2((float)($u['balance_usdt'] ?? 0)); ?></td>
<td><?php echo n2((float)($u['hold_usdt'] ?? 0)); ?></td>
<td class="actionsCol">
<div class="userActionBox">

<form method="post" style="margin-bottom:8px">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="toggle_block">
<input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
<input type="hidden" name="status" value="<?php echo e($u['status']); ?>">
</form>

<?php if (admin_can_change_roles()): ?>
<form method="post" class="row" style="margin-bottom:8px">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="update_user_access">
<input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">

<select name="role">
<option value="user" <?php echo (($u['role'] ?? '') === 'user' ? 'selected' : ''); ?>>user</option>
<option value="seller" <?php echo (($u['role'] ?? '') === 'seller' ? 'selected' : ''); ?>>seller</option>
<option value="admin" <?php echo (($u['role'] ?? '') === 'admin' ? 'selected' : ''); ?>>admin</option>
</select>

<select name="status">
<option value="active" <?php echo (($u['status'] ?? '') === 'active' ? 'selected' : ''); ?>>active</option>
<option value="blocked" <?php echo (($u['status'] ?? '') === 'blocked' ? 'selected' : ''); ?>>blocked</option>
</select>

<button type="submit"><?php echo $is_ar ? 'حفظ الصلاحيات' : 'Save Access'; ?></button>
</form>
<?php endif; ?>

<form method="post" class="row">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="reset_password">
<input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
<input name="newpass" placeholder="<?php echo $is_ar ? 'كلمة مرور جديدة' : 'New password'; ?>" required>
<button type="button" class="btnGhost" onclick="genPass(this)"><?php echo $is_ar ? 'توليد' : 'Generate'; ?></button>
<button type="submit"><?php echo $is_ar ? 'إعادة تعيين' : 'Reset'; ?></button>
</form>

<?php if (can_adjust_balance()): ?>
<form method="post" class="row" style="margin-top:8px">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="adjust_balance">
<input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">

<select name="mode">
<option value="add"><?php echo $is_ar ? 'إضافة رصيد' : 'Add Balance'; ?></option>
<option value="deduct"><?php echo $is_ar ? 'خصم رصيد' : 'Deduct Balance'; ?></option>
</select>

<input type="number" step="0.01" name="amount" placeholder="<?php echo $is_ar ? 'المبلغ' : 'Amount'; ?>" required>
<input name="reason" placeholder="<?php echo $is_ar ? 'السبب' : 'Reason'; ?>">
<button type="submit"><?php echo $is_ar ? 'تنفيذ' : 'Apply'; ?></button>
</form>
<?php endif; ?>

<form method="post" onsubmit="return confirm('<?php echo $is_ar ? 'هل تريد حذف هذا الحساب نهائيًا؟' : 'Do you want to permanently delete this account?'; ?>');" style="margin-top:8px">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="delete_user">
<input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
<button type="submit" class="btnDanger"><?php echo $is_ar ? 'حذف الحساب' : 'Delete Account'; ?></button>
</form>

</div>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
</div>
<?php endif; ?>

<?php if ($section === 'support' && mr_is_seller()): ?>
<div class="gridTickets">
<div class="panelCard">
<div class="supportStack">

<div class="formCard ticketComposer">
<div class="cardTitle"><?php echo $is_ar ? 'فتح تذكرة جديدة' : 'Open New Ticket'; ?></div>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="seller_new_ticket">

<label><?php echo $is_ar ? 'عنوان التذكرة' : 'Subject'; ?></label>
<input name="subject" required>

<label><?php echo $is_ar ? 'الرسالة' : 'Message'; ?></label>
<textarea name="message" rows="4" placeholder="<?php echo $is_ar ? 'اكتب مشكلتك أو استفسارك...' : 'Describe your issue or question...'; ?>"></textarea>

<div class="msgActions">
<div class="fileUploadRow">
<label class="fileUploadBox">
<span><?php echo $is_ar ? 'رفع صورة' : 'Upload Image'; ?></span>
<input type="file" name="image" accept="image/*" onchange="showFileName(this)">
</label>
<span class="fileUploadName"><?php echo $is_ar ? 'لم يتم اختيار صورة' : 'No image selected'; ?></span>
</div>

<button type="submit"><?php echo $is_ar ? 'فتح التذكرة' : 'Open Ticket'; ?></button>
</div>
</form>
</div>

<div>
<div class="cardTitle"><?php echo $is_ar ? 'تذاكري السابقة' : 'My Previous Tickets'; ?></div>
<div class="ticketHistoryBox">
<?php if (!$seller_tickets): ?>
<div class="emptyState"><?php echo $is_ar ? 'لا توجد تذاكر بعد.' : 'No tickets yet.'; ?></div>
<?php else: ?>
<?php foreach ($seller_tickets as $t): ?>
<a class="ticketHistoryItem <?php echo $seller_support_ticket_id === (int)$t['id'] ? 'active' : ''; ?>" href="dashboard.php?section=support&ticket_id=<?php echo (int)$t['id']; ?>">
<div style="font-weight:800">#<?php echo (int)$t['id']; ?></div>
<div class="muted" style="margin-top:6px"><?php echo e($t['subject']); ?></div>
<div class="badge <?php echo status_class($t['status'] ?? 'pending'); ?>" style="margin-top:8px"><?php echo e($t['status']); ?></div>
</a>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>

</div>
</div>

<div class="panelCard">
<?php if (!$seller_active_ticket): ?>
<div class="emptyState"><?php echo $is_ar ? 'اختر تذكرة لعرض المحادثة.' : 'Select a ticket to view the chat.'; ?></div>
<?php else: ?>
<div class="cardTitle"><?php echo $is_ar ? 'التذكرة' : 'Ticket'; ?> #<?php echo (int)$seller_active_ticket['id']; ?></div>
<div class="muted"><?php echo e($seller_active_ticket['subject']); ?></div>
<div class="badge <?php echo status_class($seller_active_ticket['status'] ?? 'pending'); ?>" style="margin-top:8px"><?php echo e($seller_active_ticket['status']); ?></div>

<div class="chatBox" id="msgBox" style="margin-top:14px">
<?php if (!$seller_ticket_messages): ?>
<div class="emptyState"><?php echo $is_ar ? 'لا توجد رسائل بعد.' : 'No messages yet.'; ?></div>
<?php else: ?>
<?php foreach ($seller_ticket_messages as $m): ?>
<div class="bubble <?php echo $m['sender'] === 'user' ? 'me' : 'them'; ?>">
<?php if (!empty($m['message'])): ?>
<div class="msgText"><?php echo nl2br(e($m['message'])); ?></div>
<?php endif; ?>

<?php if (!empty($m['image_url'])): ?>
<div class="msgImageWrap">
<a href="../<?php echo e($m['image_url']); ?>" target="_blank">
<img class="msgImage" src="../<?php echo e($m['image_url']); ?>">
</a>
</div>
<?php endif; ?>

<div class="timeTxt"><?php echo e($m['created_at']); ?></div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<?php if (($seller_active_ticket['status'] ?? '') === 'closed'): ?>
<div class="notice errNotice" style="margin-top:12px"><?php echo $is_ar ? 'التذكرة مغلقة حاليًا.' : 'This ticket is currently closed.'; ?></div>
<?php else: ?>
<form method="post" enctype="multipart/form-data" class="msgForm">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="seller_reply_ticket">
<input type="hidden" name="ticket_id" value="<?php echo (int)$seller_active_ticket['id']; ?>">
<textarea name="message" rows="3" placeholder="<?php echo $is_ar ? 'اكتب رسالتك...' : 'Type your message...'; ?>"></textarea>

<div class="msgActions">
<div class="fileUploadRow">
<label class="fileUploadBox">
<span><?php echo $is_ar ? 'رفع صورة' : 'Upload Image'; ?></span>
<input type="file" name="image" accept="image/*" onchange="showFileName(this)">
</label>
<span class="fileUploadName"><?php echo $is_ar ? 'لم يتم اختيار صورة' : 'No image selected'; ?></span>
</div>

<button type="submit"><?php echo $is_ar ? 'إرسال' : 'Send'; ?></button>
</div>
</form>
<?php endif; ?>
<?php endif; ?>
</div>
</div>
<?php endif; ?>

<?php if ($section === 'tickets' && !mr_is_seller() && mr_can_manage_tickets()): ?>
<div class="gridTickets">
<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'صندوق التذاكر' : 'Tickets Inbox'; ?></div>
<div class="ticketList">
<?php foreach ($tickets as $t): ?>
<a class="ticketItem <?php echo $ticket_id === (int)$t['id'] ? 'active' : ''; ?>" href="dashboard.php?section=tickets&ticket_id=<?php echo (int)$t['id']; ?>">
<div style="font-weight:800">#<?php echo (int)$t['id']; ?> - <?php echo e($t['username']); ?></div>
<div class="muted" style="margin-top:6px"><?php echo e($t['subject']); ?></div>
<div class="badge <?php echo status_class($t['status'] ?? 'pending'); ?>" style="margin-top:8px"><?php echo e($t['status']); ?></div>
</a>
<?php endforeach; ?>
</div>
</div>

<div class="panelCard">
<?php if (!$active_ticket): ?>
<div class="emptyState"><?php echo $is_ar ? 'اختر تذكرة لعرض المحادثة.' : 'Select a ticket to view the chat.'; ?></div>
<?php else: ?>
<div class="cardTitle"><?php echo $is_ar ? 'تذكرة' : 'Ticket'; ?> #<?php echo (int)$active_ticket['id']; ?> - <?php echo e($active_ticket['username']); ?></div>
<div class="muted"><?php echo e($active_ticket['subject']); ?></div>
<div class="badge <?php echo status_class($active_ticket['status'] ?? 'pending'); ?>" style="margin-top:8px"><?php echo e($active_ticket['status']); ?></div>

<div class="chatBox" id="msgBox" style="margin-top:14px">
<?php if (!$ticket_messages): ?>
<div class="emptyState"><?php echo $is_ar ? 'لا توجد رسائل بعد.' : 'No messages yet.'; ?></div>
<?php else: ?>
<?php foreach ($ticket_messages as $m): ?>
<div class="bubble <?php echo $m['sender'] === 'admin' ? 'them' : 'me'; ?>">
<?php if (!empty($m['message'])): ?>
<div class="msgText"><?php echo nl2br(e($m['message'])); ?></div>
<?php endif; ?>

<?php if (!empty($m['image_url'])): ?>
<div class="msgImageWrap">
<a href="../<?php echo e($m['image_url']); ?>" target="_blank">
<img class="msgImage" src="../<?php echo e($m['image_url']); ?>">
</a>
</div>
<?php endif; ?>

<div class="timeTxt"><?php echo e($m['created_at']); ?></div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<?php if (($active_ticket['status'] ?? '') !== 'closed'): ?>
<form method="post" enctype="multipart/form-data" class="msgForm">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="reply_ticket">
<input type="hidden" name="ticket_id" value="<?php echo (int)$active_ticket['id']; ?>">
<textarea name="message" rows="3" placeholder="<?php echo $is_ar ? 'اكتب الرد...' : 'Type reply...'; ?>"></textarea>
<div class="msgActions">
<div class="fileUploadRow">
<label class="fileUploadBox">
<span><?php echo $is_ar ? 'رفع صورة' : 'Upload Image'; ?></span>
<input type="file" name="image" accept="image/*" onchange="showFileName(this)">
</label>
<span class="fileUploadName"><?php echo $is_ar ? 'لم يتم اختيار صورة' : 'No image selected'; ?></span>
</div>

<button type="submit"><?php echo $is_ar ? 'إرسال' : 'Send'; ?></button>
</div>
</form>
<?php else: ?>
<div class="notice" style="margin-top:12px"><?php echo $is_ar ? 'التذكرة مغلقة الآن. لا يمكن إرسال ردود جديدة إلا بعد إعادة فتحها.' : 'This ticket is closed. No new replies can be sent unless it is reopened.'; ?></div>
<?php endif; ?>

<form method="post" style="margin-top:10px">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="ticket_id" value="<?php echo (int)$active_ticket['id']; ?>">
<?php if (($active_ticket['status'] ?? '') !== 'closed'): ?>
<input type="hidden" name="action" value="close_ticket">
<button type="submit" class="btnGhost"><?php echo $is_ar ? 'إغلاق التذكرة' : 'Close Ticket'; ?></button>
<?php else: ?>
<input type="hidden" name="action" value="open_ticket">
<button type="submit" class="btnGhost"><?php echo $is_ar ? 'إعادة فتح' : 'Reopen'; ?></button>
<?php endif; ?>
</form>
<?php endif; ?>
</div>
</div>
<?php endif; ?>

<?php if ($section === 'products' && mr_can_manage_products()): ?>
<div class="grid2">
<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'قائمة المنتجات' : 'Products'; ?></div>

<?php if (!$products): ?>
<div class="emptyState"><?php echo $is_ar ? 'لا توجد منتجات بعد.' : 'No products yet.'; ?></div>
<?php else: ?>
<div class="productsScroll">
<div class="productsGrid">
<?php foreach ($products as $p): ?>
<div class="pCard">
<?php if (!empty($p['image_url'])): ?>
<img src="../<?php echo e($p['image_url']); ?>">
<?php else: ?>
<div style="height:180px;background:#111;display:flex;align-items:center;justify-content:center;opacity:.7">No Image</div>
<?php endif; ?>
<div class="pBody">
<div class="pName"><?php echo e($p['name']); ?></div>
<div class="pMeta"><?php echo $is_ar ? 'القسم' : 'Category'; ?>: <?php echo e($p['category'] ?: 'General'); ?></div>
<div class="pMeta"><?php echo $is_ar ? 'السعر' : 'Price'; ?>: <?php echo n2((float)$p['price_usdt']); ?> USDT</div>
<div class="pMeta"><?php echo $is_ar ? 'المتاح' : 'Available'; ?>: <?php echo (int)$p['available_stock']; ?> | <?php echo $is_ar ? 'المباع' : 'Sold'; ?>: <?php echo (int)$p['sold_stock']; ?></div>
<div class="pMeta">
<?php echo $is_ar ? 'نوع المخزون' : 'Stock Type'; ?>:
<?php
$stockTypeLabel = match($p['stock_type'] ?? 'email') {
'email' => 'Email',
'email_2fa' => 'Email + 2FA',
'email_access_mail' => 'Email + Access Mail',
'email_2fa_access_mail' => 'Email + 2FA + Access Mail',
'rdp' => 'RDP',
default => (string)($p['stock_type'] ?? 'Email')
};
echo e($stockTypeLabel);
?>
</div>
<div class="pMeta"><?php echo $is_ar ? 'الحالة' : 'Status'; ?>:
<span class="badge <?php echo !empty($p['is_active']) ? 'statusGood' : 'statusBad'; ?>">
<?php echo !empty($p['is_active']) ? ($is_ar ? 'مفعل' : 'Active') : ($is_ar ? 'معطل' : 'Disabled'); ?>
</span>
</div>

<div class="productActions">
<a class="pill" href="dashboard.php?section=products&edit_product_id=<?php echo (int)$p['id']; ?>"><?php echo $is_ar ? 'تعديل' : 'Edit'; ?></a>
<a class="pill" href="dashboard.php?section=products&stock_product_id=<?php echo (int)$p['id']; ?>"><?php echo $is_ar ? 'المخزون' : 'Stock'; ?></a>

<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="enable_product">
<input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
<button type="submit" class="btnOk"><?php echo $is_ar ? 'تفعيل' : 'Enable'; ?></button>
</form>

<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="disable_product">
<input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
<button type="submit" class="btnGhost"><?php echo $is_ar ? 'تعطيل' : 'Disable'; ?></button>
</form>

<form method="post" onsubmit="return confirm('<?php echo $is_ar ? 'حذف المنتج نهائيًا؟' : 'Delete this product permanently?'; ?>');">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="delete_product">
<input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
<button type="submit" class="btnDanger"><?php echo $is_ar ? 'حذف' : 'Delete'; ?></button>
</form>

<form method="post" onsubmit="return confirm('<?php echo $is_ar ? 'حذف إجباري نهائي للمنتج وكل المخزون وكل العناصر المرتبطة؟' : 'Force delete this product, stock and related items permanently?'; ?>');">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="force_delete_product">
<input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
<button type="submit" class="btnDanger"><?php echo $is_ar ? 'Force Delete' : 'Force Delete'; ?></button>
</form>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
</div>

<div class="panelCard">
<?php if ($edit_product): ?>
<div class="cardTitle"><?php echo $is_ar ? 'تعديل المنتج' : 'Edit Product'; ?> #<?php echo (int)$edit_product['id']; ?></div>
<div class="formCard">
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="update_product">
<input type="hidden" name="product_id" value="<?php echo (int)$edit_product['id']; ?>">

<label><?php echo $is_ar ? 'اسم المنتج' : 'Product Name'; ?></label>
<input name="name" value="<?php echo e($edit_product['name']); ?>" required>

<label><?php echo $is_ar ? 'القسم' : 'Category'; ?></label>
<input name="category" value="<?php echo e($edit_product['category'] ?? ''); ?>">

<label><?php echo $is_ar ? 'السعر USDT' : 'Price USDT'; ?></label>
<input name="price_usdt" type="number" step="0.01" value="<?php echo e($edit_product['price_usdt']); ?>">

<label><?php echo $is_ar ? 'نوع المخزون' : 'Stock Type'; ?></label>
<select name="stock_type">
<option value="email" <?php echo (($edit_product['stock_type'] ?? '') === 'email' ? 'selected' : ''); ?>>Email</option>
<option value="email_2fa" <?php echo (($edit_product['stock_type'] ?? '') === 'email_2fa' ? 'selected' : ''); ?>>Email + 2FA</option>
<option value="email_access_mail" <?php echo (($edit_product['stock_type'] ?? '') === 'email_access_mail' ? 'selected' : ''); ?>>Email + Access Mail</option>
<option value="email_2fa_access_mail" <?php echo (($edit_product['stock_type'] ?? '') === 'email_2fa_access_mail' ? 'selected' : ''); ?>>Email + 2FA + Access Mail</option>
<option value="rdp" <?php echo (($edit_product['stock_type'] ?? '') === 'rdp' ? 'selected' : ''); ?>>RDP</option>
</select>

<label><?php echo $is_ar ? 'الوصف' : 'Description'; ?></label>
<textarea name="description" rows="4"><?php echo e($edit_product['description'] ?? ''); ?></textarea>

<label><?php echo $is_ar ? 'صورة جديدة' : 'New Image'; ?></label>
<input type="file" name="image" accept="image/*">

<label style="display:flex;align-items:center;gap:8px;margin-top:10px">
<input type="checkbox" name="is_active" value="1" <?php echo !empty($edit_product['is_active']) ? 'checked' : ''; ?> style="width:auto;margin:0">
<span><?php echo $is_ar ? 'المنتج مفعل' : 'Product is active'; ?></span>
</label>

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'حفظ التعديل' : 'Save Product'; ?></button>
</form>
</div>
<?php elseif ($stock_product): ?>
<div class="cardTitle"><?php echo $is_ar ? 'إدارة المخزون' : 'Manage Stock'; ?> - <?php echo e($stock_product['name']); ?></div>

<div class="formCard" style="margin-bottom:14px">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="add_stock">
<input type="hidden" name="product_id" value="<?php echo (int)$stock_product['id']; ?>">
<input type="hidden" name="stock_type" value="<?php echo e((string)($stock_product['stock_type'] ?? 'email')); ?>">

<label><?php echo $is_ar ? 'إضافة مخزون جديد' : 'Add New Stock'; ?></label>
<textarea name="bulk" rows="8" placeholder="<?php
echo match($stock_product['stock_type']) {
'rdp' => 'host|user|pass',
'email' => 'user@example.com:pass123',
'email_2fa' => 'user@example.com:pass123:2fa-secret',
'email_access_mail' => 'user@example.com:pass123:access@example.com:accessPass',
'email_2fa_access_mail' => 'user@example.com:pass123:2fa-secret:access@example.com:accessPass',
default => 'user@example.com:pass123'
};
?>"></textarea>

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'إضافة المخزون' : 'Add Stock'; ?></button>
</form>
</div>

<div class="tableWrap stockScroll">
<table>
<tr>
<th style="min-width:80px">ID</th>
<th style="min-width:280px"><?php echo $is_ar ? 'البيانات' : 'Data'; ?></th>
<th style="min-width:110px"><?php echo $is_ar ? 'الحالة' : 'Status'; ?></th>
<th style="min-width:110px"><?php echo $is_ar ? 'إجراء' : 'Action'; ?></th>
</tr>
<?php foreach ($stock_rows as $s): ?>
<tr>
<td>#<?php echo (int)$s['id']; ?></td>
<td style="white-space:normal;word-break:break-word">
<?php if (($s['type'] ?? '') === 'rdp'): ?>
Host: <?php echo e($s['host']); ?><br>
User: <?php echo e($s['user']); ?><br>
Pass: <?php echo e($s['pass']); ?>

<?php elseif (($s['type'] ?? '') === 'email'): ?>
Email: <?php echo e($s['email'] ?? ''); ?><br>
Pass: <?php echo e($s['pass'] ?? ''); ?>

<?php elseif (($s['type'] ?? '') === 'email_2fa'): ?>
Email: <?php echo e($s['email'] ?? ''); ?><br>
Pass: <?php echo e($s['pass'] ?? ''); ?><br>
2FA: <?php echo e($s['twofa_code'] ?? ''); ?>

<?php elseif (($s['type'] ?? '') === 'email_access_mail'): ?>
Email: <?php echo e($s['email'] ?? ''); ?><br>
Pass: <?php echo e($s['pass'] ?? ''); ?><br>
Access Email: <?php echo e($s['access_email'] ?? ''); ?><br>
Access Pass: <?php echo e($s['access_pass'] ?? ''); ?>

<?php elseif (($s['type'] ?? '') === 'email_2fa_access_mail'): ?>
Email: <?php echo e($s['email'] ?? ''); ?><br>
Pass: <?php echo e($s['pass'] ?? ''); ?><br>
2FA: <?php echo e($s['twofa_code'] ?? ''); ?><br>
Access Email: <?php echo e($s['access_email'] ?? ''); ?><br>
Access Pass: <?php echo e($s['access_pass'] ?? ''); ?>

<?php else: ?>
<?php echo e($s['email'] ?? ''); ?><br>
<?php echo e($s['pass'] ?? ''); ?>
<?php endif; ?>
</td>
<td><span class="badge <?php echo ($s['status'] === 'available' ? 'statusGood' : 'statusBad'); ?>"><?php echo e($s['status']); ?></span></td>
<td>
<?php if ($s['status'] === 'available'): ?>
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="delete_stock">
<input type="hidden" name="stock_id" value="<?php echo (int)$s['id']; ?>">
<input type="hidden" name="product_id" value="<?php echo (int)$stock_product['id']; ?>">
<button type="submit" class="btnDanger"><?php echo $is_ar ? 'حذف' : 'Delete'; ?></button>
</form>
<?php else: ?>
<span class="muted"><?php echo $is_ar ? 'تم البيع' : 'Sold'; ?></span>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php else: ?>
<div class="emptyState"><?php echo $is_ar ? 'اختر منتجًا للتعديل أو إدارة المخزون.' : 'Choose a product to edit or manage stock.'; ?></div>
<?php endif; ?>
</div>
</div>
<?php endif; ?>

<?php if ($section === 'add_product' && mr_can_manage_products()): ?>
<div class="grid2">
<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'إضافة منتج جديد' : 'Add New Product'; ?></div>
<div class="formCard">
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="add_product">

<label><?php echo $is_ar ? 'اسم المنتج' : 'Product Name'; ?></label>
<input name="name" required>

<label><?php echo $is_ar ? 'القسم' : 'Category'; ?></label>
<input name="category" placeholder="<?php echo $is_ar ? 'مثال: Microsoft / Education / Streaming' : 'Example: Microsoft / Education / Streaming'; ?>">

<label><?php echo $is_ar ? 'السعر USDT' : 'Price USDT'; ?></label>
<input name="price_usdt" type="number" step="0.01" required>

<label><?php echo $is_ar ? 'نوع المخزون' : 'Stock Type'; ?></label>
<select name="stock_type" id="addProductStockType">
<option value="email">Email</option>
<option value="email_2fa">Email + 2FA</option>
<option value="email_access_mail">Email + Access Mail</option>
<option value="email_2fa_access_mail">Email + 2FA + Access Mail</option>
<option value="rdp">RDP</option>
</select>

<label><?php echo $is_ar ? 'الوصف' : 'Description'; ?></label>
<textarea name="description" rows="4"></textarea>

<label><?php echo $is_ar ? 'صورة المنتج' : 'Product Image'; ?></label>
<input type="file" name="image" accept="image/*">

<label><?php echo $is_ar ? 'مخزون أولي' : 'Initial Stock'; ?></label>
<textarea id="addProductBulk" name="bulk" rows="10" placeholder="user@example.com:pass123"></textarea>

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'إضافة المنتج' : 'Add Product'; ?></button>
</form>
</div>
</div>

<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'تنسيق الإدخال' : 'Input Format'; ?></div>
<div class="formCard">

<div style="font-weight:800">Email</div>
<div class="muted">email:password</div>
<div class="muted" style="margin-top:4px">user@example.com:pass123</div>

<div style="font-weight:800;margin-top:16px">Email + 2FA</div>
<div class="muted">email:password:2fa</div>
<div class="muted" style="margin-top:4px">user@example.com:pass123:2fa-secret</div>

<div style="font-weight:800;margin-top:16px">Email + Access Mail</div>
<div class="muted">email:password:access_email:access_password</div>
<div class="muted" style="margin-top:4px">user@example.com:pass123:access@example.com:accessPass</div>

<div style="font-weight:800;margin-top:16px">Email + 2FA + Access Mail</div>
<div class="muted">email:password:2fa:access_email:access_password</div>
<div class="muted" style="margin-top:4px">user@example.com:pass123:2fa-secret:access@example.com:accessPass</div>

<div style="font-weight:800;margin-top:16px">RDP</div>
<div class="muted">host|user|pass</div>

<div style="font-weight:800;margin-top:16px"><?php echo $is_ar ? 'ملاحظة' : 'Note'; ?></div>
<div class="muted"><?php echo $is_ar ? 'كل سطر يعتبر عنصر مخزون مستقل حسب نوع المنتج المختار.' : 'Each line is treated as a separate stock item based on the selected stock type.'; ?></div>
</div>
</div>
</div>
<?php endif; ?>

<?php if ($section === 'my_deposits' && admin_can_view_own_finance_tabs()): ?>
<div class="grid2">
<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'إنشاء طلب إيداع' : 'Create Deposit Request'; ?></div>
<div class="formCard">
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="create_my_deposit">

<label><?php echo $is_ar ? 'اختر وسيلة الدفع' : 'Select Payment Method'; ?></label>
<select name="payment_method_id" required id="depositPaymentMethod">
<option value=""><?php echo $is_ar ? 'اختر وسيلة الدفع' : 'Select payment method'; ?></option>
<?php foreach ($active_payment_methods as $pm): ?>
<option
value="<?php echo (int)$pm['id']; ?>"
data-title="<?php echo e($pm['title'] ?? ''); ?>"
data-currency="<?php echo e($pm['currency'] ?? ''); ?>"
data-network="<?php echo e($pm['network'] ?? ''); ?>"
data-address="<?php echo e($pm['address'] ?? ''); ?>"
data-account="<?php echo e($pm['account_name'] ?? ''); ?>"
>
<?php echo e((($pm['title'] ?? '') !== '' ? $pm['title'] : (($pm['currency'] ?? 'USDT') . ' - ' . ($pm['network'] ?? '')))); ?>
</option>
<?php endforeach; ?>
</select>

<div id="depositMethodInfo" class="muted" style="margin-top:10px"></div>

<label><?php echo $is_ar ? 'المبلغ' : 'Amount'; ?></label>
<input type="number" step="0.01" name="amount" required>

<label>TxID</label>
<input name="txid">

<label><?php echo $is_ar ? 'صورة الإثبات' : 'Proof Image'; ?></label>
<input type="file" name="proof_image" accept="image/*">

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'إرسال الطلب' : 'Submit Request'; ?></button>
</form>
</div>
</div>

<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'إيداعاتي' : 'My Deposits'; ?></div>
<div class="financeGrid">
<div class="financeStat">
<div class="financeTitle"><?php echo $is_ar ? 'الرصيد الحالي' : 'Current Balance'; ?></div>
<div class="financeValue"><?php echo n2($admin_wallet_usdt); ?> USDT</div>
</div>
<div class="financeStat">
<div class="financeTitle"><?php echo $is_ar ? 'الرصيد المحجوز' : 'On Hold'; ?></div>
<div class="financeValue"><?php echo n2($admin_hold_usdt); ?> USDT</div>
</div>
</div>
<div class="tableWrap">
<table>
<tr>
<th>ID</th>
<th><?php echo $is_ar ? 'الشبكة' : 'Network'; ?></th>
<th><?php echo $is_ar ? 'المبلغ' : 'Amount'; ?></th>
<th>TxID</th>
<th><?php echo $is_ar ? 'الحالة' : 'Status'; ?></th>
<th><?php echo $is_ar ? 'ملاحظة الأدمن' : 'Admin Note'; ?></th>
</tr>
<?php if (!$my_deposit_rows): ?>
<tr><td colspan="6"><div class="emptyState"><?php echo $is_ar ? 'لا توجد طلبات بعد.' : 'No requests yet.'; ?></div></td></tr>
<?php else: ?>
<?php foreach ($my_deposit_rows as $r): ?>
<tr>
<td>#<?php echo (int)$r['id']; ?></td>
<td><?php echo e($r['network'] ?? ''); ?></td>
<td><?php echo n2((float)$r['amount']); ?> USDT</td>
<td style="max-width:220px;word-break:break-all"><?php echo e($r['txid'] ?? ''); ?></td>
<td><span class="badge <?php echo status_class($r['status'] ?? 'pending'); ?>"><?php echo e($r['status']); ?></span></td>
<td><?php echo e($r['admin_note'] ?? ''); ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<?php if ($my_dep_total > $my_per_page): ?>
<div class="row" style="margin-top:12px;justify-content:space-between">
<div class="muted">
<?php echo $is_ar ? 'صفحة' : 'Page'; ?> <?php echo (int)$my_dep_page; ?> / <?php echo (int)$my_dep_pages; ?>
</div>
<div class="row" style="flex:0 0 auto">
<?php if ($my_dep_page > 1): ?>
<a class="pill" href="dashboard.php?section=my_deposits&dep_page=<?php echo $my_dep_page - 1; ?>">
<?php echo $is_ar ? 'السابق' : 'Prev'; ?>
</a>
<?php endif; ?>

<?php if ($my_dep_page < $my_dep_pages): ?>
<a class="pill" href="dashboard.php?section=my_deposits&dep_page=<?php echo $my_dep_page + 1; ?>">
<?php echo $is_ar ? 'التالي' : 'Next'; ?>
</a>
<?php endif; ?>
</div>
</div>
<?php endif; ?>

</div>
</div>
</div>
<?php endif; ?>

<?php if ($section === 'my_withdraws' && admin_can_view_own_finance_tabs()): ?>
<div class="grid2">
<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'إنشاء طلب سحب' : 'Create Withdraw Request'; ?></div>
<div class="formCard">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="create_my_withdraw">

<label><?php echo $is_ar ? 'اختر وسيلة السحب' : 'Select Withdraw Method'; ?></label>
<select name="payment_method_id" required>
<option value=""><?php echo $is_ar ? 'اختر وسيلة السحب' : 'Select withdraw method'; ?></option>
<?php foreach ($active_payment_methods as $pm): ?>
<option value="<?php echo (int)$pm['id']; ?>">
<?php echo e((($pm['title'] ?? '') !== '' ? $pm['title'] : (($pm['currency'] ?? 'USDT') . ' - ' . ($pm['network'] ?? '')))); ?>
</option>
<?php endforeach; ?>
</select>

<label><?php echo $is_ar ? 'العنوان / المعرف / بيانات الاستلام' : 'Address / ID / Receiving Details'; ?></label>
<input name="withdraw_value" required>

<label><?php echo $is_ar ? 'المبلغ' : 'Amount'; ?></label>
<input type="number" step="0.01" name="amount" required>

<label><?php echo $is_ar ? 'ملاحظة' : 'Note'; ?></label>
<textarea name="note" rows="3"></textarea>

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'إرسال الطلب' : 'Submit Request'; ?></button>
</form>
</div>
</div>

<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'طلبات السحب الخاصة بي' : 'My Withdraw Requests'; ?></div>
<div class="financeGrid">
<div class="financeStat">
<div class="financeTitle"><?php echo $is_ar ? 'الرصيد الحالي' : 'Current Balance'; ?></div>
<div class="financeValue"><?php echo n2($admin_wallet_usdt); ?> USDT</div>
</div>
<div class="financeStat">
<div class="financeTitle"><?php echo $is_ar ? 'الرصيد المحجوز' : 'On Hold'; ?></div>
<div class="financeValue"><?php echo n2($admin_hold_usdt); ?> USDT</div>
</div>
</div>
<div class="tableWrap">
<table>
<tr>
<th>ID</th>
<th><?php echo $is_ar ? 'الشبكة' : 'Network'; ?></th>
<th><?php echo $is_ar ? 'العنوان' : 'Address'; ?></th>
<th><?php echo $is_ar ? 'المبلغ' : 'Amount'; ?></th>
<th><?php echo $is_ar ? 'الحالة' : 'Status'; ?></th>
<th><?php echo $is_ar ? 'ملاحظة الأدمن' : 'Admin Note'; ?></th>
</tr>
<?php if (!$my_withdraw_rows): ?>
<tr><td colspan="6"><div class="emptyState"><?php echo $is_ar ? 'لا توجد طلبات بعد.' : 'No requests yet.'; ?></div></td></tr>
<?php else: ?>
<?php foreach ($my_withdraw_rows as $r): ?>
<tr>
<td>#<?php echo (int)$r['id']; ?></td>
<td><?php echo e($r['network'] ?? ''); ?></td>
<td style="max-width:220px;word-break:break-all"><?php echo e($r['address'] ?? ''); ?></td>
<td><?php echo n2((float)$r['amount']); ?> USDT</td>
<td><span class="badge <?php echo status_class($r['status'] ?? 'pending'); ?>"><?php echo e($r['status']); ?></span></td>
<td><?php echo e($r['admin_note'] ?? ''); ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<?php if ($my_wd_total > $my_per_page): ?>
<div class="row" style="margin-top:12px;justify-content:space-between">
<div class="muted">
<?php echo $is_ar ? 'صفحة' : 'Page'; ?> <?php echo (int)$my_wd_page; ?> / <?php echo (int)$my_wd_pages; ?>
</div>
<div class="row" style="flex:0 0 auto">
<?php if ($my_wd_page > 1): ?>
<a class="pill" href="dashboard.php?section=my_withdraws&wd_page=<?php echo $my_wd_page - 1; ?>">
<?php echo $is_ar ? 'السابق' : 'Prev'; ?>
</a>
<?php endif; ?>

<?php if ($my_wd_page < $my_wd_pages): ?>
<a class="pill" href="dashboard.php?section=my_withdraws&wd_page=<?php echo $my_wd_page + 1; ?>">
<?php echo $is_ar ? 'التالي' : 'Next'; ?>
</a>
<?php endif; ?>
</div>
</div>
<?php endif; ?>

</div>
</div>
</div>
<?php endif; ?>

<?php if ($section === 'deposits' && mr_can_manage_deposits()): ?>
<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'طلبات الشحن' : 'Deposit Requests'; ?></div>
<div class="tableWrap">
<table>
<tr>
<th>ID</th>
<th><?php echo $is_ar ? 'المستخدم' : 'User'; ?></th>
<th><?php echo $is_ar ? 'الشبكة' : 'Network'; ?></th>
<th><?php echo $is_ar ? 'المبلغ' : 'Amount'; ?></th>
<th>TxID</th>
<th><?php echo $is_ar ? 'الإثبات' : 'Proof'; ?></th>
<th><?php echo $is_ar ? 'الحالة' : 'Status'; ?></th>
<th><?php echo $is_ar ? 'إجراء' : 'Action'; ?></th>
</tr>
<?php foreach ($deposit_rows as $r): ?>
<tr>
<td>#<?php echo (int)$r['id']; ?></td>
<td><?php echo e($r['username']); ?></td>
<td style="max-width:240px;word-break:break-word"><?php echo e($r['network'] ?? ''); ?></td>
<td><?php echo n2((float)$r['amount']); ?> USDT</td>
<td style="max-width:220px;word-break:break-all"><?php echo e($r['txid'] ?? ''); ?></td>
<td>
<?php if (!empty($r['proof_image'])): ?>
<a href="../<?php echo e($r['proof_image']); ?>" target="_blank">
<img src="../<?php echo e($r['proof_image']); ?>" class="proofThumb">
</a>
<?php endif; ?>
</td>
<td><span class="badge <?php echo status_class($r['status'] ?? 'pending'); ?>"><?php echo e($r['status']); ?></span></td>
<td style="min-width:260px">
<?php if (($r['status'] ?? '') === 'pending'): ?>
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
<input name="note" placeholder="<?php echo $is_ar ? 'ملاحظة اختيارية' : 'Optional note'; ?>">
<div class="row" style="margin-top:8px">
<button type="submit" name="action" value="approve_deposit" class="btnOk"><?php echo $is_ar ? 'موافقة' : 'Approve'; ?></button>
<button type="submit" name="action" value="reject_deposit" class="btnDanger"><?php echo $is_ar ? 'رفض' : 'Reject'; ?></button>
</div>
</form>
<?php else: ?>
<div class="muted"><?php echo e($r['admin_note'] ?? ''); ?></div>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
<?php endif; ?>

<?php if ($section === 'withdraws' && mr_can_manage_withdraws()): ?>
<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'طلبات السحب' : 'Withdraw Requests'; ?></div>
<div class="tableWrap">
<table>
<tr>
<th>ID</th>
<th><?php echo $is_ar ? 'المستخدم' : 'User'; ?></th>
<th><?php echo $is_ar ? 'الشبكة' : 'Network'; ?></th>
<th><?php echo $is_ar ? 'العنوان' : 'Address'; ?></th>
<th><?php echo $is_ar ? 'المبلغ' : 'Amount'; ?></th>
<th><?php echo $is_ar ? 'الملاحظة' : 'Note'; ?></th>
<th><?php echo $is_ar ? 'الحالة' : 'Status'; ?></th>
<th><?php echo $is_ar ? 'إجراء' : 'Action'; ?></th>
</tr>
<?php if (!$withdraw_rows): ?>
<tr>
<td colspan="8"><div class="emptyState"><?php echo $is_ar ? 'لا توجد طلبات سحب بعد.' : 'No withdraw requests yet.'; ?></div></td>
</tr>
<?php else: ?>
<?php foreach ($withdraw_rows as $r): ?>
<tr>
<td>#<?php echo (int)$r['id']; ?></td>
<td><?php echo e($r['username']); ?></td>
<td><?php echo e($r['network'] ?? ''); ?></td>
<td style="max-width:240px;word-break:break-all"><?php echo e($r['address'] ?? ''); ?></td>
<td><?php echo n2((float)$r['amount']); ?> USDT</td>
<td><?php echo e($r['note'] ?? ''); ?></td>
<td><span class="badge <?php echo status_class($r['status'] ?? 'pending'); ?>"><?php echo e($r['status']); ?></span></td>
<td style="min-width:260px">
<?php if (($r['status'] ?? '') === 'pending'): ?>
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
<input name="note" placeholder="<?php echo $is_ar ? 'ملاحظة اختيارية' : 'Optional note'; ?>">
<div class="row" style="margin-top:8px">
<button type="submit" name="action" value="approve_withdraw" class="btnOk"><?php echo $is_ar ? 'موافقة' : 'Approve'; ?></button>
<button type="submit" name="action" value="reject_withdraw" class="btnDanger"><?php echo $is_ar ? 'رفض واسترجاع' : 'Reject & Refund'; ?></button>
</div>
</form>
<?php else: ?>
<div class="muted"><?php echo e($r['admin_note'] ?? ''); ?></div>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>
</div>
</div>
<?php endif; ?>

<?php if ($section === 'settings' && mr_can_manage_settings()): ?>
<div class="settingsGrid">
<div class="panelCard">
<div class="cardTitle"><?php echo $is_ar ? 'الإعدادات العامة والثيم' : 'General Settings & Theme'; ?></div>
<div class="formCard">
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="save_general_settings">

<label><?php echo $is_ar ? 'اسم الموقع' : 'Site Name'; ?></label>
<input name="site_name" value="<?php echo e($site_name); ?>">

<label style="display:flex;align-items:center;gap:8px;margin-top:10px">
<input type="checkbox" name="welcome_bonus_enabled" value="1" <?php echo $welcome_bonus_enabled === '1' ? 'checked' : ''; ?> style="width:auto;margin:0">
<span><?php echo $is_ar ? 'تفعيل بونس الترحيب' : 'Enable welcome bonus'; ?></span>
</label>

<label><?php echo $is_ar ? 'قيمة بونس الترحيب (USDT)' : 'Welcome bonus amount (USDT)'; ?></label>
<input name="welcome_bonus_amount" type="number" step="0.01" value="<?php echo e($welcome_bonus_amount); ?>">
<label style="display:flex;align-items:center;gap:8px;margin-top:10px">
<input type="checkbox" name="seller_commission_enabled" value="1" <?php echo $seller_commission_enabled === '1' ? 'checked' : ''; ?> style="width:auto;margin:0">
<span><?php echo $is_ar ? 'تفعيل عمولة على السيلرز' : 'Enable seller commission'; ?></span>
</label>

<label><?php echo $is_ar ? 'نسبة العمولة %' : 'Commission percent %'; ?></label>
<input name="seller_commission_percent" type="number" step="0.01" min="0" max="100" value="<?php echo e($seller_commission_percent); ?>">
<label><?php echo $is_ar ? 'اللغة الافتراضية' : 'Default Language'; ?></label>
<select name="site_lang_default">
<option value="en" <?php echo $site_lang_default === 'en' ? 'selected' : ''; ?>>English</option>
<option value="ar" <?php echo $site_lang_default === 'ar' ? 'selected' : ''; ?>>العربية</option>
</select>

<div class="modeTabs">
<label class="modeChip">
<input type="radio" name="theme_mode" value="dark" <?php echo $theme_mode === 'dark' ? 'checked' : ''; ?>>
<span>Dark</span>
</label>
<label class="modeChip">
<input type="radio" name="theme_mode" value="light" <?php echo $theme_mode === 'light' ? 'checked' : ''; ?>>
<span>Light</span>
</label>
</div>

<label><?php echo $is_ar ? 'الثيم الجاهز' : 'Theme Preset'; ?></label>
<select name="theme_preset">
<option value="account_modern" <?php echo ($theme['preset_key'] ?? '') === 'account_modern' ? 'selected' : ''; ?>>Account Modern</option>
<option value="clean_light" <?php echo ($theme['preset_key'] ?? '') === 'clean_light' ? 'selected' : ''; ?>>Clean Light</option>
<option value="glass_light" <?php echo ($theme['preset_key'] ?? '') === 'glass_light' ? 'selected' : ''; ?>>Glass Light</option>
<option value="violet_modern" <?php echo ($theme['preset_key'] ?? '') === 'violet_modern' ? 'selected' : ''; ?>>Violet Modern</option>
<option value="emerald_modern" <?php echo ($theme['preset_key'] ?? '') === 'emerald_modern' ? 'selected' : ''; ?>>Emerald Modern</option>
<option value="sunset_modern" <?php echo ($theme['preset_key'] ?? '') === 'sunset_modern' ? 'selected' : ''; ?>>Sunset Modern</option>
</select>

<label style="display:flex;align-items:center;gap:8px;margin-top:10px">
<input type="checkbox" name="payment_enabled" value="1" <?php echo $payment_enabled === '1' ? 'checked' : ''; ?> style="width:auto;margin:0">
<span><?php echo $is_ar ? 'الدفع مفعل' : 'Payments enabled'; ?></span>
</label>

<label style="display:flex;align-items:center;gap:8px;margin-top:10px">
<input type="checkbox" name="announce_enabled" value="1" <?php echo $announce_enabled === '1' ? 'checked' : ''; ?> style="width:auto;margin:0">
<span><?php echo $is_ar ? 'إظهار إعلان رئيسي' : 'Show main announcement'; ?></span>
</label>

<label><?php echo $is_ar ? 'نص الإعلان الرئيسي' : 'Main Announcement Text'; ?></label>
<textarea name="announce_text" rows="4"><?php echo e($announce_text); ?></textarea>

<label><?php echo $is_ar ? 'أيقونة الموقع' : 'Site Icon'; ?></label>
<input type="file" name="site_icon" accept=".ico,image/png,image/jpeg,image/webp,image/svg+xml">

<?php if ($site_icon): ?>
<div style="display:flex;align-items:center;gap:10px;margin-top:10px">
<div class="previewIcon">
<img src="../<?php echo e($site_icon); ?>">
</div>
<div class="muted"><?php echo $is_ar ? 'الأيقونة الحالية' : 'Current icon'; ?></div>
</div>
<?php endif; ?>

<button type="submit" style="margin-top:12px"><?php echo $is_ar ? 'حفظ الإعدادات' : 'Save Settings'; ?></button>
</form>
</div>
</div>

<div class="panelCard" id="payments">
<div class="cardTitle"><?php echo $is_ar ? 'وسائل الدفع المرنة' : 'Flexible Payment Methods'; ?></div>

<div class="formCard" style="margin-bottom:14px">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="add_payment_method">

<label><?php echo $is_ar ? 'نوع الوسيلة' : 'Method Type'; ?></label>
<select name="method_type">
<option value="crypto"><?php echo $is_ar ? 'عملة رقمية' : 'Crypto'; ?></option>
<option value="binance_id">Binance ID</option>
<option value="manual"><?php echo $is_ar ? 'يدوي' : 'Manual'; ?></option>
</select>

<label><?php echo $is_ar ? 'عنوان العرض' : 'Display Title'; ?></label>
<input name="title" placeholder="<?php echo $is_ar ? 'مثال: USDT TRC20 أو Binance ID' : 'Example: USDT TRC20 or Binance ID'; ?>">

<label><?php echo $is_ar ? 'العملة' : 'Currency'; ?></label>
<input name="currency" placeholder="USDT / BTC / ETH / BNB">

<label><?php echo $is_ar ? 'الشبكة' : 'Network'; ?></label>
<input name="network" placeholder="TRC20 / BEP20 / ERC20">

<label><?php echo $is_ar ? 'العنوان أو المعرف' : 'Address or ID'; ?></label>
<input name="address" placeholder="<?php echo $is_ar ? 'المحفظة أو Binance ID أو أي معرف' : 'Wallet address or Binance ID'; ?>">

<label><?php echo $is_ar ? 'اسم الحساب' : 'Account Name'; ?></label>
<input name="account_name" placeholder="<?php echo $is_ar ? 'اسم صاحب الحساب' : 'Account holder'; ?>">

<label><?php echo $is_ar ? 'ملاحظة' : 'Note'; ?></label>
<input name="note" placeholder="<?php echo $is_ar ? 'مثال: أرسل لقطة بعد التحويل' : 'Example: send screenshot after payment'; ?>">

<label><?php echo $is_ar ? 'حقل إضافي 1 - الاسم' : 'Extra Field 1 Label'; ?></label>
<input name="label1" placeholder="<?php echo $is_ar ? 'مثال: البريد' : 'Example: Email'; ?>">

<label><?php echo $is_ar ? 'حقل إضافي 1 - القيمة' : 'Extra Field 1 Value'; ?></label>
<input name="value1">

<label><?php echo $is_ar ? 'حقل إضافي 2 - الاسم' : 'Extra Field 2 Label'; ?></label>
<input name="label2">

<label><?php echo $is_ar ? 'حقل إضافي 2 - القيمة' : 'Extra Field 2 Value'; ?></label>
<input name="value2">

<label><?php echo $is_ar ? 'الترتيب' : 'Sort Order'; ?></label>
<input name="sort_order" type="number" value="0">

<label style="display:flex;align-items:center;gap:8px;margin-top:10px">
<input type="checkbox" name="is_active" value="1" checked style="width:auto;margin:0">
<span><?php echo $is_ar ? 'مفعلة' : 'Active'; ?></span>
</label>

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'إضافة وسيلة دفع' : 'Add Payment Method'; ?></button>
</form>
</div>

<div class="tableWrap">
<table>
<tr>
<th>ID</th>
<th><?php echo $is_ar ? 'النوع' : 'Type'; ?></th>
<th><?php echo $is_ar ? 'العنوان' : 'Title'; ?></th>
<th><?php echo $is_ar ? 'العملة' : 'Currency'; ?></th>
<th><?php echo $is_ar ? 'الشبكة' : 'Network'; ?></th>
<th><?php echo $is_ar ? 'العنوان/المعرف' : 'Address / ID'; ?></th>
<th><?php echo $is_ar ? 'الحالة' : 'Status'; ?></th>
<th><?php echo $is_ar ? 'إجراء' : 'Action'; ?></th>
</tr>
<?php foreach ($payment_methods as $pm): ?>
<tr>
<td>#<?php echo (int)$pm['id']; ?></td>
<td><?php echo e($pm['method_type'] ?? 'crypto'); ?></td>
<td><?php echo e($pm['title'] ?? ''); ?></td>
<td><?php echo e($pm['currency'] ?? ''); ?></td>
<td><?php echo e($pm['network'] ?? ''); ?></td>
<td style="max-width:260px;word-break:break-all"><?php echo e($pm['address'] ?? ''); ?></td>
<td><span class="badge <?php echo !empty($pm['is_active']) ? 'statusGood' : 'statusBad'; ?>"><?php echo !empty($pm['is_active']) ? ($is_ar ? 'مفعل' : 'Active') : ($is_ar ? 'معطل' : 'Disabled'); ?></span></td>
<td>
<div class="row">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="toggle_payment_method">
<input type="hidden" name="id" value="<?php echo (int)$pm['id']; ?>">
<button type="submit" class="btnGhost"><?php echo $is_ar ? 'تبديل' : 'Toggle'; ?></button>
</form>
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="delete_payment_method">
<input type="hidden" name="id" value="<?php echo (int)$pm['id']; ?>">
<button type="submit" class="btnDanger"><?php echo $is_ar ? 'حذف' : 'Delete'; ?></button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
</div>

<div class="settingsSubGrid">
<div class="panelCard" id="banners">
<div class="cardTitle"><?php echo $is_ar ? 'البنرات' : 'Banners'; ?></div>

<div class="formCard" style="margin-bottom:14px">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="add_banner">

<label><?php echo $is_ar ? 'العنوان' : 'Title'; ?></label>
<input name="title">

<label><?php echo $is_ar ? 'النص' : 'Body'; ?></label>
<textarea name="body" rows="3"></textarea>

<label><?php echo $is_ar ? 'الترتيب' : 'Sort Order'; ?></label>
<input name="sort_order" type="number" value="0">

<label style="display:flex;align-items:center;gap:8px;margin-top:10px">
<input type="checkbox" name="is_active" value="1" checked style="width:auto;margin:0">
<span><?php echo $is_ar ? 'مفعلة' : 'Active'; ?></span>
</label>

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'إضافة بنر' : 'Add Banner'; ?></button>
</form>
</div>

<div class="tableWrap">
<table>
<tr><th>ID</th><th><?php echo $is_ar ? 'العنوان' : 'Title'; ?></th><th><?php echo $is_ar ? 'الحالة' : 'Status'; ?></th><th><?php echo $is_ar ? 'إجراء' : 'Action'; ?></th></tr>
<?php foreach ($banners as $b): ?>
<tr>
<td>#<?php echo (int)$b['id']; ?></td>
<td><?php echo e($b['title']); ?></td>
<td><span class="badge <?php echo !empty($b['is_active']) ? 'statusGood' : 'statusBad'; ?>"><?php echo !empty($b['is_active']) ? ($is_ar ? 'مفعل' : 'Active') : ($is_ar ? 'معطل' : 'Disabled'); ?></span></td>
<td>
<div class="row">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="toggle_banner">
<input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
<button type="submit" class="btnGhost"><?php echo $is_ar ? 'تبديل' : 'Toggle'; ?></button>
</form>
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="delete_banner">
<input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
<button type="submit" class="btnDanger"><?php echo $is_ar ? 'حذف' : 'Delete'; ?></button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>

<div class="panelCard" id="announcements">
<div class="cardTitle"><?php echo $is_ar ? 'الإشعارات' : 'Announcements'; ?></div>

<div class="formCard" style="margin-bottom:14px">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="add_announcement">

<label><?php echo $is_ar ? 'العنوان' : 'Title'; ?></label>
<input name="title">

<label><?php echo $is_ar ? 'النص' : 'Body'; ?></label>
<textarea name="body" rows="3"></textarea>

<label><?php echo $is_ar ? 'الترتيب' : 'Sort Order'; ?></label>
<input name="sort_order" type="number" value="0">

<label style="display:flex;align-items:center;gap:8px;margin-top:10px">
<input type="checkbox" name="is_active" value="1" checked style="width:auto;margin:0">
<span><?php echo $is_ar ? 'مفعلة' : 'Active'; ?></span>
</label>

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'إضافة إشعار' : 'Add Announcement'; ?></button>
</form>
</div>

<div class="tableWrap">
<table>
<tr><th>ID</th><th><?php echo $is_ar ? 'العنوان' : 'Title'; ?></th><th><?php echo $is_ar ? 'الحالة' : 'Status'; ?></th><th><?php echo $is_ar ? 'إجراء' : 'Action'; ?></th></tr>
<?php foreach ($announcements as $a): ?>
<tr>
<td>#<?php echo (int)$a['id']; ?></td>
<td><?php echo e($a['title']); ?></td>
<td><span class="badge <?php echo !empty($a['is_active']) ? 'statusGood' : 'statusBad'; ?>"><?php echo !empty($a['is_active']) ? ($is_ar ? 'مفعل' : 'Active') : ($is_ar ? 'معطل' : 'Disabled'); ?></span></td>
<td>
<div class="row">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="toggle_announcement">
<input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
<button type="submit" class="btnGhost"><?php echo $is_ar ? 'تبديل' : 'Toggle'; ?></button>
</form>
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="delete_announcement">
<input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
<button type="submit" class="btnDanger"><?php echo $is_ar ? 'حذف' : 'Delete'; ?></button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>

<div class="panelCard fullRow" id="contacts">
<div class="cardTitle"><?php echo $is_ar ? 'وسائل التواصل كرابط' : 'Contact Links'; ?></div>

<div class="formCard" style="margin-bottom:14px">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="add_contact_method">

<label><?php echo $is_ar ? 'الاسم' : 'Label'; ?></label>
<input name="label" placeholder="Telegram">

<label><?php echo $is_ar ? 'الرابط' : 'Profile Link'; ?></label>
<input name="value" placeholder="https://t.me/username">

<label><?php echo $is_ar ? 'النوع' : 'Type'; ?></label>
<input name="type" value="link" placeholder="link">

<label><?php echo $is_ar ? 'الترتيب' : 'Sort Order'; ?></label>
<input name="sort_order" type="number" value="0">

<label style="display:flex;align-items:center;gap:8px;margin-top:10px">
<input type="checkbox" name="is_active" value="1" checked style="width:auto;margin:0">
<span><?php echo $is_ar ? 'مفعلة' : 'Active'; ?></span>
</label>

<label style="display:flex;align-items:center;gap:8px;margin-top:10px">
<input type="checkbox" name="is_required" value="1" style="width:auto;margin:0">
<span><?php echo $is_ar ? 'إجبارية للعرض' : 'Required display'; ?></span>
</label>

<button type="submit" style="margin-top:10px"><?php echo $is_ar ? 'إضافة رابط' : 'Add Link'; ?></button>
</form>
</div>

<div class="tableWrap">
<table>
<tr><th>ID</th><th><?php echo $is_ar ? 'الاسم' : 'Label'; ?></th><th><?php echo $is_ar ? 'الرابط' : 'Link'; ?></th><th><?php echo $is_ar ? 'الحالة' : 'Status'; ?></th><th><?php echo $is_ar ? 'إجراء' : 'Action'; ?></th></tr>
<?php foreach ($contacts as $c): ?>
<tr>
<td>#<?php echo (int)$c['id']; ?></td>
<td><?php echo e($c['label']); ?></td>
<td style="max-width:260px;word-break:break-all"><?php echo e($c['value']); ?></td>
<td><span class="badge <?php echo !empty($c['is_active']) ? 'statusGood' : 'statusBad'; ?>"><?php echo !empty($c['is_active']) ? ($is_ar ? 'مفعل' : 'Active') : ($is_ar ? 'معطل' : 'Disabled'); ?></span></td>
<td>
<div class="row">
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="toggle_contact_method">
<input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
<button type="submit" class="btnGhost"><?php echo $is_ar ? 'تبديل' : 'Toggle'; ?></button>
</form>
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo e(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="delete_contact_method">
<input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
<button type="submit" class="btnDanger"><?php echo $is_ar ? 'حذف' : 'Delete'; ?></button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
</div>
<?php endif; ?>

</div>

<script>
function genPass(btn){
const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@#$";
let p = "";
for(let i=0;i<12;i++) p += chars[Math.floor(Math.random()*chars.length)];
const wrap = btn.closest("form");
const inp = wrap.querySelector("input[name='newpass']");
if(inp){
inp.value = p;
inp.focus();
}
}

function showFileName(input){
const row = input.closest('.fileUploadRow');
if(!row) return;

const nameBox = row.querySelector('.fileUploadName');
if(!nameBox) return;

if(input.files && input.files[0]){
nameBox.textContent = input.files[0].name;
} else {
nameBox.textContent = '<?php echo $is_ar ? 'لم يتم اختيار صورة' : 'No image selected'; ?>';
}
}

const msgBox = document.getElementById('msgBox');
if (msgBox) msgBox.scrollTop = msgBox.scrollHeight;

let lastTicketHtml = msgBox ? msgBox.innerHTML : '';

function pollTicketMessages(){
const box = document.getElementById('msgBox');
if (!box) return;

const urlParams = new URLSearchParams(window.location.search);
const ticketId = urlParams.get('ticket_id');
if (!ticketId) return;

fetch('dashboard.php?ajax_ticket_messages=1&ticket_id=' + encodeURIComponent(ticketId), {
credentials: 'same-origin'
})
.then(r => r.json())
.then(data => {
if (!data || !data.ok) return;

if (typeof data.html === 'string' && data.html !== lastTicketHtml) {
const nearBottom = Math.abs(box.scrollHeight - box.scrollTop - box.clientHeight) < 80;
box.innerHTML = data.html;
lastTicketHtml = data.html;

if (nearBottom) {
box.scrollTop = box.scrollHeight;
}
}
})
.catch(() => {});
}

if (document.getElementById('msgBox')) {
setInterval(pollTicketMessages, 3000);
}
const depositMethodSelect = document.getElementById('depositPaymentMethod');
const depositMethodInfo = document.getElementById('depositMethodInfo');

function renderDepositMethodInfo() {
if (!depositMethodSelect || !depositMethodInfo) return;

const opt = depositMethodSelect.options[depositMethodSelect.selectedIndex];
if (!opt || !opt.value) {
depositMethodInfo.innerHTML = '';
return;
}

const title = opt.getAttribute('data-title') || '';
const currency = opt.getAttribute('data-currency') || '';
const network = opt.getAttribute('data-network') || '';
const address = opt.getAttribute('data-address') || '';
const account = opt.getAttribute('data-account') || '';

let html = '';

if (title) html += '<div><strong><?php echo $is_ar ? 'الوسيلة' : 'Method'; ?>:</strong> ' + title + '</div>';
if (currency) html += '<div><strong><?php echo $is_ar ? 'العملة' : 'Currency'; ?>:</strong> ' + currency + '</div>';
if (network) html += '<div><strong><?php echo $is_ar ? 'الشبكة' : 'Network'; ?>:</strong> ' + network + '</div>';
if (address) html += '<div><strong><?php echo $is_ar ? 'العنوان / المعرف' : 'Address / ID'; ?>:</strong> ' + address + '</div>';
if (account) html += '<div><strong><?php echo $is_ar ? 'اسم الحساب' : 'Account Name'; ?>:</strong> ' + account + '</div>';

depositMethodInfo.innerHTML = html;
}

if (depositMethodSelect) {
depositMethodSelect.addEventListener('change', renderDepositMethodInfo);
renderDepositMethodInfo();
}
const addProductStockType = document.getElementById('addProductStockType');
const addProductBulk = document.getElementById('addProductBulk');

function updateAddProductPlaceholder() {
if (!addProductStockType || !addProductBulk) return;

const map = {
email: 'user@example.com:pass123',
email_2fa: 'user@example.com:pass123:2fa-secret',
email_access_mail: 'user@example.com:pass123:access@example.com:accessPass',
email_2fa_access_mail: 'user@example.com:pass123:2fa-secret:access@example.com:accessPass',
rdp: 'host|user|pass'
};

addProductBulk.placeholder = map[addProductStockType.value] || 'user@example.com:pass123';
}

if (addProductStockType && addProductBulk) {
addProductStockType.addEventListener('change', updateAddProductPlaceholder);
updateAddProductPlaceholder();
}
</script>
</body>
</html>