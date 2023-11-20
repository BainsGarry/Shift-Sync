<?php
@include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from the request
    $data = json_decode(file_get_contents('php://input'));

    $name = $data->name;
    $date = $data->date;
    $shiftStart = $data->shiftStart;
    $shiftEnd = $data->shiftEnd;

    // Check for an existing shift
    $checkSql = "SELECT * FROM user_availability 
                 WHERE user_id = (SELECT id FROM user_form WHERE name = ?) 
                 AND day_of_week = ?";

    if ($checkStmt = $conn->prepare($checkSql)) {
        $checkStmt->bind_param("ss", $name, $date);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            // Shift already exists for this date
            echo json_encode(['error' => 'Shift already exists for this date. Please delete existing shift first.']);
            exit();
        }
    } else {
        echo json_encode(['error' => 'Error checking for existing shift']);
        exit();
    }

    // Perform the SQL query to insert the new shift
    $sql = "INSERT INTO user_availability (user_id, day_of_week, is_available, shift_start, shift_end)
            VALUES (
                (SELECT id FROM user_form WHERE name = ?),
                ?,
                'available', 
                ?, 
                ?
            )";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $name, $date, $shiftStart, $shiftEnd);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
            exit();
        } else {
            echo json_encode(['error' => 'Error creating shift']);
            exit();
        }
    } else {
        echo json_encode(['error' => 'Error preparing statement']);
        exit();
    }
}
?>

