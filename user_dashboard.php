<?php
// Start the session to run PHP code
session_start();

// Include the database connection file
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$toastMessage = ''; // Variable to hold toast message
$toastColor = ''; // Variable to hold toast color

// Get the user id from session
$user_id = $_SESSION['user_id'];

// Fetch user name and photo
$user_query = "SELECT username, photo FROM users WHERE id = '$user_id'";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

// Fetch total recipes for the respective user
$recipe_count_query = "SELECT COUNT(*) as total_recipes FROM recipes WHERE user_id = '$user_id'";
$recipe_count_result = $conn->query($recipe_count_query);
$recipe_count = $recipe_count_result->fetch_assoc()['total_recipes'];

// Fetch total comments for the respective user
$comment_count_query = "SELECT COUNT(*) as total_comments FROM comments WHERE user_id = '$user_id'";
$comment_count_result = $conn->query($comment_count_query);
$comment_count = $comment_count_result->fetch_assoc()['total_comments'];

// Handle form submission for adding a recipe

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_recipe'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $ingredients = $_POST['ingredients'];

    $user_id = $_SESSION['user_id']; // Assuming the user is logged in

    // Check if all required fields are filled
    if (empty($name) || empty($description) || empty($category_id) || empty($ingredients)) {
        echo "Please fill in all fields!";
        exit;
    }

    // Check if a new category is being added
    if ($category_id == 'new_category' && !empty($_POST['new_category_name'])) {
        $new_category_name = $_POST['new_category_name'];

        // Insert the new category into the categories table
        $insert_category_query = "INSERT INTO categories (name) VALUES ('$new_category_name')";
        if ($conn->query($insert_category_query) === TRUE) {
            // Fetch the ID of the newly added category
            $category_id = $conn->insert_id; // Get the last inserted ID
        } else {
            echo "Error adding new category: " . $conn->error;
            exit;
        }
    }

    // Insert the recipe into the recipes table
    $insert_recipe_query = "INSERT INTO recipes (name, description, category_id, user_id) 
                            VALUES ('$name', '$description', '$category_id', '$user_id')";
    if ($conn->query($insert_recipe_query) === TRUE) {
        // Insert ingredients into the ingredients table
        $ingredients_array = explode(',', $ingredients); // Split ingredients by comma
        foreach ($ingredients_array as $ingredient) {
            $ingredient = trim($ingredient);
            $insert_ingredient_query = "INSERT INTO ingredients (recipe_id, ingredient) 
                                        VALUES (LAST_INSERT_ID(), '$ingredient')";
            $conn->query($insert_ingredient_query);
        }
        echo "Recipe added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}


// Handle form submission for editing a recipe
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_recipe'])) {
    $recipe_id = $_POST['recipe_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $ingredients = $_POST['ingredients'];

    // Check if category_id exists in categories table
    $category_check_query = "SELECT COUNT(*) as count FROM categories WHERE id = '$category_id'";
    $category_check_result = $conn->query($category_check_query);
    $category_check = $category_check_result->fetch_assoc();

    if ($category_check['count'] == 0) {
        $toastMessage = "The category does not exist!";
        $toastColor = "red";
    } else {
        // Update the recipe in the database
        $update_query = "UPDATE recipes SET name = '$name', description = '$description', category_id = '$category_id' 
                         WHERE id = '$recipe_id' AND user_id = '$user_id'";

        if ($conn->query($update_query) === TRUE) {
            // Delete existing ingredients to update them later
            $delete_ingredients_query = "DELETE FROM ingredients WHERE recipe_id = '$recipe_id'";
            $conn->query($delete_ingredients_query);

            // Insert new ingredients
            $ingredients_array = explode(',', $ingredients);
            foreach ($ingredients_array as $ingredient) {
                $ingredient = trim($ingredient);
                $insert_ingredient_query = "INSERT INTO ingredients (recipe_id, ingredient) 
                                             VALUES ('$recipe_id', '$ingredient')";
                $conn->query($insert_ingredient_query);
            }

            $toastMessage = "Recipe Edited successfully!";
            $toastColor = "green";
            header("Location: user_dashboard.php");
            exit();
        } else {
            $toastMessage = "Error updating recipe: " . $conn->error;
            $toastColor = "red";
        }
    }
}

// Handle recipe deletion
if (isset($_GET['id'])) {
    $recipe_id = $_GET['id'];

    // First, delete associated comments
    $delete_comments_query = "DELETE FROM comments WHERE recipe_id = '$recipe_id'";
    $conn->query($delete_comments_query);

    // Delete the recipe
    $delete_recipe_query = "DELETE FROM recipes WHERE id = '$recipe_id' AND user_id = '$user_id'";
    if ($conn->query($delete_recipe_query) === TRUE) {
        // Delete associated ingredients
        $delete_ingredients_query = "DELETE FROM ingredients WHERE recipe_id = '$recipe_id'";
        $conn->query($delete_ingredients_query);

        $toastMessage = "Recipe deleted successfully!";
        $toastColor = "green";
        header("Location: user_dashboard.php");
        exit();
    } else {
        $toastMessage = "Error deleting recipe: " . $conn->error;
        $toastColor = "red";
    }
}

// Fetch user recipes with ingredients to display them
$recipes_query = "
    SELECT 
        r.id, 
        r.name, 
        r.description, 
        c.name AS category, 
        GROUP_CONCAT(i.ingredient SEPARATOR ', ') AS ingredients,
        GROUP_CONCAT(DISTINCT co.rating SEPARATOR ', ') AS ratings,
        GROUP_CONCAT(DISTINCT co.comment SEPARATOR '; ') AS comments
    FROM 
        recipes r
    LEFT JOIN 
        categories c ON r.category_id = c.id
    LEFT JOIN 
        ingredients i ON r.id = i.recipe_id
    LEFT JOIN 
        comments co ON r.id = co.recipe_id
    WHERE 
        r.user_id = '$user_id'
    GROUP BY 
        r.id
";
$recipes_result = $conn->query($recipes_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Include Toastify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <!-- Include Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
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
            margin-left: 319px;
            margin-right: 30px;
            padding: 30px 60px;
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
        }

        .add-recipe-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.5);
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
        .recipies-table-heading input{
            width: 98.5%;
            margin-top: 30px;
            background-color: transparent;
            border: none;
            border-bottom: 1px solid whitesmoke;
            border-radius: 0px;
            outline: none;
            color: white;

        }

        ::placeholder{
            color: white;
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

        form p {
            margin-top: 20px;
            text-align: center;
        }

        input {
            display: block;
            height: 40px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.07);
            border-radius: 3px;
            padding: 0 10px;
            margin-top: 70px;
            font-size: 14px;
            font-weight: 300;
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
        }

        .user-card {
            background-color: #2ecc71;
        }
    </style>
