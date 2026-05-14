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

    // Handle form submissions
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $image_path = '';
                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $upload_dir = 'uploads/';
                        
                        // Create uploads directory if it doesn't exist
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }

                        // Generate unique filename
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $file_name = uniqid() . '.' . $file_extension;
                        $target_file = $upload_dir . $file_name;

                        // Validate file type
                        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                        if (!in_array($file_extension, $allowed_types)) {
                            $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
                            break;
                        }

                        // Move uploaded file
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                            $image_path = $target_file;
                        } else {
                            $_SESSION['error'] = "Failed to upload image.";
                            break;
                        }
                    }

                    // Only proceed if no errors occurred
                    if (!isset($_SESSION['error'])) {
                        $is_available = isset($_POST['is_available']) ? 1 : 0;

                        $stmt = $conn->prepare("INSERT INTO ice_creams (name, description, price, image_path, category, is_available) 
                                             VALUES (:name, :description, :price, :image_path, :category, :is_available)");
                        $stmt->execute([
                            ':name' => $_POST['name'],
                            ':description' => $_POST['description'],
                            ':price' => $_POST['price'],
                            ':image_path' => $image_path,
                            ':category' => $_POST['category'],
                            ':is_available' => $is_available
                        ]);
                        $_SESSION['message'] = "Ice cream added successfully!";
                    }
                    break;

                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM ice_creams WHERE id = :id");
                    $stmt->execute([':id' => $_POST['id']]);
                    $_SESSION['message'] = "Ice cream deleted successfully!";
                    break;

                case 'update':
                    $image_path = null;
                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $upload_dir = 'uploads/';
                        
                        // Create uploads directory if it doesn't exist
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }

                        // Generate unique filename
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $file_name = uniqid() . '.' . $file_extension;
                        $target_file = $upload_dir . $file_name;

                        // Validate file type
                        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                        if (!in_array($file_extension, $allowed_types)) {
                            $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
                            break;
                        }

                        // Move uploaded file
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                            $image_path = $target_file;
                        } else {
                            $_SESSION['error'] = "Failed to upload image.";
                            break;
                        }
                    }

                    // Only proceed if no errors occurred
                    if (!isset($_SESSION['error'])) {
                        $is_available = isset($_POST['is_available']) ? 1 : 0;

                        $sql = "UPDATE ice_creams SET 
                                name = :name, 
                                description = :description, 
                                price = :price, 
                                category = :category,
                                is_available = :is_available";
                        
                        if ($image_path !== null) {
                            $sql .= ", image_path = :image_path";
                        }
                        
                        $sql .= " WHERE id = :id";

                        $stmt = $conn->prepare($sql);
                        $params = [
                            ':id' => $_POST['id'],
                            ':name' => $_POST['name'],
                            ':description' => $_POST['description'],
                            ':price' => $_POST['price'],
                            ':category' => $_POST['category'],
                            ':is_available' => $is_available
                        ];

                        if ($image_path !== null) {
                            $params[':image_path'] = $image_path;
                        }

                        $stmt->execute($params);
                        $_SESSION['message'] = "Ice cream updated successfully!";
                    }
                    break;
            }
            header("Location: admin_dashboard.php");
            exit();
        }
    }

    // Fetch all ice creams
    $stmt = $conn->query("SELECT * FROM ice_creams ORDER BY name");
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
    <title>Admin Dashboard - Ice Cream Bliss</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            padding: 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .content-area {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
        }

        .add-form {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .ice-creams-list {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .ice-cream-item {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 10px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            align-items: center;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <div class="header-actions">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="sales.php" class="btn btn-primary">View Sales</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message success">
            <?php 
            echo htmlspecialchars($_SESSION['message']);
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error">
            <?php 
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="content-area">
        <div class="add-form">
            <h2>Add New Ice Cream</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="Classic">Classic</option>
                        <option value="Special">Special</option>
                        <option value="Premium">Premium</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="is_available">Available</label>
                    <input type="checkbox" id="is_available" name="is_available" value="1" checked>
                </div>

                <button type="submit" class="btn btn-primary">Add Ice Cream</button>
            </form>
        </div>

        <div class="ice-creams-list">
            <h2>Ice Cream List</h2>
            <?php foreach ($ice_creams as $ice_cream): ?>
                <div class="ice-cream-item">
                    <div>
                        <h3><?php echo htmlspecialchars($ice_cream['name']); ?></h3>
                        <p><?php echo htmlspecialchars($ice_cream['description']); ?></p>
                        <p>Price: $<?php echo htmlspecialchars($ice_cream['price']); ?></p>
                        <p>Category: <?php echo htmlspecialchars($ice_cream['category']); ?></p>
                    </div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $ice_cream['id']; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                    <button onclick="editIceCream(<?php echo htmlspecialchars(json_encode($ice_cream)); ?>)" 
                            class="btn btn-primary">Edit</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function editIceCream(iceCream) {
            document.getElementById('name').value = iceCream.name;
            document.getElementById('description').value = iceCream.description;
            document.getElementById('price').value = iceCream.price;
            document.getElementById('category').value = iceCream.category;
            
            const form = document.querySelector('.add-form form');
            form.innerHTML += `<input type="hidden" name="id" value="${iceCream.id}">`;
            form.querySelector('[name="action"]').value = 'update';
            form.querySelector('button').textContent = 'Update Ice Cream';
        }
    </script>
</body>
</html>