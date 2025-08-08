<?php  
session_start();
include('includes/db_connect.php');

// Ensure only admin can access this page
if ($_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Handle deleting users
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $delete_sql = "DELETE FROM users WHERE id='$user_id'";
    if ($conn->query($delete_sql) === TRUE) {
        $message = "User deleted successfully!";
        $status = "success";
    } else {
        $message = "Error deleting user: " . $conn->error;
        $status = "error";
    }
}

// Handle deleting quizzes
if (isset($_GET['delete_quiz'])) {
    $quiz_id = $_GET['delete_quiz'];
    $delete_sql = "DELETE FROM quizzes WHERE id='$quiz_id'";
    if ($conn->query($delete_sql) === TRUE) {
        $message = "Quiz deleted successfully!";
        $status = "success";
    } else {
        $message = "Error deleting quiz: " . $conn->error;
        $status = "error";
    }
}

// Handle approving or rejecting role change requests
if (isset($_GET['approve_request'])) {
    $message_id = $_GET['approve_request'];
    // Mark the message as approved
    $update_sql = "UPDATE messages SET status='approved' WHERE id='$message_id'";
    if ($conn->query($update_sql) === TRUE) {
        // Get the student ID from the message
        $message_sql = "SELECT sender_id FROM messages WHERE id='$message_id'";
        $message_result = $conn->query($message_sql);
        $message = $message_result->fetch_assoc();
        $student_id = $message['sender_id'];

        // Update the user's role to teacher
        $role_sql = "UPDATE users SET role='teacher' WHERE id='$student_id'";
        $conn->query($role_sql);

        $message = "Role change request approved!";
        $status = "success";
    } else {
        $message = "Error approving request: " . $conn->error;
        $status = "error";
    }
}

if (isset($_GET['reject_request'])) {
    $message_id = $_GET['reject_request'];
    // Mark the message as rejected
    $update_sql = "UPDATE messages SET status='rejected' WHERE id='$message_id'";
    if ($conn->query($update_sql) === TRUE) {
        $message = "Role change request rejected.";
        $status = "success";
    } else {
        $message = "Error rejecting request: " . $conn->error;
        $status = "error";
    }
}

// Fetch users and quizzes for display
$user_sql = "SELECT * FROM users";
$user_result = $conn->query($user_sql);

$quiz_sql = "SELECT * FROM quizzes";
$quiz_result = $conn->query($quiz_sql);

// Updated SQL for Role Change Requests - Including user name and email
$message_sql = "SELECT messages.id, messages.message, messages.status, users.name, users.email 
                FROM messages
                JOIN users ON messages.sender_id = users.id
                WHERE messages.status='pending'";

$message_result = $conn->query($message_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        // Function to show a popup message
        function showPopup(message, status) {
            let popup = document.createElement("div");
            popup.classList.add("popup-message", status);
            popup.innerHTML = message;
            document.body.appendChild(popup);

            // Close the popup after 3 seconds
            setTimeout(function() {
                popup.style.display = "none";
            }, 3000);
        }

        window.onload = function() {
            <?php if (isset($message)) { ?>
                showPopup("<?php echo $message; ?>", "<?php echo $status; ?>");
            <?php } ?>
        };
    </script>
</head>
<body>
    <div class="container">
		<!-- Header with Navigation Menu -->
		<header>
			<div class="logo">
				<img src="images/logo.png" alt="Quiz Logo" width="150">
			</div>
			<div class="nav-menu">
				<a href="account.php">My Account</a> | 
				<a href="index.php">Home</a> | 
				<a href="logout.php">Logout</a>
			</div>
		</header>
        <h2>Admin Dashboard</h2>

        <h3>Role Change Requests</h3>
        <table>
            <tr>
                <th>User Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while ($message = $message_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $message['name']; ?></td>
                    <td><?php echo $message['email']; ?></td>
                    <td><?php echo $message['message']; ?></td>
                    <td><?php echo ucfirst($message['status']); ?></td>
                    <td>
                        <a href="dashboard.php?approve_request=<?php echo $message['id']; ?>">Approve</a> |
                        <a href="dashboard.php?reject_request=<?php echo $message['id']; ?>">Reject</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h3>Users</h3>
        <table>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Action</th></tr>
            <?php while ($user = $user_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td><a href="dashboard.php?delete_user=<?php echo $user['id']; ?>">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h3>Quizzes</h3>
        <table>
            <tr><th>Title</th><th>Description</th><th>Action</th></tr>
            <?php while ($quiz = $quiz_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $quiz['title']; ?></td>
                    <td><?php echo $quiz['description']; ?></td>
                    <td><a href="dashboard.php?delete_quiz=<?php echo $quiz['id']; ?>">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        </table>

    </div>
</body>
</html>

<?php $conn->close(); ?>
