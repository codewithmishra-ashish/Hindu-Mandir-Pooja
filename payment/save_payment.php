<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'u765668449_puja1';
$username = 'u765668449_puja1';
$password = '??KA30Xt';

// Razorpay credentials
$razorpay_key = 'rzp_live_UcnCYzIPLHdtGJ'; // Replace with your Razorpay Key ID
$razorpay_secret = 'qtz09fsxYy6asz1HEgM2G6sp'; // Replace with your Razorpay Secret

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the JSON data from the request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate required fields for payments
    if (!isset($data['eventName'], $data['name'], $data['email'], $data['phone'], $data['gotra'], $data['address'], $data['date'], $data['amount'], $data['paymentStatus'], $data['paymentId'], $data['cartItems']) || !is_array($data['cartItems'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields or invalid cart items']);
        exit;
    }

    // Validate phone format (10 digits)
    if (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
        exit;
    }

    // Verify Razorpay signature if payment is successful
    if ($data['paymentStatus'] === 'complete') {
        if (!isset($data['razorpay_order_id'], $data['razorpay_signature'])) {
            echo json_encode(['success' => false, 'message' => 'Missing Razorpay order ID or signature']);
            exit;
        }

        require 'razorpay-php/Razorpay.php'; // Include Razorpay PHP SDK
        use Razorpay\Api\Api;

        $api = new Api($razorpay_key, $razorpay_secret);
        $attributes = [
            'razorpay_order_id' => $data['razorpay_order_id'],
            'razorpay_payment_id' => $data['paymentId'],
            'razorpay_signature' => $data['razorpay_signature']
        ];

        try {
            $api->utility->verifyPaymentSignature($attributes);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Signature verification failed: ' . $e->getMessage()]);
            exit;
        }
    }

    // Begin transaction to ensure data consistency
    $pdo->beginTransaction();

    // Prepare the SQL statement for payments
    $sql = "INSERT INTO payments (event_name, name, email, phone, gotra, address, amount, payment_id, payment_status, payment_date)
            VALUES (:eventName, :name, :email, :phone, :gotra, :address, :amount, :paymentId, :paymentStatus, :date)";
    $stmt = $pdo->prepare($sql);

    // Bind parameters for payments
    $stmt->bindParam(':eventName', $data['eventName']);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':gotra', $data['gotra']);
    $stmt->bindParam(':address', $data['address']);
    $stmt->bindParam(':amount', $data['amount'], PDO::PARAM_STR);
    $stmt->bindParam(':paymentId', $data['paymentId']);
    $stmt->bindParam(':paymentStatus', $data['paymentStatus']);
    $stmt->bindParam(':date', $data['date']);

    // Execute the statement for payments
    $stmt->execute();

    // Prepare the SQL statement for cart_items
    $sqlCart = "INSERT INTO cart_items (payment_id, item_name, quantity, price)
                VALUES (:paymentId, :itemName, :quantity, :price)";
    $stmtCart = $pdo->prepare($sqlCart);

    // Insert each cart item
    foreach ($data['cartItems'] as $item) {
        if (!isset($item['itemName'], $item['quantity'], $item['price']) || $item['quantity'] <= 0 || $item['price'] < 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Invalid cart item data']);
            exit;
        }
        $stmtCart->bindParam(':paymentId', $data['paymentId']);
        $stmtCart->bindParam(':itemName', $item['itemName']);
        $stmtCart->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
        $stmtCart->bindParam(':price', $item['price'], PDO::PARAM_STR);
        $stmtCart->execute();
    }

    // Commit the transaction
    $pdo->commit();

    // Return success response
    echo json_encode(['success' => true, 'message' => 'Data and cart items saved successfully']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>