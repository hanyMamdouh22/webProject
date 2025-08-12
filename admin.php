<?php
session_start();
// if (!isset($_SESSION['logged_in'])) {
//     header("Location: login.php");
//     exit();
// }

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'delete' && isset($_POST['id'])) {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("DELETE FROM forAdmin WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $stmt2 = $conn->prepare("DELETE FROM reservations WHERE id = ?");
                $stmt2->bind_param("i", $id);
                if ($stmt2->execute()) {
                    echo "<p class='message success'>Record deleted successfully.</p>";
                } else {
                    echo "<p class='message error'>Error deleting record: " . $conn->error . "</p>";
                }
                $stmt2->close();
            }
            else {
                echo "<p class='message error'>Error deleting record: " . $conn->error . "</p>";
            }
            $stmt->close();
        }

        else if ($action === 'add' && isset($_POST['customer_name'], $_POST['branch'], $_POST['room_type'], $_POST['cost'], $_POST['people'], $_POST['check_in'], $_POST['check_out'])) {
            $name = htmlspecialchars($_POST['customer_name']);
            $national_id = htmlspecialchars($_POST['national_id']);
            $branch = htmlspecialchars($_POST['branch']);
            $room_type = htmlspecialchars($_POST['room_type']);
            $cost = floatval($_POST['cost']);
            $people = intval($_POST['people']);
            $new_check_in = htmlspecialchars($_POST['check_in']);
            $new_check_out = htmlspecialchars($_POST['check_out']);
            $email = htmlspecialchars($_POST['email']);

            if (empty($name) || empty($national_id) || empty($branch) || empty($room_type) || empty($cost) || empty($people) || empty($new_check_in) || empty($new_check_out) || empty($email)) {
                echo "<p class='message error'>All fields are required.</p>";
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
                $room_id = $room['room_id'];
    
                $stmt_reservation_check = $conn->prepare("
                    SELECT room_id FROM forAdmin 
                    WHERE room_id = ? 
                    AND NOT (check_out <= ? OR check_in >= ?)");
                $stmt_reservation_check->bind_param("sss", $room_id, $new_check_in, $new_check_out);
                $stmt_reservation_check->execute();
                $result_reservation_check = $stmt_reservation_check->get_result();
    
                if ($result_reservation_check->num_rows == 0) {
                    $available_room_id = $room_id;
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
    
            $stmt1 = $conn->prepare("INSERT INTO reservations (name, national_id, people, check_in, check_out) 
                                    VALUES (?, ?, ?, ?, ?)");
            $stmt1->bind_param("sssss", $name, $national_id, $people, $new_check_in, $new_check_out);
    
            if ($stmt1->execute()) {
                $stmt2 = $conn->prepare("INSERT INTO forAdmin (customer_name, national_id, branch, room_id, room_type, cost, people, check_in, check_out, email) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("ssssssssss", $name, $national_id, $branch, $available_room_id, $room_type, $cost, $people, $new_check_in, $new_check_out, $email);
    
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
    }
}
$searchQuery = '';
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['search_national_id'])) {
    $searchQuery = htmlspecialchars($_GET['search_national_id']);
}
$sql = "SELECT * FROM forAdmin";
if (!empty($searchQuery)) {
    $sql .= " WHERE national_id LIKE '%" . $conn->real_escape_string($searchQuery) . "%'";
}
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #44628b, #2C4A6B);
        color: #333;
        overflow-x: hidden;
    }

    nav {
        background-color: rgba(10, 45, 72, 0.8);
        color: #ffffff;
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    nav a {
        color: #ffffff;
        text-decoration: none;
        margin-right: 20px;
        font-size: 20px;
    }

    nav a:hover {
        text-decoration: underline;
        text-decoration: none; 
        color: #ffeb3b;
    }

    .container {
        padding: 40px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        background-color: #ffffff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    table, th, td {
        border: 1px solid #e1e1e1;
    }

    th, td {
        padding: 11px;
        text-align: left;
        font-size: 16px;
    }

    th {
        background-color: #f2f2f2;
        color: #333;
    }

    button {
        padding: 10px 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);;
        border-radius:5px;
        background-color:rgba(10, 45, 72, 0.8);
        font-size: 18px;
        cursor: pointer;
        color: #ffffff;
        transition: background-color 0.3s ease;

    }

    button:hover {
        background-color: hsl(240, 100%, 27%);
    }

    form {
        margin-top: 40px;
        background: rgba(255, 255, 255, 0.1);
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        margin: 0 auto;
    }

    input[type="text"], input[type="number"], input[type="date"], input[type="email"], select {
        width: calc(100% - 20px);
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 18px;
        background-color: #f9f9f9;
        color: #333;
    }

    input[type="submit"] {
        width: 100%;
        padding: 12px;
        border: 1px solid #4CAF50;
        background-color: #4CAF50;
        color: #ffffff;
        font-size: 18px;
        cursor: pointer;
        border-radius: 4px;
        transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
        background-color: #45a049;
    }

    label {
        font-weight: bold;
        margin-bottom: 5px;
        display: inline-block;
    }
    form label{
    color: #FFD700;
    }
    .message {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
    }

    .message.success {
        background-color: #28a745;
        color: #ffffff;
    }

    .message.error {
        background-color: #dc3545;
        color: #ffffff;
    }

    select {
        background-color: #f9f9f9;
        color: #333;
    }
    h1{
        color:#FFD700;
    }

    @media (max-width: 768px) {
        nav {
            flex-direction: column;
            text-align: center;
        }

        nav a {
            margin-bottom: 10px;
        }

        .container {
            padding: 20px;
        }

        form {
            padding: 20px;
        }

        table {
            margin-top: 20px;
        }
    }
</style>

</head>
<body>
    <nav>
        <div>
            <a href="?page=dashboard">Dashboard</a>
            <a href="?page=manage">Manage</a>
        </div>
        <a href="logout.php">Log Out</a>
    </nav>
    <div class="container">
        <?php
        $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

        if ($page === 'dashboard') {
            echo "<h1>Dashboard</h1>";
            echo '<form method="GET" style="margin-bottom: 20px;">
                <input type="text" name="search_national_id" placeholder="Search by National ID" value="' . htmlspecialchars($searchQuery) . '">
                <button type="submit">Search</button>
            </form>';
            echo "<table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>National ID</th>
                        <th>Branch</th>
                        <th>Room ID</th>
                        <th>Room Type</th>
                        <th>Cost</th>
                        <th>People</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['customer_name']}</td>
                    <td>{$row['national_id']}</td>
                    <td>{$row['branch']}</td>
                    <td>{$row['room_id']}</td>
                    <td>{$row['room_type']}</td>
                    <td>{$row['cost']}</td>
                    <td>{$row['people']}</td>
                    <td>{$row['check_in']}</td>
                    <td>{$row['check_out']}</td>
                    <td>{$row['email']}</td>
                    <td>
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='action' value='delete'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <button type='submit'>Delete</button>
                        </form>
                    </td>
                </tr>";
            }
            echo "</tbody></table>";
        } elseif ($page === 'manage') {
            echo "<h1>Manage</h1>";
        echo '<form method="POST">
            <input type="hidden" name="action" value="add">
            <label>Customer Name:</label><input type="text" name="customer_name" required><br>
            <label>National ID:</label><input type="text" name="national_id" required><br>
            <label>Email:</label><input type="email" name="email" required><br>
            <label for="branch">Branch:</label>
            <select name="branch" id="branch" required>
                <option value="" disabled selected>Branch</option>
                <option value="sharm">SHARM</option>
                <option value="marsa_matrouh">MARSA MATROH</option>
                <option value="hurghada">HURGHADA</option>
                <option value="aswan">ASWAN</option>
                <option value="el_luxor">EL LUXOR</option>
                <option value="el_alamin">EL ALAMIN</option>
                <option value="el_giza">EL GIZA</option>
                <option value="cairo">CAIRO</option>
            </select><br>
            <label for="room_type">Room Type:</label>
            <select name="room_type" id="room_type" required>
                <option value="" disabled selected>Room Type</option>
                <option value="single">SINGLE ROOMS</option>
                <option value="double">DOUBLE ROOMS</option>
                <option value="suits">SUITS</option>
            </select><br>
            <label for="cost">Cost:</label>
            <select name="cost" id="cost" required>
                <option value="" disabled selected>Cost</option>
            </select><br>
            <label for="people">People:</label><input type="number" name="people" id="people" min="1" required><br>
            <label>Check-In Date:</label><input type="date" name="check_in" required><br>
            <label>Check-Out Date:</label><input type="date" name="check_out" required><br>
            <input type="submit" value="Add Record">
        </form>';
        }
        ?>
    </div>
    <script>

        const costOptions = {
            single: [
                { value: 150, text: "$150" },
                { value: 200, text: "$200" },
                { value: 300, text: "$300" },
                { value: 400, text: "$400" }
            ],
            double: [
                { value: 400, text: "$400" },
                { value: 500, text: "$500" },
                { value: 600, text: "$600" },
                { value: 800, text: "$800" }
            ],
            suits: [
                { value: 1000, text: "$1000" },
                { value: 1200, text: "$1200" },
                { value: 1400, text: "$1400" },
                { value: 1600, text: "$1600" }
            ]
        };

        const roomTypeSelect = document.getElementById('room_type');
        const costSelect = document.getElementById('cost');
        const peopleInput = document.getElementById('people');

        roomTypeSelect.addEventListener('change', function () {
            const selectedRoomType = roomTypeSelect.value;

            costSelect.innerHTML = '<option value="" disabled selected>Cost</option>';

            if (costOptions[selectedRoomType]) {
                costOptions[selectedRoomType].forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    costSelect.appendChild(opt);
                });
            }
        });

        peopleInput.addEventListener('input', function () {
            if (peopleInput.value < 1) {
                peopleInput.value = 1;
            }
        });
</script>

</body>
</html>