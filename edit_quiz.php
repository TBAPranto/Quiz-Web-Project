<?php
// Include the database connection
include('includes/db_connect.php');

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's role
$user_id = $_SESSION['user_id'];
$sql = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if the logged-in user is a teacher or admin
if ($user['role'] !== 'teacher' && $user['role'] !== 'admin') {
    // Redirect non-teachers to the account page (or any other page you prefer)
    header("Location: account.php");
    exit;
}

// Get the quiz ID from URL
$quiz_id = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;
if (!$quiz_id) {
    header("Location: index.php"); // Redirect if no quiz ID is provided
    exit;
}

//Fetch the quiz, questions, and options from the database
$sql = "SELECT q.title, q.description, q.cover_image, 
               qs.id AS question_id, qs.question_text, qs.score, qs.correct_option,
               o.id AS option_id, o.option_text
        FROM quizzes q
        LEFT JOIN questions qs ON q.id = qs.quiz_id
        LEFT JOIN options o ON qs.id = o.question_id
        WHERE q.id = ?
        ORDER BY qs.id ASC, o.id ASC";  // Ensure options are ordered by option_id for each question

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$quiz_data = $result->fetch_all(MYSQLI_ASSOC);

// Group options by question_id
$options_by_question = [];
foreach ($quiz_data as $data) {
    if ($data['option_id']) {
        $options_by_question[$data['question_id']][] = $data;
    }
}

// If the quiz does not exist
if (empty($quiz_data)) {
    echo "<script>alert('Quiz not found.');</script>";
    header("Location: index.php");
    exit;
}

// Fetch current quiz details
$quiz_details = $quiz_data[0]; // First row will contain quiz details

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Quiz Update
    $title = $_POST['title'];
    $description = $_POST['description'];
    $cover_image = $_FILES['cover_image']['name'] ? $_FILES['cover_image']['name'] : $quiz_details['cover_image'];

    // Handle uploading new cover image
    if ($_FILES['cover_image']['name']) {
        $image_path = 'images/' . $cover_image;
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $image_path);
    }

    // Update quiz details in the database
    $update_quiz_sql = "UPDATE quizzes SET title = ?, description = ?, cover_image = ? WHERE id = ?";
    $stmt = $conn->prepare($update_quiz_sql);
    $stmt->bind_param("sssi", $title, $description, $cover_image, $quiz_id);
    $stmt->execute();

    // Handle Question and Option Updates
    foreach ($_POST['questions'] as $question_id => $question_data) {
        $question_text = $question_data['question_text'];
        $score = $question_data['score'];
        $correct_option = $question_data['correct_option'];

        // Update question
        $update_question_sql = "UPDATE questions SET question_text = ?, score = ?, correct_option = ? WHERE id = ?";
        $stmt = $conn->prepare($update_question_sql);
        $stmt->bind_param("siis", $question_text, $score, $correct_option, $question_id);
        $stmt->execute();

        // Update options
        foreach ($question_data['options'] as $option_id => $option_data) {
            $option_text = $option_data['option_text'];

            // Set 'is_correct' to 1 if the option_id matches the correct_option
            $is_correct = ($option_id == $correct_option) ? 1 : 0;

            // Update option
            $update_option_sql = "UPDATE options SET option_text = ?, is_correct = ? WHERE id = ?";
            $stmt = $conn->prepare($update_option_sql);
            $stmt->bind_param("sii", $option_text, $is_correct, $option_id);
            $stmt->execute();
        }
    }

    echo "<script>alert('Quiz updated successfully!');</script>";
    header("Location: account.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz</title>
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
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="dashboard.php">Admin Panel</a> |
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <h2>Edit Quiz: <?php echo htmlspecialchars($quiz_details['title']); ?></h2>

        <form action="edit_quiz.php?quiz_id=<?php echo $quiz_id; ?>" method="POST" enctype="multipart/form-data">
            <!-- Quiz Information -->
            <div class="form-section">
                <label for="title">Quiz Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($quiz_details['title']); ?>" required>
            </div>

            <div class="form-section">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($quiz_details['description']); ?></textarea>
            </div>

            <div class="form-section">
                <label for="cover_image">Cover Image:</label>
                <input type="file" id="cover_image" name="cover_image">
                <?php if ($quiz_details['cover_image']) : ?>
                    <p>Current image: <img src="images/<?php echo htmlspecialchars($quiz_details['cover_image']); ?>" alt="Cover Image" width="100"></p>
                <?php endif; ?>
            </div>

            <!-- Questions and Options -->
			<h3>Questions</h3>
			<?php foreach ($quiz_data as $row) : ?>
				<!-- Display each question only once -->
				
				<?php if (!isset($displayed_questions[$row['question_id']])) : ?>
					<div class="question-section">
						<input type="hidden" name="questions[<?php echo $row['question_id']; ?>][question_id]" value="<?php echo $row['question_id']; ?>">
						<label for="question_<?php echo $row['question_id']; ?>">Question Text:</label>
						<input type="text" name="questions[<?php echo $row['question_id']; ?>][question_text]" value="<?php echo htmlspecialchars($row['question_text']); ?>" required>

						<label for="score_<?php echo $row['question_id']; ?>">Score:</label>
						<input type="number" name="questions[<?php echo $row['question_id']; ?>][score]" value="<?php echo $row['score']; ?>" required>

						<label for="correct_option_<?php echo $row['question_id']; ?>">Correct Option:</label>
						<select name="questions[<?php echo $row['question_id']; ?>][correct_option]" required>
							<?php
							// Fetch the options for this question from the grouped options
							$options_for_question = $options_by_question[$row['question_id']];
							foreach ($options_for_question as $index => $option) :
							?>
								<option value="<?php echo $option['option_id']; ?>" <?php echo ($row['correct_option'] == $option['option_id']) ? 'selected' : ''; ?>>
									Option <?php echo $index + 1; ?>  <!-- Display Option numbering -->
								</option>
							<?php endforeach; ?>
						</select>

						<!-- Display options dynamically -->
						<?php foreach ($options_for_question as $option) : ?>
							<div class="option-section">
								<label for="option_<?php echo $row['question_id']; ?>">Option:</label>
								<input type="text" name="questions[<?php echo $row['question_id']; ?>][options][<?php echo $option['option_id']; ?>][option_text]" value="<?php echo htmlspecialchars($option['option_text']); ?>" required>
							</div>
						<?php endforeach; ?>
					</div>
					<?php $displayed_questions[$row['question_id']] = true; ?>
				<?php endif; ?>
			<?php endforeach; ?>

            <button type="submit">Update Quiz</button>
        </form>
    </div>
</body>
</html>
