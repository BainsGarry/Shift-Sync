<?php
// Include the configuration file
@include 'config.php';

session_start();

date_default_timezone_set('America/New_York');

if (!isset($_SESSION['admin_name'])) {
    header('location: login_form.php');
    exit; // Add exit to stop script execution if not logged in
}



$user_id = $_SESSION['user_id']; // Make sure you have user_id in your session

// Fetch user details from the database
$sql = "SELECT name, email FROM user_form WHERE id = ?";
if($stmt = $conn->prepare($sql)){
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 1){
        $row = $result->fetch_assoc();
        $user_name = $row['name'];
        $user_email = $row['email'];
    } else {
        // Handle error - user not found
        $user_name = "Not found";
        $user_email = "Not found";
    }
    $stmt->close();
} else {
    // Handle SQL error
    $user_name = "Error";
    $user_email = "Error";
}

// Define the getAvailabilityForUser function
function getAvailabilityForUser($userName, $date, $conn) {
    $sql = "SELECT user_availability.shift_start, user_availability.shift_end, user_availability.is_available
            FROM user_availability
            INNER JOIN user_form ON user_availability.user_id = user_form.id
            WHERE user_form.name = ? AND user_availability.day_of_week = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $userName, $date);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if ($row['is_available'] === 'available') {
                $shiftStart = date('g:i A', strtotime($row['shift_start']));
                $shiftEnd = date('g:i A', strtotime($row['shift_end']));
                return $shiftStart . ' - ' . $shiftEnd;
            }
        }
        return ''; // Return empty for unavailable or no data
    } else {
        return "Error fetching availability";
    }
}


// Assuming you want to display availability for the current week
$today = date('Y-m-d');
$nextWeek = date('Y-m-d', strtotime('+6 days'));

// SQL query to retrieve user names
$sqlUsers = "SELECT DISTINCT user_form.name
             FROM user_availability
             INNER JOIN user_form ON user_availability.user_id = user_form.id
             WHERE user_availability.day_of_week BETWEEN ? AND ?";

// Prepare and execute the SQL query
if ($stmtUsers = $conn->prepare($sqlUsers)) {
    $stmtUsers->bind_param("ss", $today, $nextWeek);
    $stmtUsers->execute();
    $resultUsers = $stmtUsers->get_result();
}

$sqlUsers = "SELECT name FROM user_form";

// Prepare and execute the SQL query for users
if ($stmtUsers = $conn->prepare($sqlUsers)) {
    $stmtUsers->execute();
    $resultUsers = $stmtUsers->get_result();
}
?>

<!DOCTYPE html>
<html lang="en" class="<?= $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/user_page_style.css">
    <link rel="stylesheet" href="css/notifications.css">
    <link rel="stylesheet" href="css/admin_page.css"> <!-- Add the CSS file for styling -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@latest/main.min.css' rel='stylesheet' />
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="admin_user_details.php">Enter Shift Details</a></li>
            <li><a href="admin_view_full_schedule.php">View Full Schedule</a></li>
            <li><a href="admin_settings.php">Settings</a></li>
            <li><a href="admin_notifications.php">Notifications</a></li>
            <li><a href="admin_page.php">Admin</a></li>
            <li><a href="logout.php">Logout</a></li>
            <li>
                <a href="#" style="pointer-events: none; cursor: default;">
                    Name: <?= htmlspecialchars($user_name); ?>
                </a>
            </li>
            <li>
                <a href="#" style="pointer-events: none; cursor: default;">
                    Email: <?= htmlspecialchars($user_email); ?>
                </a>
            </li>
        </ul>
        
    </div>
    <!-- Displays all employee availabilities -->
    <div class="main-content">
        <h1>Employee Schedule for the Current Week</h1>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <?php
                    $currentDate = strtotime($today);
                    while ($currentDate <= strtotime($nextWeek)) {
                        echo '<th>' . date('Y-m-d', $currentDate) . '</th>';
                        $currentDate = strtotime('+1 day', $currentDate);
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($rowUsers = $resultUsers->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($rowUsers['name']) . '</td>';
                    $currentDate = strtotime($today);
                    while ($currentDate <= strtotime($nextWeek)) {
                        $date = date('Y-m-d', $currentDate);
                        $availability = getAvailabilityForUser($rowUsers['name'], $date, $conn);
            
                        echo '<td>' . ($availability ?: 'No Shift') . '</td>'; // Show 'Unavailable' for empty availability
                        $currentDate = strtotime('+1 day', $currentDate);
                    }
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

</div>


</body>
</html>
