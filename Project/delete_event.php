<?php
@include 'config.php';

// Check if the event ID is provided via POST
if (isset($_POST['eventId'])) {
    // Get the event ID from POST data
    $eventId = $_POST['eventId'];

    // Create a database connection
    $conn = mysqli_connect('localhost', 'root', '', 'user_db');

    // Check if the database connection is successful
    if (!$conn) {
        http_response_code(500); // Internal Server Error
        die("Database connection error: " . mysqli_connect_error());
    }

    // Prepare and execute a query to delete the event by ID
    $query = "DELETE FROM user_availability WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $eventId);

    if (mysqli_stmt_execute($stmt)) {
        // Event deleted successfully
        echo "success";
    } else {
        // Error occurred while deleting
        http_response_code(500); // Internal Server Error
        echo "Error deleting event: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    // Event ID not provided in POST data
    http_response_code(400); // Bad Request
    echo "Event ID not provided.";
}
?>
