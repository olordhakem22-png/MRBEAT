<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';

date_default_timezone_set($config['app']['timezone']);

session_name($config['security']['session_name']);

if (session_status() !== PHP_SESSION_ACTIVE) {
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
'httponly' => true,
'secure' => $https,
'samesite' => 'Lax',
]);

session_start();
}

function mr_config(string $key, $default = null) {
static $cfg = null;
if ($cfg === null) {
$cfg = require __DIR__ . '/config.php';
}

$parts = explode('.', $key);
$cur = $cfg;

foreach ($parts as $p) {
if (!is_array($cur) || !array_key_exists($p, $cur)) {
return $default;
}
$cur = $cur[$p];
}

return $cur;
}

function mr_db(): PDO {
static $pdo = null;
if ($pdo instanceof PDO) {
return $pdo;
}

$dsn = 'mysql:host=' . mr_config('db.host')
. ';dbname=' . mr_config('db.name')
. ';charset=' . mr_config('db.charset');

$pdo = new PDO(
$dsn,
(string)mr_config('db.user'),
(string)mr_config('db.pass'),
[
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]
);

return $pdo;
}

function h($s): string {
return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function mr_base_url(): string {
return rtrim((string)mr_config('app.base_url'), '/');
}

function mr_asset_url(string $path = ''): string {
$path = ltrim($path, '/');
return $path === '' ? mr_base_url() : mr_base_url() . '/' . $path;
}

function mr_admin_role(): string {
return (string)($_SESSION['admin_role'] ?? $_SESSION['role'] ?? '');
}

function mr_is_admin(): bool {
return !empty($_SESSION['admin_id']);
}

function mr_is_super_admin(): bool {
return mr_is_admin() && mr_admin_role() === 'admin';
}

function mr_is_seller(): bool {
return mr_is_admin() && mr_admin_role() === 'seller';
}

function mr_is_support(): bool {
return mr_is_admin() && mr_admin_role() === 'support';
}

function mr_require_admin(array $roles = ['admin']): void {
if (!mr_is_admin()) {
header('Location: ' . mr_base_url() . mr_config('app.admin_path') . '/login.php');
exit;
}

$role = mr_admin_role();
if (!in_array($role, $roles, true)) {
http_response_code(403);
exit('Access denied');
}
}

function mr_default_sections_for_role(string $role): array {
switch ($role) {
case 'admin':
return [
'overview',
'profile',
'users',
'tickets',
'products',
'add_product',
'deposits',
'withdraws',
'settings',
'my_deposits',
'my_withdraws',
'product_permissions',
'seller_products',
];

case 'support':
return [
'overview',
'profile',
'tickets',
];

case 'seller':
return [
'overview',
'profile',
'products',
'add_product',
'my_deposits',
'my_withdraws',
'support',
];

default:
return [
'overview',
'profile',
];
}
}

function mr_table_exists(string $table): bool {
try {
$st = mr_db()->prepare("SHOW TABLES LIKE ?");
$st->execute([$table]);
return (bool)$st->fetch();
} catch (Throwable $e) {
return false;
}
}

function mr_column_exists(string $table, string $column): bool {
try {
$st = mr_db()->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
$st->execute([$column]);
return (bool)$st->fetch();
} catch (Throwable $e) {
return false;
}
}

function mr_seller_allowed_sections(): array {
if (!mr_is_seller()) {
return [];
}

$adminId = (int)($_SESSION['admin_id'] ?? 0);
if ($adminId <= 0) {
return mr_default_sections_for_role('seller');
}

try {
if (!mr_table_exists('admins') || !mr_column_exists('admins', 'allowed_sections')) {
return mr_default_sections_for_role('seller');
}

$st = mr_db()->prepare("SELECT allowed_sections FROM admins WHERE id=? LIMIT 1");
$st->execute([$adminId]);
$raw = trim((string)$st->fetchColumn());

if ($raw === '') {
return mr_default_sections_for_role('seller');
}

$allowedPool = [
'overview',
'profile',
'products',
'add_product',
'my_deposits',
'my_withdraws',
'support',
];

$out = [];
foreach (explode(',', $raw) as $part) {
$part = trim($part);
if ($part !== '' && in_array($part, $allowedPool, true)) {
$out[] = $part;
}
}

if (!in_array('overview', $out, true)) $out[] = 'overview';
if (!in_array('profile', $out, true)) $out[] = 'profile';

return array_values(array_unique($out));
} catch (Throwable $e) {
return mr_default_sections_for_role('seller');
}
}

function mr_admin_sections_map(): array {
return [
'admin' => mr_default_sections_for_role('admin'),
'support' => mr_default_sections_for_role('support'),
'seller' => mr_seller_allowed_sections(),
];
}

function mr_admin_allowed_sections(): array {
$role = mr_admin_role();

if ($role === 'seller') {
return mr_seller_allowed_sections();
}

$map = mr_admin_sections_map();
return $map[$role] ?? mr_default_sections_for_role($role);
}

function mr_require_admin_section(string $section): void {
$allowed = mr_admin_allowed_sections();

if (!in_array($section, $allowed, true)) {
if (mr_is_seller()) {
header('Location: ' . mr_base_url() . mr_config('app.admin_path') . '/dashboard.php?section=overview');
exit;
}

http_response_code(403);
exit('Access denied');
}
}

function mr_can_manage_users(): bool {
return mr_is_super_admin();
}

function mr_can_adjust_balance(): bool {
return mr_is_super_admin();
}

function mr_can_manage_products(): bool {
return mr_is_super_admin() || mr_is_seller();
}

function mr_can_manage_tickets(): bool {
return mr_is_super_admin() || mr_is_support();
}

function mr_can_manage_deposits(): bool {
return mr_is_super_admin();
}

function mr_can_manage_withdraws(): bool {
return mr_is_super_admin();
}

function mr_can_manage_settings(): bool {
return mr_is_super_admin();
}

function mr_can_view_own_deposits(): bool {
return mr_is_seller();
}

function mr_can_view_own_withdraws(): bool {
return mr_is_seller();
}

function mr_csrf_token(): string {
if (empty($_SESSION['_csrf'])) {
$_SESSION['_csrf'] = bin2hex(random_bytes(16));
}
return $_SESSION['_csrf'];
}

function mr_csrf(): string {
return mr_csrf_token();
}

function mr_csrf_check(): void {
$t = $_POST['_csrf'] ?? $_POST['csrf'] ?? '';
if (!$t || empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], (string)$t)) {
http_response_code(403);
echo 'CSRF check failed';
exit;
}
}

