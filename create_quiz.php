<?php
session_start();
include('includes/db_connect.php');

// Ensure the user is logged in as a teacher or admin
if ($_SESSION['user_role'] != 'teacher' && $_SESSION['user_role'] != 'admin') {
    header("Location: account.php");
    exit();
}

if (isset($_POST['create_quiz'])) {
    // Get the data from the form
    $quiz_title = mysqli_real_escape_string($conn, $_POST['quiz_title']);
    $quiz_description = mysqli_real_escape_string($conn, $_POST['quiz_description']);
    $cover_image = $_FILES['cover_image']['name'] ? $_FILES['cover_image']['name'] : 'default_cover.jpg';
    $num_questions = $_POST['num_questions'];
    $num_options = $_POST['num_options']; // Number of options per question

    // Upload cover image if provided
    if ($_FILES['cover_image']['name']) {
        move_uploaded_file($_FILES['cover_image']['tmp_name'], 'images/' . $cover_image);
    }

    // Insert quiz details into the database
    $sql = "INSERT INTO quizzes (title, description, cover_image, created_by) 
            VALUES ('$quiz_title', '$quiz_description', '$cover_image', '{$_SESSION['user_id']}')";

    if ($conn->query($sql) === TRUE) {
        // Get the last inserted quiz ID
        $quiz_id = $conn->insert_id;
        header("Location: set_quiz.php?quiz_id=$quiz_id&num_questions=$num_questions&num_options=$num_options");  // Redirect to set_quiz.php to define questions
    } else {
        echo "Error: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function validateForm() {
            // Validate the text input
            var title = document.forms["quizForm"]["quiz_title"].value;
            var description = document.forms["quizForm"]["quiz_description"].value;
            var numQuestions = document.forms["quizForm"]["num_questions"].value;
            var numOptions = document.forms["quizForm"]["num_options"].value;

            // Check if the title and description are not empty
            if (title.trim() == "") {
                alert("Quiz title must be filled out.");
                return false;
            }
            if (description.trim() == "") {
                alert("Quiz description must be filled out.");
                return false;
            }

            // Check if num_questions and num_options are positive integers and not empty
            if (numQuestions.trim() == "" || numQuestions <= 0 || isNaN(numQuestions)) {
                alert("Please enter a valid positive number for the number of questions.");
                return false;
            }
            if (numOptions.trim() == "" || numOptions <= 0 || isNaN(numOptions)) {
                alert("Please enter a valid positive number for the number of options per question.");
                return false;
            }

            return true; // All checks passed, form can be submitted
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
				<a href="account.php">My Account</a> |
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="dashboard.php">Admin Panel</a> |
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </div>
        </header>
        <h2>Create a New Quiz</h2>
        <form name="quizForm" action="create_quiz.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <input type="text" name="quiz_title" placeholder="Quiz Title" required>
            <textarea name="quiz_description" placeholder="Quiz Description" required></textarea>
            <input type="file" name="cover_image">
            <input type="number" name="num_questions" placeholder="Number of Questions" required>
            <input type="number" name="num_options" placeholder="Number of Options per Question" required>
            <button type="submit" name="create_quiz">Create Quiz</button>
        </form>
    </div>
</body>
</html>