</head>

<body>
    <!-- Edit Recipe Popup -->
    <div id="editRecipePopup" class="popup">
        <div class="popup-content">
            <form method="POST" action="">
                <h2>Edit Recipe</h2>
                <input type="hidden" name="recipe_id" id="editRecipeId" spellcheck="true">
                <input type="text" name="name" id="editRecipeName" placeholder="Recipe Name" required spellcheck="true">
                <textarea name="description" id="editRecipeDescription" placeholder="Recipe Description"
                    required spellcheck="true"></textarea>
                <input type="text" name="category_id" id="editRecipeCategory" placeholder="Category ID" required spellcheck="true">
                <textarea name="ingredients" id="editRecipeIngredients" placeholder="Ingredients (comma separated)"
                    required spellcheck="true"></textarea>
                <div class="buttons-add">
                    <button type="submit" name="edit_recipe">Update Recipe</button>
                    <button type="button"
                        onclick="document.getElementById('editRecipePopup').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <div id="addRecipePopup" class="popup">
        <div class="popup-content">
           <form method="POST" action="user_dashboard.php">
    <label for="name">Recipe Name:</label>
    <input type="text" id="name" name="name" required>

    <label for="description">Recipe Description:</label>
    <textarea id="description" name="description" required></textarea>

    <label for="category_id">Category:</label>
    <select id="category_id" name="category_id">
        <option value="">Select a Category</option>
        <?php
        // Fetch existing categories from the database
        $category_query = "SELECT id, name FROM categories";
        $category_result = $conn->query($category_query);

        // Display categories dynamically in the dropdown
        while ($category = $category_result->fetch_assoc()) {
            echo "<option value='" . $category['id'] . "'>" . $category['name'] . "</option>";
        }
        ?>
        <option value="new_category">Add a New Category</option>
    </select>

    <label for="new_category_name" id="new_category_label" style="display:none;">New Category Name:</label>
    <input type="text" id="new_category_name" name="new_category_name" style="display:none;">

    <label for="ingredients">Ingredients (comma separated):</label>
    <input type="text" id="ingredients" name="ingredients" required>

    <button type="submit" name="add_recipe">Add Recipe</button>
