<?php
@include 'config.php';
session_start();

if (!isset($_SESSION['admin_name'])) {
    header('location:login_form.php');
}

// Fetch notifications
$sql = "SELECT * FROM notifications ORDER BY created_at DESC";
$result = $conn->query($sql);

$notifications = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
} else {
    $notifications = [];
}

?>

<!DOCTYPE html>
<!-- Formatting -->
<html lang="en" class="<?= $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <script src="https://kit.fontawesome.com/d9278020ed.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>

<!-- Navigation menu -->
<header class="header" style="text-align: right;">
    <ul class="menu">
        <li><a href="admin_user_page.php">Home</a></li>
        <li><a href="admin_user_details.php">Enter Shift Details</a></li>
        <li><a href="admin_view_full_schedule.php">View Full Schedule</a></li>
        <li><a href="admin_settings.php">Settings</a></li>
        <li><a href="admin_page.php">Admin</a></li>
        <li><a href="logout.php">Logout</a></li>
        <div style="margin-left: auto; margin-right: 20px; color: white; font-weight: bold;"><?php echo $_SESSION['admin_name']; ?></div>
    </ul>
</header>

<!-- Notifications display -->
<div class="main-content">
    <?php foreach ($notifications as $notification): ?>
        <div class="notification" data-id="<?php echo $notification['id']; ?>">
            <div class="notification-content" onclick="viewNotification(this)">
                <p><?php echo htmlspecialchars($notification['message']); ?></p>
            </div>
        <div class="notification-actions">
            <i class="far fa-eye-slash" onclick="markAsViewed(this)"></i>
            <i class="fas fa-bookmark" onclick="toggleBookmark(this)"></i>
            <i class="fas fa-trash" onclick="confirmDelete(this)"></i>
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
    // Logic to remove "unread" marker
    element.classList.toggle('viewed');
}

function toggleBookmark(element) {
    // Logic to bookmark notification
    element.classList.toggle('bookmarked');
}

function confirmDelete(element) {
    // Logic to delete notification
    if (confirm('Are you sure you want to delete this notification?')) {
        element.parentElement.parentElement.remove();
    }
}
</script>

<script>
function confirmDelete(element) {
    // Function to delete notification + error handling
    if (confirm('Are you sure you want to delete this notification?')) {
        var notificationDiv = element.parentElement.parentElement;
        var notificationId = notificationDiv.getAttribute('data-id');

        fetch('delete_notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + notificationId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notificationDiv.remove(); // Remove the notification from the page
            } else {
                alert(data.error); // Show error message
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}

</script>
</body>
</html>
