<?php
require_once 'razorpay-php/src/Razorpay.php';
require_once 'razorpay-php/src/Api.php';
require_once 'razorpay-php/src/Errors/SignatureVerificationError.php';
require_once 'razorpay-php/src/Utility.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

header('Content-Type: application/json');

// Razorpay Keys
$api_key = 'rzp_live_UcnCYzIPLHdtGJ';
$api_secret = 'YOUR_SECRET_KEY'; // 🔁 Secure this in .env

// DB credentials
$conn = new mysqli("localhost", "u765668449_puja1", "??KA30Xt", "u765668449_puja1");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'DB connect failed']));
}

// Required POST fields
$fields = ['eventName', 'name', 'phone', 'email', 'gotra', 'address', 'amount', 'payment_id', 'order_id', 'signature', 'payment_status', 'cart_items'];
foreach ($fields as $f) {
    if (!isset($_POST[$f])) {
        echo json_encode(['success' => false, 'message' => "Missing $f"]);
        exit;
    }
}

// Extract and sanitize
$eventName = $conn->real_escape_string($_POST['eventName']);
$name = $conn->real_escape_string($_POST['name']);
$phone = $conn->real_escape_string($_POST['phone']);
$email = $conn->real_escape_string($_POST['email']);
$gotra = $conn->real_escape_string($_POST['gotra']);
$address = $conn->real_escape_string($_POST['address']);
$amount = (float)$_POST['amount'];
$payment_id = $_POST['payment_id'];
$order_id = $_POST['order_id'];
$signature = $_POST['signature'];
$payment_status = $_POST['payment_status'];
$payment_date = date('Y-m-d H:i:s');
$cart_items = json_decode($_POST['cart_items'], true);

// Signature verification
$api = new Api($api_key, $api_secret);
$attributes = [
    'razorpay_order_id' => $order_id,
    'razorpay_payment_id' => $payment_id,
    'razorpay_signature' => $signature
];

try {
    $api->utility->verifyPaymentSignature($attributes);
} catch (SignatureVerificationError $e) {
    echo json_encode(['success' => false, 'message' => 'Invalid signature']);
    exit;
}

// Start DB Transaction
$conn->begin_transaction();
try {
    // Insert into payments
    $stmt = $conn->prepare("INSERT INTO payments (event_name, name, phone, email, gotra, address, amount, payment_id, payment_status, payment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssdsss", $eventName, $name, $phone, $email, $gotra, $address, $amount, $payment_id, $payment_status, $payment_date);
    if (!$stmt->execute()) throw new Exception("Payment insert failed: " . $stmt->error);
    $stmt->close();

    // Insert cart items
    if (is_array($cart_items)) {
        $stmt = $conn->prepare("INSERT INTO cart_items (payment_id, item_name, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $item_name = $conn->real_escape_string($item['name']);
            $qty = (int)$item['quantity'];
            $price = (float)$item['price'];
            $stmt->bind_param("ssid", $payment_id, $item_name, $qty, $price);
            if (!$stmt->execute()) throw new Exception("Cart insert failed: " . $stmt->error);
        }
        $stmt->close();
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
