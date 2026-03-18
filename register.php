<?php
require_once 'db.php';
if (isLoggedIn()) redirect('index.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean($_POST['name']     ?? '');
    $email    = clean($_POST['email']    ?? '');
    $phone    = clean($_POST['phone']    ?? '');
    $address  = clean($_POST['address']  ?? '');
    $password = $_POST['password']  ?? '';
    $confirm  = $_POST['confirm']   ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check duplicate email
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $name, $email, $hash, $phone, $address);

            if ($stmt->execute()) {
                $_SESSION['user_id']   = $stmt->insert_id;
                $_SESSION['user_name'] = $name;
                redirect('index.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $stmt->close();
        }
        $chk->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Register — FoodZone</title>
<link rel="stylesheet" href="assets/style.css"/>
</head>
<body>

<div class="auth-wrap">

  <!-- LEFT PANEL -->
  <div class="auth-left">
    <div class="auth-left-bg"></div>
    <div class="auth-left-content">
      <a href="index.php" style="color:rgba(255,255,255,.5);font-size:13px;display:flex;align-items:center;gap:6px;margin-bottom:2.5rem;">← Back to Home</a>
      <h2>Join <span>FoodZone</span><br/>Today 🍕</h2>
      <p>Create your free account and start ordering from the best restaurants in your city.</p>
      <div class="auth-perks">
        <div class="perk"><div class="perk-icon">🎁</div>Welcome discount on first order</div>
        <div class="perk"><div class="perk-icon">⭐</div>Earn loyalty points</div>
        <div class="perk"><div class="perk-icon">🔔</div>Real-time order updates</div>
        <div class="perk"><div class="perk-icon">❤️</div>Save favourite meals</div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="auth-right">
    <div class="auth-card">
      <div class="auth-logo">Food<span>Zone</span></div>
      <div class="auth-title">Create account</div>
      <div class="auth-sub">Join thousands of happy customers</div>

      <?php if ($error): ?>
        <div class="err-msg">⚠ <?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="field-row">
          <div class="field">
            <label>Full Name *</label>
            <input type="text" name="name" placeholder="Jane Doe" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required/>
          </div>
          <div class="field">
            <label>Phone</label>
            <input type="tel" name="phone" placeholder="+1 555 0100" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"/>
          </div>
        </div>
        <div class="field">
          <label>Email Address *</label>
          <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
        </div>
        <div class="field">
          <label>Delivery Address</label>
          <input type="text" name="address" placeholder="123 Main St, City" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"/>
        </div>
        <div class="field-row">
          <div class="field">
            <label>Password *</label>
            <input type="password" name="password" placeholder="Min 6 chars" required oninput="checkStr(this.value)"/>
            <div style="height:4px;background:#ece9e4;border-radius:2px;margin-top:6px;overflow:hidden;">
              <div id="str-bar" style="height:100%;width:0;border-radius:2px;transition:all .3s;"></div>
            </div>
          </div>
          <div class="field">
            <label>Confirm Password *</label>
            <input type="password" name="confirm" placeholder="Repeat password" required/>
          </div>
        </div>
        <div class="remember-row" style="margin-bottom:.75rem;">
          <label><input type="checkbox" class="check" required/> I agree to <a href="#">Terms & Privacy</a></label>
        </div>
        <button type="submit" class="auth-btn">Create Account →</button>
      </form>

      <div class="divider">or</div>
      <div class="auth-switch">Already have an account? <a href="login.php">Sign in</a></div>
    </div>
  </div>

</div>

<script>
function checkStr(v) {
  let s = 0;
  if (v.length >= 6)  s++;
  if (v.length >= 10) s++;
  if (/[A-Z]/.test(v)) s++;
  if (/[0-9]/.test(v)) s++;
  if (/[^A-Za-z0-9]/.test(v)) s++;
  const bar = document.getElementById('str-bar');
  bar.style.width = (s / 5 * 100) + '%';
  bar.style.background = s <= 1 ? '#e8341c' : s <= 3 ? '#f5a623' : '#22a85a';
}
</script>
</body>
</html>
