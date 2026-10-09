<?php
require __DIR__ . '/app/bootstrap.php';

mr_require_active_user(true);
mr_csrf_check();

header('Content-Type: application/json; charset=utf-8');

$pdo = mr_db();
$user_id = (int)($_SESSION['user_id'] ?? 0);

$seller_id = (int)($_POST['seller_id'] ?? 0); // 0 = Admin
$rating = (int)($_POST['rating'] ?? 0);

if ($user_id <= 0 || $seller_id < 0 || $rating < 1 || $rating > 5) {
echo json_encode(['ok'=>false, 'msg'=>'Invalid data']);
exit;
}

if ($seller_id > 0 && $seller_id === $user_id) {
echo json_encode(['ok'=>false, 'msg'=>'You cannot rate yourself']);
exit;
}

if ($seller_id > 0) {
$st = $pdo->prepare("SELECT 1 FROM users WHERE id=? LIMIT 1");
$st->execute([$seller_id]);
if (!$st->fetchColumn()) {
echo json_encode(['ok'=>false, 'msg'=>'Seller not found']);
exit;
}
}

$is_admin = false;
$roleStmt = $pdo->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
$roleStmt->execute([$user_id]);
$is_admin = ((string)($roleStmt->fetchColumn() ?: '') === 'admin');

/* normal users must buy from this seller first (except Admin seller_id=0) */
if (!$is_admin && $seller_id > 0) {
$chk = $pdo->prepare("
SELECT 1
FROM orders o
JOIN order_items oi ON oi.order_id = o.id
JOIN products p ON p.id = oi.product_id
WHERE o.user_id=? AND o.status='paid' AND p.seller_id=?
LIMIT 1
");
$chk->execute([$user_id, $seller_id]);

if (!$chk->fetch()) {
echo json_encode(['ok'=>false, 'msg'=>'You must buy from this seller first']);
exit;
}
}

/* if seller_id=0 (Admin), allow rating for any logged-in user */
try {
$sql = "
INSERT INTO seller_reviews (seller_id, user_id, rating, created_at, updated_at)
VALUES (?,?,?,NOW(),NOW())
ON DUPLICATE KEY UPDATE rating=VALUES(rating), updated_at=NOW()
";
$pdo->prepare($sql)->execute([$seller_id, $user_id, $rating]);

echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
echo json_encode(['ok'=>false, 'msg'=>'Server error']);
}

?>  