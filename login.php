<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../app/bootstrap.php';

if (mr_is_admin()) {
header('Location: ' . mr_base_url() . mr_config('app.admin_path') . '/dashboard.php');
exit;
}

$error = '';
$is_ar = (mr_lang() === 'ar');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
mr_csrf_check();

$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');

$u = null;
$adminRow = null;

$stmt = mr_db()->prepare("
SELECT id, username, email, password_hash, role, status
FROM users
WHERE username = ?
AND role IN ('admin','seller','support')
LIMIT 1
");
$stmt->execute([$username]);
$u = $stmt->fetch();

if ($u && mr_table_exists('admins')) {
try {
$fields = ['id', 'username', 'email', 'password_hash'];
if (mr_column_exists('admins', 'role')) $fields[] = 'role';
if (mr_column_exists('admins', 'status')) $fields[] = 'status';
if (mr_column_exists('admins', 'allowed_sections')) $fields[] = 'allowed_sections';

$sql = "SELECT " . implode(',', $fields) . " FROM admins WHERE id=? LIMIT 1";
$st2 = mr_db()->prepare($sql);
$st2->execute([(int)$u['id']]);
$adminRow = $st2->fetch();
} catch (Throwable $e) {
$adminRow = null;
}
}

$loginRole = (string)($adminRow['role'] ?? $u['role'] ?? '');
$loginStatus = (string)($adminRow['status'] ?? $u['status'] ?? '');
$hashToCheck = (string)($adminRow['password_hash'] ?? $u['password_hash'] ?? '');

if (
!$u ||
$loginStatus !== 'active' ||
!$hashToCheck ||
!password_verify($password, $hashToCheck)
) {
$error = $is_ar ? 'بيانات الدخول غير صحيحة' : 'Invalid login';
} else {
session_regenerate_id(true);

$_SESSION['admin_id'] = (int)$u['id'];
$_SESSION['admin_role'] = $loginRole;
$_SESSION['role'] = $loginRole;
$_SESSION['username'] = (string)($adminRow['username'] ?? $u['username'] ?? '');

header('Location: ' . mr_base_url() . mr_config('app.admin_path') . '/dashboard.php?section=overview');
exit;
}
}

$announcements = [];
try {
$announcements = mr_db()->query("SELECT * FROM announcements WHERE is_active=1 ORDER BY sort_order ASC, id DESC LIMIT 10")->fetchAll();
} catch (Throwable $e) {
$announcements = [];
}

$admin_banner = null;
try {
$st = mr_db()->query("SELECT * FROM banners WHERE is_active=1 ORDER BY sort_order ASC, id DESC LIMIT 1");
$admin_banner = $st->fetch();
} catch (Throwable $e) {
$admin_banner = null;
}

$banner_body = '';
if ($admin_banner) {
$banner_body = trim((string)($admin_banner['body'] ?? ''));
}

$site_name = htmlspecialchars(mr_site_name(), ENT_QUOTES, 'UTF-8');
$admin_path = htmlspecialchars((string)mr_config('app.admin_path'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="<?php echo $is_ar ? 'ar' : 'en'; ?>" dir="<?php echo $is_ar ? 'rtl' : 'ltr'; ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php echo mr_head_tags(); ?>
<title><?php echo $site_name; ?> - <?php echo $is_ar ? 'دخول الإدارة' : 'Admin Login'; ?></title>

<style>
:root{
--glass: rgba(255,255,255,.08);
--glass-strong: rgba(255,255,255,.14);
--line: rgba(255,255,255,.12);
--shadow: 0 20px 60px rgba(0,0,0,.35);
}

*{box-sizing:border-box}

html,body{
min-height:100%;
margin:0;
}

body{
min-height:100vh;
display:flex;
align-items:center;
justify-content:center;
padding:24px;
overflow-x:hidden;
position:relative;
font-family:Arial, sans-serif;
background:
radial-gradient(circle at 15% 20%, rgba(59,130,246,.20), transparent 24%),
radial-gradient(circle at 85% 15%, rgba(168,85,247,.16), transparent 22%),
radial-gradient(circle at 50% 100%, rgba(34,197,94,.10), transparent 28%),
linear-gradient(135deg, #08111f 0%, #0b1730 45%, #111827 100%);
animation:bgShift 14s ease-in-out infinite alternate;
color:#fff;
}

body::before,
body::after{
content:"";
position:fixed;
width:320px;
height:320px;
border-radius:50%;
filter:blur(70px);
z-index:0;
opacity:.35;
pointer-events:none;
}

body::before{
top:-60px;
left:-40px;
background:#3b82f6;
animation:floatBlob1 11s ease-in-out infinite;
}

body::after{
right:-50px;
bottom:-70px;
background:#8b5cf6;
animation:floatBlob2 13s ease-in-out infinite;
}

.pageWrap{
width:100%;
max-width:1180px;
display:grid;
grid-template-columns:1.08fr .92fr;
gap:22px;
align-items:start;
position:relative;
z-index:1;
}

.heroPanel,
.loginPanel{
position:relative;
border:1px solid var(--line);
background:linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.04));
box-shadow:var(--shadow);
backdrop-filter:blur(18px);
-webkit-backdrop-filter:blur(18px);
}

.heroPanel{
border-radius:30px;
padding:28px;
overflow:hidden;
animation:fadeUp 1s ease;
}

.heroPanel::before{
content:"";
position:absolute;
inset:auto -20% -35% auto;
width:420px;
height:420px;
border-radius:50%;
background:radial-gradient(circle, rgba(59,130,246,.20), transparent 65%);
animation:pulseGlow 5s ease-in-out infinite;
}

.heroPanel::after{
content:"";
position:absolute;
inset:0;
background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.06) 45%, transparent 60%);
transform:translateX(-120%);
animation:shineSweep 7s linear infinite;
pointer-events:none;
}

