<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// File to store sales
$sales_file = 'sales.json';
$sales_data = [];
$total_sales_amount = 0;
$total_sales_count = 0;

// Default selected date is today
$selected_date = $_POST['sales_date'] ?? '';

// Load sales.json if exists
if (file_exists($sales_file)) {
    $all_sales = json_decode(file_get_contents($sales_file), true) ?: [];

    // Filter sales by selected date
    foreach ($all_sales as $sale) {
        if (substr($sale['date'], 0, 10) === $selected_date) {
            $sales_data[] = $sale;
            $total_sales_count += count($sale['items']);
            $total_sales_amount += $sale['total'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Per Day Sales - Ice Cream Bliss</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #3498db;
            margin-bottom: 20px;
            text-align: center;
        }
        a {
            color: #3498db;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        a:hover { text-decoration: underline; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
        }
        tbody tr:hover { background-color: #f9f9f9; }
        .error {
            color: #e74c3c;
            background-color: #f8d7da;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .back-to-dashboard {
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .back-to-dashboard:hover { background-color: #2980b9; }
        .date-form { margin-bottom: 20px; }
        .date-form label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .date-form input[type="date"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 200px;
        }
        .sales-summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .sales-summary p {
            margin-bottom: 8px;
            font-size: 1.1em;
            color: #555;
        }
        .order-time {
    text-align: center;
    font-weight: normal;
    color: #555;
    margin: 20px 0 10px 0;
}
    </style>
</head>
<body>
    <div class="container">
        <h1>Per Day Sales</h1>
        <a href="admin_dashboard.php" class="back-to-dashboard">Back to Dashboard</a>

        <form method="POST" class="date-form">
            <label for="sales_date">Select Date:</label>
            <input type="date" id="sales_date" name="sales_date" value="<?php echo htmlspecialchars($selected_date); ?>">
            <button type="submit" class="back-to-dashboard">View Sales</button>
        </form>

       <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>

    <?php foreach ($sales_data as $sale_index => $sale): ?>

        <!-- Show ONLY time -->
        <h3 class="order-time">
<?php 
$dateObj = new DateTime($sale['date'], new DateTimeZone('Asia/Kolkata'));
echo $dateObj->format('h:i A'); 
?>
</h3>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sale['items'] as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>$<?php echo number_format($item['price'],2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['subtotal'],2); ?></td>
                    </tr>
                <?php endforeach; ?>

                <tr class="total-row">
                    <td colspan="3"><strong>Order Total</strong></td>
                    <td><strong>$<?php echo number_format($sale['total'],2); ?></strong></td>
                </tr>
            </tbody>
        </table>

    <?php endforeach; ?>

    <div class="sales-summary">
        <p><strong>Total Orders:</strong> <?php echo count($sales_data); ?></p>
        <p><strong>Total Sales Amount:</strong> $<?php echo number_format($total_sales_amount,2); ?></p>
    </div>

<?php else: ?>
    <p>No sales data found for the selected date.</p>
<?php endif; ?>
    </div>
</body>
</html>