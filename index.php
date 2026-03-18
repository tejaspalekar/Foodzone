<?php
require_once 'db.php';
$cartCount = cartCount($conn);

// Fetch categories
$cats = $conn->query("SELECT * FROM categories");

// Fetch foods (filter by category if set)
$cat_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$where = $cat_id > 0 ? "WHERE f.category_id = $cat_id" : "";
$foods = $conn->query("SELECT f.*, c.name as cat_name FROM foods f JOIN categories c ON f.category_id=c.id $where ORDER BY f.is_popular DESC, f.rating DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FoodZone — Order Delicious Food</title>
<link rel="stylesheet" href="assets/style.css"/>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="logo">Food<span>Zone</span></div>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="index.php#menu">Menu</a>
    <a href="#">About</a>
    <a href="#">Contact</a>
  </div>
  <div class="nav-actions">
    <?php if (isLoggedIn()): ?>
      <a href="dashboard.php" class="btn btn-ghost">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></a>
      <button class="btn btn-ghost cart-btn" onclick="openCart()">
        🛒 Cart
        <?php if ($cartCount > 0): ?>
          <span class="cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </button>
      <a href="logout.php" class="btn btn-red">Sign Out</a>
    <?php else: ?>
      <a href="login.php" class="btn btn-ghost">Login</a>
      <a href="register.php" class="btn btn-red">Register</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div class="hero-bg"></div>
  <div class="hero-content">
    <div class="hero-badge">🚀 Fast Delivery · 30 mins or less</div>
    <h1>Hungry? We've Got <span>You Covered</span></h1>
    <p>Order from the best restaurants around you. Fresh, fast, and delivered right to your door.</p>
    <div class="hero-btns">
      <a href="#menu" class="btn btn-red" style="font-size:15px;padding:13px 28px;">Order Now →</a>
      <a href="register.php" class="btn" style="background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.25);font-size:15px;padding:13px 28px;">Get Started</a>
    </div>
    <div class="hero-stats">
      <div class="hs"><div class="hs-num">50K+</div><div class="hs-label">Happy Customers</div></div>
      <div class="hs"><div class="hs-num">200+</div><div class="hs-label">Menu Items</div></div>
      <div class="hs"><div class="hs-num">4.9★</div><div class="hs-label">Average Rating</div></div>
    </div>
  </div>
  <div class="hero-img">🍕</div>
</div>

<!-- MENU SECTION -->
<div id="menu" class="section">

  <!-- Categories -->
  <div class="sec-head">
    <div class="sec-title">Browse by <span>Category</span></div>
  </div>
  <div class="cat-grid" style="margin-bottom:2.5rem;">
    <div class="cat-card <?= $cat_id===0?'active':'' ?>" onclick="location='index.php'">
      <span class="cat-icon">🍽️</span>
      <div class="cat-name">All</div>
    </div>
    <?php $cats->data_seek(0); while($c = $cats->fetch_assoc()): ?>
    <div class="cat-card <?= $cat_id==$c['id']?'active':'' ?>" onclick="location='index.php?cat=<?= $c['id'] ?>'">
      <span class="cat-icon"><?= $c['icon'] ?></span>
      <div class="cat-name"><?= htmlspecialchars($c['name']) ?></div>
    </div>
    <?php endwhile; ?>
  </div>

  <!-- Food Grid -->
  <div class="sec-head">
    <div class="sec-title">
      <?= $cat_id > 0 ? 'Results' : 'Popular <span>Picks</span>' ?>
    </div>
    <span style="font-size:14px;color:var(--muted);"><?= $foods->num_rows ?> items</span>
  </div>

  <div class="food-grid">
    <?php while($f = $foods->fetch_assoc()): ?>
    <div class="food-card">
      <div class="food-img">
        <?php if($f['is_popular']): ?><span class="food-badge">🔥 Popular</span><?php endif; ?>
        <?= $f['emoji'] ?>
      </div>
      <div class="food-body">
        <div class="food-name"><?= htmlspecialchars($f['name']) ?></div>
        <div class="food-desc"><?= htmlspecialchars($f['description']) ?></div>
        <div class="food-meta">
          <span class="food-rating">★ <?= $f['rating'] ?></span>
          <span class="food-time">⏱ <?= $f['prep_time'] ?> min</span>
          <span style="font-size:12px;color:var(--muted);"><?= htmlspecialchars($f['cat_name']) ?></span>
        </div>
        <div class="food-footer">
          <div class="food-price">$<?= number_format($f['price'],2) ?></div>
          <?php if(isLoggedIn()): ?>
            <button class="add-btn" onclick="addToCart(<?= $f['id'] ?>, this)" title="Add to cart">+</button>
          <?php else: ?>
            <a href="login.php" class="add-btn" style="display:flex;align-items:center;justify-content:center;" title="Login to order">+</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</div>

<!-- CART SIDEBAR -->
<div class="cart-overlay" id="cart-overlay" onclick="closeCart()"></div>
<div class="cart-panel" id="cart-panel">
  <div class="cart-head">
    <h3>🛒 Your Cart</h3>
    <button class="cart-close" onclick="closeCart()">×</button>
  </div>
  <div class="cart-items" id="cart-items">
    <div class="cart-empty"><span class="cart-empty-icon">🛒</span>Your cart is empty</div>
  </div>
  <div class="cart-footer" id="cart-footer" style="display:none;">
    <div class="cart-total"><span>Total</span><span id="cart-total-val">$0.00</span></div>
    <button class="btn btn-red" style="width:100%;padding:13px;font-size:15px;" onclick="checkout()">Checkout →</button>
  </div>
</div>

<footer>
  <p>© 2026 <span>FoodZone</span>. Made with ❤️ for food lovers.</p>
</footer>

<div class="toast" id="toast"></div>

<script>
function toast(msg, color) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = color || '#1a1612';
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}

