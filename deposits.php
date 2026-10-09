<?php
require __DIR__ . '/../app/bootstrap.php';
mr_require_admin();

$pdo = mr_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
mr_csrf_check();

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['do'] ?? '';
$note = trim($_POST['note'] ?? '');

$reqStmt = $pdo->prepare("SELECT * FROM deposit_requests WHERE id=? LIMIT 1");
$reqStmt->execute([$id]);
$r = $reqStmt->fetch();

if ($r && $r['status'] === 'pending') {

if ($action === 'approve') {
$pdo->beginTransaction();

$pdo->prepare("UPDATE deposit_requests
SET status='approved', admin_note=?, reviewed_at=NOW(), reviewed_by=?
WHERE id=?")->execute([$note ?: null, (int)($_SESSION['admin_id'] ?? 0), $id]);

$currency = ($r['currency'] === 'mb') ? 'mb' : 'usdt';

if ($currency === 'mb') {
$pdo->prepare("UPDATE wallets SET balance_mb = balance_mb + ? WHERE user_id=?")
->execute([(float)$r['amount'], (int)$r['user_id']]);
} else {
$pdo->prepare("UPDATE wallets SET balance_usdt = balance_usdt + ? WHERE user_id=?")
->execute([(float)$r['amount'], (int)$r['user_id']]);
}

$reason = "Top-up approved";
if (!empty($r['network'])) $reason .= " • " . $r['network'];
if (!empty($r['txid'])) $reason .= " • TXID: " . $r['txid'];

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")->execute([
(int)$r['user_id'],
(int)($_SESSION['admin_id'] ?? 0),
'credit',
$currency,
(float)$r['amount'],
$reason
]);

$pdo->commit();
}

if ($action === 'reject') {
$pdo->prepare("UPDATE deposit_requests
SET status='rejected', admin_note=?, reviewed_at=NOW(), reviewed_by=?
WHERE id=?")->execute([$note ?: null, (int)($_SESSION['admin_id'] ?? 0), $id]);

$reason = "Top-up rejected";
if (!empty($r['network'])) $reason .= " • " . $r['network'];
if (!empty($r['txid'])) $reason .= " • TXID: " . $r['txid'];
if ($note) $reason .= " • Note: " . $note;

$pdo->prepare("INSERT INTO transactions (user_id, admin_id, kind, currency, amount, reason)
VALUES (?,?,?,?,?,?)")->execute([
(int)$r['user_id'],
(int)($_SESSION['admin_id'] ?? 0),
'note',
($r['currency'] === 'mb') ? 'mb' : 'usdt',
0,
$reason
]);
}
}

header('Location: ' . mr_config('app.base_url') . mr_config('app.admin_path') . '/deposits.php');
exit;
}

$rows = $pdo->query("
SELECT d.*, u.username
FROM deposit_requests d
JOIN users u ON u.id = d.user_id
ORDER BY d.id DESC
LIMIT 50
")->fetchAll();

$is_ar = (mr_lang() === 'ar');
?>

<!doctype html>
<html lang="<?php echo $is_ar?'ar':'en'; ?>" dir="<?php echo $is_ar?'rtl':'ltr'; ?>">
<head>
<meta charset="utf-8">
<title>Deposit Requests</title>
<style><?php echo mr_theme_css(); ?></style>
<style>
body{padding:20px;margin:0}
a{text-decoration:none}
table{width:100%;border-collapse:collapse;background:var(--card);border:1px solid #222;border-radius:var(--radius);overflow:hidden}
td,th{border-bottom:1px solid #222;padding:10px;text-align:left;font-size:13px;vertical-align:top}
.badge{padding:2px 8px;border-radius:999px;background:#ffffff;border:1px solid #222;display:inline-block}
.btn{padding:6px 10px;border-radius:10px;border:0;cursor:pointer;color:#fff}
.ok{background:var(--primary)}
.no{background:#444}
.note{width:220px;padding:8px;border-radius:10px;border:1px solid #222;background:#ffffff;color:#fff}
img{max-width:140px;border-radius:12px;border:1px solid #222}
</style>
</head>
<body>

<h1><?php echo $is_ar?'طلبات الشحن':'Deposit Requests'; ?></h1>
<div style="margin-bottom:12px">
<a href="dashboard.php">← <?php echo $is_ar?'لوحة التحكم':'Dashboard'; ?></a>
</div>

<table>
<tr>
<th>ID</th>
<th>User</th>
<th>Currency</th>
<th>Network</th>
<th>Amount</th>
<th>TxID</th>
<th>Proof</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php foreach ($rows as $r): ?>
<tr>
<td>#<?php echo (int)$r['id']; ?></td>
<td><?php echo htmlspecialchars($r['username']); ?></td>
<td><?php echo htmlspecialchars($r['currency']==='mb' ? ($is_ar?'كريدت':'Credits') : 'USDT'); ?></td>
<td style="max-width:260px;word-break:break-word"><?php echo htmlspecialchars($r['network'] ?? ''); ?></td>
<td><?php echo htmlspecialchars($r['amount']); ?></td>
<td style="max-width:260px;word-break:break-all"><?php echo htmlspecialchars($r['txid'] ?? ''); ?></td>
<td>
<?php if (!empty($r['proof_image'])): ?>
<a href="../<?php echo htmlspecialchars($r['proof_image']); ?>" target="_blank">
<img src="../<?php echo htmlspecialchars($r['proof_image']); ?>">
</a>
<?php endif; ?>
</td>
<td><span class="badge"><?php echo htmlspecialchars($r['status']); ?></span></td>
<td>
<?php if ($r['status'] === 'pending'): ?>
<form method="post" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
<input class="note" name="note" placeholder="<?php echo $is_ar?'ملاحظة (اختياري)':'Admin note (optional)'; ?>">
<button class="btn ok" name="do" value="approve"><?php echo $is_ar?'موافقة':'Approve'; ?></button>
<button class="btn no" name="do" value="reject"><?php echo $is_ar?'رفض':'Reject'; ?></button>
</form>
<?php else: ?>
<?php echo htmlspecialchars($r['admin_note'] ?? ''); ?>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>