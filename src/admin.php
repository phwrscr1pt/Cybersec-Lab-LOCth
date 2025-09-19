<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin' || !isset($_SESSION['otp_verified'])) {
    header('Location: login.php');
    exit;
}

$host = $_ENV['DB_HOST'] ?? 'db';
$user = $_ENV['DB_USER'] ?? 'labuser';
$pass = $_ENV['DB_PASS'] ?? 'labpass';
$db = $_ENV['DB_NAME'] ?? 'school_lab';

$conn = new mysqli($host, $user, $pass, $db);
$flag_result = $conn->query("SELECT flag_value FROM flags WHERE flag_name = 'admin_page'");
$flag = $flag_result->fetch_assoc()['flag_value'] ?? 'FLAG{ERROR}';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/img/logo.svg">
    <title>CyberTech University - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modern-school.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>CyberTech University - Admin</h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="login.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <h2>🎉 Admin Panel Access Granted!</h2>
                <div class="alert alert-success">
                    <h3>Congratulations!</h3>
                    <p>You have successfully accessed the admin panel through SQL injection and OTP bypass.</p>
                    <p><strong>Flag:</strong> <code><?php echo htmlspecialchars($flag); ?></code></p>
                </div>

                <h3>Admin Functions</h3>
                <ul>
                    <li>User Management</li>
                    <li>System Configuration</li>
                    <li>Security Logs</li>
                    <li>Database Administration</li>
                </ul>

                <div style="margin-top: 2rem;">
                    <a href="login.php" class="btn">Logout</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 CyberTech University. For education only. Do not deploy publicly.</p>
        </div>
    </footer>
    <script src="assets/js/ui.js"></script>
</body>
</html>
