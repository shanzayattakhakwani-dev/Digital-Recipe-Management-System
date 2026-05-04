<?php
session_start();
//this is the database file in which databaseb is connected to the web page
include 'db.php';
//this line of code check that the  admin is logged in or not if not it redirect to admin login page to check
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_query = "SELECT first_name, last_name, photo FROM admins WHERE id = '$admin_id'"; //this is the select sql query to fetch first name lasst name ana image from the admin table to show up on a admin dashboard 
$result = $conn->query($admin_query); //the query is stored in a result variable
//here query execution is checking that using result variable
if (!$result) {
    die("Query failed: " . $conn->error);
}

$admin = $result->fetch_assoc(); ///used to get the information about admin
// recipe_count variable is counting the total number of recpies of all users using query...
$recipe_count = $conn->query("SELECT COUNT(*) AS total FROM recipes")->fetch_assoc()['total'];
//user_count variable is counting the total number of users throughout the system using query..
$user_count = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
//category_count is a variable counting total categories using query
$category_count = $conn->query("SELECT COUNT(*) AS total FROM categories")->fetch_assoc()['total'];
$comment_count = $conn->query("SELECT COUNT(*) AS total FROM comments")->fetch_assoc()['total'];

//here we are Handling form submission for editing a recipe
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_recipe'])) {
    $recipe_id = $_POST['recipe_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $ingredients = $_POST['ingredients'];

    // update_query is a variable that Update the recipe in the database
    $update_query = "UPDATE recipes SET name = '$name', description = '$description', category_id = '$category_id' WHERE id = '$recipe_id'";
    //this is control structure that checking the query execution 
    if ($conn->query($update_query) === TRUE) {
        // Update ingredients
        // First delete existing ingredients because we have to update them
        $delete_ingredients_query = "DELETE FROM ingredients WHERE recipe_id = '$recipe_id'";
        $conn->query($delete_ingredients_query);

        // Then insert new ingredients
        $ingredients_array = explode(',', $ingredients); //explode() is function that take 2 arguments first delimiter and 2nd is stirg to split them...
        foreach ($ingredients_array as $ingredient) {
            $ingredient = trim($ingredient); // Trim() is function use to remove the whitespace from data..
            $insert_ingredient_query = "INSERT INTO ingredients (recipe_id, ingredient) VALUES ('$recipe_id', '$ingredient')"; //this is the query to insert in to ingredients values..
            $conn->query($insert_ingredient_query);
        }

        // Redirect to the same page to avoid resubmission
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "<p>Error updating recipe: " . $conn->error . "</p>";
    }
}

