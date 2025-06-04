<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "your_username"; // Replace with your MySQL username
$password = "your_password"; // Replace with your MySQL password
$dbname = "payment_db";

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

// Sanitize inputs
$eventName = $conn->real_escape_string($eventName);
$name = $conn->real_escape_string($name);
$phone = $conn->real_escape_string($phone);
$email = $conn->real_escape_string($email);
$gotra = $conn->real_escape_string($gotra);
$address = $conn->real_escape_string($address);
$payment_id = $conn->real_escape_string($payment_id);
$payment_status = $conn->real_escape_string($payment_status);

// Insert into database only if payment is successful
if ($payment_status === 'complete') {
    $sql = "INSERT INTO payments (event_name, name, phone, email, gotra, address, amount, payment_id, payment_status, payment_date)
            VALUES ('$eventName', '$name', '$phone', '$email', '$gotra', '$address', '$amount', '$payment_id', '$payment_status', '$payment_date')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Payment not completed']);
}

$conn->close();
?>