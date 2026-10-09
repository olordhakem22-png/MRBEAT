<?php
require __DIR__ . '/../app/bootstrap.php';
mr_require_admin();

$pdo = mr_db();

function mr_can_adjust_balance(): bool {
$role = $_SESSION['admin_role'] ?? 'admin';
return in_array($role, ['admin','seller'], true);
}

$q = trim($_GET['q'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
mr_csrf_check();

$action = $_POST['action'] ?? '';

if ($action === 'create_user') {
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');
$role = $_POST['role'] ?? 'user';

if ($username && $password) {
$hash = password_hash($password, PASSWORD_DEFAULT);
$role = in_array($role, ['user','admin','seller','support'], true) ? $role : 'user';

$stmt = $pdo->prepare("INSERT INTO users (username,email,password_hash,role,status) VALUES (?,?,?,?, 'active')");
$stmt->execute([$username, $email ?: null, $hash, $role]);

$uid = (int)$pdo->lastInsertId();
$pdo->prepare("INSERT INTO wallets (user_id,balance_mb,balance_usdt) VALUES (?,?,?)")->execute([$uid,0,0]);
}
}

if ($action === 'toggle_block') {
$uid = (int)($_POST['user_id'] ?? 0);
$status = $_POST['status'] ?? 'active';
$new = $status === 'active' ? 'blocked' : 'active';
$pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new,$uid]);
}

if ($action === 'adjust_balance' && mr_can_adjust_balance()) {
$uid = (int)($_POST['user_id'] ?? 0);
$kind = ($_POST['kind'] ?? 'credit') === 'debit' ? 'debit' : 'credit';
$currency = ($_POST['currency'] ?? 'mb') === 'usdt' ? 'usdt' : 'mb';
$amount = (float)($_POST['amount'] ?? 0);
$reason = trim($_POST['reason'] ?? 'Manual adjustment');

if ($uid && $amount > 0) {
if ($currency === 'mb') {
$sql = $kind === 'credit'
? "UPDATE wallets SET balance_mb = balance_mb + ? WHERE user_id=?"
: "UPDATE wallets SET balance_mb = GREATEST(balance_mb - ?,0) WHERE user_id=?";
} else {
$sql = $kind === 'credit'
? "UPDATE wallets SET balance_usdt = balance_usdt + ? WHERE user_id=?"
: "UPDATE wallets SET balance_usdt = GREATEST(balance_usdt - ?,0) WHERE user_id=?";
}

$pdo->beginTransaction();
$pdo->prepare($sql)->execute([$amount,$uid]);

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")->execute([$uid, (int)($_SESSION['admin_id'] ?? 0), $kind, $currency, $amount, $reason]);

$pdo->commit();
}
}

header('Location: users.php?q=' . urlencode($q));
exit;
}

if ($q !== '') {
$stmt = $pdo->prepare("SELECT u.*, w.balance_mb, w.balance_usdt
FROM users u
LEFT JOIN wallets w ON w.user_id=u.id
WHERE u.username LIKE ? OR u.email LIKE ?
ORDER BY u.id DESC LIMIT 100");
$like = '%' . $q . '%';
$stmt->execute([$like,$like]);
$users = $stmt->fetchAll();
} else {
$users = $pdo->query("SELECT u.*, w.balance_mb, w.balance_usdt
FROM users u
LEFT JOIN wallets w ON w.user_id=u.id
ORDER BY u.id DESC LIMIT 50")->fetchAll();
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Users</title>
<style>
body{font-family:Arial;background:#0f141b;color:#fff;padding:20px;margin:0}
a{color:#4da3ff;text-decoration:none}
.section{background:#161d26;border:1px solid #222;border-radius:14px;padding:14px;margin-bottom:16px}
input,select{padding:10px;border-radius:10px;border:1px solid #222;background:#0b0f14;color:#fff}
button{padding:10px 12px;border-radius:10px;border:0;background:#2f6bff;color:#fff;cursor:pointer}
table{width:100%;border-collapse:collapse}
td,th{border-bottom:1px solid #222;padding:8px;text-align:left;font-size:13px;vertical-align:top}
.badge{padding:2px 8px;border-radius:999px;background:#0b0f14;border:1px solid #222;display:inline-block}
.small{font-size:12px;opacity:.85}
.row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
</style>
</head>
<body>

<h1>Users</h1>
<div style="margin-bottom:12px">
<a href="dashboard.php">← Dashboard</a>
</div>

<div class="section">
<form method="get" class="row">
<input name="q" placeholder="Search username/email" value="<?php echo htmlspecialchars($q); ?>">
<button type="submit">Search</button>
</form>
</div>

<div class="section">
<h3 style="margin:0 0 10px 0;">Create User</h3>
<form method="post" class="row">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="create_user">
<input name="username" placeholder="username" required>
<input name="email" placeholder="email (optional)">
<input name="password" placeholder="password" required>
<select name="role">
<option value="user">user</option>
<option value="admin">admin</option>
<option value="seller">seller</option>
<option value="support">support</option>
</select>
<button type="submit">Create</button>
</form>
</div>

<div class="section">
<h3 style="margin:0 0 10px 0;">Users List</h3>

<table>
<tr>
<th>ID</th><th>Username</th><th>Role</th><th>Status</th><th>MB</th><th>USDT</th><th>Actions</th>
</tr>

<?php foreach ($users as $u): ?>
<tr>
<td>#<?php echo (int)$u['id']; ?></td>
<td>
<?php echo htmlspecialchars($u['username']); ?>
<div class="small"><?php echo htmlspecialchars($u['email'] ?? ''); ?></div>
</td>
<td><span class="badge"><?php echo htmlspecialchars($u['role']); ?></span></td>
<td><span class="badge"><?php echo htmlspecialchars($u['status']); ?></span></td>
<td><?php echo htmlspecialchars($u['balance_mb'] ?? 0); ?></td>
<td><?php echo htmlspecialchars($u['balance_usdt'] ?? 0); ?></td>
<td>
<form method="post" style="display:inline-block">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="toggle_block">
<input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
<input type="hidden" name="status" value="<?php echo htmlspecialchars($u['status']); ?>">
<button type="submit" style="background:#444"><?php echo $u['status']==='active'?'Block':'Unblock'; ?></button>
</form>

<?php if (mr_can_adjust_balance()): ?>
<form method="post" style="display:inline-flex;gap:6px;align-items:center;margin-top:6px;flex-wrap:wrap">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="adjust_balance">
<input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">

<select name="kind">
<option value="credit">Add</option>
<option value="debit">Deduct</option>
</select>

<select name="currency">
<option value="mb">MB</option>
<option value="usdt">USDT</option>
</select>

<input name="amount" type="number" step="0.01" placeholder="amount" style="width:110px" required>
<input name="reason" placeholder="reason" style="width:160px">
<button type="submit">Go</button>
</form>
<?php else: ?>
<div class="small">No balance permission</div>
<?php endif; ?>

<form method="post" action="reset_password.php" style="display:inline-flex;gap:6px;align-items:center;margin-top:6px;flex-wrap:wrap">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
<input name="newpass" placeholder="new password" style="width:160px" required>
<button type="button" onclick="genPass(this)" style="background:#444">Generate</button>
<button type="submit" style="background:#6b2fff">Reset</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<script>
function genPass(btn){
const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@#$";
let p = "";
for(let i=0;i<12;i++) p += chars[Math.floor(Math.random()*chars.length)];
const wrap = btn.closest("form");
const inp = wrap.querySelector("input[name='newpass']");
inp.value = p;
inp.focus();
}
</script>

</body>
</html>