.brandBadge{
display:inline-flex;
align-items:center;
gap:10px;
padding:10px 14px;
border-radius:999px;
background:rgba(255,255,255,.08);
border:1px solid rgba(255,255,255,.10);
width:max-content;
max-width:100%;
}

.brandDot{
width:11px;
height:11px;
border-radius:50%;
background:linear-gradient(135deg,#60a5fa,#a78bfa);
box-shadow:0 0 16px rgba(96,165,250,.9);
animation:pulseDot 1.8s infinite;
}

.heroContent{
position:relative;
z-index:2;
margin-top:24px;
}

.heroTitle{
margin:0;
font-size:44px;
line-height:1.12;
font-weight:900;
letter-spacing:-.02em;
max-width:680px;
}

.heroText{
margin-top:14px;
font-size:15px;
line-height:1.8;
opacity:.88;
max-width:640px;
}

.heroStats{
margin-top:24px;
display:grid;
grid-template-columns:repeat(3,minmax(0,1fr));
gap:14px;
}

.statBox{
padding:16px;
border-radius:20px;
background:rgba(255,255,255,.06);
border:1px solid rgba(255,255,255,.10);
transform:translateY(0);
transition:.25s ease;
}

.statBox:hover{
transform:translateY(-4px);
background:rgba(255,255,255,.09);
}

.statNum{
font-size:24px;
font-weight:900;
margin-bottom:6px;
}

.statLabel{
font-size:13px;
opacity:.82;
}

.mainBanner{
margin:20px 0 0 0;
position:relative;
z-index:2;
padding:16px 18px;
border-radius:20px;
background:linear-gradient(135deg, rgba(59,130,246,.24), rgba(139,92,246,.18));
border:1px solid rgba(255,255,255,.14);
box-shadow:0 12px 30px rgba(0,0,0,.18);
animation:fadeUp .8s ease;
overflow:hidden;
width:100%;
max-width:460px;
display:inline-block;
vertical-align:top;
}

.mainBanner::before{
content:"";
position:absolute;
inset:0;
background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.08) 48%, transparent 60%);
transform:translateX(-120%);
animation:shineSweep 8s linear infinite;
pointer-events:none;
}

.mainBannerTitle{
position:relative;
z-index:1;
font-size:18px;
font-weight:900;
margin-bottom:6px;
word-break:break-word;
}

.mainBannerBody{
position:relative;
z-index:1;
font-size:13px;
line-height:1.8;
opacity:.92;
word-break:break-word;
white-space:pre-wrap;
overflow-wrap:anywhere;
max-height:160px;
overflow-y:auto;
padding-right:6px;
scrollbar-width:thin;
}

.announceWrap{
margin-top:24px;
position:relative;
z-index:2;
}

.announceTitle{
font-size:15px;
font-weight:800;
margin-bottom:12px;
}

.announceList{
display:flex;
flex-direction:column;
gap:12px;
max-height:260px;
overflow:auto;
padding-right:4px;
scrollbar-width:none;
}

.announceList::-webkit-scrollbar{
width:0;
height:0;
}

.announceItem{
padding:16px;
border-radius:20px;
background:rgba(255,255,255,.07);
border:1px solid rgba(255,255,255,.10);
animation:fadeUp .7s ease both;
}

.announceItem:nth-child(2){animation-delay:.08s}
.announceItem:nth-child(3){animation-delay:.16s}
.announceItem:nth-child(4){animation-delay:.24s}

.announceHead{
font-size:15px;
font-weight:800;
margin-bottom:8px;
}

.announceBody{
font-size:13px;
line-height:1.8;
opacity:.88;
white-space:pre-wrap;
word-break:break-word;
}

