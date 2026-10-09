<?php
require __DIR__ . '/../app/bootstrap.php';
mr_require_admin();

$pdo = mr_db();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
mr_csrf_check();
$action = $_POST['action'] ?? 'save_main';

if ($action === 'save_main') {
$site_name = trim($_POST['site_name'] ?? 'Mr Beat Store');
$primary = trim($_POST['theme_primary'] ?? '#009dff');
$bg = trim($_POST['theme_bg'] ?? '#0f141b');
$card = trim($_POST['theme_card'] ?? '#161d26');
$text = trim($_POST['theme_text'] ?? '#ffffff');
$radius = (string)((int)($_POST['theme_radius'] ?? 14));

$font_family = ($_POST['font_family'] ?? 'Cairo');
$font_family = in_array($font_family, ['Cairo','Tajawal'], true) ? $font_family : 'Cairo';

$font_size = (string)((int)($_POST['font_size'] ?? 15));
$font_weight = (string)((int)($_POST['font_weight'] ?? 400));

$default_lang = ($_POST['site_lang_default'] ?? 'en') === 'ar' ? 'ar' : 'en';

$stmt = $pdo->prepare("INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)");
foreach ([
'site_name'=>$site_name,
'theme_primary'=>$primary,
'theme_bg'=>$bg,
'theme_card'=>$card,
'theme_text'=>$text,
'theme_radius'=>$radius,
'font_family'=>$font_family,
'font_size'=>$font_size,
'font_weight'=>$font_weight,
'site_lang_default'=>$default_lang
] as $k=>$v) $stmt->execute([$k,$v]);

$msg = 'Settings saved';
}

if ($action === 'save_gift') {
$gift_enabled = !empty($_POST['gift_enabled']) ? '1' : '0';
$gift_amount = (string)((float)($_POST['gift_amount'] ?? 0));

$stmt = $pdo->prepare("INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)");
$stmt->execute(['gift_enabled',$gift_enabled]);
$stmt->execute(['gift_amount',$gift_amount]);

$msg = 'Gift settings saved';
}

if ($action === 'save_announce') {
$announce_enabled = !empty($_POST['announce_enabled']) ? '1' : '0';
$announce_text = trim($_POST['announce_text'] ?? '');

$stmt = $pdo->prepare("INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)");
$stmt->execute(['announce_enabled',$announce_enabled]);
$stmt->execute(['announce_text',$announce_text]);

$msg = 'Announcement saved';
}

if ($action === 'add_payment') {
$currency = ($_POST['currency'] ?? 'usdt') === 'mb' ? 'mb' : 'usdt';
$network = trim($_POST['network'] ?? '');
$address = trim($_POST['address'] ?? '');
if ($network !== '' && $address !== '') {
$pdo->prepare("INSERT INTO payment_methods (currency,network,address,is_active,sort_order) VALUES (?,?,?,?,0)")
->execute([$currency,$network,$address,1]);
$msg = 'Payment method added';
}
}

if ($action === 'toggle_payment') {
$id = (int)($_POST['id'] ?? 0);
$pdo->prepare("UPDATE payment_methods SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
$msg = 'Updated';
}

header('Location: settings.php?ok=1');
exit;
}

$ok = !empty($_GET['ok']);

$all = $pdo->query("SELECT k,v FROM settings")->fetchAll();
$S = [];
foreach ($all as $r) $S[$r['k']] = $r['v'];

