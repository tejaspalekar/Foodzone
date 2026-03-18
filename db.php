<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // XAMPP default
define('DB_PASS', '');          // XAMPP default (empty)
define('DB_NAME', 'foodzone');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;background:#fff0f0;color:#c00;border-radius:10px;margin:2rem;">
        <h3>Database Connection Failed</h3>
        <p>' . $conn->connect_error . '</p>
        <p>Make sure XAMPP MySQL is running and you have run <strong>setup.sql</strong> in phpMyAdmin.</p>
    </div>');
}

$conn->set_charset("utf8mb4");

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: check if logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Helper: sanitize
function clean($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}

// Cart count helper
function cartCount($conn) {
    if (!isLoggedIn()) return 0;
    $uid = $_SESSION['user_id'];
    $r = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id=$uid");
    $row = $r->fetch_assoc();
    return $row['total'] ?? 0;
}
?>
