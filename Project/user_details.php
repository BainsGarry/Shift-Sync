<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_name'])) {
    header('location: login_form.php');
    exit();
}

// Define the page title
$pageTitle = "Shift Sync - Your Personal Shift Details";

// Initialize start and end time variables
$start_time = "";
$end_time = "";

// Check if the "Available to work" option is selected
if (isset($_POST['availability']) && $_POST['availability'] === 'available') {
    $start_time = $_POST['start-time'];
    $end_time = $_POST['end-time'];
} elseif (isset($_POST['availability']) && $_POST['availability'] === 'all-day') {
    $start_time = "00:00";
    $end_time = "23:59";
}
?>

<!DOCTYPE html>
<html lang="en" class="<?= $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="css/user_details.css">
    <script>
        //ensures the correct date is selected
        function validateDate() {
            const selectDayInput = document.getElementById('select-day');
            const selectedDate = new Date(selectDayInput.value);
            const minDate = new Date();
            minDate.setDate(minDate.getDate() + 5);

            if (selectedDate <= minDate) {
                alert('You are not allowed to enter availability for the current day and the next 6 days.');
                return false;
            }
            return true;
        }
        //allows user to enter availability
        function validateTimes() {
            const startTime = document.getElementById('start-time').value;
            const endTime = document.getElementById('end-time').value;
            const availability = document.querySelector('input[name="availability"]:checked').value;

            // Check if availability is 'available' and either start or end time is empty
            if (availability === 'available' && (startTime === '' || endTime === '')) {
                alert('Please enter both start and end times.');
                return false;
            }
            return true;
    }
    </script>
</head>
<body>
    <!-- taskbar at top of screen -->
    <header class="header" style="text-align: right;">
        <ul class="menu">
            <li><a href="user_page.php">Home</a></li>
            <li><a href="view_full_schedule.php">View Full Schedule</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="notifications.php">Notifications</a></li>
            <li><a href="logout.php">Logout</a></li>
            <div style="margin-left: auto; margin-right: 20px; color: white; font-weight: bold;"><?php echo $_SESSION['user_name']; ?></div>
        </ul>
    </header>
    <!-- general information and set up of page including the buttons -->
    <div class="main-content">
        <h1 class="shift-sync-title">Shift Sync</h1>
        <p class="tagline">Your Personal Shift Details</p>
        <form class="availability-form" action="save_availability.php" method="POST" onsubmit="return validateDate() && validateTimes()">
            <label for="select-day">Select Day</label>
            <input type="date" id="select-day" name="select-day">

            <label>
                <input type="radio" id="available-to-work" name="availability" value="available">
                Available to work
            </label>

            <label>
                <input type="radio" id="unavailable-to-work" name="availability" value="unavailable">
                Unavailable to work
            </label>

            <label for="start-time">Start Time</label>
            <input type="time" id="start-time" name="start-time" value="<?php echo $start_time; ?>">

            <label for="end-time">End Time</label>
            <input type="time" id="end-time" name="end-time" value="<?php echo $end_time; ?>">

            <label>
                <input type="checkbox" id="repeats" name="repeats">
                Repeats
            </label>

            <label for="repeat-frequency">Every</label>
            <select id="repeat-frequency" name="repeat-frequency">
                <option value="week">4 Weeks</option>
            </select>

            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

            <button type="submit" class="save-button">Save Preferences</button>
        </form>
    </div>
</body>
</html>
