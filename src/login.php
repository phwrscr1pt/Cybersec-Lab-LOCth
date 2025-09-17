<?php
session_start();

$host = $_ENV['DB_HOST'] ?? 'db';
$user = $_ENV['DB_USER'] ?? 'labuser';
$pass = $_ENV['DB_PASS'] ?? 'labpass';
$db = $_ENV['DB_NAME'] ?? 'school_lab';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // VULNERABLE: Direct string interpolation - SQL Injection
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $_SESSION['user'] = $user_data;
        
        if ($user_data['role'] === 'admin') {
            header('Location: otp.php');
            exit;
        } elseif ($user_data['role'] === 'student') {
            header('Location: profile.php?sid=' . $user_data['student_sid']);
            exit;
        }
    } else {
        $error = 'Invalid credentials';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberTech University - Student Portal Login</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modern-school.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>CyberTech University</h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="academics.php">Academics</a></li>
                        <li><a href="news.php">News</a></li>
                        <li><a href="events.php">Events</a></li>
                        <li><a href="admissions.php">Admissions</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="login.php">Portal</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card" style="max-width: 500px; margin: 2rem auto;">
                <h2>Student Portal Login</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn">Login</button>
                </form>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                    <h4>Demo Accounts:</h4>
                    <p><strong>Student:</strong> student0001 / pass1</p>
                    <p><strong>Admin:</strong> admin / admin123</p>
                </div>
                
                <div style="margin-top: 1rem;">
                    <a href="search.php">Teacher Directory</a> | 
                    <a href="student.php">Course Search</a> |
                    <a href="upload.php">File Upload</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 CyberTech University. For education only. Do not deploy publicly.</p>
        </div>
    </footer>
    <script src="assets/js/ui.js"></script>
</body>
</html>
