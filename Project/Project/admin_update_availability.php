<?php
@include 'config.php';

$input = json_decode(file_get_contents('php://input'), true);

foreach ($input as $update) {
    $name = $update['name'];
    $date = $update['date'];
    $availability = $update['availability'];

    // Check if availability is in the correct format
    $parts = explode(' - ', $availability);
    if (count($parts) === 2) {
        list($shiftStart, $shiftEnd) = $parts;

        // SQL to update the database
        $sql = "UPDATE user_availability INNER JOIN user_form ON user_availability.user_id = user_form.id 
                SET shift_start = ?, shift_end = ?
                WHERE user_form.name = ? AND user_availability.day_of_week = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $shiftStart, $shiftEnd, $name, $date);
            $stmt->execute();
        }
    } else {
        // Handle the error - incorrect format
        // You can log this error or take appropriate action
    }
}

echo json_encode(['status' => 'success']);
?>

