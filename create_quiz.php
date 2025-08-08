<?php
session_start();
include('includes/db_connect.php');

// Ensure the user is logged in as a teacher
if ($_SESSION['user_role'] != 'teacher') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['create_quiz'])) {
    // Get the data from the form
    $quiz_title = $_POST['quiz_title'];
    $quiz_description = $_POST['quiz_description'];
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
				<?php if ($_SESSION['user_role'] == 'admin'): ?>
					<a href="dashboard.php">Admin Panel</a> |
				<?php endif; ?>
				<a href="logout.php">Logout</a>
			</div>
		</header>
        <h2>Create a New Quiz</h2>
        <form action="create_quiz.php" method="POST" enctype="multipart/form-data">
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
