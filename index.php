<?php
//include the database connection file
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />

    <title>Digital Recipe System</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
             background: linear-gradient(to bottom right, #a8aaff, #d4a8ff, #f8b3ff);
height:100vh;

            background-size: 200% 200%;
            animation: gradientShift 6s ease-in-out infinite;
            color: #333;
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
        h3,
        h4 {
            color: white;
        }

        a {
            color: white;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

       header {
    background: linear-gradient(135deg, #f8c8dc, #ffb3e6);
    color: #333; /* Darker color for contrast */
    padding: 20px 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Adds a soft shadow for depth */
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Optional: Change link color in navbar */
.navbar a {
    color: #333;
    text-decoration: none;
    font-weight: 500;
    padding: 0 10px;
}

.navbar a:hover {
    color: #ff69b4; /* Soft pink hover effect */
}

        .navbar h1 {
            margin: 0;
        }

        nav ul {
            list-style: none;
            padding: 0;
        }

        nav ul li {
            display: inline;
            margin-left: 20px;
        }
.hero {
    position: relative;
    background-image: url('https://cdn.culture.ru/images/44f9c8a1-a9c7-5da6-b7b7-cb57970aaf6c');
    background-size: cover;
    background-position: center;
    color: white;
    text-align: center;
    padding: 230px 20px;
    overflow: hidden;
    filter: brightness(0.9); /* Slightly dim the image */
}

/* Softer pastel overlay */
.hero::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        135deg,
        rgba(248, 200, 220, 0.6),
        rgba(255, 179, 230, 0.6),
        rgba(226, 179, 245, 0.6)
    );
    z-index: 1; /* Ensures the overlay is above the image but below the text */
}

/* Ensure text is above everything */
.hero > * {
    position: relative;
    z-index: 2;
}

        .hero h2 {
    background: linear-gradient(135deg, #000000, #8B0000, #ff4d4d); /* Adding a brighter red to the gradient */
    background-size: 200% 200%;
    animation: gradientShift 6s ease-in-out infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-align: center;
    font-size: 2.5rem;
    margin-top: 50px;
    position: relative;
    z-index: 3; /* Ensures text is above the overlay */
}

/* Gradient animation for smoother shift */
@keyframes gradientShift {
    0% {
        background-position: 200% 0;
    }
    50% {
        background-position: 0 100%;
    }
    100% {
        background-position: 200% 0;
    }
}

       

        .hero h2 {
            font-size: 2.5em;
            margin: 0;
        }

        .hero p {
            font-size: 1.2em;
        }

        .hero form {
            margin-top: 20px;
        }

        .hero input[type="text"] {
            padding: 10px;
            width: 300px;
            border: none;
            border-radius: 5px;
        }

        .hero button {
            padding: 10px 20px;
            background-color: #c0392b;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .hero button:hover {
            background-color: #000;
        }

        .featured-recipes {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .featured-recipes table {
            width: 100%;
            border-collapse: collapse;
        }

        .featured-recipes th,
        .featured-recipes td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .featured-recipes th {
            background-color: #f2f2f2;
        }

        .user-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }

        .rating-input {
            width: 60px;
        }

        footer {
            text-align: center;
            padding: 20px 0;
            background-color: #000;
            color: #fff;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        .all-functionalities-wrapper {
    color: #333; /* Darker text color for better readability */
}

.all-functionalities {
    background: linear-gradient(135deg, rgba(255, 179, 230, 0.8), rgba(226, 179, 245, 0.8));
    padding: 30px 60px;
    color: #333; /* Matches the theme's text color */
    border-radius: 10px; /* Adds rounded corners for a softer look */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
}
        .all-functionalities .photo {
            width: 100%;
        }

        .all-functionalities h2 {
    font-size: 30px;
    font-weight: 600;
    background: linear-gradient(270deg, #f8c8dc, #ffb3e6, #e2b3f5);
    padding: 20px 30px;
    border-radius: 8px;
    text-align: center;
    margin-top: 50px;
    margin-bottom: 10px;
    color: #333; /* Dark text for better contrast */
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Adds subtle depth */
}

        .all-functionalities .photo img {
            width: 100%;
            border: 1px solid white;
        }

        .functionality h3 {
            font-size: 30px;
        }

        .functionality p {
            font-size: 20px;
        }
    </style>
</head>

<body>
    <header>
        <div class="navbar">
            <h1>Digital Recipe System</h1>
            <nav>
                <ul class="navbar-items">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="user_signup.php">User Signup</a></li>
                    <li><a href="user_login.php">User Login</a></li>
                    <li><a href="admin_signup.php">Admin Signup</a></li>
                    <li><a href="admin_login.php">Admin Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <h2 data-aos="fade-up">Welcome to the Digital Recipe System</h2>
            <p data-aos="fade-up">Search, store, and share your favorite recipes!</p>
            
        </section>
        <section class="all-functionalities-wrapper">
            <div class="all-functionalities" data-aos="fade-up">
                <h2 data-aos="fade-up">Functionalities</h2>
                <div data-aos="fade-up" class="functionality">
                    <h3 data-aos="fade-up">Admin Signup</h3>
                    <p data-aos="fade-up">Admin can create account....</p>
                    <div data-aos="fade-up" class="photo">
                        <img src="admin.signup.png" alt="">
                    </div>
                </div><!-- /.functionality -->
            </div>
            <div data-aos="fade-up" class="all-functionalities">
                <div class="functionality">
                    <h3 data-aos="fade-up">Admin Login </h3>
                    <p data-aos="fade-up">Admin log in to acess his account....</p>
                    <div data-aos="fade-up" class="photo">
                        <img src="admin.login.png" alt="">
                    </div>
                </div><!-- /.functionality -->
            </div>
            <div class="all-functionalities">
                <div data-aos="fade-up" class="functionality">
                    <h3 data-aos="fade-up">Admin dashboard </h3>
                    <p data-aos="fade-up">Admin can access his dashboard....</p>
                    <div data-aos="fade-up" class="photo">
                        <img src="admin.dashboard.png" alt="">
                    </div>
                </div><!-- /.functionality -->
            </div>
            <div data-aos="fade-up" class="all-functionalities">
                <div data-aos="fade-up" class="functionality">
                    <h3 data-aos="fade-up">User Signup</h3>
                    <p data-aos="fade-up">User can signup to acess his account....</p>
                    <div data-aos="fade-up" class="photo">
                        <img src="user.signup.png" alt="">
                    </div>
                </div><!-- /.functionality -->
            </div>
            <div data-aos="fade-up" class="all-functionalities">
                <div data-aos="fade-up" class="functionality">
                    <h3 data-aos="fade-up">User dashboard</h3>
                    <p data-aos="fade-up">After login user can access the dashboard....</p>
                    <div data-aos="fade-up" class="photo">
                        <img src="user.dashboard.png" alt="">
                    </div>
                    </di><!-- /.functionality -->
                </div>
                <div data-aos="fade-up" class="all-functionalities">
                    <div data-aos="fade-up" class="functionality">
                        <h3 data-aos="fade-up">Adding Recpies</h3>
                        <p data-aos="fade-up">User can add recpies....</p>
                        <div data-aos="fade-up" class="photo">
                            <img src="add.recipe.png" alt="">
                        </div>
                    </div><!-- /.functionality -->
                </div>
                <div data-aos="fade-up" class="all-functionalities">
                    <div data-aos="fade-up" class="functionality">
                        <h3 data-aos="fade-up">Categories</h3>
                        <p data-aos="fade-up">Admin has access to add the categories</p>
                        <div data-aos="fade-up" class="photo">
                            <img src="categories.png" alt="">
                        </div>
                        </di><!-- /.functionality -->
                    </div>
                    <div data-aos="fade-up" class="all-functionalities">
                        <div data-aos="fade-up" class="functionality">
                            <h3 data-aos="fade-up">All Recipies</h3>
                            <p data-aos="fade-up">All users can view recipies of all other users....</p>
                            <div data-aos="fade-up" class="photo">
                                <img src="all.recipe.png" alt="">
                            </div>
                        </div><!-- /.functionality -->
                    </div>
        </section><!-- /.all-functionalities-wrapper -->

    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Digital Recipe System. All Rights Reserved.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            delay: 100 
        });
    </script>

</body>

</html>