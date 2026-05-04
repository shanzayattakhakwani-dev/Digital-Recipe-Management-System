<?php
//first we have to start the session...
session_start();
include 'db.php';

// first we have to check if the user is logged in or not...
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}
$toastMessage = ''; // Variable to hold toast message
//here we will handle the  form submission for ratings and comments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Loop through the submitted data for each recipe
    foreach ($_POST['submit'] as $recipe_id => $submit) {
        // Get the rating and comment for the specific recipe
        $rating = $_POST['rating'][$recipe_id] ?? null;
        $comment = $_POST['comment'][$recipe_id] ?? '';

        // Ensure rating and comment are not empty or null
        if ($rating !== null && $comment !== '') {
            $user_id = $_SESSION['user_id'];

            // Sanitize the comment to prevent SQL injection
            $comment = $conn->real_escape_string($comment);

            // Prepare the SQL query to insert the rating and comment
            $sql = "INSERT INTO comments (user_id, recipe_id, rating, comment) 
                    VALUES ($user_id, $recipe_id, $rating, '$comment')";

            // Execute the query
            if ($conn->query($sql) === TRUE) {
                $toastMessage = "Comment and Rating added successfully!";
                $toastColor = "green"; // Optional, if you want to show a success toast
            } else {
                $toastMessage = "Error: " . $conn->error;
                $toastColor = "red"; // Optional, if you want to show an error toast
            }
        } else {
            $toastMessage = "Please provide both a rating and a comment.";
            $toastColor = "red"; // Optional, if you want to show an error toast
        }
    }
}

// here we will fetch all recipes with their ratings and comments for the user dashboard
$sql = "
    SELECT 
        r.id, 
        r.name AS recipe_name, 
        r.description AS details, 
        u.username, 
        u.photo AS user_photo,
        GROUP_CONCAT(i.ingredient SEPARATOR ', ') AS ingredients,
        AVG(c.rating) AS average_rating, 
        GROUP_CONCAT(c.comment SEPARATOR '; ') AS comments
    FROM 
        recipes r
    JOIN 
        users u ON r.user_id = u.id
    LEFT JOIN 
        ingredients i ON r.id = i.recipe_id
    LEFT JOIN 
        comments c ON r.id = c.recipe_id
    GROUP BY 
        r.id
";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>All Recipes</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Include Toastify CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

