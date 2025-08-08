<?php
session_start();
include('includes/db_connect.php'); // Assuming db_connect.php connects to your database

// Fetch the quiz ID from the URL
$quiz_id = $_GET['quiz_id']; 

// Fetch quiz details
$quiz_query = "SELECT * FROM quizzes WHERE id = $quiz_id";
$quiz_result = mysqli_query($conn, $quiz_query);
$quiz = mysqli_fetch_assoc($quiz_result);

// Initialize the total score
$total_score = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture student ID from input
    $tmp_student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $_SESSION['tmp_student_id'] = $tmp_student_id;

    // Iterate over questions and calculate the total score
    foreach ($_POST['questions'] as $question_id => $selected_option_id) {
        $question_query = "SELECT * FROM questions WHERE id = $question_id";
        $question_result = mysqli_query($conn, $question_query);
        $question = mysqli_fetch_assoc($question_result);

        // Check if the selected option is correct
        $option_query = "SELECT * FROM options WHERE id = $selected_option_id AND question_id = $question_id";
        $option_result = mysqli_query($conn, $option_query);
        $option = mysqli_fetch_assoc($option_result);

        if ($option['is_correct'] == 1) {
            $total_score += $question['score']; // Add the question's score if correct
        }
    }

    // Insert result into quiz_results table
    $student_id = $_SESSION['user_id']; // Assuming student is logged in, and user_id is stored in session
    $result_query = "INSERT INTO quiz_results (student_id, tmp_student_id, quiz_id, score) 
                     VALUES ($student_id, '$tmp_student_id', $quiz_id, $total_score)";
    mysqli_query($conn, $result_query);

    // Redirect to account.php after submission
    echo "<script>window.location.href = 'account.php';</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Quiz</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function validateForm() {
            let studentId = document.getElementById("student_id").value;
            if (!/^\d+$/.test(studentId) || studentId <= 0) {
                alert("Please enter a valid positive integer for your Student ID.");
                return false;
            }

            // Check if at least one option is selected for each question
            let questions = document.querySelectorAll('.question');
            for (let i = 0; i < questions.length; i++) {
                let options = questions[i].querySelectorAll('input[type="radio"]');
                let optionSelected = false;
                for (let j = 0; j < options.length; j++) {
                    if (options[j].checked) {
                        optionSelected = true;
                        break;
                    }
                }
                if (!optionSelected) {
                    alert("Please select at least one option for each question.");
                    return false;
                }
            }

            return true; // All validations passed
        }

        // Real-time Student ID validation
        document.getElementById("student_id").addEventListener("input", function() {
            let studentId = this.value;
            let message = document.getElementById("student_id_error");

            if (!/^\d+$/.test(studentId) || studentId <= 0) {
                message.textContent = "Please enter a valid positive integer.";
                message.style.color = "red";
            } else {
                message.textContent = "";
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <h2><?php echo $quiz['title']; ?></h2>
        <p class="quiz-description"><?php echo $quiz['description']; ?></p>

        <form action="join_quiz.php?quiz_id=<?php echo $quiz_id; ?>" method="POST" onsubmit="return validateForm()">
            <div class="form-section">
                <label for="student_id">Your Student ID:</label>
                <input type="text" name="student_id" id="student_id" required>
                <span id="student_id_error"></span> <!-- Display error message here -->
            </div>

            <h3>Your Quiz Starts!</h3>

            <?php
            // Fetch the questions for this quiz
            $questions_query = "SELECT * FROM questions WHERE quiz_id = $quiz_id";
            $questions_result = mysqli_query($conn, $questions_query);
            $question_number = 1;

            while ($question = mysqli_fetch_assoc($questions_result)) {
                echo "<div class='question'>";
                echo "<strong>" . $question_number++ . ". " . $question['question_text'] . "</strong>";
                
                // Fetch options for this question
                $options_query = "SELECT * FROM options WHERE question_id = " . $question['id'];
                $options_result = mysqli_query($conn, $options_query);

                echo "<div class='options'>";
                while ($option = mysqli_fetch_assoc($options_result)) {
                    echo "<label class='option'>";
                    echo "<input type='radio' name='questions[" . $question['id'] . "]' value='" . $option['id'] . "'>";
                    echo " " . $option['option_text'];
                    echo "</label>";
                }
                echo "</div></div>";
            }
            ?>

            <button type="submit">Submit Quiz</button>
        </form>
    </div>
</body>
</html>
