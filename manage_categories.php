<?php
//first start the session...
session_start();
include 'db.php'; //then include the databases connection file 

// Check if the user is logged in and is an admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header('Location: admin_login.php'); // Redirect to login if not logged in or not an admin
//     exit();
// }

// here we handle the category submission to store the categories
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the category name from the form and trim any extra spaces
    $category_name = trim($_POST['category_name']); // trim function will remve the trash

    // Create the SQL query to insert the category into the database
    $query = "INSERT INTO categories (name) VALUES ('$category_name')";

    // Execute the query
    if ($conn->query($query) === TRUE) {
        // If the query was successful, set a success message
        $message = "New category added successfully.";
    } else {
        // If there was an error, set an error message
        $message = "Error: " . $conn->error;
    }
}

// Handle category deletion
// Check if the 'delete_id' parameter is set in the URL
    if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Check if there are any recipes associated with this category
    $check_recipes_query = "SELECT COUNT(*) as count FROM recipes WHERE category_id = $delete_id";
    $result = $conn->query($check_recipes_query);
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $message = "Cannot delete category: There are recipes associated with this category.";
    } else {
        // Now delete the category
        $delete_category_query = "DELETE FROM categories WHERE id = $delete_id";

        if ($conn->query($delete_category_query) === TRUE) {
            $message = "Category deleted successfully.";
        } else {
            $message = "Error deleting category: " . $conn->error;
        }
    }
}
// Fetch existing categories
$categories_query = "SELECT * FROM categories";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Add your existing styles here */
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

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }
        h1,
        h2,
        h3 {
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

        /* .dropdown{
            color: white;
            display: flex;
        }
        .dropdown span{
            margin-right: 5px;
            font-size: 20px;
            align-content: center
        } */
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

        .sidebar {
            width: 250px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            position: fixed;
            height: 100%;
            z-index: 1000;
        }

        .sidebar img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }

        .sidebar h3 {
            margin-top: 0;
            color: white;
        }

        /* .sidebar img {
            width: 100px;
            height: auto;
            border-radius: 50%;
        } */

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 32px 0;
            display: flex;
            align-items: center;
            /* transition: 0.75s ease; */
            background-color: transparent;
            padding: 10px 10px;
            border-radius: 8px;
        }

        .sidebar ul li:hover {
            background-color: #e74c3c;

        }

        .sidebar ul li span {
            margin-right: 10px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        .sidebar ul li a:hover {
            /* color:  #c0392b; */
        }

        .main-content {
            margin: 23px 0;
            margin-left: 40px;
            margin-right: 30px;
            padding: 40px 60px;
            background-color: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        /* .add-recipe-button{
            background-color: royalblue;
            padding: 20px 40px;
            border: none;
            color: white;
            font-size: 10px;
        } */
        .add-recipe-button {
            width: 50%;
            justify-content: center;
            background-color: red;
            margin-left: 360px;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-flex;
            align-items: center;
        }

        .add-recipe-button:hover {
            background-color: red;
            /* Darker blue on hover */
        }

        .add-recipe-button:focus {
            outline: none;
            /* Remove outline on focus */
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.5);
            /* Add a focus ring */
        }

        .main-content-wrapper h2 {
            margin-bottom: 10px;
            margin-left: 15px;
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

        .action-butttons a {
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
            position: relative;
            bottom: 0;
            width: 100%;
            margin-top: 20px;
        }

        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 1001;
        }

        .popup-content {
            background: rgba(255, 255, 255, 0.9);
            /* padding: 20px; */
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .popup-content form input,
        .popup-content form textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .popup-content form button {
            background-color: #c0392b;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        /* .popup-content form button:hover {
            background-color: #e74c3c;
        } */

        .admin-info {
            margin-top: 40px;
            text-align: center;
            /* background: red; */
        }

        .admin-info img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border-radius: 50%;
        }

        .popup-content h2 {
            color: white;
            font-weight: 500;
        }

        form {
            width: 450px;
            height: auto;
            background-color: rgba(255, 255, 255, 0.13);
            /* position: absolute; */
            /* transform: translate(-50%, -50%); */
            /* top: 50%; */
            left: 50%;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 40px rgba(8, 7, 16, 0.6);
            padding: 100px 35px;
            /* display: flex; */
            margin: 0 auto;
            margin-bottom: 40px;

        }

        form * {
            font-family: 'Poppins', sans-serif;
            color: #ffffff;
            letter-spacing: 0.5px;
            outline: none;
            border: none;
        }
        form button{
            margin-top: 20px;
            border: none;
            background-color: red;
            padding: 15px 50px;
            width: 100%;
            border-radius: 8px;
            
        }
        form button:hover{
            background-color:  #c0392b;
            
        }
        form h2 {
            font-size: 32px;
            font-weight: 500;
            line-height: 42px;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 40px;
        }

        form p {
            margin-top: 20px;
            text-align: center;
        }

        input {
            display: block;
            height: 50px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.07);
            border-radius: 3px;
            /* padding: 0 10px; */
            margin-top: 20px;
            font-size: 14px;
            font-weight: 300;
        }
        ::placeholder{
            color: rgba(0, 0, 0, 0.8);
        }
        textarea {
            background-color: rgba(255, 255, 255, 0.13);
            margin-top: 70px;

        }

        ::placeholder {
            color: white;
        }

        .buttons-add {
            margin-top: 20px;
            padding: 0 80px;
            display: flex;
            justify-content: space-between;
        }

        .buttons-add :nth-child(1) {
            background-color: green;
        }

        .buttons-add :nth-child(2) {
            background-color: red;
        }


        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            padding: 20px;
            margin: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            flex: 1 1 calc(25% - 20px);
            display: flex;
            align-items: center;
        }

        .card-details {
            display: flex;
            justify-content: space-between;
        }

        .card-details span,
        h3 {
            margin-right: 10px;
            font-size: 20px;
        }

        .recipe-card {
            background-color: #3498db;
            /* Blue */
        }

        .user-card {
            background-color: #2ecc71;
            /* Green */
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
        .main-content-wrapper h2 {
            margin-bottom: 10px;
            margin-left: 15px;
            font-size: 35px;
            padding: 20px 0;
            text-align: center;
            border-radius: 8px;
            background: linear-gradient(250deg, #000000, #8B0000);
            margin-bottom: 50px;
        }
    </style>
</head>

<body>

    <header>
        <div class="navbar">
            <h1>Manage Categories</h1>
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
            <h3>User Info</h3>
            <img src="uploads/<?= htmlspecialchars($user['photo']) ?>" alt="User  Photo" class="user-photo">
            <p><?= htmlspecialchars($user['username']); ?></p>
        </div>
        <ul>
            <li><a href="user_dashboard.php"><span class="material-icons">dashboard</span> Dashboard</a></li>
            <li><a href="user_recipes.php"><span class="material-icons">fastfood</span> All Recipes</a></li>
            <li><a href="user_profile.php"><span class="material-icons">person</span> Profile</a></li>
        </ul>
    </div> -->

    <div class="main-content">
        <div class="main-content-wrapper">
            <h2>Category</h2>
            <!-- <div class="card-details">
                <div class="stat-card recipe-card">
                    <span class="material-icons stat-icon">local_dining</span>
                    <h3>Total Recipes</h3>
                    <p><?= $recipe_count ?></p>
                </div>
                <div class="stat-card user-card">
                    <span class="material-icons stat-icon">people</span>
                    <h3>Total Comments</h3>
                    <p><?= $comment_count ?></p>
                </div>
            </div>/.card-details -->
        </div><!-- /.main-content-wrapper -->

        <!-- <h2>Add New Recipe</h2>
        <button class="add-recipe-button" onclick="document.getElementById('addRecipePopup').style.display='flex'">Add
            Recipe</button>
 -->


        <div class="recipies-table-heading">
            <?php if (isset($message)) {
                echo "<p>$message</p>";
            } ?>

        </div><!-- /.recipies-table-heading -->
        <form action="manage_categories.php" method="POST">
            <h2>Add Category</h2>
            <label for="category_name">Category Name:</label>
            <input type="text" id="category_name" name="category_name" placeholder="Category Name" required>
            <button type="submit">Add Category</button>
        </form>
        <div class="recipies-table">
        <h2>Manage Categories</h2>

            <table>
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories_result->num_rows > 0) {
                        while ($category = $categories_result->fetch_assoc()) {
                            echo "<tr>
                        <td>" . htmlspecialchars($category['name']) . "</td>
                        <td class='action-butttons'>
                            <a class='edit-button' href='edit_category.php?id=" . $category['id'] . "'>Edit</a> 
                            <a  class='delete-button' href='manage_categories.php?delete_id=" . $category['id'] . "' onclick='return confirm(\"Are you sure you want to delete this category?\");'>Delete</a>
                        </td>
                      </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>No categories found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div><!-- /.recipies-table -->
    </div>
    <!-- 
    <footer>
        <p>&copy; <?= date("Y") ?> Digital Recipe System. All rights reserved.</p>
    </footer> -->


    <script>
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
        function openEditPopup(id, name, description, category, ingredients) {
            document.getElementById('editRecipeId').value = id;
            document.getElementById('editRecipeName').value = name;
            document.getElementById('editRecipeDescription').value = description;
            document.getElementById('editRecipeCategory').value = category;
            document.getElementById('editRecipeIngredients').value = ingredients;
            document.getElementById('editRecipePopup').style.display = 'flex';
        }
    </script>
</body>

</html>