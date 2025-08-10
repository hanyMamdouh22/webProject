<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "bookings"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo "<script>
            alert('Connection failed: " . $conn->connect_error ." ');
            window.history.back();
          </script>";
          exit;
} 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['room_type'], $_POST['cost'], $_POST['branch']) && !isset($_POST['name'])) {
        $room_type = htmlspecialchars($_POST['room_type']);
        $cost = htmlspecialchars($_POST['cost']);
        $branch = htmlspecialchars($_POST['branch']);
    } 
    elseif (isset($_POST['name'], $_POST['id'], $_POST['people'], $_POST['check-in'], $_POST['check-out'], $_POST['email'])) {
        $name = htmlspecialchars($_POST['name']);
        $id = htmlspecialchars($_POST['id']);
        $people = htmlspecialchars($_POST['people']);
        $new_check_in = htmlspecialchars($_POST['check-in']);
        $new_check_out = htmlspecialchars($_POST['check-out']);
        $cost = htmlspecialchars($_POST['cost']);
        $room_type = htmlspecialchars($_POST['room_type']);
        $branch = htmlspecialchars($_POST['branch']);
        $email = htmlspecialchars($_POST['email']);
        
        if (empty($name) || empty($id) || empty($people) || empty($new_check_in) || empty($new_check_out) || empty($email)) {
            echo "<script>
            alert('All fields are required!');
            window.history.back();
          </script>";
          exit;
        }

        if (strtotime($new_check_out) <= strtotime($new_check_in)) {
            echo "<script>
            alert('Check_out date is smaller than check_in date!');
            window.history.back();
          </script>";
          exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>
            alert('Invalid email format!');
            window.history.back();
          </script>";
          exit;
        }

        $current_date = date("Y-m-d");
        if (strtotime($new_check_in) < strtotime($current_date)) {
            echo "<script>
            alert('You cannot make a reservation in the past!');
            window.history.back();
          </script>";
          exit;
        }

        if ($room_type == "single" && $people > 2)
        {
            echo "<script>
            alert('The max number of people for $room_type is 2!');
            window.history.back();
          </script>";
          exit;
        }
        if ($room_type == "double" && $people > 4)
        {
            echo "<script>
            alert('The max number of people for $room_type is 4!');
            window.history.back();
          </script>";
          exit;
        }
        if ($room_type == "suits" && $people > 8)
        {
            echo "<script>
            alert('The max number of people for $room_type is 8!');
            window.history.back();
          </script>";
          exit;
        }

        $stmt_check = $conn->prepare("
            SELECT room_id FROM $branch
            WHERE room_type = ? 
            AND cost = ?");
        $stmt_check->bind_param("ss", $room_type, $cost);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        $available_room_id = -1;

        while ($room = $result_check->fetch_assoc()) {
            $rom_id = $room['room_id'];

            $stmt_reservation_check = $conn->prepare("
                SELECT room_id FROM forAdmin 
                WHERE room_id = ? 
                AND NOT (check_out <= ? OR check_in >= ?)");
            $stmt_reservation_check->bind_param("sss", $rom_id, $new_check_in, $new_check_out);
            $stmt_reservation_check->execute();
            $result_reservation_check = $stmt_reservation_check->get_result();

            if ($result_reservation_check->num_rows == 0) {
                $available_room_id = $rom_id;
                break; 
            }
        }

        if ($available_room_id == -1) {
            echo "<script>
            alert('There are no rooms with these specifications available at this time!');
            window.history.back();
          </script>"; 
          exit;
        }

        $stmt1 = $conn->prepare("INSERT INTO reservations (name, national_id, room_id, people, check_in, check_out) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("ssssss", $name, $id, $available_room_id, $people, $new_check_in, $new_check_out);

        if ($stmt1->execute()) {
            $stmt2 = $conn->prepare("INSERT INTO forAdmin (customer_name, national_id, branch, room_id, room_type, cost, people, check_in, check_out, email) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("ssssssssss", $name, $id, $branch, $available_room_id, $room_type, $cost, $people, $new_check_in, $new_check_out, $email);

            if ($stmt2->execute()) {
            } 
            else {
                echo "<script>
                alert('Error inserting into forAdmin: " . $stmt2->error . "');
                window.history.back();
                </script>";
                exit;
            }
            $stmt2->close();
        } else {
            echo "<script>
            alert('Error inserting into reservations: " . $stmt1->error . "');
            window.history.back();
             </script>";
             exit;
        }
        $stmt1->close();
    } else {
        echo "<script>
        alert('Invalid request!');
        window.history.back();
            </script>";
            exit;
    }
} else {
    echo "<script>
    alert('No data received.');
    window.history.back();
        </script>";
        exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Form</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
         url('wepphoto/image.webp') no-repeat center center/cover;
            color: #fff;
        }

        form {
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 15px;
            padding: 30px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            color: #33241a;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="text"], input[type="date"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.3s;
        }

        input[type="text"]:focus, input[type="date"]:focus {
            border-color:  #a99377;
            border-width: 5px;
            box-shadow: 0 0 8px #a99377(74, 74, 138, 0.4);
            outline: none;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
        }

        .table-button {
            flex: 1;
            padding: 14px;
            margin: 0 5px;
            border: none;
            border-radius: 8px;
            background: #33241a;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .table-button:hover {
            background: #a99377;
            transform: scale(1.05);
        }
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 80%;
            max-width: 400px;
            text-align: center;
            display: none;
        }

        .popup h3 {
            color: #4CAF50;
        }

        .popup p {
            color: #333;
        }

        .popup button {
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .popup button:hover {
            background-color: #e53935;
        }

        #qrCode {
            margin-top: 20px;
        }
        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background-color: #33241a;
            color: #fff;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #a99377;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        button[type="submit"]:active {
            transform: scale(1.03);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        button[type="submit"]:focus {
            outline: none;
            box-shadow: 0 0 8px #a99377;
        }
    </style>
       <script>
    window.addEventListener('popstate', function(event) {
        window.location.href = 'start.php';
    });


</script>

</head>
<body>
    <?php if (isset($room_type, $cost, $branch)): ?>
        <form id="bookingForm" action="form.php" method="POST">
            <h2>Complete Your Booking</h2>
            <input type="hidden" name="room_type" value="<?php echo $room_type; ?>">
            <input type="hidden" name="cost" value="<?php echo $cost; ?>">
            <input type="hidden" name="branch" value="<?php echo $branch; ?>">

            <label for="name">Name</label>
            <input type="text" id="name" name="name" required placeholder="Your name">
            
            <label for="email">Email</label>
            <input type="text" id="email" name="email" required placeholder="example@example.com" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Enter a valid email address">

            <label for="id">National ID</label>
            <input type="text" id="id" name="id" required placeholder="00000000000000" pattern="\d{14}" title="Enter a valid 14-digit National ID">

            <label for="people">Number of People</label>
            <input type="text" id="people" name="people" required placeholder="1" pattern="\d+" title="Enter a valid number">

            <label for="check-in">Check-in</label>
            <input type="date" id="check-in" name="check-in" required>

            <label for="check-out">Check-out</label>
            <input type="date" id="check-out" name="check-out" required>

            <button type="submit">Booking Now</button>
        </form>
    <?php endif; ?>

    <div id="successPopup" class="popup" style="display:none;">
    <h3>Booking Success</h3>
    <p id="successMessage">Your booking was successful!</p>
    <div id="qrCode"></div> 
    <button onclick="closePopup()">Close</button>
</div>


    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
    <?php if (isset($available_room_id) && $available_room_id != -1): ?>
        const successPopup = document.getElementById('successPopup');
        successPopup.style.display = 'block';

        const name = "<?php echo $name; ?>";
        const nationalId = "<?php echo $id; ?>";
        const people = "<?php echo $people; ?>";
        const checkIn = "<?php echo $new_check_in; ?>";
        const checkOut = "<?php echo $new_check_out; ?>";
        const room_id = "<?php echo $available_room_id; ?>"; 
        const uniqueID = new Date().getTime(); 

        const checkInFormatted = checkIn.split('T')[0];
        const checkOutFormatted = checkOut.split('T')[0];

        const qrText = `Booking Details:\nName: ${name}\nNational ID: ${nationalId}\nPeople: ${people}\nCheck-in: ${checkInFormatted}\nCheck-out: ${checkOutFormatted}\nRoom ID: ${room_id}\nBooking ID: ${uniqueID}`;
        
        QRCode.toDataURL(qrText, function (err, url) {
            if (err) {
                console.error('Error generating QR code:', err);
                return;
            }
            document.getElementById('qrCode').innerHTML = `<img src="${url}" alt="QR Code">`;
        });

        const successMessage = `تم الحجز بنجاح!\nرقم الغرفة: ${room_id}`;
        document.getElementById('successMessage').textContent = successMessage;

    <?php endif; ?>
});

function closePopup() {
    const successPopup = document.getElementById('successPopup');
    successPopup.style.display = 'none';
    window.location.href = 'start.php';
}

function reloadData() {
    const name = "<?php echo $name; ?>";
    const nationalId = "<?php echo $id; ?>";
    const people = "<?php echo $people; ?>";
    const checkIn = "<?php echo $new_check_in; ?>";
    const checkOut = "<?php echo $new_check_out; ?>";
    const room_id = "<?php echo $available_room_id; ?>"; 
    const uniqueID = new Date().getTime();

    const checkInFormatted = checkIn.split('T')[0];
    const checkOutFormatted = checkOut.split('T')[0];

    const qrText = `Booking Details:\nName: ${name}\nNational ID: ${nationalId}\nPeople: ${people}\nCheck-in: ${checkInFormatted}\nCheck-out: ${checkOutFormatted}\nRoom ID: ${room_id}\nBooking ID: ${uniqueID}`;

    QRCode.toDataURL(qrText, function (err, url) {
        if (err) {
            console.error('Error generating QR code:', err);
            return;
        }
        document.getElementById('qrCode').innerHTML = `<img src="${url}" alt="QR Code">`;
    });
}
</script>



</body>
</html>
