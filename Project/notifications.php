<?php
@include 'config.php';
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_name'])) {
    header('location:login_form.php');
}

// Fetch notifications from database
$sql = "SELECT * FROM notifications ORDER BY created_at DESC";
$result = $conn->query($sql);

$notifications = [];
// Check if there are any notifications
if ($result->num_rows > 0) {
     // Store each notification in an array
    while($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
} else {
    // If no notifications, keep the array empty
    $notifications = [];
}
?>

<!DOCTYPE html>
<html lang="en" class="<?= $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <script src="https://kit.fontawesome.com/d9278020ed.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>

<header class="header" style="text-align: right;">
    <ul class="menu">
        <li><a href="user_page.php">Home</a></li>
        <li><a href="user_details.php">Enter Shift Details</a></li>
        <li><a href="view_full_schedule.php">View Full Schedule</a></li>
        <li><a href="settings.php">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
        <div style="margin-left: auto; margin-right: 20px; color: white; font-weight: bold;"><?php echo $_SESSION['user_name']; ?></div>
    </ul>
</header>

<div class="main-content">
    <?php foreach ($notifications as $notification): ?>
        <div class="notification">
            <div class="notification-content" onclick="viewNotification(this)">
                <p><?php echo htmlspecialchars($notification['message']); ?></p>
            </div>
            <div class="notification-actions">
                <i class="far fa-eye-slash" onclick="markAsViewed(this)"></i>
                <i class="fas fa-bookmark" onclick="toggleBookmark(this)"></i>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($notifications)): ?>
        <p>No notifications to display.</p>
    <?php endif; ?>
</div>


<script>
function viewNotification(element) {
    // Logic to expand the notification to view more
    alert('Notification clicked!');
}

function markAsViewed(element) {
    // Toggle the 'viewed' class on the element
    element.classList.toggle('viewed');
}

function toggleBookmark(element) {
    // Toggle the 'bookmarked' class on the element
    element.classList.toggle('bookmarked');
}

function confirmDelete(element) {
    // Confirm before deleting a notification
    if (confirm('Are you sure you want to delete this notification?')) {
        element.parentElement.parentElement.remove();
    }
}
</script>

</body>
</html>