function mr_setting(string $key, $default = null) {
static $cache = null;

if ($cache === null) {
$cache = [];
try {
$rows = mr_db()->query("SELECT k,v FROM settings")->fetchAll();
foreach ($rows as $r) {
$cache[$r['k']] = $r['v'];
}
} catch (Throwable $e) {
$cache = [];
}
}

return array_key_exists($key, $cache) ? $cache[$key] : $default;
}

function mr_site_name(): string {
return (string)mr_setting('site_name', 'Mr Beat Store');
}

function mr_site_icon(): string {
$icon = trim((string)mr_setting('site_icon', ''));
if ($icon === '') {
return '';
}
return mr_asset_url($icon);
}

function mr_favicon_html(): string {
$icon = mr_site_icon();
if ($icon === '') {
return '';
}

$path = (string)(parse_url($icon, PHP_URL_PATH) ?? '');
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$type = 'image/x-icon';

if ($ext === 'png') $type = 'image/png';
elseif ($ext === 'jpg' || $ext === 'jpeg') $type = 'image/jpeg';
elseif ($ext === 'webp') $type = 'image/webp';
elseif ($ext === 'svg') $type = 'image/svg+xml';
elseif ($ext === 'ico') $type = 'image/x-icon';

$safe = h($icon);

return
'<link rel="icon" type="' . $type . '" href="' . $safe . '">' . "\n" .
'<link rel="shortcut icon" type="' . $type . '" href="' . $safe . '">' . "\n" .
'<link rel="apple-touch-icon" href="' . $safe . '">';
}

function mr_head_tags(): string {
return
'<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n" .
mr_favicon_html();
}

function mr_lang(): string {
if (isset($_GET['lang'])) {
$lang = ($_GET['lang'] === 'ar') ? 'ar' : 'en';
$_SESSION['lang'] = $lang;
return $lang;
}

return (string)($_SESSION['lang'] ?? mr_setting('site_lang_default', 'en'));
}

function mr_set_lang(string $lang): void {
$_SESSION['lang'] = ($lang === 'ar') ? 'ar' : 'en';
}

