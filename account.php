<?php
require __DIR__ . '/../app/bootstrap.php';
mr_require_admin();

$pdo = mr_db();
$admin_id = (int)($_SESSION['admin_id'] ?? 0);

$tab = $_GET['tab'] ?? 'txns';
$tab_allowed = ['txns','products','sales','support','users','settings'];
if (!in_array($tab, $tab_allowed, true)) $tab = 'txns';

$adminStmt = $pdo->prepare("SELECT id, username, role, avatar_url FROM users WHERE id=? LIMIT 1");
$adminStmt->execute([$admin_id]);
$admin = $adminStmt->fetch();
if (!$admin) die("Admin not found");

$w = $pdo->prepare("SELECT balance_mb, balance_usdt FROM wallets WHERE user_id=?");
$w->execute([$admin_id]);
$w = $w->fetch() ?: ['balance_mb'=>0,'balance_usdt'=>0];

function credits_fmt($n) { return number_format((float)$n, 2, '.', ''); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }

$tx = $pdo->prepare("SELECT * FROM transactions WHERE user_id=? ORDER BY id DESC LIMIT 20");
$tx->execute([$admin_id]);
$tx = $tx->fetchAll();

$dep = $pdo->query("
  SELECT d.*, u.username
  FROM deposit_requests d
  JOIN users u ON u.id=d.user_id
  ORDER BY d.id DESC
  LIMIT 20
")->fetchAll();

$products = [];
$sales = [];
$tickets = [];
$users = [];

if ($tab === 'products') {
  $products = $pdo->query("
    SELECT p.*,
    (SELECT COUNT(*) FROM product_stock WHERE product_id=p.id AND status='available') as available_stock,
    (SELECT COUNT(*) FROM product_stock WHERE product_id=p.id AND status='sold') as sold_stock
    FROM products p
    ORDER BY p.id DESC
    LIMIT 30
  ")->fetchAll();
}

if ($tab === 'sales') {
  $sales = $pdo->query("
    SELECT o.*,
    (SELECT COALESCE(SUM(qty),0) FROM order_items WHERE order_id=o.id) as qty_total
    FROM orders o
    ORDER BY o.id DESC
    LIMIT 30
  ")->fetchAll();
}

if ($tab === 'support') {
  $tickets = $pdo->query("
    SELECT t.*, u.username
    FROM tickets t
    JOIN users u ON u.id=t.user_id
    ORDER BY t.id DESC
    LIMIT 30
  ")->fetchAll();
}

if ($tab === 'users') {
  $users = $pdo->query("
    SELECT u.*, w.balance_mb, w.balance_usdt
    FROM users u
    LEFT JOIN wallets w ON w.user_id=u.id
    ORDER BY u.id DESC
    LIMIT 30
  ")->fetchAll();
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Panel</title>
<style>
body{font-family:Arial;background:#0f141b;color:#fff;margin:0;padding:18px}
a{color:inherit;text-decoration:none}
.wrap{max-width:820px;margin:0 auto}
.card{background:#161d26;border:1px solid #222;border-radius:18px;padding:16px}
.profile{display:flex;gap:12px;align-items:center}
.avatar{width:52px;height:52px;border-radius:14px;background:#ffffff;border:1px solid #222;overflow:hidden;display:flex;align-items:center;justify-content:center}
.avatar img{width:100%;height:100%;object-fit:cover}
.name{font-weight:700;font-size:16px}
.role{font-size:12px;opacity:.85;margin-top:3px}
.sectionTitle{margin-top:14px;font-size:12px;opacity:.75;letter-spacing:.6px;text-transform:uppercase}
.balance{margin-top:8px;font-size:26px;font-weight:800}
.balanceSub{opacity:.8;font-size:12px;margin-top:4px}
.item{margin-top:10px;background:#ffffff;border:1px solid #222;border-radius:14px;padding:12px;display:flex;justify-content:space-between;align-items:center}
.item .left{display:flex;gap:10px;align-items:center}
.chev{opacity:.7}
.tx{margin-top:10px;background:#ffffff;border:1px solid #222;border-radius:14px;padding:12px}
.txRow{display:flex;justify-content:space-between;gap:10px;padding:10px 0;border-bottom:1px solid #1c1c1c}
.txRow:last-child{border-bottom:0}
.txName{font-weight:600}
.txMeta{font-size:12px;opacity:.8;margin-top:3px}
.txAmt{font-weight:800}
.neg{color:#ff7b7b}
.pos{color:#7bffb0}
.badge{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid #222;background:#161d26;font-size:12px;opacity:.9;cursor:pointer}
.panelLink{display:inline-block}
.hr{border:0;border-top:1px solid #222;margin:14px 0}

.panel{margin-top:14px;background:#ffffff;border:1px solid #222;border-radius:18px;padding:12px}
.tabs{display:flex;gap:10px;flex-wrap:wrap}
.tab{padding:8px 12px;border-radius:999px;border:1px solid #222;background:#161d26;font-size:13px;opacity:.9}
.tab.active{border-color:#2f6bff}
.listRow{padding:12px 0;border-bottom:1px solid #1c1c1c}
.listRow:last-child{border-bottom:0}
.rowTop{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}
.muted{opacity:.8;font-size:12px;margin-top:4px}
.btnSmall{display:inline-block;padding:6px 10px;border-radius:10px;border:1px solid #222;background:#161d26;font-size:12px}
</style>
</head>
<body>
<div class="wrap">

<div class="card">

  <div class="profile">
    <div class="avatar">
      <?php if (!empty($admin['avatar_url'])): ?>
        <img src="../<?php echo h($admin['avatar_url']); ?>">
      <?php else: ?>
        <div style="opacity:.7;font-weight:800">A</div>
      <?php endif; ?>
    </div>
    <div>
      <div class="name"><?php echo h($admin['username']); ?></div>
      <div class="role">
        <?php echo h(ucfirst($admin['role'])); ?>
        <a class="panelLink" href="#panel"><span class="badge">Panel</span></a>
      </div>
    </div>
  </div>

  <div class="sectionTitle">Wallet Balance</div>
  <div class="balance"><?php echo credits_fmt($w['balance_mb']); ?> Credits</div>
  <div class="balanceSub">USDT: <?php echo credits_fmt($w['balance_usdt']); ?></div>

  <div class="sectionTitle">Shortcuts</div>

  <a href="#panel"><div class="item"><div class="left"><div style="font-weight:700">Panel</div></div><div class="chev">›</div></div></a>
  <a href="tickets.php"><div class="item"><div class="left"><div style="font-weight:700">Help & Support</div></div><div class="chev">›</div></div></a>
  <a href="deposits.php"><div class="item"><div class="left"><div style="font-weight:700">Deposit Requests</div></div><div class="chev">›</div></div></a>
  <a href="products.php"><div class="item"><div class="left"><div style="font-weight:700">Products</div></div><div class="chev">›</div></div></a>
  <a href="users.php"><div class="item"><div class="left"><div style="font-weight:700">Users</div></div><div class="chev">›</div></div></a>
  <a href="dashboard.php"><div class="item"><div class="left"><div style="font-weight:700">Dashboard</div></div><div class="chev">›</div></div></a>
  <a href="logout.php"><div class="item"><div class="left"><div style="font-weight:700">Sign Out</div></div><div class="chev">›</div></div></a>

  <a id="panel"></a>
  <div class="panel">
    <div class="tabs">
      <a class="tab <?php echo $tab==='txns'?'active':''; ?>" href="account.php?tab=txns#panel">Txns</a>
      <a class="tab <?php echo $tab==='products'?'active':''; ?>" href="account.php?tab=products#panel">Products</a>
      <a class="tab <?php echo $tab==='sales'?'active':''; ?>" href="account.php?tab=sales#panel">Sales</a>
      <a class="tab <?php echo $tab==='support'?'active':''; ?>" href="account.php?tab=support#panel">Support</a>
      <a class="tab <?php echo $tab==='users'?'active':''; ?>" href="account.php?tab=users#panel">Users</a>
      <a class="tab <?php echo $tab==='settings'?'active':''; ?>" href="account.php?tab=settings#panel">Settings</a>
    </div>

    <?php if ($tab === 'txns'): ?>
      <div class="sectionTitle">Manual (Admin transactions)</div>
      <div class="tx">
        <?php if (!$tx): ?><div style="opacity:.8;font-size:13px">No manual txns.</div><?php endif; ?>
        <?php foreach ($tx as $t): ?>
          <?php
            $amt = (float)$t['amount'];
            $isNeg = $t['kind'] === 'debit';
            $cls = $isNeg ? 'neg' : 'pos';
            $label = $t['reason'] ?: $t['kind'];
            $cur = strtoupper($t['currency']);
          ?>
          <div class="txRow">
            <div>
              <div class="txName"><?php echo h($label); ?></div>
              <div class="txMeta"><?php echo h($t['created_at']); ?> • <?php echo h($t['kind']); ?></div>
            </div>
            <div class="txAmt <?php echo $cls; ?>">
              <?php echo $isNeg ? '-' : '+'; ?><?php echo credits_fmt($amt); ?> <?php echo $cur==='MB'?'Credits':$cur; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="sectionTitle">Top-up (Deposit requests)</div>
      <div class="tx">
        <?php if (!$dep): ?><div style="opacity:.8;font-size:13px">No deposits.</div><?php endif; ?>

        <?php foreach ($dep as $d): ?>
          <div class="listRow">
            <div class="rowTop">
              <div style="font-weight:700"><?php echo h($d['username']); ?> - <?php echo credits_fmt($d['amount']); ?> <?php echo $d['currency']==='mb'?'Credits':'USDT'; ?></div>
              <div class="badge"><?php echo h($d['status']); ?></div>
            </div>
            <div class="muted">
              <?php echo h($d['network'] ?? ''); ?> • TXID: <?php echo h($d['txid'] ?? ''); ?>
            </div>
            <?php if (!empty($d['admin_note'])): ?>
              <div class="muted">Note: <?php echo h($d['admin_note']); ?></div>
            <?php endif; ?>
            <?php if (!empty($d['proof_image'])): ?>
              <div style="margin-top:8px">
                <a class="btnSmall" target="_blank" href="../<?php echo h($d['proof_image']); ?>">View Proof</a>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

    <?php elseif ($tab === 'products'): ?>
      <div class="sectionTitle">Products</div>
      <div class="tx">
        <?php foreach ($products as $p): ?>
          <div class="listRow">
            <div class="rowTop">
              <div style="font-weight:700"><?php echo h($p['name']); ?></div>
              <div class="muted">#<?php echo (int)$p['id']; ?></div>
            </div>
            <div class="muted">Available: <?php echo (int)$p['available_stock']; ?> • Sold: <?php echo (int)$p['sold_stock']; ?></div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php elseif ($tab === 'sales'): ?>
      <div class="sectionTitle">Sales</div>
      <div class="tx">
        <?php foreach ($sales as $o): ?>
          <div class="listRow">
            <div class="rowTop">
              <div style="font-weight:700">Order #<?php echo (int)$o['id']; ?></div>
              <div class="muted"><?php echo h($o['created_at']); ?></div>
            </div>
            <div class="muted">User ID: <?php echo (int)$o['user_id']; ?> • Qty: <?php echo (int)$o['qty_total']; ?></div>
            <div class="muted">Total: <?php echo $o['currency']==='mb' ? credits_fmt($o['total_mb']).' Credits' : credits_fmt($o['total_usdt']).' USDT'; ?></div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php elseif ($tab === 'support'): ?>
      <div class="sectionTitle">Support</div>
      <div class="tx">
        <?php foreach ($tickets as $t): ?>
          <div class="listRow">
            <div class="rowTop">
              <div style="font-weight:700">#<?php echo (int)$t['id']; ?> • <?php echo h($t['username']); ?></div>
              <div class="badge"><?php echo h($t['status']); ?></div>
            </div>
            <div class="muted"><?php echo h($t['subject']); ?></div>
            <div style="margin-top:8px">
              <a class="btnSmall" href="tickets.php?ticket_id=<?php echo (int)$t['id']; ?>">Open Chat</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php elseif ($tab === 'users'): ?>
      <div class="sectionTitle">Users</div>
      <div class="tx">
        <?php foreach ($users as $u): ?>
          <div class="listRow">
            <div class="rowTop">
              <div style="font-weight:700"><?php echo h($u['username']); ?></div>
              <div class="badge"><?php echo h($u['status']); ?></div>
            </div>
            <div class="muted">
              Role: <?php echo h($u['role']); ?> • Credits: <?php echo credits_fmt($u['balance_mb'] ?? 0); ?> • USDT: <?php echo credits_fmt($u['balance_usdt'] ?? 0); ?>
            </div>
            <div style="margin-top:8px">
              <a class="btnSmall" href="users.php?q=<?php echo urlencode($u['username']); ?>">Manage</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <div class="sectionTitle">Settings</div>
      <div class="tx">
        <div class="muted">Settings screen جاهزة نعملها هنا بعد ما نضيف جدول settings والثيم.</div>
        <div style="margin-top:10px">
          <a class="btnSmall" href="dashboard.php">Back to dashboard</a>
        </div>
      </div>
    <?php endif; ?>

  </div>

</div>

</div>
</body>
</html>