<?php
// Start the session to check for existing session variables (optional, based on your setup)
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Registration - <b>Vote</b>Xpress</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Optional: Custom CSS (you can add this file if you have custom styles) -->
    <link rel="stylesheet" href="path/to/your/custom/styles.css">
    
    <!-- jQuery (required for Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    
    <!-- Optional: Add your custom scripts -->
    <script src="path/to/your/custom/scripts.js"></script>
</head>
<body>

    <!-- Navigation Bar (optional) -->
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="index.php"><b>Vote</b>Xpress</a>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="login.php"><i class="fa fa-sign-in"></i> Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Optional: Display session error or success message -->
    <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger text-center'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success text-center'>{$_SESSION['success']}</div>";
        unset($_SESSION['success']);
    }
    ?>

</body>
</html>