<?php
require_once 'razorpay-php/src/Razorpay.php';
require_once 'razorpay-php/src/Api.php';
require_once 'razorpay-php/src/Errors/SignatureVerificationError.php';
require_once 'razorpay-php/src/Utility.php';

use Razorpay\Api\Api;

header('Content-Type: application/json');

// Razorpay keys
$api_key = 'rzp_live_UcnCYzIPLHdtGJ';
$api_secret = 'YOUR_SECRET_KEY'; // 🔁 Replace securely

$amount = $_POST['amount'] ?? 0;
$amount = (float)$amount;

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    exit;
}

try {
    $api = new Api($api_key, $api_secret);
    $order = $api->order->create([
        'receipt' => 'receipt_' . time(),
        'amount' => $amount * 100,
        'currency' => 'INR',
        'payment_capture' => 1
    ]);

    echo json_encode(['success' => true, 'order_id' => $order->id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
