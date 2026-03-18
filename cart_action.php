<?php
require_once 'db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}

$uid    = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'add':
        $fid = (int)($_GET['food_id'] ?? 0);
        if (!$fid) { echo json_encode(['success'=>false]); exit; }
        // Check if already in cart
        $r = $conn->query("SELECT id, quantity FROM cart WHERE user_id=$uid AND food_id=$fid");
        if ($r->num_rows > 0) {
            $row = $r->fetch_assoc();
            $conn->query("UPDATE cart SET quantity=quantity+1 WHERE id={$row['id']}");
        } else {
            $conn->query("INSERT INTO cart (user_id, food_id, quantity) VALUES ($uid, $fid, 1)");
        }
        $count = cartCount($conn);
        echo json_encode(['success' => true, 'count' => $count]);
        break;

    case 'get':
        $items = [];
        $r = $conn->query("SELECT c.food_id, c.quantity, f.name, f.price, f.emoji FROM cart c JOIN foods f ON c.food_id=f.id WHERE c.user_id=$uid");
        while ($row = $r->fetch_assoc()) { $items[] = $row; }
        echo json_encode(['success' => true, 'items' => $items]);
        break;

    case 'update':
        $fid   = (int)($_GET['food_id'] ?? 0);
        $delta = (int)($_GET['delta']   ?? 0);
        if (!$fid) { echo json_encode(['success'=>false]); exit; }
        $r = $conn->query("SELECT id, quantity FROM cart WHERE user_id=$uid AND food_id=$fid");
        if ($r->num_rows > 0) {
            $row = $r->fetch_assoc();
            $newQty = $row['quantity'] + $delta;
            if ($newQty <= 0) {
                $conn->query("DELETE FROM cart WHERE id={$row['id']}");
            } else {
                $conn->query("UPDATE cart SET quantity=$newQty WHERE id={$row['id']}");
            }
        }
        $count = cartCount($conn);
        echo json_encode(['success' => true, 'count' => $count]);
        break;

    case 'clear':
        $conn->query("DELETE FROM cart WHERE user_id=$uid");
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
