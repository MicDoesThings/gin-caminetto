<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting database test...<br>";

// Check if config file exists
$config_file = '../config/db_connect.php';
if (file_exists($config_file)) {
    echo "Config file found at: " . realpath($config_file) . "<br>";
} else {
    die("Config file not found at: " . realpath('../config/') . "/" . basename($config_file) . "<br>");
}

// Include the config file
require_once $config_file;

try {
    // Test database connection
    echo "Testing database connection...<br>";
    if ($conn) {
        echo "Database connection successful!<br>";
        echo "PDO driver name: " . $conn->getAttribute(PDO::ATTR_DRIVER_NAME) . "<br>";
        echo "Server version: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br><br>";
    }

    // Check if admin_users table exists
    $tables = $conn->query("SHOW TABLES LIKE 'admin_users'")->fetchAll();
    if (count($tables) > 0) {
        echo "admin_users table exists!<br>";
    } else {
        echo "admin_users table does not exist!<br>";
        
        // Create the table
        echo "Creating admin_users table...<br>";
        $sql = "CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "Table created successfully!<br>";
    }

    // Check if admin user exists
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "Admin user exists!<br>";
        echo "Username: " . $user['username'] . "<br>";
        
        // Test password verification
        $testPassword = 'admin123';
        $isValid = password_verify($testPassword, $user['password']);
        echo "Password 'admin123' verification result: " . ($isValid ? "Valid" : "Invalid") . "<br>";
        
        if (!$isValid) {
            // Update password if verification fails
            echo "Updating admin password...<br>";
            $newPassword = password_hash($testPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
            $stmt->execute([$newPassword, 'admin']);
            echo "Password updated successfully!<br>";
        }
    } else {
        echo "Admin user does not exist!<br>";
        
        // Create admin user
        echo "Creating admin user...<br>";
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
        $stmt->execute(['admin', $password]);
        echo "Admin user created successfully!<br>";
    }

    echo "<br>Test completed successfully!<br>";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "<br>";
}
?> 