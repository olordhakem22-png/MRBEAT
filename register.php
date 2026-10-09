<?php
require __DIR__ . '/app/bootstrap.php';

$pdo = mr_db();
$error = '';
$done = false;

$is_ar = (mr_lang() === 'ar');

if (!empty($_SESSION['user_id'])) {
header('Location: ' . mr_config('app.base_url') . '/index.php');
exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
mr_csrf_check();

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = (string)($_POST['password'] ?? '');

if ($username === '' || $pass === '') {
$error = $is_ar ? 'اسم المستخدم وكلمة المرور مطلوبين' : 'Username and password required';
} else {
try {
$hash = password_hash($pass, PASSWORD_DEFAULT);

$pdo->beginTransaction();

$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, status) VALUES (?,?,?,'user','active')");
$stmt->execute([$username, $email ?: null, $hash]);

$uid = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO wallets (user_id,balance_mb,balance_usdt) VALUES (?,?,?)")
->execute([$uid, 0, 0]);

$bonus_enabled = (string)mr_setting('welcome_bonus_enabled', '0') === '1';
$bonus_amount = (float)mr_setting('welcome_bonus_amount', '0');

if ($bonus_enabled && $bonus_amount > 0) {
$pdo->prepare("UPDATE wallets SET balance_usdt = balance_usdt + ? WHERE user_id=?")
->execute([$bonus_amount, $uid]);

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")
->execute([$uid, null, 'credit', 'usdt', $bonus_amount, 'Welcome bonus']);
}

$pdo->commit();

$_SESSION['user_id'] = $uid;
$_SESSION['username'] = $username;

$done = true;

header('Location: ' . mr_config('app.base_url') . '/index.php');
exit;
} catch (Throwable $e) {
if ($pdo->inTransaction()) $pdo->rollBack();
$error = $is_ar ? 'اسم المستخدم أو البريد موجود بالفعل' : 'Username or email already exists';
}
}
}

$announcements = [];
try {
$announcements = $pdo->query("SELECT * FROM announcements WHERE is_active=1 ORDER BY sort_order ASC, id DESC LIMIT 10")->fetchAll();
} catch (Throwable $e) {
$announcements = [];
}

$register_banner = null;
try {
$st = $pdo->query("SELECT * FROM banners WHERE is_active=1 ORDER BY sort_order ASC, id DESC LIMIT 1");
$register_banner = $st->fetch();
} catch (Throwable $e) {
$register_banner = null;
}

$banner_body = '';
if ($register_banner) {
$banner_body = trim((string)($register_banner['body'] ?? ''));
}

