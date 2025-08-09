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
if ($user['role'] !== 'teacher'&&$user['role'] !== 'admin') {
    // Redirect non-teachers to the account page
    header("Location: account.php");
    exit;
}

// Get the quiz ID from the URL
$quiz_id = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;
if (!$quiz_id) {
    header("Location: index.php"); // Redirect if no quiz ID is provided
    exit;
}

// Begin transaction to ensure consistency
$conn->begin_transaction();

try {
    // Delete associated quiz results (foreign key constraint issue)
    $delete_results_sql = "DELETE FROM quiz_results WHERE quiz_id = ?";
    $stmt = $conn->prepare($delete_results_sql);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    // Delete associated options (related to questions of the quiz)
    $delete_options_sql = "DELETE FROM options WHERE question_id IN (SELECT id FROM questions WHERE quiz_id = ?)";
    $stmt = $conn->prepare($delete_options_sql);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    // Delete associated questions (linked to the quiz)
    $delete_questions_sql = "DELETE FROM questions WHERE quiz_id = ?";
    $stmt = $conn->prepare($delete_questions_sql);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    // Delete the quiz itself
    $delete_quiz_sql = "DELETE FROM quizzes WHERE id = ?";
    $stmt = $conn->prepare($delete_quiz_sql);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    // Commit the transaction
    $conn->commit();

    // Redirect back to the account page with a success message
    echo "<script>alert('Quiz deleted successfully!');</script>";
    header("Location: account.php");
    exit;

} catch (Exception $e) {
    // Rollback transaction in case of an error
    $conn->rollback();
    echo "<script>alert('Error deleting quiz. Please try again.');</script>";
    header("Location: account.php");
    exit;
}
?>
