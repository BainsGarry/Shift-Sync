<?php
@include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'));

    if (isset($data->scheduleData)) {
        $conn->begin_transaction(); // Start a transaction

        try {
            foreach ($data->scheduleData as $shift) {
                $userName = $shift->name;
                $date = $shift->date;
                $shiftDetails = $shift->shiftData;

                // Convert shift details into availability, start and end time
                list($availability, $shiftStart, $shiftEnd) = parseShiftDetails($shiftDetails);

                // Get user_id from user_form table
                $stmt = $conn->prepare("SELECT id FROM user_form WHERE name = ?");
                $stmt->bind_param("s", $userName);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows == 1) {
                    $userRow = $result->fetch_assoc();
                    $userId = $userRow['id'];

                    // Check if an availability record already exists
                    $checkStmt = $conn->prepare("SELECT id FROM user_availability WHERE user_id = ? AND day_of_week = ?");
                    $checkStmt->bind_param("is", $userId, $date);
                    $checkStmt->execute();
                    $checkResult = $checkStmt->get_result();

                    if ($checkResult->num_rows == 1) {
                        // Update existing record
                        $updateStmt = $conn->prepare("UPDATE user_availability SET is_available = ?, shift_start = ?, shift_end = ? WHERE user_id = ? AND day_of_week = ?");
                        $updateStmt->bind_param("sssis", $availability, $shiftStart, $shiftEnd, $userId, $date);
                        $updateStmt->execute();
                    } else {
                        // Insert new record
                        $insertStmt = $conn->prepare("INSERT INTO user_availability (user_id, day_of_week, is_available, shift_start, shift_end) VALUES (?, ?, ?, ?, ?)");
                        $insertStmt->bind_param("issss", $userId, $date, $availability, $shiftStart, $shiftEnd);
                        $insertStmt->execute();
                    }
                }
            }

            $conn->commit(); // Commit the transaction
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback(); // Rollback the transaction in case of error
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Invalid data received']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

function parseShiftDetails($shiftDetails) {
    // This function should parse the shiftDetails string and return availability, start time, and end time.
    // For example, it could look for keywords like 'available', 'unavailable', or time patterns.
    // This is a placeholder function. You need to implement this based on how shiftDetails are formatted.
    return ['available', '09:00:00', '17:00:00']; // Example return values
}
?>


