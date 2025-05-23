<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
}
require 'db.php';

$error = '';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
    } else {
        $error = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>

body {
   background: linear-gradient(135deg, #00bcd4, #a8e063);

    background-size: 400% 400%;
    animation: gradientAnimation 15s ease infinite;
    font-family: 'Roboto', sans-serif;
    color: #333;
}

@keyframes gradientAnimation {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.container {
    margin-top: 100px;
}

.card {
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    background: white;
    padding: 40px 30px;
}

.card h1 {
    color: #0072ff;
    font-size: 2.5rem;
    text-align: center;
    font-weight: bold;
}

.form-control {
    border-radius: 5px;
    border: 1px solid #ddd;
    padding: 15px;
    font-size: 1rem;
    background-color: #f9f9f9;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #0072ff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    background-color: #e6f7ff;
}


.btn-primary {
    background-color: #ff4081;
    border: none;
    padding: 15px;
    font-size: 1rem;
    border-radius: 5px;
    width: 100%;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #ff3366;
}


.alert-danger {
    background-color: #ff4444;
    color: white;
    border-radius: 5px;
    padding: 15px;
    text-align: center;
    font-size: 1rem;
    margin-bottom: 20px;
}


a {
    color: #0072ff;
    text-decoration: none;
}

a:hover {
    color: #ff4081;
    text-decoration: underline;
}

/* Responsive Adjustments */
@media (max-width: 767px) {
    .card {
        padding: 30px;
    }
    .card h1 {
        font-size: 2rem;
    }
}


    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <h1 class="text-center mb-4">Login</h1>
                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center shake">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" id="loginForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                    </form>
                    <p class="mt-3 text-center">Don't have an account? <a href="signup.php">Sign up here</a>.</p>
                    <p class="mt-3 text-center"> <a href="contact.php">Contact Us</a>.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
       
        const loginForm = document.getElementById('loginForm');
        const errorAlert = document.querySelector('.alert-danger');

        loginForm.addEventListener('submit', function (e) {
            if (errorAlert) {
                errorAlert.classList.remove('shake');
                void errorAlert.offsetWidth; 
                errorAlert.classList.add('shake');
            }
        });
    </script>
</body>
</html>
