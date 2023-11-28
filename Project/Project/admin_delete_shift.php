<?php
@include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from the request
    $data = json_decode(file_get_contents('php://input'));

    $name = $data->name;
    $date = $data->date;

    // Perform the SQL query to delete the shift
    $sql = "DELETE FROM user_availability WHERE user_id = (
        SELECT id FROM user_form WHERE name = ?
    ) AND day_of_week = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $name, $date);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
            exit();
        } else {
            echo json_encode(['error' => 'Error deleting shift']);
            exit();
        }
    } else {
        echo json_encode(['error' => 'Error preparing statement']);
        exit();
    }
}
?>
