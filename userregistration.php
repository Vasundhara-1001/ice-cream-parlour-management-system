<?php
session_start();

// Only process if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "icecream_db";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Validate form data
        if (empty($_POST['fullname']) || empty($_POST['username']) || 
            empty($_POST['email']) || empty($_POST['password'])) {
            throw new Exception("All fields are required");
        }

        // Get form data
        $fullname = trim($_POST['fullname']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Username or email already exists");
        }

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, password) 
                               VALUES (:fullname, :username, :email, :password)");
        
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        $stmt->execute();

        header("Location: login.php?registration=success");
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
    <title>Register - Ice Cream Bliss</title>
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
    background: rgba(255, 255, 255, 0.1); /* white overlay with 30% opacity */
    z-index: -1; /* keeps overlay behind all content */
}

        .register-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h1 {
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
    </style>
</head>
<body>
    <nav class="nav-bar">
        <div class="nav-buttons">
            <a href="home.php" class="nav-btn">Home</a>
            <a href="login.php" class="nav-btn">Login</a>
        </div>
    </nav>
    <div class="register-container">
        <div class="register-header">
            <h1>Register</h1>
            <p>Create your Ice Cream Bliss account</p>
            <?php
            if (isset($_SESSION['error'])) {
                echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
                unset($_SESSION['error']);
            }
            ?>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" required>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
</body>
</html>