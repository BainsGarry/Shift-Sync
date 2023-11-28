<?php
@include 'config.php';

session_start();

if (!isset($_SESSION['user_name'])) {
    header('location:login_form.php');
    exit();
}

// Initialize variables for messages
$success = $error = '';

// Change password logic
if (isset($_POST['change_password'])) {
    $userId = $_SESSION['user_name']; // This should be the user's ID, not username.
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmNewPassword = $_POST['confirm_new_password'];

    // Fetch the existing password from the database
    $stmt = $conn->prepare("SELECT password FROM user_form WHERE name = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $dbPassword = $result['password'];

    if (password_verify($currentPassword, $dbPassword)) {
        if ($newPassword === $confirmNewPassword) {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE user_form SET password = ? WHERE name = ?");
            $updateStmt->bind_param("ss", $hashedPassword, $userId);
            if ($updateStmt->execute()) {
                $success = "Password updated successfully.";
            } else {
                $error = "An error occurred.";
            }
            $updateStmt->close();
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
    $stmt->close();
}

// Theme preference logic
if (isset($_POST['theme'])) {
    $_SESSION['theme'] = $_POST['theme'];
    // Optionally, save the theme preference to the database
}

?>

<!DOCTYPE html>
<html lang="en" class="<?= $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="css/settings.css">
</head>
<body>

<header class="header">
    <ul class="menu">
        <li><a href="user_page.php">Home</a></li>
        <li><a href="user_details.php">Enter Shift Details</a></li>
        <li><a href="view_full_schedule.php">View Full Schedule</a></li>
        <li><a href="notifications.php">Notifications</a></li>
        <li><a href="logout.php">Logout</a></li>
        <div style="margin-left: auto; margin-right: 20px; color: white; font-weight: bold;"><?php echo $_SESSION['user_name']; ?></div>
    </ul>
</header>

<div class="main-content">
    <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <h2>Change Password</h2>
        <input type="password" name="current_password" required placeholder="Current Password">
        <input type="password" name="new_password" required placeholder="New Password">
        <input type="password" name="confirm_new_password" required placeholder="Confirm New Password">
        <input type="submit" name="change_password" value="Change Password" class="btn-blue">
    </form>

    <form method="POST" action="">
        <h2>Theme Preference</h2>
        <select name="theme" onchange="this.form.submit()" class="toggle-switch">
            <option value="light" <?= ($_SESSION['theme'] ?? 'light') === 'light' ? 'selected' : ''; ?>>Light</option>
            <option value="dark" <?= ($_SESSION['theme'] ?? 'light') === 'dark' ? 'selected' : ''; ?>>Dark</option>
        </select>
    </form>

    <!-- Add forms for other settings below, e.g., personal information update, notification preferences, and display settings -->

</div>

<script src="settings.js"></script>
</body>
</html>
