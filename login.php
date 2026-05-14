<?php
session_start();

// Process login form submission
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

        // Check user credentials
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Invalid username or password");
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;

        // Redirect to dashboard
        header("Location: listicecreams.php");
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
    <title>Login - Ice Cream Bliss</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

       body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    
    /* ✅ Background image */
    background: url('uploads/Screenshot (15).png') no-repeat center center fixed;  
    background-size: cover;
    body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0); /* white overlay with 30% opacity */
    z-index: -1; /* keeps overlay behind all content */
}


        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
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
            background: #3498db;
            color: white;
            margin-bottom: 1rem;
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid #3498db;
            color: #3498db;
        }

        .btn:hover {
            background: #2980b9;
            color: white;
        }

        .nav-bar {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 100;
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
            border: 2px solid #3498db;
            color: #3498db;
        }

        .nav-btn:hover {
            background: #3498db;
            color: white;
        }

        .navigation {
            display: none;
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
            <a href="userregistration.php" class="nav-btn">Register</a>
            <a href="adminlogin.php" class="nav-btn">Admin</a>
        </div>
    </nav>
    <div class="login-container">
        <div class="login-header">
            <h1>Login</h1>
            <p>Welcome back to Ice Cream Bliss</p>
            <?php
            if (isset($_SESSION['error'])) {
                echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
                unset($_SESSION['error']);
            }
            ?>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>