// here we are handling the recipe deletion 
if (isset($_GET['id'])) { //first  check that the value exist in this case it is id...
    $recipe_id = $_GET['id'];

    // First delete associated comments because if comments are not deleted it provide error because foreign key constraints will ineract here... 
    $delete_comments_query = "DELETE FROM comments WHERE recipe_id = '$recipe_id'";
    $conn->query($delete_comments_query);

    // now delete the recipe from the database...
    $delete_recipe_query = "DELETE FROM recipes WHERE id = '$recipe_id'";

    if ($conn->query($delete_recipe_query) === TRUE) {
        // Also delete associated ingredients to avoid any clash of keys...
        $delete_ingredients_query = "DELETE FROM ingredients WHERE recipe_id = '$recipe_id'";
        $conn->query($delete_ingredients_query);

        // redirect to the same page to avoid resubmission
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "<p>Error deleting recipe: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
           background: linear-gradient(to right, #f8c8dc, #ffb3e6, #e2b3f5);
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
                background-position: 80% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        h1,
        h2,
        h3,
        h4 {
            color: white;
        }

       header {
    background: linear-gradient(135deg, #d48ca6, #d48ccc); /* Richer and more saturated gradient */
    color: #2b2b2b; /* Dark gray text for better contrast */
    padding: 20px 0;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); /* Stronger shadow for a more defined look */
    border-bottom: 3px solid rgba(0, 0, 0, 0.1); /* More pronounced border for separation */
}

/* Updated link color for bold contrast */
.navbar a {
    color: #1a1a1a; /* Almost black for maximum readability */
    text-decoration: none;
    font-weight: 600; /* Slightly bolder font weight */
    padding: 0 10px;
}

.navbar a:hover {
    color: #b3007a; /* Vibrant magenta hover effect */
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

        .sidebar h3 {
            margin-top: 0;
            color: white;
        }

        .sidebar img {
            width: 100px;
            height: auto;
            border-radius: 50%;
        }

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
            margin: 20px 0;
            margin-right: 50px;
            margin-left: 315px;
            padding: 20px 40px;
            background-color: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .main-content-wrapper h2 {
            margin-bottom: 10px;
            margin-left: 15px;
        }

        .recipies-table-heading h2 {
            margin-left: 15px;
        }

        .card-details {
            display: flex;
            justify-content: space-between;
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

        .recipe-card {
            background-color: #3498db;
        }

        .user-card {
            background-color: #2ecc71;
        }

        .category-card {
            background-color: #f39c12;
        }

        .comment-card {
            background-color: #e74c3c;
        }

        .stat-card h3 {
            margin: 0;
            color: #fff;
            flex: 1;
        }

        .stat-card p {
            font-size: 1.5em;
            margin: 0;
            flex: 1;
            text-align: right;
            color: #fff;
        }

        .stat-icon {
            font-size: 40px;
            color: #fff;
            margin-right: 20px;
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
        }

        thead {
            display: table-header-group;
        }

        tbody {
            display: block;
            max-height: 350px;
            overflow-y: auto;
        }

        tr {
            display: table;
            table-layout: fixed;
            width: 100%;
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
        }

        .edit-button:hover {
            background-color: #2980b9;
        }

        .delete-button {
            background-color: #e74c3c;
        }

        .delete-button:hover {
            background-color: #c0392b;
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

        input,
        textarea {
            display: block;
            height: 40px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.07);
            border-radius: 3px;
            padding: 0 10px;
            margin-top: 70px;
            font-size: 14px;
            font-weight: 300;
            outline: none;
            color: white;
        }

        ::placeholder {
            color: white;
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

        form {
            width: 450px;
            height: auto;
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

        .form h2 {
            font-size: 32px;
            font-weight: 500;
            line-height: 42px;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 40px;
        }

        .recipies-table-heading input {
            width: 98.5%;
            margin-top: 30px;
            background-color: transparent;
            border: none;
            border-bottom: 1px solid whitesmoke;
            border-radius: 0px;
            outline: none;
            color: white;
        }
    </style>
</head>

<body>
    <!-- ddit Recipe Popup -->
    <div id="editRecipePopup" class="popup">
        <div class="popup-content">
            <form method="POST" action="">
                <h2>Edit Recipe</h2>
                <input type="hidden" name="recipe_id" id="editRecipeId" spellcheck="true">
                <input type="text" name="name" id="editRecipeName" placeholder="Recipe Name" required spellcheck="true">
                <textarea name="description" id="editRecipeDescription" placeholder="Recipe Description"  required spellcheck="true"></textarea>
                <input type="text" name="category_id" id="editRecipeCategory" placeholder="Category ID" required spellcheck="true">
                <textarea name="ingredients" id="editRecipeIngredients" placeholder="Ingredients (comma separated)" required spellcheck="true"></textarea>
                <div class="buttons-add">
                    <button type="submit" name="edit_recipe">Update Recipe</button>
                    <button type="button" onclick="document.getElementById('editRecipePopup').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <header>
        <div class="navbar">
            <h1>Admin Dashboard</h1>
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

    <div class="sidebar">
        <div class="admin-info">
            <h3>Admin Info</h3>
            <img src="uploads/<?= htmlspecialchars($admin['photo']) ?>" alt="Admin Photo">
            <p><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></p>
        </div><!-- /.admin-info -->
        <ul>
            <li><a href="admin_dashboard.php"><span class="material-icons">dashboard</span> Dashboard</a></li>
            <!-- <li><a href="manage_recipes.php"><span class="material-icons">fastfood</span> Manage Recipes</a></li> -->
            <li><a href="manage_categories.php"><span class="material-icons">category</span> Manage Categories</a></li>
            <!-- <li><a href="manage_users.php"><span class="material-icons">people</span> Manage Users</a></li> -->
            <!-- <li><a href="moderate_comments.php"><span class="material-icons">comment</span> Moderate Comments</a></li> -->
            <li><a href="logout.php"><span class="material-icons">logout</span>Logout</a></li>
        </ul>
    </div>



    <div class="main-content">
        <div class="main-content-wrapper">
            <h2>Dashboard</h2>
            <div class="card-details">
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
                </div>
            </div><!-- /.card-details -->
        </div><!-- /.main-content-wrapper -->

        <div class="recipies-table-heading">
            <h2>Recent Recipes</h2>
            <input type="text" id="recipeFilter" placeholder="Search recipes by name of Category..."
                onkeyup="filterTable()" spellcheck="true" />

        </div><!-- /.recipies-table-heading -->
        <div class="recipies-table">
            <table>
                <thead>
                    <tr>
                        <th>User Photo</th>
                        <th>Recipe Name</th>
                        <th>Category</th>
                        <th>User</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // SQL query to fetch recent recipes with user information
                    $recent_recipes = $conn->query("
                    SELECT recipes.id, recipes.name, recipes.description, recipes.category_id, GROUP_CONCAT(ingredients.ingredient SEPARATOR ', ') AS ingredients, categories.name AS category, users.username, users.photo 
                    FROM recipes 
                    JOIN categories ON recipes.category_id = categories.id 
                    JOIN users ON recipes.user_id = users.id 
                    LEFT JOIN ingredients ON recipes.id = ingredients.recipe_id 
                    GROUP BY recipes.id 
                    ORDER BY recipes.id DESC 
                    LIMIT 10
                ");

                    if ($recent_recipes) {
                        while ($row = $recent_recipes->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><img src='uploads/" . htmlspecialchars($row['photo']) . "' alt='User  Photo' class='user-photo'></td>"; // User Photo
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>"; //htmlspecialchard() converts the special elements in to a html elements to avoid cross scripting attacks
                            echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td class='action-buttons'>
                                    <a href='#' class='edit-button' onclick=\"openEditPopup(" . $row['id'] . ", '" . htmlspecialchars($row['name']) . "', '" . htmlspecialchars($row['description']) . "', '" . htmlspecialchars($row['category_id']) . "', '" . htmlspecialchars($row['ingredients']) . "')\">
                                        <span class='material-icons'>edit</span> Edit
                                    </a>
                                    <a href='admin_dashboard.php?id=" . $row['id'] . "' class='delete-button' onclick='return confirm(\"Are you sure you want to delete this recipe?\");'>
                                        <span class='material-icons'>delete</span> Delete
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No recent recipes found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div><!-- /.recipies-table -->
    </div>

    <!-- <footer>
        <p>&copy; <?= date("Y") ?> Digital Recipe System. All rights reserved.</p>
    </footer> -->

    <script>
        // Function to open the edit popup and populate it with recipe data
        function openEditPopup(id, name, description, category, ingredients) {
            document.getElementById('editRecipeId').value = id;
            document.getElementById('editRecipeName').value = name;
            document.getElementById('editRecipeDescription').value = description;
            document.getElementById('editRecipeCategory').value = category;
            document.getElementById('editRecipeIngredients').value = ingredients;
            document.getElementById('editRecipePopup').style.display = 'flex';
        }

        //  javaScript to handle dropdown toggle
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
         //function to filter the recpies with in a table
         function filterTable() {
            const input = document.getElementById('recipeFilter');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('.recipies-table table');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) { // Skip the header row
                const cells = rows[i].getElementsByTagName('td');
                let match = false;

                for (let j = 0; j < cells.length; j++) {
                    if (cells[j]) {
                        const textValue = cells[j].textContent || cells[j].innerText;
                        if (textValue.toLowerCase().indexOf(filter) > -1) {
                            match = true;
                            break;
                        }
                    }
                }

                rows[i].style.display = match ? '' : 'none'; // Show or hide the row
            }
        }
    </script>
</body>

</html>