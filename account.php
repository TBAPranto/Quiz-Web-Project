<?php   
session_start();
include('includes/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get logged-in user's ID
$sql = "SELECT * FROM users WHERE id='$user_id'";
$result = $conn->query($sql);

// Check if user exists
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

if (isset($_POST['send_request'])) {
    $message = $_POST['message'];

    // Get admin user ID (assuming there’s one admin)
    $admin_sql = "SELECT id FROM users WHERE role='admin' LIMIT 1";
    $admin_result = $conn->query($admin_sql);
    $admin = $admin_result->fetch_assoc();

    // Insert message into the database
    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ('$user_id', '" . $admin['id'] . "', '$message')";
    if ($conn->query($sql) === TRUE) {
        $request_sent = true; // Flag to show success message
    } else {
        $request_sent = false; // Flag to show error message
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        // Show a popup message after successful message submission
        function showPopup(message) {
            alert(message); // Popup for now, you can use custom modal if needed
        }
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
                <a href="index.php">Home</a> |
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="dashboard.php">Admin Panel</a> |
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </div>
        </header>
        <h2>My Account</h2>
        <div class="profile">
        <img src="images/<?php echo !empty($user['profile_image']) ? $user['profile_image'] : 'default.jpg'; ?>" alt="Profile Image" width="100">
        <p>Name: <?php echo htmlspecialchars($user['name']); ?></p>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p>Role: <?php echo ucfirst(htmlspecialchars($user['role'])); ?></p>
        </div>
        <a href="edit_account.php" class="button">Edit Profile</a>

        <!-- Inbox for All Users -->
        <?php if (isset($user['role']) && ($user['role'] == 'student' || $user['role'] == 'teacher' || $user['role'] == 'admin')): ?>
            <h3>Inbox</h3>
            <form action="account.php" method="POST">
                <select name="user_id" required>
                    <option value="">Select a user to message</option>
                    <?php
                    // Fetch all users from the database (excluding the logged-in user)
                    $user_sql = "SELECT id, name, email FROM users WHERE id != '$user_id'";
                    $user_result = $conn->query($user_sql);
                    while ($user_row = $user_result->fetch_assoc()) {
                        echo "<option value='{$user_row['id']}'>{$user_row['name']} - {$user_row['email']}</option>";
                    }
                    ?>
                </select>
                <button type="submit" name="connect">Connect</button>
            </form>

            <?php
            if (isset($_POST['connect'])) {
                $receiver_id = $_POST['user_id'];
                
                // Redirect to chat page with the selected user
                header("Location: inbox.php?receiver_id=$receiver_id");
                exit();
            }
            ?>
        <?php endif; ?>

        <!-- Student Section -->
        <?php if (isset($user['role']) && ($user['role'] == 'student' || $user['role'] == 'admin')): ?>
            <h3>Available Quizzes</h3>
            <div class="quiz-list">
                <?php
                // Show all quizzes available for students to join
                $quiz_sql = "SELECT * FROM quizzes WHERE created_by != '$user_id'"; // Get quizzes created by others (teachers)
                $quiz_result = $conn->query($quiz_sql);
                if ($quiz_result->num_rows > 0) {
                    while ($quiz = $quiz_result->fetch_assoc()) {
                        echo "<div class='quiz-card'>";
                        echo "<img src='images/{$quiz['cover_image']}' alt='Quiz Cover' class='quiz-cover'>";
                        echo "<div class='quiz-info'>";
                        echo "<h4>" . htmlspecialchars($quiz['title']) . "</h4>";
                        echo "<p>" . htmlspecialchars($quiz['description']) . "</p>";
                        echo "<a href='join_quiz.php?quiz_id={$quiz['id']}' class='button'>Join Quiz</a>";
                        echo "</div></div>";
                    }
                } else {
                    echo "<p>No available quizzes at the moment.</p>";
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Teacher Section -->
        <?php if (isset($user['role']) && ($user['role'] == 'teacher' || $user['role'] == 'admin')): ?>
            <h3>Create a New Quiz</h3>
            <a href="create_quiz.php" class="button">Create a Quiz</a>

            <h3>Your Quizzes</h3>
            <div class="quiz-list">
            <?php
            // Show quizzes created by the teacher
            $quiz_sql = "SELECT * FROM quizzes WHERE created_by='$user_id'";
            $quiz_result = $conn->query($quiz_sql);
            while ($quiz = $quiz_result->fetch_assoc()) {
                echo "<div class='quiz-card'>";
                echo "<img src='images/{$quiz['cover_image']}' alt='Quiz Cover' class='quiz-cover'>";
                echo "<div class='quiz-info'>";
                echo "<h4>" . htmlspecialchars($quiz['title']) . "</h4>";
                echo "<p>" . htmlspecialchars($quiz['description']) . "</p>";
                echo "<a href='edit_quiz.php?quiz_id={$quiz['id']}' class='button'>Edit Quiz</a> | ";
                echo "<a href='delete_quiz.php?quiz_id={$quiz['id']}' class='button'>Delete Quiz</a>";
                echo "</div></div>";
            }
            ?>
            </div>
            <h3>Score Board</h3>
            <table class="table">
                <tr>
                    <th>Quiz Title</th>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Score</th>
                    <th>Date & Time</th>
                </tr>
                <?php
                $score_sql = "SELECT q.title AS quiz_title, u.name AS student_name, qr.tmp_student_id, qr.score, q.total_score, qr.date_taken
                              FROM quiz_results qr
                              JOIN quizzes q ON qr.quiz_id = q.id
                              JOIN users u ON qr.student_id = u.id
                              WHERE q.created_by='$user_id'"; // Get results for quizzes created by the teacher
                $score_result = $conn->query($score_sql);

                if ($score_result->num_rows > 0) {
                    while ($score = $score_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($score['quiz_title']) . "</td>";
                        echo "<td>" . htmlspecialchars($score['student_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($score['tmp_student_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($score['score']) . " / " . htmlspecialchars($score['total_score']) . "</td>";
                        echo "<td>" . htmlspecialchars($score['date_taken']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No results available for your quizzes.</td></tr>";
                }
                ?>
            </table>
        <?php endif; ?>

        <!-- Student Quiz Results -->
        <?php if (isset($user['role']) && ($user['role'] == 'student' || $user['role'] == 'admin')): ?>
            <h3>Your Results</h3>
            <table class="table">
                <tr>
                    <th>Quiz Title</th>
                    <th>Score</th>
                    <th>Date & Time</th>
                </tr>
                <?php
                // Show quiz results for the student
                $result_sql = "SELECT q.title, qr.score, q.total_score, qr.date_taken
                               FROM quiz_results qr
                               JOIN quizzes q ON qr.quiz_id = q.id
                               WHERE qr.student_id='$user_id'";
                $result_query = $conn->query($result_sql);
                while ($result_row = $result_query->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($result_row['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($result_row['score']) . " / " . htmlspecialchars($result_row['total_score']) . "</td>";
                    echo "<td>" . htmlspecialchars($result_row['date_taken']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        <?php endif; ?>

    </div>
</body>
</html>

<?php $conn->close(); ?> <!-- Close the Connection -->
