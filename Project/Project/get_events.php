<?php
include 'config.php';

// Initialize an array to store events
$events = array();

// Get the user ID from the session or your authentication mechanism
session_start();
if (!isset($_SESSION['user_id'])) {
    // Handle the case when the user is not authenticated
    http_response_code(401); // Unauthorized
    exit();
}
$user_id = $_SESSION['user_id'];

// Create a database connection
$conn = mysqli_connect('localhost', 'root', '', 'user_db');

// Check if the database connection is successful
if (!$conn) {
    http_response_code(500); // Internal Server Error
    die("Database connection error: " . mysqli_connect_error());
}

// Query to select user's available shifts data with prepared statement
$query = "SELECT id, day_of_week, shift_start, shift_end, repeats FROM user_availability WHERE user_id = ? AND is_available = 'available'";
$stmt = mysqli_prepare($conn, $query);

// Bind the user ID as a parameter
mysqli_stmt_bind_param($stmt, "i", $user_id);

// Execute the query
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);

    // Fetch and format the data
    while ($row = mysqli_fetch_assoc($result)) {
        $start = $row['day_of_week'] . 'T' . $row['shift_start'];
        $end = $row['day_of_week'] . 'T' . $row['shift_end'];

        $event = array(
            'id' => $row['id'],
            'title' => 'Available',
            'start' => $start,
            'end' => $end,
            'repeats' => $row['repeats'],
        );

        // Add the event to the events array
        array_push($events, $event);
    }

    // Close the database connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Encode the events array as JSON
    echo json_encode($events);
} else {
    http_response_code(500); // Internal Server Error
    die("Query failed: " . mysqli_error($conn));
}

?>

