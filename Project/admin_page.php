<?php
@include 'config.php';
session_start();

date_default_timezone_set('America/New_York');

if (!isset($_SESSION['admin_name'])) {
    header('location: login_form.php');
    exit();
}

// Define the getAvailabilityForUser function
function getAvailabilityForUser($userName, $date, $conn) {
    $sql = "SELECT user_availability.shift_start, user_availability.shift_end, user_availability.is_available
            FROM user_availability
            INNER JOIN user_form ON user_availability.user_id = user_form.id
            WHERE user_form.name = ? AND user_availability.day_of_week = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $userName, $date);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if ($row['is_available'] === 'available') {
                $shiftStart = date('g:i A', strtotime($row['shift_start']));
                $shiftEnd = date('g:i A', strtotime($row['shift_end']));
                return $shiftStart . ' - ' . $shiftEnd;
            } elseif ($row['is_available'] === 'unavailable') {
                return 'Unavailable';
            }
        }
        return ''; // Return empty if no data is found
    } else {
        return "Error fetching availability";
    }
}


// Assuming you want to display availability for the current week
$today = date('Y-m-d');
$nextWeek = date('Y-m-d', strtotime('+13 days'));

// SQL query to retrieve user names
$sqlUsers = "SELECT DISTINCT user_form.name
             FROM user_availability
             INNER JOIN user_form ON user_availability.user_id = user_form.id
             WHERE user_availability.day_of_week BETWEEN ? AND ?";

// Prepare and execute the SQL query
if ($stmtUsers = $conn->prepare($sqlUsers)) {
    $stmtUsers->bind_param("ss", $today, $nextWeek);
    $stmtUsers->execute();
    $resultUsers = $stmtUsers->get_result();
}

$sqlUsers = "SELECT name FROM user_form";

// Prepare and execute the SQL query for users
if ($stmtUsers = $conn->prepare($sqlUsers)) {
    $stmtUsers->execute();
    $resultUsers = $stmtUsers->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <script src="https://kit.fontawesome.com/d9278020ed.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/notifications.css">
    <link rel="stylesheet" href="css/admin_page.css"> <!-- Add the CSS file for styling -->
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
</head>
<body>

<header class="header" style="text-align: right;">
    <ul class="menu">
        <li><a href="admin_user_page.php">Home</a></li>
        <li><a href="admin_user_details.php">Enter Shift Details</a></li>
        <li><a href="admin_view_full_schedule.php">View Full Schedule</a></li>
        <li><a href="admin_settings.php">Settings</a></li>
        <li><a href="admin_notifications.php">Notifications</a></li>
        <li><a href="logout.php">Logout</a></li>
        <div style="margin-left: auto; margin-right: 20px; color: white; font-weight: bold;"><?php echo $_SESSION['admin_name']; ?></div>
    </ul>
</header>

<div class="main-content">
    <h1>Employee Availability for the Current Week</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <!-- Date columns for the current week -->
                <?php
                $currentDate = strtotime($today);
                while ($currentDate <= strtotime($nextWeek)) {
                    echo '<th>' . date('Y-m-d', $currentDate) . '</th>';
                    $currentDate = strtotime('+1 day', $currentDate);
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($rowUsers = $resultUsers->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($rowUsers['name']) . '</td>';
                $currentDate = strtotime($today);
                while ($currentDate <= strtotime($nextWeek)) {
                    $date = date('Y-m-d', $currentDate);
                    $availability = getAvailabilityForUser($rowUsers['name'], $date, $conn);
    
                    echo '<td contenteditable="true" data-name="' . htmlspecialchars($rowUsers['name']) . '" data-date="' . $date . '">' . htmlspecialchars($availability) . '</td>';
                    $currentDate = strtotime('+1 day', $currentDate);
                }
                echo '</tr>';
            }

            ?>
        </tbody>
    </table class="table">

</div>

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

<script>
    document.querySelectorAll('td[contenteditable="true"]').forEach(cell => {
        cell.addEventListener('input', function () {
            const cellContent = cell.innerText.trim();
            const isNoShifts = cellContent.toLowerCase() === 'no shift';

            if (isNoShifts) {
                // Get the data attributes
                const name = cell.getAttribute('data-name');
                const date = cell.getAttribute('data-date');

                // Send a request to delete the shift
                fetch('admin_delete_shift.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ name, date }),
                })
                .then(response => response.json())
                .then(data => {
                    alert('Shift deleted!');
                    // Refresh the page after deleting the shift
                    window.location.reload();
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
            }
        });
    });
</script>


<script>
    document.querySelectorAll('td[contenteditable="true"]').forEach(cell => {
        cell.addEventListener('input', function () {
            const cellContent = cell.innerText.trim();
            const timePattern = /^(\d{1,2}:\d{2}) - (\d{1,2}:\d{2})$/;

            if (timePattern.test(cellContent)) {
                // Get the data attributes
                const name = cell.getAttribute('data-name');
                const date = cell.getAttribute('data-date');
                const [shiftStart, shiftEnd] = cellContent.split(' - ');

                // Send a request to create the shift
                fetch('admin_create_shift.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ name, date, shiftStart, shiftEnd }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Shift created!');
                        // Refresh the page after creating the shift
                        window.location.reload();
                    } else {
                        alert('Shift already exists for this date. Please delete existing shift first.');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
            }
        });
    });
</script>

</body>
</html>
