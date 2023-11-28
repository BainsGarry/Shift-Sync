<?php
// Include the 'config.php' file, suppressing errors if the file is not found
@include 'config.php';

// Start or resume a session
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_name'])) {
    header('location:login_form.php');
    exit();
}

// Check if the form is submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $user_id = $_SESSION['user_id'];
    $select_day = $_POST['select-day'] ?? '';
    $availability = $_POST['availability'] ?? '';
    $start_time = $_POST['start-time'] ?? '';
    $end_time = $_POST['end-time'] ?? '';
    $repeats = isset($_POST['repeats']) ? 1 : 0;
    $frequency = $_POST['repeat-frequency'] ?? '';

    // Establish a connection to the MySQL database
    $conn = mysqli_connect('localhost', 'root', '', 'user_db');
    if (!$conn) {
        die("Database connection error: " . mysqli_connect_error());
    }

    // Generate a list of dates to save based on the selected day and frequency
    $dates_to_save = [$select_day];
    if ($repeats && $frequency === 'week') {
        for ($i = 1; $i <= 3; $i++) {
            $nextDate = new DateTime($select_day);
            $nextDate->modify('+' . $i . ' week');
            array_push($dates_to_save, $nextDate->format('Y-m-d'));
        }
    }

    // Loop through the generated dates
    foreach ($dates_to_save as $date) {
        // Check if availability data already exists for the user and date
        $check_query = "SELECT * FROM user_availability WHERE user_id = ? AND day_of_week = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("is", $user_id, $date);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        // If data exists, update the record; otherwise, insert a new record
        if ($result->num_rows > 0) {
            $update_query = "UPDATE user_availability SET is_available = ?, shift_start = ?, shift_end = ?, repeats = ?, frequency = ? WHERE user_id = ? AND day_of_week = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sssisis", $availability, $start_time, $end_time, $repeats, $frequency, $user_id, $date);
            $update_stmt->execute();
        } else {
            $insert_query = "INSERT INTO user_availability (user_id, day_of_week, is_available, shift_start, shift_end, repeats, frequency) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("issssis", $user_id, $date, $availability, $start_time, $end_time, $repeats, $frequency);
            $insert_stmt->execute();
        }
    }

    // Close the database connection
    mysqli_close($conn);

    // Redirect to user_details.php after form submission
    header('location: user_details.php');
    exit();
} else {
    // Output an error message if the form is not submitted using the POST method
    echo "Form submission error.";
}
?>
