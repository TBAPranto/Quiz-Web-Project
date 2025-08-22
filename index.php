<?php
// Start the session at the beginning of the page
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz for Fun</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header with Logo and Navigation Menu -->
        <header>
            <div class="logo">
                <img src="images/logo.png" alt="Quiz Logo" width="150">
            </div>
            <div class="nav-menu">
                <?php
                if (isset($_SESSION['user_id'])) {
                    // Show My Account and Logout when the user is logged in
                    echo "<a href='account.php'>My Account</a>";
                    echo "<a href='logout.php'>Logout</a>";
                } else {
                    // Show Login and Register when the user is not logged in
                    echo "<a href='login.php'>Login</a>";
                    echo "<a href='register.php'>Register</a>";
                }
                ?>
            </div>
        </header>

        <h1 class="page-title">Quiz for Fun</h1>

        <!-- Search Bar -->
        <form action="index.php" method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search quizzes..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" class="search-input">
            <button type="submit" class="search-button">Search</button>
        </form>

        <h3 class="section-title">Available Quizzes</h3>

        <?php
        include('includes/db_connect.php');
        
        // Get quizzes based on search query or all quizzes
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $sql = "SELECT * FROM quizzes WHERE title LIKE '%$search%' OR description LIKE '%$search%'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<div class='quiz-list'>";
            while ($quiz = $result->fetch_assoc()) {
                echo "<div class='quiz-card'>";
                echo "<img src='images/" . $quiz['cover_image'] . "' alt='Quiz Cover' class='quiz-cover'>";
                echo "<div class='quiz-info'>";
                echo "<h4>" . $quiz['title'] . "</h4>";
                echo "<p>" . $quiz['description'] . "</p>";

                if (isset($_SESSION['user_id'])) {
                    // Get the logged-in user's role and ID
                    $user_role = $_SESSION['user_role'];
                    $user_id = $_SESSION['user_id'];

                    // Show different options based on user role and quiz ownership
                    if ($quiz['created_by'] == $user_id) {
                        // If the user is the creator of the quiz
                        if ($user_role == 'teacher' || $user_role == 'admin') {
                            echo "<a href='edit_quiz.php?quiz_id={$quiz['id']}' class='button'>Edit Quiz</a> | ";
                            echo "<a href='delete_quiz.php?quiz_id={$quiz['id']}' class='button'>Delete Quiz</a>";
                        }
                    } else {
                        // If the user is not the creator, allow them to join the quiz
                        echo "<a href='join_quiz.php?quiz_id={$quiz['id']}' class='button'>Join Quiz</a>";
                    }
                } else {
                    echo "<a href='login.php' class='button'>Login to Take Quiz</a>";
                }
                echo "</div></div>";
            }
            echo "</div>";
        } else {
            echo "<p>No quizzes found.</p>";
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
