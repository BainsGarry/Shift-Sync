<?php
@include 'config.php';
session_start();

if (!isset($_SESSION['admin_name'])) {
    header('location:login_form.php');
}
?>

<!DOCTYPE html>
<html lang="en" class="<?= $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Schedule</title>
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@latest/main.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="css/view_full_schedule.css">
</head>
<body>

    <header class="header" style="text-align: right;">
        <ul class="menu">
            <li><a href="admin_user_page.php">Home</a></li>
            <li><a href="admin_user_details.php">Enter Shift Details</a></li>
            <li><a href="admin_settings.php">Settings</a></li>
            <li><a href="admin_notifications.php">Notifications</a></li>
            <li><a href="admin_page.php">Admin</a></li>
            <li><a href="logout.php">Logout</a></li>
            <div style="margin-left: auto; margin-right: 20px; color: white; font-weight: bold;"><?php echo $_SESSION['admin_name']; ?></div>
        </ul>
    </header>

    <!-- FullCalendar's container -->
    <div class="main-content">
        <div id='calendar'></div>
    </div>

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@latest/main.min.js'></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'get_events.php', // Specify the URL of your PHP script
        eventClick: function(info) {
            // Handle event click here
            var eventId = info.event.id; // Assuming you have an event ID

            // Send an AJAX request to delete the event from the database
            $.ajax({
                url: 'delete_event.php', // Replace with your delete script
                method: 'POST',
                data: { eventId: eventId },
                success: function(response) {
                    if (response === 'success') {
                        // Remove the event from the calendar
                        info.event.remove();
                    } else {
                        // Handle error
                        console.error('Error deleting event:', response);
                    }
                },
                error: function(xhr, status, error) {
                    // Handle AJAX error
                    console.error('AJAX error:', error);
                }
            });
        },
        eventRender: function(info) {
            // Check if the event should repeat weekly
            if (info.event.extendedProps.repeats === 1) {
                // Calculate the end date for the recurring event (1 week later)
                var endDate = new Date(info.event.start);
                endDate.setDate(endDate.getDate() + 7);

                // Clone the event and set the recurring end date
                var recurringEvent = {
                    title: info.event.title,
                    start: info.event.start,
                    end: endDate,
                };

                // Render the recurring event
                info.el.fullCalendar('renderEvent', recurringEvent);
            }
        }
    });
    calendar.render();
});

</script>

</body>
</html>