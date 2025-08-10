<?php
session_start();
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "bookings";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = htmlspecialchars($_POST['username']);
    $pass = $_POST['password']; 

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");

    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (MD5($pass, $row['password'])) { 
            $_SESSION['username'] = $user;
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: admin.php"); 
            } else {
                header("Location: start.php"); 
            }
            exit;
        } else {
            $error = "Invalid username or password"; 
        }
    } else {
        $error = "Invalid username or password";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EGYPTION HOTEL</title>
    <link rel="icon" href="EGYPTION HOTEL.webp"/>
    <link rel="shortcut" href="EGYPTION HOTEL.webp"/>
    <link rel="apple-touch-icon" href="EGYPTION HOTEL.webp"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #44628b;
            overflow-x: hidden; 
        }

        header {
            width: 100%;
            height: 900px;
            padding: 5px;
            background: url(wepphoto/hotelpag1.jpg) no-repeat center center/cover;
            display: flex;
            color: #F0E68C;
            box-sizing: border-box;
        }

        header h1 {
            position: absolute;
            top: 20%;
            left: 30%;
            font-size: 40px;
            color: #FFD700;
        }

        nav {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 50px;
            background-color: rgba(10, 45, 72, 0.8);
        }

        .nav-icon {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            color: #FFDE00;
        }

        .nav-icon img {
            border-radius: 50%;
            margin-right: 30px;
            border: 3px solid #FFD700;
        }

        .nav-left {
            display: flex;
            gap: 20px;
            margin-right:20px;
        }

        .nav-a {
            text-decoration: none;
            color: white;
            font-weight: bold;
            font-size: 18px;
            transition: color 0.3s, transform 0.3s;
        }

        .nav-a:hover {
            transform: scale(1.1);
        }
        .sticky-nav {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            background-color: rgba(10, 45, 72, 0.7);
            color: white;
            padding: 10px 20px;
            z-index: 10;
        }

        .sticky-nav a {
            text-decoration: none;
            color: white;
            font-weight: bold;
            margin-right: 20px;
        }

        .sticky-nav a:hover {
            color: #FFD700;
        }

        main {
            padding: 20px;
            margin-top: 60px;
        }

        main h2, main h3 {
            text-align: center;
            color: #FFDE00;
        }

        .main-ul {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            padding: 0;
            list-style: none;
        }

        .branch {
            width: 22%;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            background-color: #44628b;
            border: 5px solid #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .branch:hover {
            transform: translateY(-15px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.3);
        }

        .branches-photo {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        

        .branches-title {
            font-size: 1rem;
            color: wheat;
            font-weight: bold;
            text-decoration: none;
        }

        .branches-title:hover {
            color: #FFD700;
        }

        footer {
            background-color: #2C2C2C;
            color: #F0E68C;
            text-align: center;
            padding: 30px 20px;
            box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.2);
        }

        footer a {
            color: #D3D3D3;
            text-decoration: none;
            font-weight: bold;
            margin: 0 10px;
            transition: color 0.3s;
        }

        footer a:hover{
            color: #FFD700;
            transform: scale(1.1);
        }
        .social a i{
            font-size: 2rem; 
        }
        footer p {
            font-size: 30px;
            margin-top: 15px;
            color: #D3D3D3;
            font-weight: bold;
        }

        footer .social,
        footer .subscribe,
          {
            margin-top: 20px;
        }

        footer .subscribe input {
            padding: 10px;
            border-radius: 5px;
            border: none;
            width: 250px;
            margin-right: 10px;
        }

        footer .subscribe button {
            padding: 10px 20px;
            background-color: #FF6347;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        footer .subscribe input:focus,
        footer .subscribe button:hover {
            outline: none;
            transform: scale(1.05);
        }
         
        .footer-login input[type="text"],
        .footer-login input[type="password"] {
            padding: 10px;
            margin: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 200px;
        }

        .footer-login button {
            padding: 10px 20px;
            background-color: #FF6347;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .footer-login button:hover {
            background-color: #FF4500;
        }
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #44628b;
        overflow-x: hidden;
    }

    header {
        height: 900px;
        background: url(wepphoto/hotelpag1.jpg) no-repeat center center/cover;
        display: flex;
        color: #F0E68C;
    }

    header h1 {
        position: absolute;
        top: 20%;
        left: 30%;
        font-size: 40px;
        color: #FFD700;
    }

    nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 50px;
        background-color: rgba(10, 45, 72, 0.8);
    }

    .nav-icon {
        display: flex;
        align-items: center;
        font-size: 24px;
        font-weight: bold;
        color: #FFDE00;
    }

    .nav-icon img {
        border-radius: 50%;
        margin-right: 30px;
        border: 3px solid #FFD700;
    }

    .nav-left {
        display: flex;
        gap: 20px;
        margin-right: 20px;
    }

    .nav-a {
        text-decoration: none;
        color: white;
        font-weight: bold;
        font-size: 18px;
        transition: transform 0.3s;
    }

    .nav-a:hover {
        transform: scale(1.1);
    }

    .sticky-nav {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        background-color: rgba(10, 45, 72, 0.7);
        padding: 10px 20px;
        z-index: 10;
    }

    main {
        padding: 20px;
        margin-top: 60px;
    }

    main h2, main h3 {
        text-align: center;
        color: #FFDE00;
    }

    .main-ul {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
        padding: 0;
        list-style: none;
    }

    .branch {
        width: 22%;
        text-align: center;
        overflow: hidden;
        border-radius: 15px;
        background-color: #44628b;
        border: 5px solid #fff;
        transition: transform 0.3s ease;
    }

    .branch:hover {
        transform: translateY(-15px);
    }

    .branches-photo {
        width: 100%;
        height: 250px;
        object-fit: cover;
        border-radius: 10px;
    }

    .branches-title {
        font-size: 1rem;
        color: wheat;
        font-weight: bold;
        text-decoration: none;
    }

    .branches-title:hover {
        color: #FFD700;
    }

    footer {
        background-color: #2C2C2C;
        color: #F0E68C;
        text-align: center;
        padding: 30px 20px;
    }

    footer a {
        color: #D3D3D3;
        text-decoration: none;
        font-weight: bold;
        margin: 0 10px;
    }

    footer a:hover {
        color: #FFD700;
    }

    .social a i {
        font-size: 2rem;
    }

    footer p {
        font-size: 30px;
        margin-top: 15px;
        color: #D3D3D3;
        font-weight: bold;
    }

    .footer-login input[type="text"],
    .footer-login input[type="password"] {
        padding: 10px;
        margin: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
        width: 200px;
    }

    .footer-login button {
        padding: 10px 20px;
        background-color: #FF6347;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .footer-login button:hover {
        background-color: #FF4500;
    }
