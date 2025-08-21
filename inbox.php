<?php
session_start();
include('includes/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get logged-in user's ID
$receiver_id = $_GET['receiver_id']; // Get receiver user ID from query parameter

// Fetch receiver's details
$sql = "SELECT * FROM users WHERE id='$receiver_id'";
$result = $conn->query($sql);
$receiver = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    // Insert the message into the database
    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ('$user_id', '$receiver_id', '$message')";
    if ($conn->query($sql) === TRUE) {
        // Message sent successfully
    } else {
        // Handle error
    }
}

// Fetch chat history
$chat_sql = "SELECT * FROM messages WHERE (sender_id='$user_id' AND receiver_id='$receiver_id') OR (sender_id='$receiver_id' AND receiver_id='$user_id') ORDER BY created_at ASC";
$chat_result = $conn->query($chat_sql);
?>

<style>
/* Basic styling */
:root {
    --primary-color: #395b90;
    --primary-dark: #0f5bcd;
    --secondary-color: #34a853;
    --accent-color: #4285f4;
    --background-color: #f5f7fa;
    --card-bg: #ffffff;
    --sent-msg-bg: #e3f2fd;
    --received-msg-bg: #f0f4c3;
    --text-primary: #202124;
    --text-secondary: #5f6368;
    --border-color: #dadce0;
    --shadow-color: rgba(0, 0, 0, 0.1);
    --border-radius: 12px;
    --spacing: 24px;
}

body {
    font-family: 'Roboto', sans-serif;
    background-color: #f5f7fa;
    color: var(--text-primary);
}

/* Header Section Styling */
header {
    background-color: #333;  /* Dark background */
    color: #fff;  /* White text */
    padding: 15px 20px;  /* Consistent padding */
    display: flex;  /* Flexbox for easy layout */
    justify-content: space-between;  /* Distribute logo and menu to the sides */
    align-items: center;  /* Vertically align items */
    border-bottom: 2px solid #444;  /* Subtle border for separation */
}

header .logo img {
    width: 50px;  /* Adjust logo size */
    height: 50px;
}

header .nav-menu {
    display: flex;
    gap: 20px;  /* Space between the nav links */
    font-weight: 600;
}

header .nav-menu a {
    color: white;  /* White text */
    text-decoration: none;
    transition: color 0.3s ease, transform 0.3s ease;
}

header .nav-menu a:hover {
    color: #4CAF50;  /* Green color on hover */
    transform: translateY(-2px);  /* Slight lift effect */
}

/* Mobile adjustments */
@media screen and (max-width: 768px) {
    header {
        padding: 10px 15px; /* Slightly reduced padding for mobile */
    }
    header .logo img {
        width: 40px; /* Reduce logo size on mobile */
        height: 40px;
    }
    header .nav-menu {
        gap: 10px; /* Reduce space between menu items */
    }
}

@media screen and (max-width: 480px) {
    header {
        flex-direction: column; /* Stack items vertically */
        text-align: left;  /* Align text to the left */
    }
    header .nav-menu {
        flex-direction: column; /* Stack nav links vertically */
        gap: 8px;
        margin-top: 10px; /* Space between the logo and menu */
    }
}

.chat-container {
    width: 90%;
    max-width: 600px;
    margin: 40px auto;
    background: var(--card-bg);
    border-radius: var(--border-radius);
    box-shadow: 0 12px 28px var(--shadow-color);
}

.chat-header {
    background: var(--primary-color);
    color: white;
    padding: var(--spacing);
    text-align: center;
}

.chat-icon {
    margin-right: 10px;
}

h1, h2 {
    margin: 0;
	text-align: center;
}

.chat-main {
    padding: var(--spacing);
    background: var(--background-color);
    border-top: 1px solid var(--border-color);
    max-height: 400px;
    overflow-y: auto;
}

.conversation {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message {
    display: flex;
    gap: 10px;
    max-width: 70%; /* Restricting the message width */
}

.message.sent {
    align-self: flex-end;
    text-align: right;
}

.message.received {
    align-self: flex-start;
    text-align: left;
}

.message-content {
    background: var(--sent-msg-bg);
    padding: 10px 15px;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    max-width: 100%;
    word-wrap: break-word;
}

.message.received .message-content {
    background: var(--received-msg-bg);
}

.message-time {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-top: 5px;
}

.message-form-container {
    padding: var(--spacing);
    background: var(--card-bg);
    border-top: 1px solid var(--border-color);
}

textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    resize: none;
    margin-bottom: 10px;
    font-size: 1rem;
}

button {
    width: 100%;
    padding: 12px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: var(--primary-dark);
}

/* Scrollbar styling */
.conversation::-webkit-scrollbar {
    width: 8px;
}

.conversation::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.conversation::-webkit-scrollbar-thumb {
    background: #c1c1c1;
}

.conversation::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($receiver['name']); ?></title>
</head>
<body>
	<!-- Header with Navigation Menu -->
	<header>
		<div class="logo">
			<img src="images/logo.png" alt="App Logo" width="50">
		</div>
		<div class="nav-menu">
			<a href="index.php">Home</a>
			<a href="account.php">My Account</a>
			<a href="logout.php">Logout</a>
		</div>
	</header>

    <div class="chat-container">
        <header class="chat-header">
            <h1><i class="chat-icon">💬</i>Chat: </h1>
            <?php if ($receiver): ?>
                <h2><span class="receiver-name"><?php echo htmlspecialchars($receiver['name']); ?></span></h2>
            <?php endif; ?>
        </header>

        <main class="chat-main">
            <div class="conversation" id="conversation">
                <?php if ($chat_result->num_rows > 0): ?>
                    <?php while ($message = $chat_result->fetch_assoc()): ?>
                        <div class="message <?php echo ($message['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                            <div class="message-content">
                                <p class="message-text"><?php echo htmlspecialchars($message['message']); ?></p>
                                <span class="message-time"><?php echo date('h:i A', strtotime($message['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <footer class="message-form-container">
            <form action="inbox.php?receiver_id=<?php echo $receiver_id; ?>" method="POST" class="message-form">
                <textarea name="message" placeholder="Type a message..." required></textarea>
                <button type="submit" class="send-button">Send</button>
            </form>
        </footer>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const conversation = document.getElementById("conversation");
            if (conversation) {
                conversation.scrollTop = conversation.scrollHeight;
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
