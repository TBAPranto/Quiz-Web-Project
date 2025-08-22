<?php 
include('includes/db_connect.php');

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if password and confirm password match
    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
    } else {
        // Hash the password
        $password = password_hash($password, PASSWORD_DEFAULT);

        $profile_image = $_FILES['profile_image']['name'] ? $_FILES['profile_image']['name'] : 'default.jpg';

        // Upload image if provided
        if ($_FILES['profile_image']['name']) {
            move_uploaded_file($_FILES['profile_image']['tmp_name'], 'images/' . $profile_image);
        }

        $sql = "INSERT INTO users (name, email, password, profile_image) VALUES ('$name', '$email', '$password', '$profile_image')";

        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
            header("Location: login.php"); // Redirect to login page after successful registration
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        // Real-time JavaScript validation for the form
        function validateName() {
            var name = document.forms["registerForm"]["name"].value;
            if (name == "") {
                document.getElementById('nameError').innerHTML = "Name must be filled out";
            } else {
                document.getElementById('nameError').innerHTML = "";
            }
        }

        function validateEmail() {
            var email = document.forms["registerForm"]["email"].value;
            var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            if (email == "") {
                document.getElementById('emailError').innerHTML = "Email must be filled out";
            } else if (!email.match(emailPattern)) {
                document.getElementById('emailError').innerHTML = "Please enter a valid email address";
            } else {
                document.getElementById('emailError').innerHTML = "";
            }
        }

        function validatePassword() {
            var password = document.forms["registerForm"]["password"].value;
            if (password == "") {
                document.getElementById('passwordError').innerHTML = "Password must be filled out";
            } else {
                document.getElementById('passwordError').innerHTML = "";
            }
        }

        function validateConfirmPassword() {
            var password = document.forms["registerForm"]["password"].value;
            var confirmPassword = document.forms["registerForm"]["confirm_password"].value;
            if (confirmPassword == "") {
                document.getElementById('confirmPasswordError').innerHTML = "Confirm Password must be filled out";
            } else if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').innerHTML = "Passwords do not match";
            } else {
                document.getElementById('confirmPasswordError').innerHTML = "";
            }
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
				<a href="index.php">Home</a>
				<a href="login.php">Log in</a>
			</div>
		</header>
        <h2>Register</h2>
        <form name="registerForm" action="register.php" method="POST" enctype="multipart/form-data">
            <div>
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" placeholder="Name" required oninput="validateName()">
                <div id="nameError" class="error"></div>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" placeholder="Email" required oninput="validateEmail()">
                <div id="emailError" class="error"></div>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Password" required oninput="validatePassword()">
                <div id="passwordError" class="error"></div>
            </div>
            <div>
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required oninput="validateConfirmPassword()">
                <div id="confirmPasswordError" class="error"></div>
            </div>
            <div>
                <label for="profile_image">Profile Image:</label>
                <input type="file" name="profile_image" id="profile_image">
            </div>
            <button type="submit" name="register">Register</button>
			
			<p>Already have an account? <a href="login.php">Login</a></p>
        </form>

    </div>
</body>
</html>