$pm = $pdo->query("SELECT * FROM payment_methods ORDER BY sort_order ASC, id DESC")->fetchAll();
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Website Settings</title>
<style><?php echo mr_theme_css(); ?></style>
<style>
.wrap{max-width:820px;margin:0 auto;padding:18px}
.section{padding:16px;border:1px solid #222;margin-top:14px}
h1{margin:0 0 10px 0}
h2{margin:0 0 10px 0;font-size:18px}
input,select,textarea{width:100%;padding:10px;margin-top:8px;background:#ffffff;border:1px solid #222;color:#000000;border-radius:var(--radius)}
button{margin-top:10px;padding:10px 14px;border:0;border-radius:var(--radius);background:var(--primary);color:#000000cursor:pointer}
.row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.small{font-size:12px;opacity:.85}
table{width:100%;border-collapse:collapse;margin-top:10px}
td,th{border-bottom:1px solid #222;padding:8px;text-align:left;font-size:13px}
.badge{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid #222;background:var(--card)}
a{color:var(--primary);text-decoration:none}
</style>
</head>
<body>
<div class="wrap">

<a href="dashboard.php">← Dashboard</a>

<h1>Website Settings</h1>
<div class="small"><?php echo h($S['site_name'] ?? 'Mr Beat Store'); ?></div>
<?php if ($ok): ?><div class="small" style="color:#7bffb0;margin-top:8px">Saved</div><?php endif; ?>

<div class="section">
<h2>Theme</h2>
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo h(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="save_main">

<label>Store Name</label>
<input name="site_name" value="<?php echo h($S['site_name'] ?? 'Mr Beat Store'); ?>">

<div class="row">
<div>
<label>Primary Color</label>
<input type="color" name="theme_primary" value="<?php echo h($S['theme_primary'] ?? '#009dff'); ?>">
</div>
<div>
<label>Border Radius</label>
<input type="number" name="theme_radius" value="<?php echo h($S['theme_radius'] ?? '14'); ?>">
</div>
</div>

<div class="row">
<div>
<label>Background</label>
<input type="color" name="theme_bg" value="<?php echo h($S['theme_bg'] ?? '#0f141b'); ?>">
</div>
<div>
<label>Card</label>
<input type="color" name="theme_card" value="<?php echo h($S['theme_card'] ?? '#161d26'); ?>">
</div>
</div>

<label>Text</label>
<input type="color" name="theme_text" value="<?php echo h($S['theme_text'] ?? '#ffffff'); ?>">

<div class="row">
<div>
<label>Font</label>
<select name="font_family">
<option value="Cairo" <?php echo (($S['font_family'] ?? 'Cairo')==='Cairo')?'selected':''; ?>>Cairo</option>
<option value="Tajawal" <?php echo (($S['font_family'] ?? 'Cairo')==='Tajawal')?'selected':''; ?>>Tajawal</option>
</select>
</div>
<div>
<label>Default language</label>
<select name="site_lang_default">
<option value="en" <?php echo (($S['site_lang_default'] ?? 'en')==='en')?'selected':''; ?>>English</option>
<option value="ar" <?php echo (($S['site_lang_default'] ?? 'en')==='ar')?'selected':''; ?>>Arabic</option>
</select>
</div>
</div>

<div class="row">
<div>
<label>Font size</label>
<input type="number" name="font_size" value="<?php echo h($S['font_size'] ?? '15'); ?>">
</div>
<div>
<label>Font weight</label>
<input type="number" name="font_weight" value="<?php echo h($S['font_weight'] ?? '400'); ?>">
</div>
</div>

<button type="submit">Save Settings</button>
</form>
</div>

<div class="section">
<h2>New User Gift</h2>
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo h(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="save_gift">

<label><input type="checkbox" name="gift_enabled" value="1" <?php echo (!empty($S['gift_enabled']) && $S['gift_enabled']=='1')?'checked':''; ?>> Automatically credit new users</label>
<label>Amount credited to wallet upon registration</label>
<input type="number" step="0.01" name="gift_amount" value="<?php echo h($S['gift_amount'] ?? '0'); ?>">

<button type="submit">Save Gift Settings</button>
</form>
</div>

<div class="section">
<h2>Announcement Banner</h2>
<form method="post">
<input type="hidden" name="_csrf" value="<?php echo h(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="save_announce">

<label><input type="checkbox" name="announce_enabled" value="1" <?php echo (!empty($S['announce_enabled']) && $S['announce_enabled']=='1')?'checked':''; ?>> Display message on homepage</label>
<label>Message</label>
<textarea name="announce_text" rows="3"><?php echo h($S['announce_text'] ?? ''); ?></textarea>

<button type="submit">Save Announcement</button>
</form>
</div>

<div class="section">
<h2>Payment Methods</h2>

<form method="post" class="row">
<input type="hidden" name="_csrf" value="<?php echo h(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="add_payment">

<div>
<label>Currency</label>
<select name="currency">
<option value="usdt">USDT</option>
<option value="mb">Credits (MB)</option>
</select>
</div>
<div>
<label>Network</label>
<input name="network" placeholder="TRC20">
</div>
<div style="grid-column:1/-1">
<label>Address</label>
<input name="address" placeholder="Wallet address">
</div>

<div style="grid-column:1/-1">
<button type="submit">Add</button>
</div>
</form>

<table>
<tr><th>ID</th><th>Currency</th><th>Network</th><th>Address</th><th>Status</th><th></th></tr>
<?php foreach ($pm as $m): ?>
<tr>
<td>#<?php echo (int)$m['id']; ?></td>
<td><?php echo h($m['currency']==='mb'?'Credits':'USDT'); ?></td>
<td><?php echo h($m['network']); ?></td>
<td style="max-width:300px;word-break:break-all"><?php echo h($m['address']); ?></td>
<td><span class="badge"><?php echo $m['is_active']? 'active':'disabled'; ?></span></td>
<td>
<form method="post" style="display:inline">
<input type="hidden" name="_csrf" value="<?php echo h(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="toggle_payment">
<input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>">
<button type="submit" style="background:#444"><?php echo $m['is_active']?'Disable':'Enable'; ?></button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>

</div>

</div>
</body>
</html>