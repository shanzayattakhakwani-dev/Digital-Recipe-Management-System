<?php
//this is the file to connect php to database
include 'db.php';
$alert = "";
//this is for  the form to check request method post and then using name attribute values are getting for signup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    //this is control structures to check that the password  confirm match or not
    if ($password !== $confirm_password) {
        $alert = "Passwords do not match!";
    } else {
        //this is the password hash variable in which password become hash (plain text to long string)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); //This is a constant that tells the password_hash function to use the default hashing algorithm (currently BCRYPT i.e it is a password hashing algorithum) to hash the password.
        $photo = $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photo); //this line of code move the uploaded image to the upload folder in the directory
        $sql = "INSERT INTO users (username, first_name, last_name, password, photo) VALUES ('$username', '$first_name', '$last_name', '$hashed_password', '$photo')"; //this is the sql query that insert data from form to the database tablen of users
//this is the check for query execution
        if ($conn->query($sql) === TRUE) {
            $alert = "Signup successful! You can now login.";
            header("Location: user_login.php");
            exit();
        } else {
            $alert = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Sign Up Form</title>
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
            background-color: rgba(255, 255, 255, 0.13);
            position: absolute;
            transform: translate(-50%, -50%);
            top: 50%;
            left: 50%;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 40px rgba(8, 7, 16, 0.6);
            padding: 30px 35px;
        }

        form * {
            font-family: 'Poppins', sans-serif;
            color: #ffffff;
            letter-spacing: 0.5px;
            outline: none;
            border: none;
        }

        form h3 {
            font-size: 32px;
            font-weight: 500;
            line-height: 42px;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 40px;
        }

        form p {
            text-align: center;
            margin-top: 10px;
        }

        form p a {
            color: linear-gradient(#1845ad, #23a2f6);
        }

        label {
            display: block;
            margin-top: 20px;
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
            margin-top: 15px;
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
            margin-top: 30px;
            width: 100%;
            background-color: #ffffff;
            color: #080710;
            padding: 12px 0;
            font-size: 18px;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <h3>Sign Up</h3>

        <!-- <label for="username">Username</label> -->
        <input type="text" name="username" placeholder="Username" required id="username">

        <!-- <label for="first_name">First Name</label> -->
        <input type="text" name="first_name" placeholder="First Name" required id="first_name">

        <!-- <label for="last_name">Last Name</label> -->
        <input type=" text" name="last_name" placeholder="Last Name" required id="last_name">

        <!-- <label for="password">Password</label> -->
        <div class="password-container">
            <input type="password" name="password" placeholder="Password" required id="password">
            <i class="fas fa-eye toggle-password" onclick="togglePassword('password', this)"></i>
        </div>

        <!-- <label for="confirm_password">Confirm Password</label> -->
        <div class="password-container">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required
                id="confirm_password">
            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password', this)"></i>
        </div>

        <!-- <label for="photo">Profile Picture</label> -->
        <input type="file" name="photo" required id="photo">

        <button type="submit">Sign Up</button>
        <p>Alreday have account?<a href="user_login.php">Login</a></p>
    </form>

    <script>
        //this is the toggle password that show the password visible or not
        function togglePassword(id, element) {
            const passwordField = document.getElementById(id);
            const passwordType = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', passwordType);
            element.classList.toggle('fa-eye');
            element.classList.toggle('fa-eye-slash');
        }
    </script>
</body>

</html>