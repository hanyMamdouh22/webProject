<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "bookings"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && !empty($_POST['email']) && isset($_POST['room_id']) && !empty($_POST['room_id'])) {
        $email = htmlspecialchars($_POST['email']);
        $room_id = htmlspecialchars($_POST['room_id']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "<p class='alert'>Invalid email format!</p>";
        } else {
            $stmt_check = $conn->prepare("SELECT room_id FROM forAdmin WHERE email = ? AND room_id = ?");
            $stmt_check->bind_param("si", $email, $room_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $stmt_delete_admin = $conn->prepare("DELETE FROM forAdmin WHERE email = ? AND room_id = ?");
                $stmt_delete_admin->bind_param("si", $email, $room_id);

                $stmt_delete_reservations = $conn->prepare("DELETE FROM reservations WHERE national_id IN 
                    (SELECT national_id FROM forAdmin WHERE email = ? AND room_id = ?)");
                $stmt_delete_reservations->bind_param("si", $email, $room_id);

                if ($stmt_delete_admin->execute() && $stmt_delete_reservations->execute()) {
                    $message = "<p class='alert success'>Booking successfully canceled!</p>";
                } else {
                    $message = "<p class='alert error'>Error canceling booking: " . $conn->error . "</p>";
                }

                $stmt_delete_admin->close();
                $stmt_delete_reservations->close();
            } else {
                $message = "<p class='alert error'>No booking found with this email and room ID!</p>";
            }

            $stmt_check->close();
        }
    } else {
        $message = "<p class='alert error'>Email and Room ID are required!</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking</title>
    <style>
    body {
        font-family: 'Poppins', Arial, sans-serif;
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #44628b, #2C4A6B); 
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        flex-direction: column;
    }

    form {
        background: rgba(255, 255, 255, 0.1);         
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        max-width: 400px;
        width: 100%;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #FFD700;
    }

    label {
        display: block;
        font-weight: bold;
        margin-bottom: 8px;
        color: #FFD700;
    }

    input[type="text"] {
        width: 95%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #2C4A6B;
        border-radius: 5px;
        background-color: #2C4A6B;
        color: #fff;
    }

    input[type="text"]::placeholder {
        color: #FFD700;
    }

    button {
        width: 100%;
        padding: 10px;
        background-color: #FFD700;
        color: #2C4A6B;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    input[type="text"]:focus {
        border: 1px solid #FFD700; 
        outline: none; 
    }

    .alert {
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
        text-align: center;
        width: 80%;
        max-width: 500px;
        font-size: 18px;
    }

    .success {
        background-color: #4CAF50;
    }

    .error {
        background-color: #D8000C;
    }
</style>

    <script>
    window.history.pushState(null, document.title, window.location.href);
    window.addEventListener('popstate', function () {
        window.location.href = 'start.php';
    });
</script>

</head>
<body>
    <form action="" method="POST">
        <h2>Cancel Booking</h2>
        <label for="email">Email</label>
        <input type="text" id="email" name="email" required placeholder="example@example.com">

        <label for="room_id">Room ID</label>
        <input type="text" id="room_id" name="room_id" required placeholder="Enter Room ID">

        <button type="submit">Cancel Booking</button>
    </form>
    
    <?php if (!empty($message)) echo $message; ?>
</body>
</html>
