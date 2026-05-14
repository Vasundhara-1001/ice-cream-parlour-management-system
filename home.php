<!-- index.php -->
<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "icecream_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch available ice creams
    $stmt = $conn->query("SELECT * FROM ice_creams WHERE is_available = 1 ORDER BY name");
    $ice_creams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ice Cream Bliss</title>
    <link rel="stylesheet" href="styles.css">
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
            padding: 20px;
        }

        .header {
            text-align: center;
            padding: 40px 0;
        }

        .header h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            max-width: 300px;
            margin: 0 auto;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }

        .card h2 {
            padding: 12px 15px 8px;
            color: #2c3e50;
            font-size: 1.2rem;
        }

        .card p {
            padding: 0 20px 20px;
            color: #666;
        }

        .card button {
            display: block;
            width: calc(100% - 40px);
            margin: 0 20px 20px;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s ease;
        }

        .card button:hover {
            background: #2980b9;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            margin-top: 40px;
        }

        .nav-bar {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
        }

        .nav-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;  /* Remove underline from links */
            display: inline-block;  /* Make links behave like buttons */
        }

        .login-btn {
            background-color: transparent;
            border: 2px solid #3498db;
            color: #3498db;
        }

        .login-btn:hover {
            background-color: #3498db;
            color: white;
        }

        .register-btn {
            background-color: #3498db;
            color: white;
        }

        .register-btn:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .main-content {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
                padding: 10px;
            }
            
            .card {
                margin-bottom: 15px;
            }

            .nav-bar {
                position: static;
                margin-bottom: 20px;
            }
            
            .nav-buttons {
                justify-content: center;
            }
        }

        .order-btn {
            display: block;
            width: calc(100% - 30px);
            margin: 0 15px 15px;
            padding: 10px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s ease;
            text-align: center;
            text-decoration: none;
        }

        .order-btn:hover {
            background: #2980b9;
        }

        .price {
            font-size: 1.1rem;
            font-weight: bold;
            color: #2c3e50;
            padding: 0 15px;
            margin-bottom: 8px;
        }

        .category {
            color: #7f8c8d;
            font-size: 0.8rem;
            padding: 0 15px;
            margin-bottom: 12px;
        }

        .description {
            color: #666;
            padding: 0 15px;
            margin-bottom: 12px;
            line-height: 1.4;
            font-size: 0.9rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <nav class="nav-bar">
                <div class="nav-buttons">
                    <a href="login.php" class="nav-btn login-btn">Login</a>
                    <a href="userregistration.php" class="nav-btn register-btn">Register</a>
                </div>
            </nav>
            <h1>Welcome to Ice Cream Bliss</h1>
        </header>

        <main class="main-content">
            <?php foreach ($ice_creams as $ice_cream): ?>
                <div class="card">
                    <?php if ($ice_cream['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($ice_cream['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($ice_cream['name']); ?>">
                    <?php else: ?>
                        <img src="images/default-ice-cream.jpg" alt="Default Ice Cream Image">
                    <?php endif; ?>
                    
                    <h2><?php echo htmlspecialchars($ice_cream['name']); ?></h2>
                    <p class="description"><?php echo htmlspecialchars($ice_cream['description']); ?></p>
                    <p class="price">$<?php echo number_format($ice_cream['price'], 2); ?></p>
                    <p class="category"><?php echo htmlspecialchars($ice_cream['category']); ?></p>
                    <a href="login.php" class="order-btn">Login to Order</a>
                </div>
            <?php endforeach; ?>
        </main>

        <footer class="footer">
            <p>&copy; 2025 Ice Cream Bliss. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>