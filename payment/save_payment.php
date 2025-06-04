<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "u765668449_puja1";
$password = "??KA30Xt";
$dbname = "u765668449_puja1"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Retrieve form data
$eventName = $_POST['eventName'] ?? '';
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$gotra = $_POST['gotra'] ?? '';
$address = $_POST['address'] ?? '';
$amount = $_POST['amount'] ?? 0;
$payment_id = $_POST['payment_id'] ?? 'N/A';
$payment_status = $_POST['payment_status'] ?? 'failed';
$payment_date = date('Y-m-d H:i:s');
$cart_items_json = $_POST['cart_items'] ?? '[]';

// Sanitize inputs
$eventName = $conn->real_escape_string($eventName);
$name = $conn->real_escape_string($name);
$phone = $conn->real_escape_string($phone);
$email = $conn->real_escape_string($email);
$gotra = $conn->real_escape_string($gotra);
$address = $conn->real_escape_string($address);
$payment_id = $conn->real_escape_string($payment_id);
$payment_status = $conn->real_escape_string($payment_status);

// Decode cart items
$cart_items = json_decode($cart_items_json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart items format']);
    $conn->close();
    exit;
}

// Insert into database only if payment is successful
if ($payment_status === 'complete') {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert payment record
        $sql = "INSERT INTO payments (event_name, name, phone, email, gotra, address, amount, payment_id, payment_status, payment_date)
                VALUES ('$eventName', '$name', '$phone', '$email', '$gotra', '$address', '$amount', '$payment_id', '$payment_status', '$payment_date')";

        if (!$conn->query($sql)) {
            throw new Exception('Database error: ' . $conn->error);
        }

        // Insert cart items if any
        if (!empty($cart_items)) {
            $stmt = $conn->prepare("INSERT INTO cart_items (payment_id, item_name, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $item_name = $conn->real_escape_string($item['name']);
                $quantity = (int)$item['quantity'];
                $price = (float)$item['price'];
                $stmt->bind_param("ssid", $payment_id, $item_name, $quantity, $price);
                if (!$stmt->execute()) {
                    throw new Exception('Cart item insertion failed: ' . $stmt->error);
                }
            }
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Payment not completed']);
}

$conn->close();
?>