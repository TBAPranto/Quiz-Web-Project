<?php
// Include the database connection
include('includes/db_connect.php');

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch user data excluding id, role, and created_at
$sql = "SELECT name, email, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// If form is submitted, update user information
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Handle profile image upload
    if ($_FILES['profile_image']['name']) {
        // If a new image is uploaded, set the image path
        $image_name = $_FILES['profile_image']['name'];
        $image_path = 'images/' . $image_name;

        // Move the uploaded image to the images folder
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $image_path)) {
            // Successfully uploaded the image
        } else {
            // If there is an error in uploading the image
            echo "<script>alert('Error uploading the image. Please try again.')</script>";
        }
    } else {
        // If no new image is uploaded, keep the old image path
        $image_name = $user_data['profile_image'] ? $user_data['profile_image'] : 'default.jpg';
        $image_path = 'images/' . $image_name;
    }

    // Update user information in the database
    $update_sql = "UPDATE users SET name = ?, email = ?, profile_image = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssi", $name, $email, $image_name, $user_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('Account updated successfully!')</script>";
        header("Location: account.php");
    } else {
        echo "<script>alert('Error updating account. Please try again.')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header with Navigation Menu -->
        <header>
            <div class="logo">
                <img src="images/logo.png" alt="App Logo" width="150">
            </div>
            <div class="nav-menu">
                <a href="index.php">Home</a> |
                <a href="account.php">My Account</a> |
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <h2>Edit Your Account</h2>
        
        <form action="edit_account.php" method="POST" enctype="multipart/form-data">
            <div class="form-section">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
            </div>

            <div class="form-section">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
            </div>

            <div class="form-section">
                <label for="profile_image">Profile Image:</label>
                <input type="file" id="profile_image" name="profile_image">
                <?php if ($user_data['profile_image']) : ?>
                    <p>Current image: <img src="images/<?php echo htmlspecialchars($user_data['profile_image']); ?>" alt="Profile Image" width="100"></p>
                <?php else: ?>
                    <p>Current image: <img src="images/default.jpg" alt="Default Image" width="100"></p>
                <?php endif; ?>
            </div>

            <button type="submit">Update Account</button>
        </form>
    </div>
</body>
</html>
