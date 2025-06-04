<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Error logging setup
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// Database connection
$host = 'localhost';
$dbname = 'u765668449_puja1';
$username = 'u765668449_puja1';
$password = '??KA30Xt';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Database connection successful.");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}

// Admin credentials (hardcoded for simplicity; use hashed passwords in production)
$admin_username = 'admin';
$admin_password = 'admin123'; // In production, hash this password and store securely

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    if ($input_username === $admin_username && $input_password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        session_regenerate_id(true);
        error_log("Login successful for user: $input_username");
    } else {
        $error = "Invalid username or password.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_panel.php");
    exit;
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Display login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - Hindu Mandir Puja</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body {
                font-family: 'Inter', sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #fef3e7 0%, #f7d9c4 100%);
            }
            .login-container {
                background: white;
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 15px 30px rgba(165, 42, 42, 0.1);
                width: 100%;
                max-width: 450px;
                text-align: center;
                transition: transform 0.3s ease;
            }
            .login-container:hover {
                transform: translateY(-5px);
            }
            .login-container h1 {
                font-size: 1.75rem;
                font-weight: 700;
                background: linear-gradient(to right, #a52a2a, #cc3333);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .login-container input {
                transition: border-color 0.3s ease;
            }
            .login-container button {
                background: linear-gradient(to right, #a52a2a, #cc3333);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .login-container button:hover {
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(165, 42, 42, 0.3);
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1 class="mb-8">Admin Login</h1>
            <?php if (isset($error)) { ?>
                <p class="text-red-500 mb-6 text-sm font-medium"><?php echo $error; ?></p>
            <?php } ?>
            <form method="POST" action="">
                <div class="mb-6">
                    <label for="username" class="block text-[#a52a2a] font-semibold mb-2">Username</label>
                    <input type="text" id="username" name="username" class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:border-[#a52a2a] text-gray-700" required>
                </div>
                <div class="mb-8">
                    <label for="password" class="block text-[#a52a2a] font-semibold mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:border-[#a52a2a] text-gray-700" required>
                </div>
                <button type="submit" name="login" class="text-white px-6 py-3 rounded-lg w-full font-semibold text-lg">
                    Login
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Test a simple query to verify table access
try {
    $test_query = $pdo->query("SELECT 1 FROM payments LIMIT 1");
    error_log("Test query successful.");
} catch (PDOException $e) {
    error_log("Test query failed: " . $e->getMessage());
    die("Error accessing payments table: " . $e->getMessage());
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = '';
$search_params = [];
if ($search) {
    $search_query = " WHERE name LIKE :search_name OR payment_id LIKE :search_payment_id";
    $search_term = "%$search%";
    $search_params = [
        ':search_name' => $search_term,
        ':search_payment_id' => $search_term
    ];
    error_log("Search query: $search_query, params: " . print_r($search_params, true));
}

// Fetch total records for pagination
try {
    $total_records_query = "SELECT COUNT(*) FROM payments" . $search_query;
    $stmt = $pdo->prepare($total_records_query);
    $stmt->execute($search_params);
    $total_records = $stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);
    error_log("Total records: $total_records, Total pages: $total_pages");
} catch (PDOException $e) {
    error_log("Total records query failed: " . $e->getMessage());
    die("Error fetching total records: " . $e->getMessage());
}

// Fetch dashboard data
try {
    $total_users = $pdo->query("SELECT COUNT(DISTINCT name) FROM payments")->fetchColumn();
    error_log("Total users: $total_users");
} catch (PDOException $e) {
    error_log("Total users query failed: " . $e->getMessage());
    $total_users = 0;
}

try {
    $total_revenue = $pdo->query("SELECT SUM(amount) FROM payments")->fetchColumn() ?? 0;
    error_log("Total revenue: $total_revenue");
} catch (PDOException $e) {
    error_log("Total revenue query failed: " . $e->getMessage());
    $total_revenue = 0;
}

try {
    $total_items_sold = $pdo->query("SELECT SUM(quantity) FROM cart_items")->fetchColumn() ?? 0;
    error_log("Total items sold: $total_items_sold");
} catch (PDOException $e) {
    error_log("Total items sold query failed: " . $e->getMessage());
    $total_items_sold = 0;
}

try {
    $recent_payments = $pdo->query("SELECT name, amount, payment_date FROM payments ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    error_log("Recent payments fetched: " . count($recent_payments));
} catch (PDOException $e) {
    error_log("Recent payments query failed: " . $e->getMessage());
    $recent_payments = [];
}

// Fetch users with pagination and search
try {
    $query = "SELECT * FROM payments" . $search_query . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    
    // Bind search parameters if any
    foreach ($search_params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    
    // Bind LIMIT and OFFSET as integers
    $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    error_log("Users query: $query, limit: $records_per_page, offset: $offset");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Users fetched: " . count($users));
} catch (PDOException $e) {
    error_log("Users query failed: " . $e->getMessage());
    die("Error fetching users: " . $e->getMessage());
}

// Fetch cart items for each payment
$cart_items = [];
foreach ($users as $user) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE payment_id = ?");
        $stmt->execute([$user['payment_id']]);
        $cart_items[$user['payment_id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Cart items fetched for payment_id {$user['payment_id']}: " . count($cart_items[$user['payment_id']]));
    } catch (PDOException $e) {
        error_log("Cart items query failed for payment_id {$user['payment_id']}: " . $e->getMessage());
        $cart_items[$user['payment_id']] = [];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Hindu Mandir Puja</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fff5e6 0%, #ffe4cc 100%);
            min-height: 100vh;
            margin: 0;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            border: 1px solid #ffe4cc;
            box-shadow: 0 10px 20px rgba(255, 147, 71, 0.05);
            margin-bottom: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(255, 147, 71, 0.1);
        }
        .dashboard-card {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .dashboard-card .icon {
            background: linear-gradient(to right, #ff9347, #ff6f61);
            padding: 12px;
            border-radius: 10px;
            color: white;
        }
        .table th {
            background: linear-gradient(to right, #fff5e6, #ffe4cc);
            color: #ff6f61;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
            padding: 15px;
            border-bottom: 2px solid #ffe4cc;
        }
        .table td {
            padding: 15px;
            border-bottom: 1px solid #f5f5f5;
            color: #4a4a4a;
            font-size: 0.95rem;
        }
        .table tr {
            transition: background-color 0.3s ease;
        }
        .table tr:hover {
            background-color: #fff5e6;
        }
        .pagination a {
            padding: 8px 14px;
            margin: 0 4px;
            border: 1px solid #ffe4cc;
            border-radius: 8px;
            color: #ff6f61;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .pagination a:hover {
            background-color: #ff6f61;
            color: white;
            border-color: #ff6f61;
        }
        .pagination a.active {
            background-color: #ff6f61;
            color: white;
            border-color: #ff6f61;
        }
        .search-form input {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .search-form input:focus {
            border-color: #ff6f61;
            box-shadow: 0 0 0 3px rgba(255, 111, 97, 0.1);
        }
        .search-form button {
            background: linear-gradient(to right, #ff9347, #ff6f61);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .search-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 111, 97, 0.3);
        }
        .logout-button {
            background: linear-gradient(to right, #e53e3e, #f56565);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .logout-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 62, 62, 0.3);
        }
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            .table th, .table td {
                font-size: 0.85rem;
                padding: 10px;
            }
            .table-responsive {
                overflow-x: auto;
            }
            .dashboard-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-[#ff9347] to-[#ff6f61] bg-clip-text text-transparent">Admin Panel - Hindu Mandir Puja</h1>
            <a href="?logout=true" class="logout-button text-white px-5 py-2 rounded-lg font-semibold text-sm">Logout</a>
        </div>

        <!-- Dashboard Summary -->
        <div class="dashboard-grid grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="card dashboard-card">
                <div class="icon">
                    <i data-feather="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-[#ff6f61] mb-2">Total Users</h2>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $total_users; ?></p>
                </div>
            </div>
            <div class="card dashboard-card">
                <div class="icon">
                    <i data-feather="dollar-sign" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-[#ff6f61] mb-2">Total Revenue</h2>
                    <p class="text-3xl font-bold text-gray-800">₹<?php echo number_format($total_revenue, 2); ?></p>
                </div>
            </div>
            <div class="card dashboard-card">
                <div class="icon">
                    <i data-feather="shopping-cart" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-[#ff6f61] mb-2">Total Items Sold</h2>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $total_items_sold; ?></p>
                </div>
            </div>
            <div class="card">
                <h2 class="text-lg font-semibold text-[#ff6f61] mb-3">Recent Payments</h2>
                <ul class="list-disc list-inside text-gray-600 text-sm space-y-1">
                    <?php foreach ($recent_payments as $payment) { ?>
                        <li><?php echo htmlspecialchars($payment['name']); ?> - ₹<?php echo number_format($payment['amount'], 2); ?> (<?php echo date('d M Y', strtotime($payment['payment_date'])); ?>)</li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card mb-10">
            <form method="GET" action="" class="search-form flex items-center gap-4">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or payment ID..." class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none text-gray-700">
                <button type="submit" class="text-white px-5 py-3 rounded-lg font-semibold">Search</button>
            </form>
        </div>

        <!-- Users and Payments Table -->
        <div class="card table-responsive">
            <h2 class="text-2xl font-semibold text-[#ff6f61] mb-6">Users and Payments</h2>
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Gotra</th>
                        <th>Address</th>
                        <th>Event Name</th>
                        <th>Amount</th>
                        <th>Payment ID</th>
                        <th>Payment Date</th>
                        <th>Created At</th>
                        <th>Cart Items</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)) { ?>
                        <tr>
                            <td colspan="10" class="text-center text-gray-500 italic py-4">No records found.</td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($users as $user) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td><?php echo htmlspecialchars($user['gotra']); ?></td>
                                <td><?php echo htmlspecialchars($user['address']); ?></td>
                                <td><?php echo htmlspecialchars($user['event_name']); ?></td>
                                <td>₹<?php echo number_format($user['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($user['payment_id']); ?></td>
                                <td><?php echo date('d M Y, H:i', strtotime($user['payment_date'])); ?></td>
                                <td><?php echo date('d M Y, H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if (!empty($cart_items[$user['payment_id']])) { ?>
                                        <span class="text-gray-600 text-sm">
                                            <?php
                                            $items = [];
                                            foreach ($cart_items[$user['payment_id']] as $item) {
                                                $items[] = htmlspecialchars($item['item_name']) . " (Qty: {$item['quantity']}, Price: ₹" . number_format($item['price'], 2) . ")";
                                            }
                                            echo implode(", ", $items);
                                            ?>
                                        </span>
                                    <?php } else { ?>
                                        <span class="text-gray-500 italic">No items</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination mt-6 flex justify-center gap-3">
                <?php if ($page > 1) { ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                <?php } ?>
                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php } ?>
                <?php if ($page < $total_pages) { ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                <?php } ?>
            </div>
        </div>
    </div>
    <script>
        feather.replace();
    </script>
</body>
</html>