function openCart() {
  document.getElementById('cart-overlay').classList.add('open');
  document.getElementById('cart-panel').classList.add('open');
  loadCart();
}

function closeCart() {
  document.getElementById('cart-overlay').classList.remove('open');
  document.getElementById('cart-panel').classList.remove('open');
}

function addToCart(foodId, btn) {
  btn.textContent = '✓';
  btn.style.background = '#22a85a';
  fetch('cart_action.php?action=add&food_id=' + foodId)
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        toast('Added to cart! 🛒');
        updateBadge(d.count);
        setTimeout(() => { btn.textContent = '+'; btn.style.background = ''; }, 1000);
      } else {
        toast(d.message || 'Error', '#e8341c');
      }
    });
}

function loadCart() {
  fetch('cart_action.php?action=get')
    .then(r => r.json())
    .then(d => {
      const el = document.getElementById('cart-items');
      const footer = document.getElementById('cart-footer');
      if (!d.items || d.items.length === 0) {
        el.innerHTML = '<div class="cart-empty"><span class="cart-empty-icon">🛒</span>Your cart is empty</div>';
        footer.style.display = 'none';
        return;
      }
      let html = '', total = 0;
      d.items.forEach(item => {
        const sub = item.price * item.quantity;
        total += sub;
        html += `<div class="cart-item">
          <div class="cart-item-icon">${item.emoji}</div>
          <div>
            <div class="ci-name">${item.name}</div>
            <div class="ci-price">$${parseFloat(item.price).toFixed(2)}</div>
          </div>
          <div class="ci-qty">
            <button class="qty-btn" onclick="updateQty(${item.food_id}, -1)">−</button>
            <span>${item.quantity}</span>
            <button class="qty-btn" onclick="updateQty(${item.food_id}, 1)">+</button>
          </div>
        </div>`;
      });
      el.innerHTML = html;
      document.getElementById('cart-total-val').textContent = '$' + total.toFixed(2);
      footer.style.display = 'block';
    });
}

function updateQty(foodId, delta) {
  fetch(`cart_action.php?action=update&food_id=${foodId}&delta=${delta}`)
    .then(r => r.json())
    .then(d => {
      if (d.success) { loadCart(); updateBadge(d.count); }
    });
}

function updateBadge(count) {
  let badge = document.querySelector('.cart-badge');
  const btn = document.querySelector('.cart-btn');
  if (count > 0) {
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'cart-badge';
      btn && btn.appendChild(badge);
    }
    badge.textContent = count;
  } else if (badge) {
    badge.remove();
  }
}

function checkout() {
  window.location.href = 'checkout.php';
}
</script>
</body>
</html>
