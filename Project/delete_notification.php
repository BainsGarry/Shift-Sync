<?php
// Include the configuration file for database connection parameters
@include 'config.php';

// Start or resume a session
session_start();

// Check if the 'admin_name' session variable is set, which indicates an authenticated admin user
if (!isset($_SESSION['admin_name'])) {
    // If not set, return an error message in JSON format and exit the script
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

// Check if the 'id' POST variable is set, which should be the ID of the notification to delete
if (isset($_POST['id'])) {
    // Store the notification ID from POST data into a variable
    $notification_id = $_POST['id'];

    // Prepare a SQL statement for execution to delete the notification with the specified ID
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");

    // Bind the notification ID variable as an integer parameter to the prepared statement
    $stmt->bind_param("i", $notification_id);

    // Execute the prepared statement
    if ($stmt->execute()) {
        // If execution is successful, return a success message in JSON format
        echo json_encode(['success' => true]);
    } else {
        // If execution fails, return an error message in JSON format with the error from the statement
        echo json_encode(['error' => "Error deleting notification: " . $stmt->error]);
    }

    // Close the prepared statement to free resources
    $stmt->close();
} else {
    // If the 'id' POST variable is not set, return an error message in JSON format
    echo json_encode(['error' => 'No notification ID provided.']);
}
?>
