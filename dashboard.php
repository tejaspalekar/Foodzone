<?php
require_once 'db.php';
if (!isLoggedIn()) redirect('login.php');

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
$orders = $conn->query("SELECT * FROM orders WHERE user_id=$uid ORDER BY created_at DESC LIMIT 10");
$order_count = $conn->query("SELECT COUNT(*) as c FROM orders WHERE user_id=$uid")->fetch_assoc()['c'];
$spent = $conn->query("SELECT SUM(total) as s FROM orders WHERE user_id=$uid AND status='delivered'")->fetch_assoc()['s'] ?? 0;
$cartCount = cartCount($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Dashboard — FoodZone</title>
<link rel="stylesheet" href="assets/style.css"/>
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="logo">Food<span>Zone</span></a>
  <div class="nav-links">
    <a href="index.php">Menu</a>
    <a href="dashboard.php" style="color:var(--red);">Dashboard</a>
  </div>
  <div class="nav-actions">
    <button class="btn btn-ghost cart-btn" onclick="location='index.php#menu'">
      🛒 Cart <?php if($cartCount>0):?><span class="cart-badge"><?=$cartCount?></span><?php endif;?>
    </button>
    <a href="logout.php" class="btn btn-red">Sign Out</a>
  </div>
</nav>

<div class="dashboard">
  <div class="dash-header">
    <div class="dash-greeting">Hey, <span><?= htmlspecialchars($user['name']) ?></span> 👋</div>
    <a href="index.php" class="btn btn-red">Order More Food →</a>
  </div>

  <div class="dash-cards">
    <div class="dash-card">
      <div class="dash-card-num"><?= $order_count ?></div>
      <div class="dash-card-label">Total Orders</div>
    </div>
    <div class="dash-card">
      <div class="dash-card-num">$<?= number_format($spent, 2) ?></div>
      <div class="dash-card-label">Total Spent</div>
    </div>
    <div class="dash-card">
      <div class="dash-card-num"><?= $cartCount ?></div>
      <div class="dash-card-label">Items in Cart</div>
    </div>
  </div>

  <div style="margin-bottom:1rem;">
    <div class="sec-title" style="font-size:18px;">Recent Orders</div>
  </div>

  <?php if ($orders->num_rows > 0): ?>
  <table class="orders-table">
    <thead>
      <tr>
        <th>Order #</th>
        <th>Date</th>
        <th>Total</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php while($o = $orders->fetch_assoc()): ?>
      <tr>
        <td>#<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></td>
        <td><?= date('M d, Y · H:i', strtotime($o['created_at'])) ?></td>
        <td style="font-weight:700;">$<?= number_format($o['total'], 2) ?></td>
        <td><span class="status-pill s-<?= $o['status'] ?>"><?= ucfirst(str_replace('_', ' ', $o['status'])) ?></span></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div style="background:var(--white);border:1px solid var(--border);border-radius:16px;padding:3rem;text-align:center;color:var(--muted);">
    <div style="font-size:48px;margin-bottom:1rem;">🍽️</div>
    <p>You haven't placed any orders yet.</p>
    <a href="index.php" class="btn btn-red" style="margin-top:1rem;display:inline-flex;">Browse Menu →</a>
  </div>
  <?php endif; ?>

  <!-- Profile Info -->
  <div style="margin-top:2.5rem;">
    <div class="sec-title" style="font-size:18px;margin-bottom:1rem;">Your Profile</div>
    <div style="background:var(--white);border:1px solid var(--border);border-radius:16px;padding:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
      <div><div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;">Name</div><div style="font-size:15px;margin-top:3px;"><?= htmlspecialchars($user['name']) ?></div></div>
      <div><div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;">Email</div><div style="font-size:15px;margin-top:3px;"><?= htmlspecialchars($user['email']) ?></div></div>
      <div><div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;">Phone</div><div style="font-size:15px;margin-top:3px;"><?= $user['phone'] ?: '—' ?></div></div>
      <div><div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;">Address</div><div style="font-size:15px;margin-top:3px;"><?= $user['address'] ?: '—' ?></div></div>
    </div>
  </div>
</div>

<footer><p>© 2026 <span>FoodZone</span>. Made with ❤️ for food lovers.</p></footer>
</body>
</html>
