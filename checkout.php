<?php
require_once 'db.php';
if (!isLoggedIn()) redirect('login.php');

$uid  = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();

// Get cart items
$cartItems = [];
$total = 0;
$r = $conn->query("SELECT c.food_id, c.quantity, f.name, f.price, f.emoji FROM cart c JOIN foods f ON c.food_id=f.id WHERE c.user_id=$uid");
while ($row = $r->fetch_assoc()) {
    $cartItems[] = $row;
    $total += $row['price'] * $row['quantity'];
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && count($cartItems) > 0) {
    $address = clean($_POST['address'] ?? '');
    if (!$address) { $error = 'Please enter a delivery address.'; }
    else {
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, address) VALUES (?,?,?)");
        $stmt->bind_param("ids", $uid, $total, $address);
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();
        // Insert order items
        foreach ($cartItems as $item) {
            $st2 = $conn->prepare("INSERT INTO order_items (order_id, food_id, quantity, price) VALUES (?,?,?,?)");
            $st2->bind_param("iiid", $orderId, $item['food_id'], $item['quantity'], $item['price']);
            $st2->execute();
            $st2->close();
        }
        // Clear cart
        $conn->query("DELETE FROM cart WHERE user_id=$uid");
        $success = $orderId;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Checkout — FoodZone</title>
<link rel="stylesheet" href="assets/style.css"/>
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="logo">Food<span>Zone</span></a>
  <div class="nav-actions">
    <a href="index.php" class="btn btn-ghost">← Back to Menu</a>
  </div>
</nav>

<div class="section" style="max-width:800px;">

  <?php if ($success): ?>
  <div style="text-align:center;padding:4rem 1rem;">
    <div style="font-size:64px;margin-bottom:1rem;">🎉</div>
    <h2 style="font-size:28px;font-weight:800;margin-bottom:.5rem;">Order Placed!</h2>
    <p style="color:var(--muted);margin-bottom:2rem;">Order <strong>#<?= str_pad($success, 4, '0', STR_PAD_LEFT) ?></strong> is confirmed. We're preparing your food!</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="dashboard.php" class="btn btn-red">View Orders</a>
      <a href="index.php" class="btn btn-ghost">Order More</a>
    </div>
  </div>

  <?php elseif (count($cartItems) === 0): ?>
  <div style="text-align:center;padding:4rem 1rem;">
    <div style="font-size:56px;margin-bottom:1rem;">🛒</div>
    <h2 style="margin-bottom:.5rem;">Your cart is empty</h2>
    <a href="index.php" class="btn btn-red" style="margin-top:1rem;display:inline-flex;">Browse Menu →</a>
  </div>

  <?php else: ?>
  <h2 style="font-size:24px;font-weight:800;margin-bottom:2rem;">Checkout</h2>

  <?php if ($error): ?><div class="err-msg">⚠ <?= $error ?></div><?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 360px;gap:2rem;align-items:start;">

    <!-- Order Summary -->
    <div>
      <div style="font-size:16px;font-weight:700;margin-bottom:1rem;">Order Summary</div>
      <div style="background:var(--white);border:1px solid var(--border);border-radius:16px;overflow:hidden;">
        <?php foreach($cartItems as $item): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid var(--border);">
          <div style="font-size:28px;width:44px;height:44px;background:var(--red-light);border-radius:10px;display:flex;align-items:center;justify-content:center;"><?= $item['emoji'] ?></div>
          <div style="flex:1;">
            <div style="font-size:14px;font-weight:600;"><?= htmlspecialchars($item['name']) ?></div>
            <div style="font-size:12px;color:var(--muted);">Qty: <?= $item['quantity'] ?></div>
          </div>
          <div style="font-size:15px;font-weight:700;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
        </div>
        <?php endforeach; ?>
        <div style="padding:16px;display:flex;justify-content:space-between;font-size:16px;font-weight:800;">
          <span>Total</span><span style="color:var(--red);">$<?= number_format($total, 2) ?></span>
        </div>
      </div>
    </div>

    <!-- Delivery Form -->
    <div>
      <div style="font-size:16px;font-weight:700;margin-bottom:1rem;">Delivery Details</div>
      <div style="background:var(--white);border:1px solid var(--border);border-radius:16px;padding:1.5rem;">
        <form method="POST">
          <div class="field">
            <label>Delivery Address *</label>
            <input type="text" name="address" placeholder="Street, City, ZIP" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required/>
          </div>
          <div class="field">
            <label>Name</label>
            <input type="text" value="<?= htmlspecialchars($user['name']) ?>" disabled style="background:#faf9f7;color:var(--muted);"/>
          </div>
          <div class="field">
            <label>Phone</label>
            <input type="text" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" disabled style="background:#faf9f7;color:var(--muted);"/>
          </div>
          <div style="background:var(--red-light);border-radius:10px;padding:12px;font-size:13px;color:var(--red);margin-bottom:1rem;">
            🚀 Estimated delivery: <strong>25–35 minutes</strong>
          </div>
          <button type="submit" class="auth-btn">Place Order — $<?= number_format($total, 2) ?></button>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<footer><p>© 2026 <span>FoodZone</span>. Made with ❤️ for food lovers.</p></footer>
</body>
</html>
