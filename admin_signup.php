<?php
//include the file to connect the database
include 'db.php';
//this is the variable for the alert message on success or failure
$alert = "";
//this is for  the form to check request method post and then using name attribute values are getting for signup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // this conditiob Check if passwords match or not
    if ($password !== $confirm_password) {
        $alert = "Passwords do not match!";
    } else {
        // this is the Hash password function that make password hash (convert plain text in to long string)   pasword-hash()
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $photo = $_FILES['photo']['name']; //this  is the permanent one 
        $photo_tmp = $_FILES['photo']['tmp_name']; //this is the temporary storage to store image

        // in this control structure the photo Move to the "uploads" directory....
        if (move_uploaded_file($photo_tmp, "uploads/" . $photo)) {
            // this is SQL statement that insert the signup form information to the database   (INSERT query)
            $sql = "INSERT INTO admins (username, first_name, last_name, password, photo) VALUES ('$username', '$first_name', '$last_name', '$hashed_password', '$photo')";

            // this is control structure that check the query means that Execute the query and check if data inserted or query fails
            if ($conn->query($sql) === TRUE) {
                $alert = "Signup successful! You can now login.";
                header("Location: admin_login.php");
                exit();
            } else {
                $alert = "Error: " . $conn->error;
            }
        } else {
            $alert = "Error uploading photo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Signup</title>
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
            /* Adjusted padding */
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
            margin-top: 30px;
            margin-bottom: 40px;
        }

        form p {
            margin-top: 20px;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 20px;
            /* Adjusted margin */
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
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        button:hover {
            background: linear-gradient(to right, #ff512f, #f09819);

        }
    </style>
</head>

<body>
    <!-- these are the circles behind the form -->
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <!-- signup form taking information from theb admin  -->
    <form method="POST" enctype="multipart/form-data">
        <h2>Admin Signup</h2>

        <input type="text" name="username" placeholder="Username" required>

        <input type="text" name="first_name" placeholder="First Name " required>

        <input type="text" name="last_name" placeholder="Last Name" required>

        <div class="password-container">
            <input type="password" name="password" placeholder="Password" required>
            <span class="toggle-password" onclick="togglePasswordVisibility('password')"><i
                    class="fas fa-eye"></i></span>
        </div>

        <div class="password-container">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <span class="toggle-password" onclick="togglePasswordVisibility('confirm_password')"><i
                    class="fas fa-eye"></i></span>
        </div>

        <input type="file" name="photo" required>
        <button type="submit">Signup</button>
        <p>Already have account? <a href="admin_login.php">Login</a></p>
    </form>

    <script>
        function togglePasswordVisibility(inputName) {
            const input = document.querySelector(`input[name="${inputName}"]`);
            const icon = input.nextElementSibling.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>