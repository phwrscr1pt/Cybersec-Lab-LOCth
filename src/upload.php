<?php
session_start();

$message = '';
$messageType = '';

if ($_POST && isset($_FILES['file'])) {
    $uploadDir = __DIR__ . '/uploads/';  // Use absolute path
    $webPath = 'uploads/';  // Web-accessible path
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $uploadFile = $uploadDir . basename($_FILES['file']['name']);
    
    // VULNERABLE: No file type validation
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        // Set proper permissions
        chmod($uploadFile, 0644);
        
        $message = "File uploaded successfully: " . htmlspecialchars($_FILES['file']['name']);
        $message .= "<br>Access at: <a href='" . $webPath . htmlspecialchars($_FILES['file']['name']) . "' target='_blank'>" . $webPath . htmlspecialchars($_FILES['file']['name']) . "</a>";
        $messageType = 'success';
        
        // If it's a PHP file, show the flag
        if (pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) === 'php') {
            $host = $_ENV['DB_HOST'] ?? 'db';
            $user = $_ENV['DB_USER'] ?? 'labuser';
            $pass = $_ENV['DB_PASS'] ?? 'labpass';
            $db = $_ENV['DB_NAME'] ?? 'school_lab';
            
            $conn = new mysqli($host, $user, $pass, $db);
            $flag_result = $conn->query("SELECT flag_value FROM flags WHERE flag_name = 'upload_shell'");
            $flag = $flag_result->fetch_assoc()['flag_value'] ?? 'FLAG{ERROR}';
            
            $message .= "<br><strong>PHP File Detected! Flag: " . htmlspecialchars($flag) . "</strong>";
        }
    } else {
        $message = "Upload failed! Check permissions.";
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberTech University - File Upload</title>
    <link rel="stylesheet" href="assets/css/styles.css">
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
                        <li><a href="login.php">Portal</a></li>
                        <li><a href="student.php">Courses</a></li>
                        <li><a href="search.php">Teachers</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <h2>File Upload</h2>
                <p>Upload your assignments and documents</p>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file">Select File:</label>
                        <input type="file" id="file" name="file" required>
                    </div>
                    <button type="submit" class="btn">Upload File</button>
                </form>

                <div class="alert alert-warning" style="margin-top: 2rem;">
                    <strong>Vulnerability Demo:</strong>
                    <p>This upload form accepts ANY file type without validation!</p>
                    <p>Try uploading a PHP file with this content:</p>
                    <pre style="background: #f8f8f8; padding: 1rem; border-radius: 4px; font-size: 0.9rem;">&lt;?php echo "Hello from PHP!"; phpinfo(); ?&gt;</pre>
                    <p>After upload, visit: <code>http://localhost:8080/uploads/yourfile.php</code></p>
                </div>

                <div style="margin-top: 2rem;">
                    <h3>Uploaded Files:</h3>
                    <?php
                    $uploadDir = 'uploads/';
                    if (is_dir($uploadDir)) {
                        $files = array_diff(scandir($uploadDir), array('.', '..', '.htaccess'));
                        if ($files) {
                            echo '<ul>';
                            foreach ($files as $file) {
                                echo '<li><a href="uploads/' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . '</a></li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p>No files uploaded yet.</p>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 CyberTech University. For education only. Do not deploy publicly.</p>
        </div>
    </footer>
</body>
</html>
