<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "icecream_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $total = 0;
    $bucket_items = [];

    if (isset($_SESSION['bucket']) && !empty($_SESSION['bucket'])) {
        $ice_cream_ids = array_keys($_SESSION['bucket']);
        $placeholders = str_repeat('?,', count($ice_cream_ids) - 1) . '?';
        
        $stmt = $conn->prepare("SELECT * FROM ice_creams WHERE id IN ($placeholders)");
        $stmt->execute($ice_cream_ids);
        $ice_creams = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ice_creams as $ice_cream) {
            $quantity = $_SESSION['bucket'][$ice_cream['id']];
            $subtotal = $quantity * $ice_cream['price'];
            $total += $subtotal;

            $bucket_items[] = [
                'id' => $ice_cream['id'],
                'name' => $ice_cream['name'],
                'price' => $ice_cream['price'],
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
    }

    $success_message = '';
    // Handle "Create Bill" / print action
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($bucket_items)) {
        $sale_record = [
            'date' => date('Y-m-d H:i:s'),
            'items' => $bucket_items,
            'total' => $total
        ];

        $file = 'sales.json';
        $sales_data = [];

        if (file_exists($file)) {
            $sales_data = json_decode(file_get_contents($file), true) ?: [];
        }

        $sales_data[] = $sale_record;
        file_put_contents($file, json_encode($sales_data, JSON_PRETTY_PRINT));

        // Clear bucket after saving
        unset($_SESSION['bucket']);

        $success_message = "Bill created successfully and bucket cleared!";
        $bucket_items = []; // Now the bucket is empty
    }

} catch(PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Bucket - Ice Cream Bliss</title>
<style>
* {margin:0;padding:0;box-sizing:border-box;}
body {font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
background: url('uploads/home1.png') no-repeat center center fixed;
background-size: cover;
min-height:100vh;padding:20px;}
.container {max-width:800px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
.header {display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;padding-bottom:20px;border-bottom:1px solid #eee;}
.bucket-table {width:100%;border-collapse:collapse;margin-bottom:30px;}
.bucket-table th, .bucket-table td {padding:12px;text-align:left;border-bottom:1px solid #eee;}
.bucket-table th {background-color:#f8f9fa;font-weight:600;}
.total-row {font-weight:bold;font-size:1.1em;}
.btn {padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-size:1rem;transition:background 0.3s ease;text-decoration:none;display:inline-block;}
.btn-primary {background:#3498db;color:white;}
.btn-primary:hover {background:#2980b9;}
.actions {display:flex;justify-content:space-between;margin-top:20px;}
.header-actions {display:flex;gap:10px;align-items:center;}
.btn-danger {background:#e74c3c;color:white;}
.btn-danger:hover {background:#c0392b;}
.success-message {color:green;margin-bottom:20px;font-weight:bold;}
.no-print {display:inline-flex;}
</style>
<script>
function printBill() {
    window.print();
}
</script>
</head>
<body>
<div class="container">
<div class="header">
<h1>Your Ice Cream Bucket</h1>
<div class="header-actions no-print">
    <a href="listicecreams.php" class="btn btn-primary">Continue Shopping</a>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>
</div>

<?php if (!empty($success_message)): ?>
<p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
<?php endif; ?>

<?php if (empty($bucket_items)): ?>
<p>Your bucket is empty.</p>
<?php else: ?>
<form method="POST">
<table class="bucket-table">
    <thead>
        <tr>
            <th>Item</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($bucket_items as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['name']); ?></td>
            <td>$<?php echo number_format($item['price'],2); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>$<?php echo number_format($item['subtotal'],2); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="total-row">
            <td colspan="3">Total</td>
            <td>$<?php echo number_format($total,2); ?></td>
        </tr>
    </tbody>
</table>
<div class="actions no-print">
<button type="submit" class="btn btn-primary" onclick="printBill()">Create Bill</button>
</div>
</form>
<?php endif; ?>
</div>
</body>
</html>