<?php
session_start();
require_once '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            header('Location: index.php');
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Al Caminetto</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #eeeee4;
            font-family: 'Montserrat', sans-serif;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: #333;
        }

        .form-group input {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #e28743;
            box-shadow: 0 0 0 2px rgba(226, 135, 67, 0.1);
        }
        
        .error-message {
            color: #dc3545;
            padding: 0.5rem;
            background: #ffe6e6;
            border-radius: 4px;
            font-size: 0.9rem;
            text-align: center;
        }

        button {
            background: #e28743;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
        }

        button:hover {
            background: #d17632;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-container img {
            height: 60px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 2rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <main>
        <div class="login-container">
            <div class="logo-container">
                <img src="../images/logo.png" alt="Al Caminetto">
            </div>
            <h1>Admin Access</h1>
            
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit">Login</button>
            </form>
        </div>
    </main>
</body>
</html> 