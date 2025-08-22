<?php
session_start();
include('includes/db_connect.php');

// Ensure the user is logged in as a teacher or admin
if ($_SESSION['user_role'] != 'teacher' && $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['quiz_id']) && isset($_GET['num_questions']) && isset($_GET['num_options'])) {
    $quiz_id = $_GET['quiz_id'];
    $num_questions = $_GET['num_questions'];
    $num_options = $_GET['num_options'];
}

if (isset($_POST['save_quiz'])) {
    $total_score = 0;

    // Insert questions and options into the database
    for ($i = 1; $i <= $num_questions; $i++) {
        $question_text = $_POST["question_$i"];
        $question_score = $_POST["score_$i"];
        $correct_option = $_POST["correct_option_$i"];

        // Insert the question into the questions table
        $question_sql = "INSERT INTO questions (quiz_id, question_text, correct_option, score) 
                         VALUES ('$quiz_id', '$question_text', '$correct_option', '$question_score')";
        $conn->query($question_sql);

        // Get the last inserted question ID
        $question_id = $conn->insert_id;

        // Insert options for the question
        for ($j = 1; $j <= $num_options; $j++) {
            $option_text = $_POST["question_{$i}_option_{$j}"];
            $is_correct = ($j == $correct_option) ? 1 : 0;
            $option_sql = "INSERT INTO options (question_id, option_text, is_correct) 
                           VALUES ('$question_id', '$option_text', '$is_correct')";
            $conn->query($option_sql);
        }

        // Add to total score
        $total_score += $question_score;
    }

    // Update total score for the quiz
    $update_quiz_sql = "UPDATE quizzes SET total_score='$total_score' WHERE id='$quiz_id'";
    $conn->query($update_quiz_sql);

    // Redirect to the account page with a success message
    header("Location: account.php?quiz_created=true");  // Redirect back to account.php to show popup
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Define Your Quiz</title>
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
                <a href="index.php">Home</a>
				<a href="account.php">My Account</a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="dashboard.php">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </div>
        </header>
        <h2>Define Your Quiz</h2>
        <form action="set_quiz.php?quiz_id=<?php echo $quiz_id; ?>&num_questions=<?php echo $num_questions; ?>&num_options=<?php echo $num_options; ?>" method="POST">
            <?php for ($i = 1; $i <= $num_questions; $i++): ?>
                <h3>Question <?php echo $i; ?></h3>
                <input type="text" name="question_<?php echo $i; ?>" placeholder="Question Text" required><br>
                <input type="number" name="score_<?php echo $i; ?>" placeholder="Score" required><br>

                <!-- Input fields for options -->
                <?php for ($j = 1; $j <= $num_options; $j++): ?>
                    <input type="text" name="question_<?php echo $i; ?>_option_<?php echo $j; ?>" placeholder="Option <?php echo $j; ?>" required><br>
                <?php endfor; ?>

                <h4>Correct Option (1-<?php echo $num_options; ?>):</h4>
                <input type="number" name="correct_option_<?php echo $i; ?>" min="1" max="<?php echo $num_options; ?>" required><br><br>
            <?php endfor; ?>
            <button type="submit" name="save_quiz">Save Quiz</button>
        </form>
    </div>
</body>
</html>