.loginPanel{
border-radius:30px;
padding:26px;
display:flex;
align-items:center;
justify-content:center;
animation:fadeUp .9s ease;
}

.loginCard{
width:100%;
max-width:420px;
}

.loginTop{
margin-bottom:18px;
}

.kicker{
display:inline-block;
padding:8px 12px;
border-radius:999px;
background:rgba(255,255,255,.07);
border:1px solid rgba(255,255,255,.10);
font-size:12px;
opacity:.9;
margin-bottom:12px;
}

.loginTitle{
margin:0;
font-size:34px;
font-weight:900;
letter-spacing:-.02em;
}

.loginSub{
margin-top:8px;
font-size:14px;
opacity:.82;
line-height:1.7;
}

.err{
margin-bottom:14px;
padding:12px 14px;
border-radius:16px;
background:rgba(220,38,38,.14);
color:#fecaca;
border:1px solid rgba(248,113,113,.25);
animation:shakeX .35s ease;
}

.formGroup{
margin-bottom:14px;
}

.inputLabel{
display:block;
margin-bottom:8px;
font-size:13px;
opacity:.88;
font-weight:700;
}

.inputWrap{
position:relative;
}

.inputWrap input{
width:100%;
padding:14px 16px;
border-radius:18px;
border:1px solid rgba(255,255,255,.12);
background:rgba(255,255,255,.06);
color:#fff;
outline:none;
transition:.25s ease;
font-size:14px;
box-sizing:border-box;
}

.inputWrap input::placeholder{
color:rgba(255,255,255,.48);
}

.inputWrap input:focus{
border-color:rgba(96,165,250,.9);
box-shadow:0 0 0 4px rgba(59,130,246,.14);
background:rgba(255,255,255,.08);
transform:translateY(-1px);
}