function mr_theme_css(): string {
$theme_mode = (string)mr_setting('theme_mode', 'dark');

$theme_bg = (string)mr_setting('theme_bg', '#051424');
$theme_card = (string)mr_setting('theme_card', '#05202e');
$theme_primary = (string)mr_setting('theme_primary', '#3b82f6');
$theme_text = (string)mr_setting('theme_text', '#ffffff');

$theme_bg_light = (string)mr_setting('theme_bg_light', '#f7f8fb');
$theme_card_light = (string)mr_setting('theme_card_light', '#ffffff');
$theme_primary_light = (string)mr_setting('theme_primary_light', '#2563eb');
$theme_text_light = (string)mr_setting('theme_text_light', '#111827');

$theme_gradient_from = (string)mr_setting('theme_gradient_from', '#0f172a');
$theme_gradient_to = (string)mr_setting('theme_gradient_to', '#1d4ed8');
$theme_gradient_from_light = (string)mr_setting('theme_gradient_from_light', '#dbeafe');
$theme_gradient_to_light = (string)mr_setting('theme_gradient_to_light', '#f8fafc');

$radius = (int)mr_setting('theme_radius', '14');

$font = (string)mr_setting('font_family', 'Cairo');
$fontSize = (int)mr_setting('font_size', '15');
$fontWeight = (int)mr_setting('font_weight', '400');

$bg = $theme_mode === 'light' ? $theme_bg_light : $theme_bg;
$card = $theme_mode === 'light' ? $theme_card_light : $theme_card;
$primary = $theme_mode === 'light' ? $theme_primary_light : $theme_primary;
$text = $theme_mode === 'light' ? $theme_text_light : $theme_text;
$g1 = $theme_mode === 'light' ? $theme_gradient_from_light : $theme_gradient_from;
$g2 = $theme_mode === 'light' ? $theme_gradient_to_light : $theme_gradient_to;

return "
:root{
--primary: {$primary};
--bg: {$bg};
--card: {$card};
--text: {$text};
--g1: {$g1};
--g2: {$g2};
--radius: {$radius}px;
--font: '{$font}', Arial, sans-serif;
--fontSize: {$fontSize}px;
--fontWeight: {$fontWeight};
}
html,body{
margin:0;
padding:0;
background:var(--bg);
color:var(--text);
font-family:var(--font);
font-size:var(--fontSize);
font-weight:var(--fontWeight);
}
body{
background:
radial-gradient(circle at top right, color-mix(in srgb, var(--g2) 14%, transparent), transparent 28%),
linear-gradient(180deg, var(--bg), color-mix(in srgb, var(--bg) 88%, #000 12%));
}
.card,.section,.box{
background:var(--card);
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
border-radius:var(--radius);
}
.btn,.primary{
background:var(--primary);
color:#fff;
}
a{
color:var(--primary);
}
";
}

function mr_user_id(): int {
return (int)($_SESSION['user_id'] ?? 0);
}

function mr_user_status(int $user_id): string {
static $cache = [];

if ($user_id <= 0) {
return 'guest';
}

if (isset($cache[$user_id])) {
return $cache[$user_id];
}

try {
$st = mr_db()->prepare("SELECT status FROM users WHERE id=? LIMIT 1");
$st->execute([$user_id]);
$row = $st->fetch();
$cache[$user_id] = $row ? (string)$row['status'] : 'guest';
} catch (Throwable $e) {
$cache[$user_id] = 'guest';
}

return $cache[$user_id];
}

function mr_admin_status(int $admin_id): string {
static $cache = [];

if ($admin_id <= 0) {
return 'inactive';
}

if (isset($cache[$admin_id])) {
return $cache[$admin_id];
}

try {
if (!mr_table_exists('admins')) {
$cache[$admin_id] = 'inactive';
return $cache[$admin_id];
}

$st = mr_db()->prepare("SELECT status FROM admins WHERE id=? LIMIT 1");
$st->execute([$admin_id]);
$row = $st->fetch();
$cache[$admin_id] = $row ? (string)$row['status'] : 'inactive';
} catch (Throwable $e) {
$cache[$admin_id] = 'inactive';
}

return $cache[$admin_id];
}

function mr_require_active_user(bool $allow_support_page = false): void {
$uid = mr_user_id();

if ($uid > 0) {
$status = mr_user_status($uid);

if ($status === 'blocked') {
if ($allow_support_page) {
return;
}
header('Location: ' . mr_base_url() . '/support.php');
exit;
}
}

$adminId = (int)($_SESSION['admin_id'] ?? 0);
if ($adminId > 0) {
$adminStatus = mr_admin_status($adminId);

if ($adminStatus === 'blocked') {
if (mr_is_seller()) {
if ($allow_support_page) {
return;
}

header('Location: ' . mr_base_url() . mr_config('app.admin_path') . '/dashboard.php?section=support');
exit;
}

http_response_code(403);
exit('Account blocked');
}
}
}