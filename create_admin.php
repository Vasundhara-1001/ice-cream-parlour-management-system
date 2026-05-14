<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "icecream_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create admin_users table
    $sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);

    // Insert admin user with properly hashed password
    $admin_username = 'admin';
    $admin_password = 'admin123';
    $admin_email = 'admin@icecreambliss.com';
    
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admin_users (username, password, email) 
                           VALUES (:username, :password, :email)");
    
    $stmt->execute([
        ':username' => $admin_username,
        ':password' => $hashed_password,
        ':email' => $admin_email
    ]);

    echo "Admin user created successfully!";
    echo "\nUsername: admin";
    echo "\nPassword: Admin@123";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>