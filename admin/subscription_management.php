<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.html");
    exit();
}

require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add_subscription') {
            $user_email = $_POST['user_email'];
            $user_name = $_POST['user_name'];
            $plan = $_POST['plan'];
            $amount = $_POST['amount'];
            $payment_id = $_POST['payment_id'];
            $end_date = $_POST['end_date'];
            $status = $_POST['status'];

            $query = "INSERT INTO subscriptions (user_email, user_name, plan, amount, payment_id, end_date, status) 
                      VALUES ('$user_email', '$user_name', '$plan', '$amount', '$payment_id', '$end_date', '$status')";
            mysqli_query($conn, $query);
        } elseif ($action === 'update_subscription' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $query = "UPDATE subscriptions SET status='$status' WHERE id=$id";
            mysqli_query($conn, $query);
        } elseif ($action === 'delete_subscription' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $query = "DELETE FROM subscriptions WHERE id=$id";
            mysqli_query($conn, $query);
        }
    }
}

$subscriptions = mysqli_query($conn, "SELECT * FROM subscriptions ORDER BY end_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Management</title>
    <link rel="stylesheet" href="subscription_management.css">
</head>
<body>
    <div class="container">
        <h1 class="title">Subscription Management</h1>

        <form class="form-container" method="POST" action="">
            <input type="hidden" name="action" value="add_subscription">
            <input type="text" name="user_name" placeholder="User Name" required>
            <input type="email" name="user_email" placeholder="User Email" required>
            <select name="plan">
                <option value="Basic">Basic</option>
                <option value="Standard">Standard</option>
                <option value="VIP">VIP</option>
                <option value="Premium">Premium</option>
            </select>
            <input type="number" name="amount" placeholder="Amount" required>
            <input type="text" name="payment_id" placeholder="Payment ID" required>
            <input type="date" name="end_date" required>
            <select name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <button class="btn add" type="submit">Add Subscription</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($subscriptions)) { ?>
                    <tr>
                        <form method="POST" action="">
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['user_name']; ?></td>
                            <td><?php echo $row['user_email']; ?></td>
                            <td><?php echo $row['plan']; ?></td>
                            <td><?php echo $row['amount']; ?></td>
                            <td><?php echo $row['end_date']; ?></td>
                            <td>
                                <select name="status">
                                    <option value="active" <?php echo $row['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $row['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="cancelled" <?php echo $row['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </td>
                            <td>
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button class="btn edit" type="submit" name="action" value="update_subscription">Update</button>
                                <button class="btn delete" type="button" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</button>
                            </td>
                        </form>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div id="customConfirm" class="confirm-box">
        <p>Are you sure you want to delete this subscription?</p>
        <button id="confirmYes" class="btn confirm">Yes</button>
        <button id="confirmNo" class="btn cancel">No</button>
    </div>

    <script>
        let deleteId = null;

        function confirmDelete(id) {
            deleteId = id;
            document.getElementById('customConfirm').style.display = 'block';
        }

        document.getElementById('confirmNo').addEventListener('click', function () {
            document.getElementById('customConfirm').style.display = 'none';
        });

        document.getElementById('confirmYes').addEventListener('click', function () {
            if (deleteId) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_subscription';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = deleteId;

                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    </script>
</body>
</html>
