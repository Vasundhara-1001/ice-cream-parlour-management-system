<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: adminlogin.php");
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

    // Fetch sales data
    $stmt = $conn->query("SELECT id, name, description, price FROM ice_creams");
    $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $sales_data = []; // Prevent undefined variable
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales - Ice Cream Bliss</title>
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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

        a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
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

        tbody tr:hover {
            background-color: #f9f9f9;
        }

        .graph {
            margin-top: 30px;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .graph h2 {
            font-size: 1.5em;
            color: #555;
            margin-bottom: 15px;
        }

        .bar-chart {
            display: flex;
            align-items: flex-end;
            height: 200px;
            border-bottom: 2px solid #777;
            padding-bottom: 10px;
        }

        .bar {
            width: 40px;
            margin: 0 5px;
            background-color: #3498db;
            transition: height 0.5s ease;
            position: relative;
        }

        .bar::before {
            content: attr(data-value);
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.8em;
            color: #555;
        }

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

        .back-to-dashboard:hover {
            background-color: #2980b9;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #3498db;
            color: #fff;
            border-radius: 5px;
        }

        .dashboard-header h1 {
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: #fff;
            transition: background-color 0.3s ease;
        }

        .btn-primary {
            background-color: #2980b9;
        }

        .btn-primary:hover {
            background-color: #1f5f8b;
        }

        .btn-danger {
            background-color: #e74c3c;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <div class="header-actions">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                <a href="perdaysales.php" class="btn btn-primary">Per Day Sales</a>
                <a href="sales.php" class="btn btn-primary">View Sales</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <h1>Sales Record</h1>
        <a href="admin_dashboard.php" class="back-to-dashboard">Back to Dashboard</a>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="error">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sales_data)): ?>
                    <?php foreach ($sales_data as $sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sale['id']); ?></td>
                            <td><?php echo htmlspecialchars($sale['name']); ?></td>
                            <td><?php echo htmlspecialchars($sale['description']); ?></td>
                            <td><?php echo htmlspecialchars($sale['price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No sales data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
