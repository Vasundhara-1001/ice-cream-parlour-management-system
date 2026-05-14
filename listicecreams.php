<?php
session_start();

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

    // Initialize shopping bucket if not exists
    if (!isset($_SESSION['bucket'])) {
        $_SESSION['bucket'] = array();
    }

    // Handle add to bucket
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_bucket'])) {
        $ice_cream_id = $_POST['ice_cream_id'];
        $quantity = $_POST['quantity'];
        
        // Add or update bucket
        if (isset($_SESSION['bucket'][$ice_cream_id])) {
            $_SESSION['bucket'][$ice_cream_id] += $quantity;
        } else {
            $_SESSION['bucket'][$ice_cream_id] = $quantity;
        }
        
        $_SESSION['message'] = "Added to bucket successfully!";
    }

    // Fetch available ice creams
    $stmt = $conn->query("SELECT * FROM ice_creams WHERE is_available = 1 ORDER BY name");
    $ice_creams = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ice Cream List - Ice Cream Bliss</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

      body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    /* ✅ Background image */
    background: url('uploads/Screenshot (16).png') no-repeat center center fixed;
    background-size: cover;  /* makes the image cover the entire page */
    color: #333;
}
body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.3); /* white overlay with 30% opacity */
    z-index: -1; /* keeps overlay behind all content */
}

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .ice-cream-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }

        .card-content {
            padding: 15px;
        }

        .card h2 {
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 8px;
        }

        .description {
            color: #666;
            margin-bottom: 12px;
            line-height: 1.4;
            font-size: 0.9rem;
        }

        .price {
            font-size: 1.1rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 12px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            gap: 10px;
        }

        .quantity-selector input {
            width: 60px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        .add-to-bucket {
            width: 100%;
            padding: 10px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .add-to-bucket:hover {
            background: #2980b9;
        }

        .bucket-icon {
            position: relative;
            padding: 10px;
        }

        .bucket-count {
            position: absolute;
            top: 0;
            right: 0;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .logout-btn {
            padding: 8px 16px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Ice Cream Selection</h1>
            <div class="user-actions">
                <div class="bucket-icon">
                    <a href="bucket.php" style="text-decoration: none; color: #333;">
                        🧊 Bucket
                        <?php
                        if (isset($_SESSION['bucket']) && count($_SESSION['bucket']) > 0) {
                            echo '<span class="bucket-count">' . array_sum($_SESSION['bucket']) . '</span>';
                        }
                        ?>
                    </a>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success">
                <?php 
                echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="ice-cream-grid">
            <?php foreach ($ice_creams as $ice_cream): ?>
                <div class="card">
                    <?php if ($ice_cream['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($ice_cream['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($ice_cream['name']); ?>">
                    <?php else: ?>
                        <img src="images/default-ice-cream.jpg" alt="Default Ice Cream Image">
                    <?php endif; ?>
                    
                    <div class="card-content">
                        <h2><?php echo htmlspecialchars($ice_cream['name']); ?></h2>
                        <p class="description"><?php echo htmlspecialchars($ice_cream['description']); ?></p>
                        <p class="price">$<?php echo number_format($ice_cream['price'], 2); ?></p>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                            <input type="hidden" name="ice_cream_id" value="<?php echo $ice_cream['id']; ?>">
                            <div class="quantity-selector">
                                <label for="quantity-<?php echo $ice_cream['id']; ?>">Quantity:</label>
                                <input type="number" id="quantity-<?php echo $ice_cream['id']; ?>" 
                                       name="quantity" value="1" min="1" max="10">
                            </div>
                            <button type="submit" name="add_to_bucket" class="add-to-bucket">
                                Add to Bucket
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>