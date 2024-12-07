<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db_connect.php';

try {
    // Create the gin if it doesn't exist
    $stmt = $conn->prepare("SELECT COUNT(*) FROM gins WHERE name = ?");
    $stmt->execute(['Hendricks Orbium']);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Insert the Hendricks Orbium gin
        $stmt = $conn->prepare("INSERT INTO gins (name, distillery, alcohol_volume, country, botanics, recommended_tonic, garnish, image_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            'Hendricks Orbium',
            'Hendricks',
            43.4,
            'Scotland',
            'Juniper, Coriander, Angelica Root, Orris Root, Caraway Seeds, Chamomile, Yarrow, Blue Lotus Blossom',
            'Fever Tree Mediterranean Tonic Water',
            'Cucumber slice',
            'images/gin.png'
        ]);

        echo "Hendricks Orbium gin added successfully!<br>";
    } else {
        echo "Hendricks Orbium gin already exists.<br>";
    }

    // Check if admin user exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
    $stmt->execute(['admin']);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Create default admin user
        $username = 'admin';
        $password = 'admin123';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashed_password]);

        echo "Default admin user created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Admin user already exists.<br>";
    }

    // Debug: Show current gin data
    $stmt = $conn->query("SELECT * FROM gins");
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "SQL State: " . $e->getCode() . "<br>";
}
?> 