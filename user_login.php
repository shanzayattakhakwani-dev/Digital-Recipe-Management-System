<?php
session_start();
include 'db.php';
$alert = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, password FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            header("Location: user_dashboard.php");
            exit();
        } else {
            $alert = "Incorrect password!";
        }
    } else {
        $alert = "No user found with that username!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Login</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <style media="screen">
        *,
        *:before,
        *:after {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(to bottom right, #a8aaff, #d4a8ff, #f8b3ff);
height:100vh;
        }
        .background {
            width: 430px;
            height: 520px;
            position: absolute;
            transform: translate(-50%, -50%);
            left: 50%;
            top: 50%;
        }
        .background .shape {
            height: 200px;
            width: 200px;
            position: absolute;
            border-radius: 50%;
        }
        .shape:first-child {
            background: linear-gradient(#1845ad, #23a2f6);
            left: -80px;
            top: -80px;
        }
        .shape:last-child {
            background: linear-gradient(to right, #ff512f, #f09819);
            right: -30px;
            bottom: -80px;
        }
        form {
            width: 450px;
            height: 550px;
            background-color: rgba(255, 255, 255, 0.13);
            position: absolute;
            transform: translate(-50%, -50%);
            top: 50%;
            left: 50%;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 40px rgba(8, 7, 16, 0.6);
            padding: 100px 35px;
        }
        form * {
            font-family: 'Poppins', sans-serif;
            color: #ffffff;
            letter-spacing: 0.5px;
            outline: none;
            border: none;
        }
        form h2 {
            font-size: 32px;
            font-weight: 500;
            line-height: 42px;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 40px;
        }
        form p{
            margin-top: 20px;
            text-align: center;
        }
        label {
            display: block;
            margin-top: 20px; /* Adjusted margin */
            font-size: 16px;
            font-weight: 500;
        }
        input {
            display: block;
            height: 50px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.07);
            border-radius: 3px;
            padding: 0 10px;
            margin-top: 40px;
            font-size: 14px;
            font-weight: 300;
        }
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #ffffff;
        }
        ::placeholder {
            color: #e5e5e5;
        }
        button {
            margin-top: 40px;
            width: 100%;
            background-color: #ffffff;
            color: #080710;
            padding: 12px 0;
            font-size: 18px;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background:linear-gradient( #1845ad, #23a2f6);
            transition: 0.75s   ;
        }
    </style>
</head>
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <form method="POST">
        <h2>User Login</h2>

        <input type="text" name="username" placeholder="Username" required id="username">

        <div class="password-container">
            <input type="password" name="password" placeholder="Password" required id="password">
            <i class="fas fa-eye toggle-password" onclick="togglePassword('password', this)"></i>
        </div>

        <button type="submit">Login</button>
        <p>Don't have account? <a href="user_signup.php">SignUp</a></p>
    </form>

    <script>
        function togglePassword(id, element) {
            const passwordField = document.getElementById (id);
            const passwordType = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', passwordType);
            element.classList.toggle('fa-eye');
            element.classList.toggle('fa-eye-slash');
        }
    </script>
</body>
</html>