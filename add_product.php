<?php
require __DIR__ . '/../app/bootstrap.php';
mr_require_admin();

$pdo = mr_db();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
mr_csrf_check();

$name = trim($_POST['name']);
$price_mb = (float)$_POST['price_mb'];
$price_usdt = (float)$_POST['price_usdt'];
$stock_type = $_POST['stock_type'];
$bulk = trim($_POST['bulk']);

$image_path = null;

if (!empty($_FILES['image']['name'])) {
$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','webp'];

if (in_array($ext, $allowed)) {
$filename = 'prod_' . time() . '_' . rand(1000,9999) . '.' . $ext;
$dest = __DIR__ . '/../uploads/' . $filename;

if (!is_dir(__DIR__ . '/../uploads')) {
mkdir(__DIR__ . '/../uploads', 0777, true);
}

if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
$image_path = 'uploads/' . $filename;
}
}
}

$stmt = $pdo->prepare("INSERT INTO products (seller_id, name, price_mb, price_usdt, stock_type, image_url) VALUES (?,?,?,?,?,?)");
$stmt->execute([$admin_id, $name, $price_mb, $price_usdt, $stock_type, $image_path]);
$product_id = $pdo->lastInsertId();

if ($bulk) {
$lines = explode("\n",$bulk);
foreach ($lines as $line) {
$line = trim($line);
if (!$line) continue;

if ($stock_type === 'rdp') {
$parts = explode('|',$line);
if (count($parts) === 3) {
$pdo->prepare("INSERT INTO product_stock (product_id,type,host,user,pass) VALUES (?,?,?,?,?)")
->execute([$product_id,'rdp',trim($parts[0]),trim($parts[1]),trim($parts[2])]);
}
} else {
$parts = explode(':',$line);
if (count($parts) === 2) {
$pdo->prepare("INSERT INTO product_stock (product_id,type,email,pass) VALUES (?,?,?,?)")
->execute([$product_id,'email',trim($parts[0]),trim($parts[1])]);
}
}
}
}

$msg = "Product added successfully";
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Add Product</title>
<style>
body{font-family:Arial;background:#0f141b;color:#fff;padding:20px}
input,textarea,select{width:100%;padding:10px;margin-top:8px;background:#161d26;border:1px solid #222;color:#fff;border-radius:8px}
button{margin-top:12px;padding:10px 16px;background:#2f6bff;border:0;border-radius:8px;color:#fff;cursor:pointer}
label{margin-top:12px;display:block}
</style>
</head>
<body>

<h1>Add Product</h1>

<?php if($msg): ?>
<div style="color:#4da3ff;"><?php echo $msg; ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?php echo mr_csrf_token(); ?>">

<label>Name</label>
<input name="name" required>

<label>Price MB</label>
<input name="price_mb" type="number" step="0.01">

<label>Price USDT</label>
<input name="price_usdt" type="number" step="0.01">

<label>Stock Type</label>
<select name="stock_type">
<option value="rdp">RDP (host|user|pass)</option>
<option value="email">Email (email:pass)</option>
</select>

<label>Product Image</label>
<input type="file" name="image" accept="image/*">

<label>Bulk Stock</label>
<textarea name="bulk" rows="10" placeholder="111.22.33.44|user|pass"></textarea>

<button type="submit">Add Product</button>
</form>

</body>
</html>