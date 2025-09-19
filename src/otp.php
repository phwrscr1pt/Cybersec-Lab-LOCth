<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $otp = $_POST['otp'] ?? '';
    
    // Simple OTP check - 4 digits between 0000-0200
    if (is_numeric($otp) && strlen($otp) === 4 && intval($otp) >= 0 && intval($otp) <= 200) {
        if ($otp === '0123') { // Correct OTP
            $_SESSION['otp_verified'] = true;
            header('Location: admin.php');
            exit;
        }
    }
    $error = 'Invalid OTP. Try 4 digits between 0000-0200.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ban Nong Ngu Hao University - OTP Verification</title>
    <link rel="icon" href="assets/img/logo.svg">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modern-school.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>Ban Nong Ngu Hao University</h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="login.php">Portal</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card" style="max-width: 500px; margin: 2rem auto;">
                <h2>Admin Access - OTP Required</h2>
                <p>Hello, <strong><?php echo htmlspecialchars($_SESSION['user']['username']); ?></strong>!</p>
                <p>Please enter the 4-digit OTP to access the admin panel:</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="otp">OTP (4 digits):</label>
                        <input type="text" id="otp" name="otp" maxlength="4" placeholder="0000" required>
                        <small style="color: #666;">Hint: Range is 0000-0200</small>
                    </div>
                    
                    <button type="submit" class="btn">Verify OTP</button>
                </form>

                <div style="margin-top: 1rem;">
                    <a href="login.php">Back to Login</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Ban Nong Ngu Hao University. For education only. Do not deploy publicly.</p>
        </div>
    </footer>
    <script src="assets/js/ui.js"></script>
</body>
</html>