<!-- Include Toastify JS -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
             background: linear-gradient(to bottom right, #a8aaff, #d4a8ff, #f8b3ff);
height:100vh;

            background-size: 200% 200%;
            animation: gradientShift 6s ease-in-out infinite;
            color: #fff;
        }

        /*         
        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 80% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        } */

        h1,
        h2,
        h3,
        h4 {
            color: white;
        }

        header {
            background-color: #000;
            color: #fff;
            padding: 10px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            align-items: center;
        }

        .navbar ul li {
            margin-left: 20px;
            position: relative;
        }

        .navbar ul li a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s;
        }

        .navbar ul li a:hover {
            color: #c0392b;
        }

        .dropdown {
            display: none;
            position: absolute;
            background-color: #000;
            min-width: 160px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .dropdown a {
            color: #fff;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown a:hover {
            /* background-color: #c0392b; */
        }

        .navbar ul li:hover .dropdown {
            display: block;
        }

        .main-content {
            margin: 20px 0;
            margin-right: 50px;
            margin-left: 60px;
            padding: 20px 40px;
            background-color: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .main-content-wrapper h2 {
            margin-bottom: 10px;
            margin-left: 15px;
            font-size: 35px;
            padding: 20px 0;
            text-align: center;
            border-radius: 8px;
            background: linear-gradient(250deg, #000000, #8B0000);
        }

        .recipies-table-heading h2 {
            margin-left: 15px;
        }

        .card-details {
            display: flex;
            justify-content: space-between;
        }



        .recipies-table {
            width: 100%;
            margin: 20px -8px;
            padding: 0px 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            /* border-radius: 8px; */
            /* overflow: hidden; Ensure rounded corners are visible */
        }

        thead {
            display: table-header-group;
            /* Ensure the header stays fixed */
        }

        tbody {
            display: block;
            /* Make tbody block to allow scrolling */
            max-height: 350px;
            /* Set the maximum height for the tbody */
            overflow-y: auto;
            /* Enable vertical scrolling */
        }

        tr {
            display: table;
            /* Ensure rows are displayed as table rows */
            table-layout: fixed;
            /* Ensure fixed layout for proper alignment */
            width: 100%;
            /* Ensure rows take full width */
        }

        th,
        td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        th {
            background-color: rgba(255, 255, 255, 0.2);
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .action-buttons a {
            color: #fff;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            margin-right: 5px;
            display: inline-flex;
            align-items: center;
            transition: background-color 0.3s;
        }

        .edit-button {
            background-color: #3498db;
            /* Blue */
        }

        .edit-button:hover {
            background-color: #2980b9;
            /* Darker Blue */
        }

        .delete-button {
            background-color: #e74c3c;
            /* Red */
        }

        .delete-button:hover {
            background-color: #c0392b;
            /* Darker Red */
        }

        footer {
            text-align: center;
            padding: 10px 0;
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            /* position: relative; */
            bottom: 0;
            width: 100%;
            margin-top: 100px;
        }

        footer p {
            margin-left: 20px;
        }

        .user-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .rating-input {
            width: 60px;
            /* Fixed width for the rating input */
            padding: 10px;
            /* Padding inside the input */
            border: none;

            border-bottom: 1px solid #ccc;
            /* Light gray border */
            /* border-radius: 4px; */
            /* Rounded corners */
            font-size: 16px;
            outline: none;
            /* Font size */
            background-color:transparent;
            /* Slightly transparent background */
            /* transition: border-color 0.3s; */
            /* Smooth transition for border color */
        }
::placeholder{
    color: white;
}
        .rating-input:focus {
            border-bottom-color: red;
            /* Change border color on focus */
        }

        .comment-input {
            width: 50%;
            flex: 1;
            /* Take up remaining space */
            padding: 10px;
            /* Padding inside the input */
            border: none;
            border-bottom: 1px solid #ccc;
            /* Light gray border */
            /* border-radius: 4px; */
            /* Rounded corners */
            font-size: 16px;
            /* Font size */
            /* margin-right: 10px; */
            /* Space between input and button */
            background-color: transparent;
            /* Slightly transparent background */
            transition: border-color 0.3s;
            /* Smooth transition for border color */
        }

        .comment-input:focus {
            border-color: #3498db;
            /* Change border color on focus */
            outline: none;
            /* Remove default outline */
        }

        .send-button {
            background-color: #c0392b;
            /* Red background */
            color: white;
            /* White text */
            border: none;
            /* No border */
            padding: 10px 15px;
            /* Padding inside the button */
            border-radius: 5px;
            /* Rounded corners */
            cursor: pointer;
            /* Pointer cursor on hover */
            font-size: 16px;
            /* Font size */
            transition: background-color 0.3s;
            /* Smooth transition for background color */
        }

        .send-button:hover {
            background-color: #a93226;
            /* Darker red on hover */
        }

        .comment-section {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .toastify {
            position: fixed; /* Fixed position to prevent scrolling */
            top: 300px; /* Adjust this value to set the distance from the top */
            right: 60px; /* Distance from the right */
            z-index: 9999; /* Ensure it appears above other elements */
        }
    </style>
</head>

<body>
  
    </div>
    <header>
        <div class="navbar">
            <h1>All Recpies</h1>
            <nav>
                <ul>
                    <li>
                        <a href="#" id="userDropdown"><span class="material-icons"> person</span></a>
                        <div class="dropdown">
                            <a href="logout.php"><span class="material-icons">logout</span> Logout</a>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- <div class="sidebar">
        <div class="admin-info">
            <h3>Admin Info</h3>
            <img src="uploads/<?= htmlspecialchars($admin['photo']) ?>" alt="Admin Photo">
            <p><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></p>
        </div>
        <ul>
            <li><a href="admin_dashboard.php"><span class="material-icons">dashboard</span> Dashboard</a></li>
            <li><a href="manage_recipes.php"><span class="material-icons">fastfood</span> Manage Recipes</a></li>
            <li><a href="manage_categories.php"><span class="material-icons">category</span> Manage Categories</a></li>
            <li><a href="manage_users.php"><span class="material-icons">people</span> Manage Users</a></li>
            <li><a href="moderate_comments.php"><span class="material-icons">comment</span> Moderate Comments</a></li>
        </ul>
    </div> -->



    <div class="main-content">
        <div class="main-content-wrapper">
            <h2>Dashboard</h2>
            <!-- <div class="card-details">
                <div class="stat-card recipe-card">
                    <span class="material-icons stat-icon">local_dining</span>
                    <h3>Total Recipes</h3>
                    <p><?= $recipe_count ?></p>
                </div>
                <div class="stat-card user-card">
                    <span class="material-icons stat-icon">people</span>
                    <h3>Total Users</h3>
                    <p><?= $user_count ?></p>
                </div>
                <div class="stat-card category-card">
                    <span class="material-icons stat-icon">category</span>
                    <h3>Total Categories</h3>
                    <p><?= $category_count ?></p>
                </div>
                <div class="stat-card comment-card">
                    <span class="material-icons stat-icon">comment</span>
                    <h3>Total Comments</h3>
                    <p><?= $comment_count ?></p>
                </div> -->
        </div><!-- /.card-details -->
        <div class="recipies-table-heading">
            <h2>All Recipes</h2>
        </div><!-- /.recipies-table-heading -->
        <div class="recipies-table">
        <form method="POST" action="">
    <div class="recipies-table">
        <table>
            <thead>
                <tr>
                    <th>User Photo</th>
                    <th>Username</th>
                    <th>Recipe Name</th>
                    <th>Details</th>
                    <th>Ingredients</th>
                    <th>Rating</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0) { //here we are using while loop to display recpies in the js
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><img src='uploads/" . htmlspecialchars($row['user_photo']) . "' alt='User  Photo' class='user-photo'></td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['recipe_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['details']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ingredients']) . "</td>";
                        echo "<td>
                            <input type='number' min='1' max='5' placeholder='Rate' class='rating-input' name='rating[" . $row['id'] . "]'>
                          </td>";
                        echo "<td>
                            <input type='text' placeholder='Leave a comment... '  class='comment-input' name='comment[" . $row['id'] . "]'>
                            <button type='submit' class='send-button' name='submit[" . $row['id'] . "]'>Send</button>
                          </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No recipes found!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div><!-- /.recipies-table -->
</form>
        </div><!-- /.recipies-table -->
    </div>
    </div><!-- /.main-content-wrapper -->
    <?php if ($toastMessage): ?>
        <script>
            Toastify({
                text: "<?php echo $toastMessage; ?>",
                duration: 2000,
                gravity: "top",
                position: 'right, 50%   ',
                backgroundColor: "<?php echo strpos($toastMessage, 'Error') !== false ? 'red' : 'green'; ?>",
                stopOnFocus: true
            }).showToast();
        </script>
    <?php endif; ?>



    <!-- <footer>
        <p>&copy; <?= date("Y") ?> Digital Recipe System. All rights reserved.</p>
    </footer> -->

    <script>
        // // Optional: Add JavaScript to handle dropdown toggle
        // document.getElementById('userDropdown').addEventListener('click', function (event) {
        //     event.preventDefault();
        //     const dropdown = this.nextElementSibling;
        //     dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        // });

        // // Close the dropdown if the user clicks outside of it
        // window.onclick = function (event) {
        //     if (!event.target.matches('#userDropdown')) {
        //         const dropdowns = document.getElementsByClassName("dropdown");
        //         for (let i = 0; i < dropdowns.length; i++) {
        //             dropdowns[i].style.display = "none";
        //         }
        //     }
        // }
        // Function to open the edit popup and populate it with recipe data
        function openEditPopup(id, name, description, category, ingredients) {
            document.getElementById('editRecipeId').value = id;
            document.getElementById('editRecipeName').value = name;
            document.getElementById('editRecipeDescription').value = description;
            document.getElementById('editRecipeCategory').value = category;
            document.getElementById('editRecipeIngredients').value = ingredients;
            document.getElementById('editRecipePopup').style.display = 'flex';
        }

        // Optional: Add JavaScript to handle dropdown toggle
        document.getElementById('userDropdown').addEventListener('click', function (event) {
            event.preventDefault();
            const dropdown = this.nextElementSibling;
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Close the dropdown if the user clicks outside of it
        window.onclick = function (event) {
            if (!event.target.matches('#userDropdown')) {
                const dropdowns = document.getElementsByClassName("dropdown");
                for (let i = 0; i < dropdowns.length; i++) {
                    dropdowns[i].style.display = "none";
                }
            }
        }
        function showSuccessToast() {
            Toastify({
                text: "Success! Your action was successful.",
                duration: 3000,
                // gravity: "top", 
                position: 'right', 
                backgroundColor: "green",
                className: "toastify",
                stopOnFocus: true // Prevents dismissing of toast on hover
            }).showToast();
        }

        function showWarningToast() {
            Toastify({
                text: "Warning! Please check your input.",
                duration: 3000,
                gravity: "top",
                position: 'right',
                className: "toastify",
                backgroundColor: "orange",
                stopOnFocus: true
            }).showToast();
        }
        function showErrorToast() {
            Toastify({
                text: "Error! Something went wrong.",
                duration: 3000,
                gravity: "top",
                position: 'right',
                className: "toastify",
                backgroundColor: "red",
                stopOnFocus: true
            }).showToast();
        }
    </script>
</body>

</html>