<?php
require_once 'db.php';
if (isLoggedIn()) redirect('index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                redirect('index.php');
            } else {
                $error = 'Incorrect password. Please try again.';
            }
        } else {
            $error = 'No account found with that email.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login — FoodZone</title>
<link rel="stylesheet" href="assets/style.css"/>
</head>
<body>

<div class="auth-wrap">

  <!-- LEFT PANEL -->
  <div class="auth-left">
    <div class="auth-left-bg"></div>
    <div class="auth-left-content">
      <a href="index.php" style="color:rgba(255,255,255,.5);font-size:13px;display:flex;align-items:center;gap:6px;margin-bottom:2.5rem;">← Back to Home</a>
      <h2>Good to see<br/>you <span>back!</span></h2>
      <p>Sign in to track your orders, manage your cart, and enjoy exclusive deals.</p>
      <div class="auth-perks">
        <div class="perk"><div class="perk-icon">🚀</div>Fast 30-min delivery</div>
        <div class="perk"><div class="perk-icon">🔥</div>Hot food, guaranteed</div>
        <div class="perk"><div class="perk-icon">💳</div>Secure payments</div>
        <div class="perk"><div class="perk-icon">📦</div>Live order tracking</div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="auth-right">
    <div class="auth-card">
      <div class="auth-logo">Food<span>Zone</span></div>
      <div class="auth-title">Welcome back</div>
      <div class="auth-sub">Sign in to your account to continue</div>

      <?php if ($error): ?>
        <div class="err-msg">⚠ <?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="field">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="you@example.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required autofocus/>
        </div>
        <div class="field">
          <label>Password</label>
          <input type="password" name="password" placeholder="Your password" required/>
        </div>
        <div class="remember-row">
          <label><input type="checkbox" class="check" name="remember"/> Remember me</label>
          <a href="#">Forgot password?</a>
        </div>
        <button type="submit" class="auth-btn">Sign In →</button>
      </form>

      <div class="divider">or</div>

      <div class="auth-switch">
        Don't have an account? <a href="register.php">Create one free</a>
      </div>
    </div>
  </div>

</div>
</body>
</html>
