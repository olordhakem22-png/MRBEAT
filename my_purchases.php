<?php
require __DIR__ . '/app/bootstrap.php';
mr_require_active_user();
if (empty($_SESSION['user_id'])) {
header('Location: ' . mr_config('app.base_url') . '/login.php');
exit;
}
$user_id = (int)$_SESSION['user_id'];

$pdo = mr_db();

$ordersStmt = $pdo->prepare("
SELECT o.*
FROM orders o
WHERE o.user_id=?
ORDER BY o.id DESC
");
$ordersStmt->execute([$user_id]);
$orders = $ordersStmt->fetchAll();
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>My Purchases</title>
<style>
body{font-family:Arial;background:#0f141b;color:#fff;padding:20px}
.card{background:#161d26;padding:15px;border-radius:12px;margin-bottom:15px;border:1px solid #222}
.box{background:#0b0f14;padding:10px;border-radius:8px;margin-top:6px}
.copy{background:#2f6bff;border:0;padding:4px 8px;border-radius:6px;color:#fff;cursor:pointer;margin-left:6px}
a{color:#4da3ff;text-decoration:none}
</style>
<script>
function copyText(text){
navigator.clipboard.writeText(text);
alert("Copied");
}
</script>
</head>
<body>

<a href="index.php">← Home</a>

<h1>My Purchases</h1>

<?php if (!$orders): ?>
<div>No purchases yet.</div>
<?php endif; ?>

<?php foreach ($orders as $order): ?>
<div class="card">
<strong>Order #<?php echo (int)$order['id']; ?></strong><br>
Currency: <?php echo htmlspecialchars($order['currency']); ?><br>
Total: <?php echo $order['currency']==='mb' ? $order['total_mb'].' MB' : $order['total_usdt'].' USDT'; ?><br>
Date: <?php echo htmlspecialchars($order['created_at']); ?>

<?php
$itemsStmt = $pdo->prepare("SELECT s.*, p.name as product_name
FROM product_stock s
JOIN products p ON p.id = s.product_id
WHERE s.sold_order_id=?
ORDER BY s.id ASC");
$itemsStmt->execute([(int)$order['id']]);
$items = $itemsStmt->fetchAll();
?>

<?php foreach ($items as $item): ?>
<div class="box">
<div style="opacity:.85;font-size:13px;margin-bottom:6px;">
Product: <?php echo htmlspecialchars($item['product_name']); ?>
</div>

<?php if ($item['type'] === 'rdp'): ?>
Host: <?php echo htmlspecialchars($item['host']); ?>
<button class="copy" onclick="copyText('<?php echo htmlspecialchars($item['host'], ENT_QUOTES); ?>')">Copy</button><br>

Username: <?php echo htmlspecialchars($item['user']); ?>
<button class="copy" onclick="copyText('<?php echo htmlspecialchars($item['user'], ENT_QUOTES); ?>')">Copy</button><br>

Password: <?php echo htmlspecialchars($item['pass']); ?>
<button class="copy" onclick="copyText('<?php echo htmlspecialchars($item['pass'], ENT_QUOTES); ?>')">Copy</button>
<?php else: ?>
Email: <?php echo htmlspecialchars($item['email']); ?>
<button class="copy" onclick="copyText('<?php echo htmlspecialchars($item['email'], ENT_QUOTES); ?>')">Copy</button><br>

Password: <?php echo htmlspecialchars($item['pass']); ?>
<button class="copy" onclick="copyText('<?php echo htmlspecialchars($item['pass'], ENT_QUOTES); ?>')">Copy</button>
<?php endif; ?>
</div>
<?php endforeach; ?>

</div>
<?php endforeach; ?>

</body>
</html>