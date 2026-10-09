<?php
require __DIR__ . '/../app/bootstrap.php';
mr_require_admin();

$pdo = mr_db();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
mr_csrf_check();

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
if ($action === 'disable') {
$st = $pdo->prepare("UPDATE products SET is_active=0 WHERE id=? LIMIT 1");
$st->execute([$id]);
$msg = 'Product disabled successfully';
}

elseif ($action === 'enable') {
$st = $pdo->prepare("UPDATE products SET is_active=1 WHERE id=? LIMIT 1");
$st->execute([$id]);
$msg = 'Product enabled successfully';
}

elseif ($action === 'delete') {
try {
$st = $pdo->prepare("DELETE FROM products WHERE id=? LIMIT 1");
$st->execute([$id]);

if ($st->rowCount()) {
$msg = 'Product deleted successfully';
} else {
$err = 'Product not found or could not be deleted';
}
} catch (Throwable $e) {
$err = 'Normal delete failed. Use Force Delete if this product has related stock or order items.';
}
}

elseif ($action === 'force_delete') {
try {
$pdo->beginTransaction();

$pdo->prepare("DELETE FROM product_stock WHERE product_id=?")->execute([$id]);
$pdo->prepare("DELETE FROM order_items WHERE product_id=?")->execute([$id]);
$pdo->prepare("DELETE FROM products WHERE id=? LIMIT 1")->execute([$id]);

$pdo->commit();
$msg = 'Product force deleted successfully';
} catch (Throwable $e) {
if ($pdo->inTransaction()) $pdo->rollBack();
$err = 'Force delete failed: ' . $e->getMessage();
}
}
}
}

$products = $pdo->query("
SELECT p.*,
(SELECT COUNT(*) FROM product_stock WHERE product_id=p.id AND status='available') as available_stock,
(SELECT COUNT(*) FROM product_stock WHERE product_id=p.id AND status='sold') as sold_stock
FROM products p
ORDER BY p.id DESC
")->fetchAll();
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<?php echo mr_head_tags(); ?>
<title>Products</title>
<style><?php echo mr_theme_css(); ?></style>
<style>
*{box-sizing:border-box}
body{
font-family:var(--font);
font-size:var(--fontSize);
font-weight:var(--fontWeight);
background:var(--bg);
color:var(--text);
padding:20px;
margin:0;
}
.top{
display:flex;
justify-content:space-between;
align-items:center;
gap:12px;
margin-bottom:20px;
flex-wrap:wrap;
}
h1{margin:0}
.btn{
display:inline-block;
background:var(--primary);
color:#fff;
padding:10px 14px;
border-radius:10px;
text-decoration:none;
border:none;
cursor:pointer;
}
.btn.gray{
background:#475569;
}
.btn.green{
background:#059669;
}
.btn.red{
background:#dc2626;
}
.btn.orange{
background:#ea580c;
}
.msg{
padding:12px 14px;
border-radius:12px;
margin-bottom:14px;
background:#052e16;
color:#86efac;
border:1px solid #166534;
}
.err{
padding:12px 14px;
border-radius:12px;
margin-bottom:14px;
background:#3b0a0a;
color:#fecaca;
border:1px solid #7f1d1d;
}
.tableWrap{
overflow:auto;
background:var(--card);
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
border-radius:16px;
}
table{
width:100%;
border-collapse:collapse;
}
th,td{
padding:12px;
border-bottom:1px solid color-mix(in srgb, var(--text) 10%, transparent);
text-align:left;
vertical-align:middle;
}
th{
background:color-mix(in srgb, var(--card) 85%, #000 15%);
}
tr:last-child td{
border-bottom:none;
}
.prodImg{
width:72px;
height:72px;
object-fit:cover;
border-radius:10px;
background:#111;
border:1px solid color-mix(in srgb, var(--text) 10%, transparent);
}
.badge{
display:inline-block;
padding:5px 10px;
border-radius:999px;
font-size:12px;
font-weight:700;
}
.badge.on{
background:#052e16;
color:#86efac;
border:1px solid #166534;
}
.badge.off{
background:#3b0a0a;
color:#fca5a5;
border:1px solid #7f1d1d;
}
.actions{
display:flex;
gap:8px;
flex-wrap:wrap;
}
.actions form{
margin:0;
}
.small{
font-size:12px;
opacity:.8;
}
.backLink{
display:inline-block;
margin-bottom:16px;
color:var(--primary);
text-decoration:none;
}
</style>
</head>
<body>

<a class="backLink" href="dashboard.php">← Back to Dashboard</a>

<div class="top">
<h1>Products</h1>
<a class="btn" href="add_product.php">+ Add Product</a>
</div>

<?php if ($msg): ?>
<div class="msg"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<?php if ($err): ?>
<div class="err"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<div class="tableWrap">
<table>
<tr>
<th>ID</th>
<th>Image</th>
<th>Name</th>
<th>Prices</th>
<th>Stock</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php foreach ($products as $p): ?>
<tr>
<td>#<?php echo (int)$p['id']; ?></td>
<td>
<?php if (!empty($p['image_url'])): ?>
<img class="prodImg" src="../<?php echo htmlspecialchars($p['image_url']); ?>" alt="">
<?php else: ?>
<img class="prodImg" src="https://via.placeholder.com/100x100/111/555?text=No+Image" alt="">
<?php endif; ?>
</td>
<td>
<strong><?php echo htmlspecialchars($p['name']); ?></strong>
<div class="small"><?php echo !empty($p['category']) ? htmlspecialchars($p['category']) : 'General'; ?></div>
</td>
<td>
<div><?php echo htmlspecialchars((string)$p['price_mb']); ?> MB</div>
<div><?php echo htmlspecialchars((string)$p['price_usdt']); ?> USDT</div>
</td>
<td>
<div>Available: <?php echo (int)$p['available_stock']; ?></div>
<div>Sold: <?php echo (int)$p['sold_stock']; ?></div>
</td>
<td>
<?php if ((int)$p['is_active'] === 1): ?>
<span class="badge on">Active</span>
<?php else: ?>
<span class="badge off">Disabled</span>
<?php endif; ?>
</td>
<td>
<div class="actions">
<a class="btn gray" href="edit_product.php?id=<?php echo (int)$p['id']; ?>">Edit</a>

<?php if ((int)$p['is_active'] === 1): ?>
<form method="post" onsubmit="return confirm('Disable this product?');">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="disable">
<input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
<button class="btn orange" type="submit">Disable</button>
</form>
<?php else: ?>
<form method="post" onsubmit="return confirm('Enable this product?');">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="enable">
<input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
<button class="btn green" type="submit">Enable</button>
</form>
<?php endif; ?>

<form method="post" onsubmit="return confirm('Delete this product?');">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
<button class="btn red" type="submit">Delete</button>
</form>

<form method="post" onsubmit="return confirm('Force delete this product and related stock/order items?');">
<input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(mr_csrf_token()); ?>">
<input type="hidden" name="action" value="force_delete">
<input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
<button class="btn red" type="submit">Force Delete</button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

</body>
</html>