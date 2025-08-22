<?php 
session_start();
include('includes/db_connect.php');

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists in the database
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if the password matches
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: account.php"); // Redirect to account page after successful login
        } else {
            $login_error = "Invalid credentials.";
        }
    } else {
        $login_error = "User not found.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        // Function to display error message as a popup
        function showPopup(message) {
            let popup = document.createElement('div');
            popup.classList.add('popup-message');
            popup.innerHTML = message;
            document.body.appendChild(popup);

            // Automatically hide the popup after 3 seconds
            setTimeout(() => {
                popup.remove();
            }, 3000);
        }

        // Show popup if there's an error message
        <?php if (isset($login_error)): ?>
            window.onload = function() {
                showPopup("<?php echo $login_error; ?>");
            };
        <?php endif; ?>

        // Real-time JavaScript validation for the form
        function validateEmail() {
            var email = document.forms["loginForm"]["email"].value;
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
            var password = document.forms["loginForm"]["password"].value;
            if (password == "") {
                document.getElementById('passwordError').innerHTML = "Password must be filled out";
            } else {
                document.getElementById('passwordError').innerHTML = "";
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
                <a href="index.php">Home</a> |
				<a href="register.php">Register</a>
            </div>
        </header>
        <h2>Login</h2>

        <form name="loginForm" action="login.php" method="POST">
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
            <button type="submit" name="login">Login</button>
            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
        </form>
    </div>
</body>
</html>