</style>

        window.addEventListener('popstate', function () {
            if (document.referrer) {
                window.location.href = document.referrer;
            } else {
                window.location.href = 'start.php';
            }
        });
   
     
    </script>
</head>
<body>
    <header>
        <nav class="sticky-nav">
            <div class="nav-icon">
                <img src="EGYPTION HOTEL.webp" alt="sorry something went wrong" width="60px" height="60px">
                <span>EGYPTION HOTEL</span>
            </div>
            <div class="nav-left">
                <a href="start.php" class="nav-a">Home</a>
                <a href="abouthotel.html" class="nav-a">About</a>
                <a href="contactUs.html" class="nav-a">Contact Us</a>
                <a href="CancelBooking.php" class="nav-a">Cancel Booking</a>
            </div>
        </nav>
        <div>
            <h1>Welcome to Egyptian Hotel</h1>
        </div>
    </header>

    <main>
        <h2>Our branches around Egypt</h2>
        <h3>Please choose the branch you want to book in</h3>
        <ul class="main-ul">
            <li class="branch">
                <a class="branches-title" href="sharmroom.html">
                    <img class="branches-photo" src="wepphoto/sh2.jpg" alt="SHARM ELSHIKH">
                    SHARM ELSHIKH
                </a>
            </li>
            <li class="branch">
                <a class="branches-title" href="marsa-matrohroom.html">
                    <img class="branches-photo" src="wepphoto/hotel2.jpg" alt="MARSA MATROH">
                    MARSA MATROH
                </a>
            </li>
            <li class="branch">
                <a class="branches-title" href="horgadaroom.html">
                    <img class="branches-photo" src="wepphoto/hotel3.jpg" alt="HURGHADA">
                    HURGHADA
                </a>
            </li>
            <li class="branch">
                <a class="branches-title" href="aswanroom.html">
                    <img class="branches-photo" src="wepphoto/hotel4.jpeg" alt="ASWAN">
                    ASWAN
                </a>
            </li>
        
    </main>

    <footer>
        <div class="social">
            <p>Follow us on:</p>
            <a href="https://www.facebook.com"><i class="fab fa-facebook"></i></a>
            <a href="https://twitter.com"><i class="fab fa-twitter"></i></a>
            <a href="https://www.instagram.com"><i class="fab fa-instagram"></i></a>
            <a href="https://www.linkedin.com"><i class="fab fa-linkedin"></i></a>
        </div>
        <p>Connect with us for the best rates</p>

        <form method="POST" action="">
            <div class="footer-login">
                <input type="text" id="username" name="username" placeholder="Username" required>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button id="login-button" onclick="login()">Submit</button>
            </div>
        </form>
    </footer>
    <!-- <script>
        function login() {
            username = document.getElementById('username');
            password = document.getElementById('password');
            username.value = '';
            password.value = '';
        }
    </script> -->
</body>
</html>
