<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.html");
    exit();
}

require_once 'connect.php'; // Include database connection

// Handle Create, Update, Delete Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add_user') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $role = $_POST['role'];
            $query = "INSERT INTO user (name, email, role, signup_date) VALUES ('$name', '$email', '$role', NOW())";
            mysqli_query($conn, $query);
        } elseif ($action === 'update_user' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $role = $_POST['role'];

            // Debugging: Check if data is received
            if (empty($id)) {
                die("Error: ID is missing in update request.");
            }

            $query = "UPDATE user SET name='$name', email='$email', role='$role' WHERE id=$id";
            if (mysqli_query($conn, $query)) {
                header("Location: user_management.php"); // Refresh the page after update
                exit();
            } else {
                die("Update failed: " . mysqli_error($conn));
            }
        } elseif ($action === 'delete_user' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $query = "DELETE FROM user WHERE id=$id";
            mysqli_query($conn, $query);
        }
    }
}

// Fetch All Users
$users = mysqli_query($conn, "SELECT id, name, email, role, signup_date FROM user ORDER BY signup_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="user_management.css">
    <script>
        function confirmDelete(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                document.getElementById("delete-form-" + userId).submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1 class="title">User Management</h1>

        <form class="form-container" method="POST" action="">
            <input type="hidden" name="action" value="add_user">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <select name="role">
                <option value="client">Client</option>
                <option value="therapist">Therapist</option>
                <option value="admin">Admin</option>
            </select>
            <button class="btn add" type="submit">Add User</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Signup Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($users)) { ?>
                    <?php if (!isset($row['id'])) continue; // Skip if 'id' is missing ?>
                    <tr>
                        <form method="POST" action="">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <td><?php echo $row['id']; ?></td>
                            <td><input type="text" name="name" value="<?php echo $row['name']; ?>"></td>
                            <td><input type="email" name="email" value="<?php echo $row['email']; ?>"></td>
                            <td>
                                <select name="role">
                                    <option value="client" <?php echo $row['role'] === 'client' ? 'selected' : ''; ?>>Client</option>
                                    <option value="therapist" <?php echo $row['role'] === 'therapist' ? 'selected' : ''; ?>>Therapist</option>
                                    <option value="admin" <?php echo $row['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </td>
                            <td><?php echo $row['signup_date']; ?></td>
                            <td>
                                <button class="btn edit" type="submit" name="action" value="update_user">Update</button>
                            </td>
                        </form>
                        <td>
                            <form id="delete-form-<?php echo $row['id']; ?>" method="POST" action="">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action" value="delete_user">
                                <button class="btn delete" type="button" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <p>Are you sure you want to delete this user?</p>
            <div class="modal-buttons">
                <button class="btn confirm-delete" id="confirmDeleteBtn">Yes</button>
                <button class="btn cancel-delete" onclick="closeDeleteModal()">No</button>
            </div>
        </div>
    </div>

    <script>
    let deleteUserId = null;

    function openDeleteModal(userId) {
        deleteUserId = userId;
        document.getElementById("deleteModal").style.display = "flex";
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").style.display = "none";
    }

    document.getElementById("confirmDeleteBtn").addEventListener("click", function() {
        if (deleteUserId) {
            let form = document.createElement("form");
            form.method = "POST";
            form.action = "";

            let inputAction = document.createElement("input");
            inputAction.type = "hidden";
            inputAction.name = "action";
            inputAction.value = "delete_user";
            
            let inputId = document.createElement("input");
            inputId.type = "hidden";
            inputId.name = "id";
            inputId.value = deleteUserId;

            form.appendChild(inputAction);
            form.appendChild(inputId);
            document.body.appendChild(form);
            form.submit();
        }
    });
    </script>
</body>
</html>
