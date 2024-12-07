<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$gin = null;
$isEdit = false;
$error = null;

// If ID is provided, fetch gin data for editing
if (isset($_GET['id'])) {
    $isEdit = true;
    $stmt = $conn->prepare("SELECT * FROM gins WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $gin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$gin) {
        header('Location: index.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate form data
        $required_fields = ['name', 'distillery', 'alcohol_volume', 'country', 'botanics', 'recommended_tonic', 'garnish'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }

        // Start transaction
        $conn->beginTransaction();

        if ($isEdit) {
            // Update existing gin
            $sql = "UPDATE gins SET 
                    name = :name,
                    distillery = :distillery,
                    alcohol_volume = :alcohol_volume,
                    country = :country,
                    botanics = :botanics,
                    recommended_tonic = :recommended_tonic,
                    garnish = :garnish
                   WHERE id = :id";
            
            $stmt = $conn->prepare($sql);
            $params = [
                ':name' => $_POST['name'],
                ':distillery' => $_POST['distillery'],
                ':alcohol_volume' => $_POST['alcohol_volume'],
                ':country' => $_POST['country'],
                ':botanics' => $_POST['botanics'],
                ':recommended_tonic' => $_POST['recommended_tonic'],
                ':garnish' => $_POST['garnish'],
                ':id' => $_GET['id']
            ];
            
            if (!$stmt->execute($params)) {
                throw new Exception('Failed to update gin');
            }
            
            $gin_id = $_GET['id'];
        } else {
            // Insert new gin
            $sql = "INSERT INTO gins (
                        name, distillery, alcohol_volume, country,
                        botanics, recommended_tonic, garnish
                    ) VALUES (
                        :name, :distillery, :alcohol_volume, :country,
                        :botanics, :recommended_tonic, :garnish
                    )";
            
            $stmt = $conn->prepare($sql);
            $params = [
                ':name' => $_POST['name'],
                ':distillery' => $_POST['distillery'],
                ':alcohol_volume' => $_POST['alcohol_volume'],
                ':country' => $_POST['country'],
                ':botanics' => $_POST['botanics'],
                ':recommended_tonic' => $_POST['recommended_tonic'],
                ':garnish' => $_POST['garnish']
            ];
            
            if (!$stmt->execute($params)) {
                throw new Exception('Failed to add new gin');
            }
            
            $gin_id = $conn->lastInsertId();
            if (!$gin_id) {
                throw new Exception('Failed to get last insert ID');
            }
        }
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($extension, $allowed_extensions)) {
                throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowed_extensions));
            }
            
            $new_filename = "gin_" . $gin_id . "." . $extension;
            $upload_dir = "../images/gins";
            $upload_path = $upload_dir . "/" . $new_filename;
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    throw new Exception('Failed to create upload directory');
                }
            }
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                throw new Exception('Failed to upload image');
            }
            
            // Update image path in database
            $stmt = $conn->prepare("UPDATE gins SET image_path = :image_path WHERE id = :id");
            if (!$stmt->execute([':image_path' => "images/gins/" . $new_filename, ':id' => $gin_id])) {
                throw new Exception('Failed to update image path');
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Set success message in session
        $_SESSION['success_message'] = 'Gin successfully ' . ($isEdit ? 'updated' : 'added') . '!';
        
        // Redirect to admin page
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add New'; ?> Gin - I gin del Caminetto</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
            padding-bottom: 2rem;
        }

        .admin-header {
            background: #333;
            color: white;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            position: relative; /* Changed from fixed */
            width: 100%;
        }

        .admin-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            border-color: #333;
            outline: none;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .image-preview {
            margin-top: 1rem;
            max-width: 200px;
        }

        .image-preview img {
            width: 100%;
            height: auto;
            border-radius: 4px;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-submit {
            background: #27ae60;
            color: white;
        }

        .btn-submit:hover {
            background: #2ecc71;
        }

        .btn-cancel {
            background: #95a5a6;
            color: white;
        }

        .btn-cancel:hover {
            background: #7f8c8d;
        }

        .error-message {
            background: #fee;
            color: #c00;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 0 1rem;
            }

            .form-container {
                padding: 1.5rem;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><?php echo $isEdit ? 'Edit' : 'Add New'; ?> Gin</h1>
    </header>

    <main class="admin-container">
        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Gin Name:</label>
                    <input type="text" id="name" name="name" required
                           value="<?php echo htmlspecialchars($gin['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="distillery">Distillery:</label>
                    <input type="text" id="distillery" name="distillery" required
                           value="<?php echo htmlspecialchars($gin['distillery'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="alcohol_volume">Alcohol Volume (%):</label>
                    <input type="number" id="alcohol_volume" name="alcohol_volume" step="0.1" required
                           value="<?php echo htmlspecialchars($gin['alcohol_volume'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="country">Country:</label>
                    <input type="text" id="country" name="country" required
                           value="<?php echo htmlspecialchars($gin['country'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="botanics">Botanicals (comma-separated):</label>
                    <textarea id="botanics" name="botanics" required><?php echo htmlspecialchars($gin['botanics'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="recommended_tonic">Recommended Tonic:</label>
                    <input type="text" id="recommended_tonic" name="recommended_tonic" required
                           value="<?php echo htmlspecialchars($gin['recommended_tonic'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="garnish">Garnish:</label>
                    <input type="text" id="garnish" name="garnish" required
                           value="<?php echo htmlspecialchars($gin['garnish'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="image">Bottle Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" <?php echo $isEdit ? '' : 'required'; ?>>
                    <?php if ($isEdit && $gin['image_path']): ?>
                        <div class="image-preview">
                            <img src="../<?php echo htmlspecialchars($gin['image_path']); ?>" 
                                 alt="Current gin image">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-save"></i>
                        <?php echo $isEdit ? 'Update' : 'Add'; ?> Gin
                    </button>
                    <a href="index.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html> 