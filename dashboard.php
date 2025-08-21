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

// Fetch users and quizzes for display
$user_sql = "SELECT * FROM users";
$user_result = $conn->query($user_sql);

$quiz_sql = "SELECT * FROM quizzes";
$quiz_result = $conn->query($quiz_sql);

$message_sql = "SELECT messages.id, messages.message, users.name, users.email, messages.sender_id 
                FROM messages
                JOIN users ON messages.sender_id = users.id
                WHERE messages.receiver_id = 1"; // Assuming admin has ID = 1
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

        // Confirmation before delete operation
        function confirmDelete(message, url) {
            if (confirm(message)) {
                window.location.href = url; // Proceed with the delete operation if confirmed
            }
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

        <!-- User's Messages -->
        <h3>User's Messages</h3>
        <table>
            <tr>
                <th>User Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Action</th>
            </tr>
            <?php while ($message = $message_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $message['name']; ?></td>
                    <td><?php echo $message['email']; ?></td>
                    <td><?php echo $message['message']; ?></td>
                    <td>
                        <a href="inbox.php?receiver_id=<?php echo $message['sender_id']; ?>">Reply</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Users Table -->
        <h3>Users</h3>
        <table>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Action</th></tr>
            <?php while ($user = $user_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td>
                        <a href="edit_account.php?id=<?php echo $user['id']; ?>">Edit</a> |
                        <a href="javascript:void(0);" onclick="confirmDelete('Are you sure you want to delete this user?', 'dashboard.php?delete_user=<?php echo $user['id']; ?>')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Quizzes Table -->
        <h3>Quizzes</h3>
        <table>
            <tr><th>Title</th><th>Description</th><th>Created By</th><th>Action</th></tr>
            <?php while ($quiz = $quiz_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $quiz['title']; ?></td>
                    <td><?php echo $quiz['description']; ?></td>
                    <td>
                        <?php 
                        // Fetch creator's name
                        $creator_sql = "SELECT name FROM users WHERE id = ".$quiz['created_by'];
                        $creator_result = $conn->query($creator_sql);
                        $creator = $creator_result->fetch_assoc();
                        echo $creator['name'];
                        ?>
                    </td>
                    <td>
                        <!-- Link to delete quiz using delete_quiz.php -->
                        <a href="javascript:void(0);" onclick="confirmDelete('Are you sure you want to delete this quiz?', 'dashboard.php?delete_quiz=<?php echo $quiz['id']; ?>')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

    </div>
</body>
</html>

<?php $conn->close(); ?>
