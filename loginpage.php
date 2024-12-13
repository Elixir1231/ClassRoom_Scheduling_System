<?php
session_start(); // Start the session at the top of your PHP file

// Check if the user is already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php'); // Redirect to index.php if already logged in
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if username and password are 'admin'
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['loggedin'] = true; // Set session variable
        header('Location: index.php'); // Redirect to index.php
        exit();
    } else {
        $error = "Invalid username or password"; // Set error message
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #343A40;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            width: 320px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .login-card h3 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #343A40;
            margin-bottom: 20px;
        }
        .user-icon {
            background-color: #17a2b8;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 20px;
        }
        .form-control {
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 10px 15px;
            background-color: #e9ecef;
        }
        .btn-login {
            background-color: #17a2b8;
            border: none;
            border-radius: 8px;
            padding: 10px;
            font-weight: bold;
            color: white;
        }
        .btn-login:hover {
            background-color: #138496;
        }
        .form-check-label, .forgot-password {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .forgot-password:hover {
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="login-card d-flex flex-column align-items-center justify-content-center">
        <h3>CLASSROOM SCHEDULING SYSTEM</h3>
        <div class="user-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
            </svg>
        </div>

        <!-- Error message alert -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="POST">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-login">LOGIN</button>
            </div>
        </form>
    </div>
</body>
</html>
