<?php
@include 'config.php';

session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['admin_name'])) {
    header('location:login_form.php');
    exit();
}

// Connect to database & error handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $select_day = $_POST['select-day'] ?? '';
    $availability = $_POST['availability'] ?? '';
    $start_time = $_POST['start-time'] ?? '';
    $end_time = $_POST['end-time'] ?? '';
    $repeats = isset($_POST['repeats']) ? 1 : 0;
    $frequency = $_POST['repeat-frequency'] ?? '';

    $conn = mysqli_connect('localhost', 'root', '', 'user_db');
    if (!$conn) {
        die("Database connection error: " . mysqli_connect_error());
    }

    // Repeat availability over multiple weeks (if it's reoccuring)
    $dates_to_save = [$select_day];
    if ($repeats && $frequency === 'week') {
        for ($i = 1; $i <= 3; $i++) {
            $nextDate = new DateTime($select_day);
            $nextDate->modify('+' . $i . ' week');
            array_push($dates_to_save, $nextDate->format('Y-m-d'));
        }
    }

    // Save and/or modify availability
    foreach ($dates_to_save as $date) {
        $check_query = "SELECT * FROM user_availability WHERE user_id = ? AND day_of_week = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("is", $user_id, $date);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

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

    // Disconnect from database
    mysqli_close($conn);
    header('location: admin_user_details.php');
    exit();
} else {
    echo "Form submission error.";
}
?>
