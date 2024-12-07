<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all gins with explicit ordering
$stmt = $conn->prepare("
    SELECT * FROM gins 
    ORDER BY name ASC
");
$stmt->execute();
$gins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - I gin del Caminetto</title>
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
            color: #333;
        }

        .admin-header {
            background: #333;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-title {
            font-size: 1.5rem;
            font-weight: 400;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: #2980b9;
        }

        .btn-view:hover {
            background: #3498db;
        }

        .btn-logout {
            background: #c0392b;
        }

        .btn-logout:hover {
            background: #e74c3c;
        }

        .btn-add {
            background: #27ae60;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            margin: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-add:hover {
            background: #2ecc71;
        }

        .content {
            padding: 0 2rem;
        }

        .gins-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 1rem;
        }

        .gins-table th {
            background: #333;
            color: white;
            text-align: left;
            padding: 1rem;
            font-weight: 500;
        }

        .gins-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .gins-table tr:last-child td {
            border-bottom: none;
        }

        .gin-image {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
        }

        .btn-edit {
            background: #2980b9;
        }

        .btn-edit:hover {
            background: #3498db;
        }

        .btn-delete {
            background: #c0392b;
        }

        .btn-delete:hover {
            background: #e74c3c;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }

        .modal-title {
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .modal-actions {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1rem;
            }

            .header-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .gins-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1 class="admin-title">Admin Dashboard</h1>
        <div class="header-actions">
            <a href="../index.php" class="btn btn-view">
                <i class="fas fa-home"></i>
                View Site
            </a>
            <a href="logout.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </header>

    <main class="content">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php 
                echo htmlspecialchars($_SESSION['success_message']);
                unset($_SESSION['success_message']); 
                ?>
            </div>
        <?php endif; ?>

        <a href="edit.php" class="btn btn-add">
            <i class="fas fa-plus"></i>
            Add New Gin
        </a>

        <table class="gins-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Distillery</th>
                    <th>Country</th>
                    <th>ABV</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($gins as $gin): ?>
                    <tr>
                        <td>
                            <img src="../<?php echo htmlspecialchars($gin['image_path'] ?? 'images/default-gin.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($gin['name']); ?>"
                                 class="gin-image">
                        </td>
                        <td><?php echo htmlspecialchars($gin['name']); ?></td>
                        <td><?php echo htmlspecialchars($gin['distillery']); ?></td>
                        <td><?php echo htmlspecialchars($gin['country']); ?></td>
                        <td><?php echo htmlspecialchars($gin['alcohol_volume']); ?>%</td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit.php?id=<?php echo $gin['id']; ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn-action btn-delete" onclick="showDeleteModal(<?php echo $gin['id']; ?>, '<?php echo htmlspecialchars($gin['name']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h3 class="modal-title">Confirm Deletion</h3>
            <p>Are you sure you want to delete <span id="ginName"></span>?</p>
            <div class="modal-actions">
                <button class="btn btn-view" onclick="hideDeleteModal()">Cancel</button>
                <button class="btn btn-delete" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    <script>
        let ginToDelete = null;

        function showDeleteModal(id, name) {
            ginToDelete = id;
            document.getElementById('ginName').textContent = name;
            document.getElementById('deleteModal').classList.add('show');
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
            ginToDelete = null;
        }

        function confirmDelete() {
            if (ginToDelete) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete.php';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = ginToDelete;
                
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
            hideDeleteModal();
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideDeleteModal();
            }
        });
    </script>
</body>
</html> 