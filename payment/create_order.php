<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Razorpay credentials
$razorpay_key = 'rzp_live_UcnCYzIPLHdtGJ'; // Replace with your Razorpay Key ID
$razorpay_secret = 'qtz09fsxYy6asz1HEgM2G6sp'; // Replace with your Razorpay Secret

// Database configuration (optional, for logging orders)
$host = 'localhost';
$dbname = 'u765668449_puja1';
$username = 'u765668449_puja1';
$password = '??KA30Xt';

try {
    // Get the JSON data from the request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate required fields
    if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid or missing amount']);
        exit;
    }

    // Initialize Razorpay
        require 'razorpay-php/Razorpay.php'; // Install via: composer require razorpay/razorpay
    use Razorpay\Api\Api;

    $api = new Api($razorpay_key, $razorpay_secret);

    // Create order
    $orderData = [
        'amount' => $data['amount'] * 100, // Convert to paise
        'currency' => 'INR',
        'payment_capture' => 1 // Auto-capture payment
    ];

    $order = $api->order->create($orderData);
    $order_id = $order['id'];

    // Optionally log order to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "INSERT INTO orders (order_id, amount, currency, status, created_at)
            VALUES (:order_id, :amount, :currency, 'created', NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':order_id' => $order_id,
        ':amount' => $data['amount'],
        ':currency' => 'INR'
    ]);

    // Return order_id
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>