.loginBtn{
width:100%;
padding:14px;
border:0;
border-radius:18px;
cursor:pointer;
color:#fff;
font-size:15px;
font-weight:800;
background:linear-gradient(135deg,#3b82f6,#8b5cf6);
box-shadow:0 14px 32px rgba(59,130,246,.28);
transition:.25s ease;
margin-top:4px;
}

.loginBtn:hover{
transform:translateY(-2px) scale(1.01);
box-shadow:0 18px 36px rgba(99,102,241,.32);
}

.loginBtn:active{
transform:translateY(0) scale(.995);
}

.loginBottom{
margin-top:14px;
font-size:13px;
opacity:.88;
}

.pathBadge{
display:inline-block;
margin-top:10px;
padding:8px 12px;
border-radius:999px;
background:rgba(255,255,255,.06);
border:1px solid rgba(255,255,255,.10);
font-size:12px;
opacity:.9;
word-break:break-word;
}

.floatingLines{
position:absolute;
inset:0;
pointer-events:none;
overflow:hidden;
border-radius:30px;
}

.floatingLines span{
position:absolute;
display:block;
width:160px;
height:1px;
background:linear-gradient(90deg, transparent, rgba(255,255,255,.24), transparent);
opacity:.45;
animation:floatLine 10s linear infinite;
}

.floatingLines span:nth-child(1){top:16%; left:-10%; animation-delay:0s}
.floatingLines span:nth-child(2){top:38%; left:-18%; animation-delay:2.2s}
.floatingLines span:nth-child(3){top:72%; left:-15%; animation-delay:4.8s}
.floatingLines span:nth-child(4){top:84%; left:-20%; animation-delay:7s}

@keyframes fadeUp{
from{opacity:0;transform:translateY(24px)}
to{opacity:1;transform:translateY(0)}
}

@keyframes pulseDot{
0%,100%{transform:scale(1);opacity:1}
50%{transform:scale(1.25);opacity:.8}
}

@keyframes pulseGlow{
0%,100%{transform:scale(1);opacity:.35}
50%{transform:scale(1.08);opacity:.55}
}

@keyframes shineSweep{
0%{transform:translateX(-120%)}
100%{transform:translateX(120%)}
}

@keyframes floatBlob1{
0%,100%{transform:translate(0,0)}
50%{transform:translate(30px,40px)}
}

@keyframes floatBlob2{
0%,100%{transform:translate(0,0)}
50%{transform:translate(-25px,-35px)}
}

@keyframes floatLine{
0%{transform:translateX(0)}
100%{transform:translateX(140vw)}
}

@keyframes bgShift{
0%{filter:hue-rotate(0deg)}
100%{filter:hue-rotate(10deg)}
}

@keyframes shakeX{
0%,100%{transform:translateX(0)}
25%{transform:translateX(4px)}
50%{transform:translateX(-4px)}
75%{transform:translateX(3px)}
}

@media (max-width: 980px){
.pageWrap{
grid-template-columns:1fr;
max-width:560px;
}

.heroTitle{
font-size:34px;
}

.heroStats{
grid-template-columns:1fr;
}
}

@media (max-width: 640px){
body{
padding:14px;
}

.heroPanel,
.loginPanel{
border-radius:24px;
padding:18px;
}

.loginTitle{
font-size:28px;
}

.heroTitle{
font-size:28px;
}

.announceList{
max-height:220px;
}

.mainBanner{
padding:16px;
border-radius:20px;
max-width:100%;
}

.mainBannerTitle{
font-size:17px;
}
}
</style>
</head>
<body>

<div class="pageWrap">
<div class="heroPanel">
<div class="floatingLines">
<span></span>
<span></span>
<span></span>
<span></span>
</div>

<div class="brandBadge">
<span class="brandDot"></span>
<span><?php echo $site_name; ?></span>
</div>

<div class="heroContent">
<h1 class="heroTitle">
<?php echo $is_ar ? 'مرحبًا بك في لوحة الإدارة' : 'Welcome to the admin panel'; ?>
</h1>

<div class="heroText">
<?php
echo $is_ar
? 'سجّل الدخول للوصول إلى لوحة الإدارة، متابعة الطلبات، إدارة المستخدمين، والتحكم الكامل في إعدادات المنصة.'
: 'Sign in to access the admin dashboard, review requests, manage users, and control the platform settings.';
?>
</div>

<div class="heroStats">
<div class="statBox">
<div class="statNum">Admin</div>
<div class="statLabel"><?php echo $is_ar ? 'وصول إداري' : 'Administrative access'; ?></div>
</div>
<div class="statBox">
<div class="statNum">Secure</div>
<div class="statLabel"><?php echo $is_ar ? 'دخول محمي' : 'Protected access'; ?></div>
</div>
<div class="statBox">
<div class="statNum">Control</div>
<div class="statLabel"><?php echo $is_ar ? 'تحكم كامل' : 'Full control'; ?></div>
</div>
</div>

<?php if ($admin_banner): ?>
<div class="mainBanner">
<div class="mainBannerTitle"><?php echo htmlspecialchars($admin_banner['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
<div class="mainBannerBody"><?php echo htmlspecialchars($banner_body, ENT_QUOTES, 'UTF-8'); ?></div>
</div>
<?php endif; ?>
</div>

<div class="announceWrap">
<div class="announceTitle">
<?php echo $is_ar ? 'آخر الإعلانات' : 'Latest Announcements'; ?>
</div>

<div class="announceList">
<?php if (!$announcements): ?>
<div class="announceItem">
<div class="announceHead"><?php echo $is_ar ? 'لا توجد إعلانات حالياً' : 'No announcements right now'; ?></div>
<div class="announceBody">
<?php echo $is_ar ? 'عند إضافة أي إعلان من لوحة الأدمن سيظهر هنا تلقائيًا.' : 'Any active announcement added by admin will appear here automatically.'; ?>
</div>
</div>
<?php else: ?>
<?php foreach ($announcements as $a): ?>
<div class="announceItem">
<div class="announceHead"><?php echo htmlspecialchars($a['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
<div class="announceBody"><?php echo htmlspecialchars($a['body'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>
</div>

<div class="loginPanel">
<div class="loginCard">
<div class="loginTop">
<div class="kicker"><?php echo $is_ar ? 'دخول الإدارة' : 'Admin Access'; ?></div>
<h2 class="loginTitle"><?php echo $is_ar ? 'تسجيل الدخول' : 'Login'; ?></h2>
<div class="loginSub">
<?php echo $is_ar ? 'اكتب بيانات الإدارة للوصول إلى لوحة التحكم.' : 'Enter your admin credentials to access the dashboard.'; ?></div>
<div class="pathBadge"><?php echo $is_ar ? 'المسار' : 'Path'; ?>: <?php echo $admin_path; ?></div>
</div>

<?php if ($error): ?>
<div class="err"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<form method="post">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

<div class="formGroup">
<label class="inputLabel"><?php echo $is_ar ? 'اسم المستخدم' : 'Username'; ?></label>
<div class="inputWrap">
<input name="username" placeholder="<?php echo $is_ar ? 'اكتب اسم المستخدم' : 'Enter username'; ?>" autocomplete="username" required>
</div>
</div>

<div class="formGroup">
<label class="inputLabel"><?php echo $is_ar ? 'كلمة المرور' : 'Password'; ?></label>
<div class="inputWrap">
<input name="password" placeholder="<?php echo $is_ar ? 'اكتب كلمة المرور' : 'Enter password'; ?>" type="password" autocomplete="current-password" required>
</div>
</div>

<button type="submit" class="loginBtn"><?php echo $is_ar ? 'دخول الآن' : 'Login Now'; ?></button>
</form>
</div>
</div>
</div>

</body>
</html>