$site_name = htmlspecialchars(mr_site_name(), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="<?php echo $is_ar ? 'ar' : 'en'; ?>" dir="<?php echo $is_ar ? 'rtl' : 'ltr'; ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $site_name; ?> - <?php echo $is_ar ? 'إنشاء حساب' : 'Register'; ?></title>

<style><?php echo mr_theme_css(); ?></style>
<link rel="stylesheet" href="<?php echo mr_config('app.base_url'); ?>/assets/app.css">

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
background:
radial-gradient(circle at 15% 20%, rgba(59,130,246,.20), transparent 24%),
radial-gradient(circle at 85% 15%, rgba(168,85,247,.16), transparent 22%),
radial-gradient(circle at 50% 100%, rgba(34,197,94,.10), transparent 28%),
linear-gradient(135deg, #08111f 0%, #0b1730 45%, #111827 100%);
animation:bgShift 14s ease-in-out infinite alternate;
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
.registerPanel{
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

.registerPanel{
border-radius:30px;
padding:26px;
display:flex;
align-items:center;
justify-content:center;
animation:fadeUp .9s ease;
}

.registerCard{
width:100%;
max-width:420px;
}

.registerTop{
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

.registerTitle{
margin:0;
font-size:34px;
font-weight:900;
letter-spacing:-.02em;
}

.registerSub{
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

.ok{
margin-bottom:14px;
padding:12px 14px;
border-radius:16px;
background:rgba(34,197,94,.14);
color:#bbf7d0;
border:1px solid rgba(74,222,128,.22);
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
height:54px;
border-radius:18px;
border:1px solid rgba(255,255,255,.12);
background:rgba(255,255,255,.06);
color:#fff;
padding:0 16px;
outline:none;
transition:.25s ease;
font-size:14px;
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

.registerBtn{
width:100%;
height:54px;
border:0;
border-radius:18px;
cursor:pointer;
color:#fff;
font-size:15px;
font-weight:800;
background:linear-gradient(135deg,#3b82f6,#8b5cf6);
box-shadow:0 14px 32px rgba(59,130,246,.28);
transition:.25s ease;
}

.registerBtn:hover{
transform:translateY(-2px) scale(1.01);
box-shadow:0 18px 36px rgba(99,102,241,.32);
}

.registerBtn:active{
transform:translateY(0) scale(.995);
}

.registerBottom{
margin-top:14px;
font-size:13px;
opacity:.88;
}

.registerBottom a{
color:#93c5fd;
text-decoration:none;
font-weight:800;
}

.registerBottom a:hover{
text-decoration:underline;
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

@media (max-width: 768px){
body{
padding:12px;
align-items:flex-start;
}

.pageWrap{
grid-template-columns:1fr;
max-width:100%;
gap:14px;
}

.heroPanel,
.registerPanel{
border-radius:22px;
padding:16px;
}

.registerPanel{
display:block;
}

.registerCard{
max-width:100%;
}

.brandBadge{
width:100%;
justify-content:center;
padding:10px 12px;
}

.heroContent{
margin-top:18px;
}

.heroTitle{
font-size:28px;
line-height:1.2;
max-width:100%;
}

.heroText{
font-size:14px;
line-height:1.8;
margin-top:12px;
max-width:100%;
}

.heroStats{
grid-template-columns:1fr;
gap:10px;
margin-top:18px;
}

.statBox{
padding:14px;
border-radius:16px;
}

.statNum{
font-size:22px;
}

.statLabel{
font-size:12px;
}

.mainBanner{
display:block;
width:100%;
max-width:100%;
margin-top:16px;
padding:14px;
border-radius:18px;
}

.mainBannerTitle{
font-size:16px;
line-height:1.5;
}

.mainBannerBody{
font-size:12px;
line-height:1.8;
max-height:none;
padding-right:0;
}

.announceWrap{
margin-top:18px;
}

.announceTitle{
font-size:14px;
margin-bottom:10px;
}

.announceList{
gap:10px;
max-height:none;
overflow:visible;
padding-right:0;
}

.announceItem{
padding:14px;
border-radius:16px;
}

.announceHead{
font-size:14px;
line-height:1.5;
}

.announceBody{
font-size:12px;
line-height:1.8;
}

.registerTop{
margin-bottom:16px;
}

.kicker{
font-size:11px;
padding:7px 11px;
}

.registerTitle{
font-size:26px;
line-height:1.2;
}

.registerSub{
font-size:13px;
line-height:1.7;
}

.err,
.ok{
padding:11px 12px;
border-radius:14px;
font-size:13px;
}

.formGroup{
margin-bottom:12px;
}

.inputLabel{
font-size:12px;
margin-bottom:7px;
}

.inputWrap input{
height:50px;
border-radius:16px;
padding:0 14px;
font-size:16px;
}

.registerBtn{
height:50px;
border-radius:16px;
font-size:15px;
}

.registerBottom{
margin-top:12px;
font-size:12px;
line-height:1.7;
}

.floatingLines{
display:none;
}
}
@media (max-width: 480px){
body{
padding:10px;
}

.heroPanel,
.registerPanel{
border-radius:18px;
padding:14px;
}

.brandBadge{
font-size:13px;
gap:8px;
}

.heroTitle{
font-size:24px;
}

.heroText{
font-size:13px;
}

.registerTitle{
font-size:22px;
}

.registerSub{
font-size:12px;
}

.statNum{
font-size:20px;
}

.mainBannerTitle,
.announceHead{
font-size:13px;
}

.mainBannerBody,
.announceBody{
font-size:11.5px;
}

.inputWrap input,
.registerBtn{
height:48px;
}

body::before,
body::after{
width:220px;
height:220px;
filter:blur(55px);
opacity:.28;
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
<?php echo $is_ar ? 'ابدأ رحلتك الآن' : 'Start your journey now'; ?>
</h1>

<div class="heroText">
<?php
echo $is_ar
? 'أنشئ حسابًا جديدًا في ثوانٍ، واحصل على تجربة سلسة وآمنة للوصول إلى الخدمات، العروض، وكل ما هو جديد داخل المنصة.'
: 'Create your account in seconds and enjoy a smooth, secure experience to access services, offers, and everything new on the platform.';
?>
</div>

<div class="heroStats">
<div class="statBox">
<div class="statNum">Easy</div>
<div class="statLabel"><?php echo $is_ar ? 'تسجيل سريع' : 'Quick signup'; ?></div>
</div>
<div class="statBox">
<div class="statNum">Secure</div>
<div class="statLabel"><?php echo $is_ar ? 'بياناتك محمية' : 'Your data is protected'; ?></div>
</div>
<div class="statBox">
<div class="statNum">Bonus</div>
<div class="statLabel"><?php echo $is_ar ? 'مكافآت ترحيبية حسب الإعدادات' : 'Welcome bonus if enabled'; ?></div>
</div>
</div>

<?php if ($register_banner): ?>
<div class="mainBanner">
<div class="mainBannerTitle"><?php echo htmlspecialchars($register_banner['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
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

<div class="registerPanel">
<div class="registerCard">
<div class="registerTop">
<div class="kicker"><?php echo $is_ar ? 'حساب جديد' : 'New Account'; ?></div>
<h2 class="registerTitle"><?php echo $is_ar ? 'إنشاء حساب' : 'Create account'; ?></h2>
<div class="registerSub">
<?php echo $is_ar ? 'اكتب بياناتك الأساسية وابدأ فورًا.' : 'Enter your basic details and get started right away.'; ?>
</div>
</div>

<?php if ($error): ?>
<div class="err"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<?php if ($done): ?>
<div class="ok"><?php echo $is_ar ? 'تم التسجيل بنجاح.' : 'Registered successfully.'; ?></div>
<?php endif; ?>

<form method="post">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

<div class="formGroup">
<label class="inputLabel"><?php echo $is_ar ? 'اسم المستخدم' : 'Username'; ?></label>
<div class="inputWrap">
<input name="username" placeholder="<?php echo $is_ar ? 'اكتب اسم المستخدم' : 'Enter username'; ?>" required>
</div>
</div>

<div class="formGroup">
<label class="inputLabel"><?php echo $is_ar ? 'البريد الإلكتروني' : 'Email'; ?></label>
<div class="inputWrap">
<input name="email" placeholder="<?php echo $is_ar ? 'البريد الإلكتروني اختياري' : 'Email optional'; ?>">
</div>
</div>

<div class="formGroup">
<label class="inputLabel"><?php echo $is_ar ? 'كلمة المرور' : 'Password'; ?></label>
<div class="inputWrap">
<input name="password" type="password" placeholder="<?php echo $is_ar ? 'اكتب كلمة المرور' : 'Enter password'; ?>" required>
</div>
</div>

<button type="submit" class="registerBtn"><?php echo $is_ar ? 'إنشاء الحساب' : 'Create Account'; ?></button>
</form>

<div class="registerBottom">
<?php echo $is_ar ? 'عندك حساب بالفعل؟' : 'Already have an account?'; ?>
<a href="login.php"><?php echo $is_ar ? 'تسجيل دخول' : 'Login'; ?></a>
</div>
</div>
</div>
</div>

</body>
</html>