</form>

<script>
    // Show/hide the new category input based on selection
    document.getElementById('category_id').addEventListener('change', function () {
        var newCategoryInput = document.getElementById('new_category_name');
        var newCategoryLabel = document.getElementById('new_category_label');
        if (this.value == 'new_category') {
            newCategoryInput.style.display = 'block';
            newCategoryLabel.style.display = 'block';
        } else {
            newCategoryInput.style.display = 'none';
            newCategoryLabel.style.display = 'none';
        }
    });
</script>
        </div>
    </div>
    <header>
        <div class="navbar">
            <h1>User Dashboard</h1>
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
            <h3>User Info</h3>
            <img src="uploads/<?= htmlspecialchars($user['photo']) ?>" alt="User  Photo" class="user-photo">
            <p><?= htmlspecialchars($user['username']); ?></p>
        </div>
        <ul>
            <li><a href="user_dashboard.php"><span class="material-icons">dashboard</span> Dashboard</a></li>
            <li><a href="user_recipes.php"><span class="material-icons">fastfood</span> All Recipes</a></li>
            <li><a href="user_profile.php"><span class="material-icons">person</span> Profile</a></li>
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
                    <h3>Total Comments</h3>
                    <p><?= $comment_count ?></p>
                </div>
            </div><!-- /.card-details -->
        </div><!-- /.main-content-wrapper -->

        <h2>Add New Recipe</h2>
        <button class="add-recipe-button" onclick="document.getElementById('addRecipePopup').style.display='flex'">Add
            Recipe</button>



        <div class="recipies-table-heading">
            <h2>Your Recipes</h2>
            <input type="text" id="recipeFilter" placeholder="Search recipes by name of Category..." onkeyup="filterTable()" />
        </div><!-- /.recipies-table-heading -->


        <div class="recipies-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Recipe Name</th>
                        <th>Category</th>
                        <th>Ingredients</th>
                        <th>Ratings</th>
                        <th>Comments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($recipe = $recipes_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($recipe['id']); ?></td>
                            <td><?= htmlspecialchars($recipe['name']); ?></td>
                            <td><?= htmlspecialchars($recipe['category']); ?></td>
                            <td><?= htmlspecialchars($recipe['ingredients']); ?></td>
                            <td><?= htmlspecialchars($recipe['ratings'] ?? 'No ratings'); ?></td>
                            <td><?= htmlspecialchars($recipe['comments'] ?? 'No comments'); ?></td>
                            <td class="action-buttons">
                                <a href="#" class="edit-button"
                                    onclick="openEditPopup(<?= $recipe['id']; ?>, '<?= htmlspecialchars($recipe['name']); ?>', '<?= htmlspecialchars($recipe['description']); ?>', '<?= htmlspecialchars($recipe['category']); ?>', '<?= htmlspecialchars($recipe['ingredients']); ?>')">Edit</a>
                                <a href="user_dashboard.php?id=<?= $recipe['id']; ?>" class="delete-button"
                                    onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div><!-- /.recipies-table -->
    </div>
    <?php if ($toastMessage): ?>
        <script>
            Toastify({
                text: "<?php echo $toastMessage; ?>",
                duration: 2000,
                gravity: "top",
                position: 'right',
                backgroundColor: "<?php echo $toastColor; ?>",
                stopOnFocus: true
            }).showToast();
        </script>
    <?php endif; ?>
    <!-- 
    <footer>
        <p>&copy; <?= date("Y") ?> Digital Recipe System. All rights reserved.</p>
    </footer> -->


    <script>
        //this is js code to trigger the drop down in navbar..
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
        //this is popup code to pop the form on onclick event listner
        function openEditPopup(id, name, description, category, ingredients) {
            document.getElementById('editRecipeId').value = id;
            document.getElementById('editRecipeName').value = name;
            document.getElementById('editRecipeDescription').value = description;
            document.getElementById('editRecipeCategory').value = category;
            document.getElementById('editRecipeIngredients').value = ingredients;
            document.getElementById('editRecipePopup').style.display = 'flex';
        }
        function showSuccessToast() {
            Toastify({
                text: "Success! Your action was successful.",
                duration: 3000,
                // gravity: "top", 
                position: 'right',
                backgroundColor: "green",
                className: "toastify",
                stopOnFocus: true
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