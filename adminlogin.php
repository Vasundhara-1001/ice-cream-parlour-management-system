<?php
session_start();

// Process admin login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "icecream_db";

    try {
        // Validate form data
        if (empty($_POST['username']) || empty($_POST['password'])) {
            throw new Exception("Please fill in all fields");
        }

        // Connect to database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $db_username, $db_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get and sanitize user input
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Check admin credentials
        $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($password, $admin['password'])) {
            throw new Exception("Invalid admin credentials");
        }

        // Set admin session variables
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['is_admin'] = true;

        // Redirect to admin dashboard
        header("Location: admin_dashboard.php");
        exit();

    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    } finally {
        $conn = null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Ice Cream Bliss</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-login-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .admin-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .admin-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-primary {
            background: #e74c3c;
            color: white;
            margin-bottom: 1rem;
        }

        .btn-primary:hover {
            background: #c0392b;
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
            text-decoration: none;
            background: transparent;
            border: 2px solid #e74c3c;
            color: #e74c3c;
        }

        .nav-btn:hover {
            background: #e74c3c;
            color: white;
        }

        @media (max-width: 768px) {
            .nav-bar {
                position: static;
                margin-bottom: 20px;
            }
            
            .nav-buttons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <nav class="nav-bar">
        <div class="nav-buttons">
            <a href="home.php" class="nav-btn">Home</a>
            <a href="login.php" class="nav-btn">User Login</a>
        </div>
    </nav>
    <div class="admin-login-container">
        <div class="admin-header">
            <h1>Admin Login</h1>
            <p>Access admin dashboard</p>
            <?php
            if (isset($_GET['status']) && $_GET['status'] === 'logged_out') {
                echo '<p style="color: green;">You have been successfully logged out.</p>';
            }
            if (isset($_SESSION['error'])) {
                echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
                unset($_SESSION['error']);
            }
            ?>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="username">Admin Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login as Admin</button>
        </form>
    </div>
</body>
</html>