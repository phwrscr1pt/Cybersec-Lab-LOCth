<?php 
session_start();

$message = '';
$messageType = '';

/* ===== Ensure /uploads exists and executes PHP (IDEMPOTENT) =====
   Requires your vhost to allow .htaccess:  AllowOverride All  */
$absUploadDir = __DIR__ . '/uploads/';
if (!is_dir($absUploadDir)) {
    @mkdir($absUploadDir, 0777, true);
}
$ht = $absUploadDir . '.htaccess';
if (!file_exists($ht)) {
    @file_put_contents($ht, <<<HT
Options +Indexes
AddType application/x-httpd-php .php .phtml .php5 .php7 .phar
<Files "*.php*">
  SetHandler application/x-httpd-php
</Files>
HT);
    @chmod($ht, 0666);
}
/* ===== end ensure ===== */

if ($_POST && isset($_FILES['file'])) {
    $uploadDir = $absUploadDir;      // absolute path on disk
    $webPath   = 'uploads/';         // web-accessible path

    // Create directory if it doesn't exist (idempotent)
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    $clientName = basename($_FILES['file']['name']); // VULN: trust client filename
    $uploadFile = $uploadDir . $clientName;

    // VULNERABLE: No file type validation
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        @chmod($uploadFile, 0644);

        $safeName = htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8');
        $message = "File uploaded successfully: " . $safeName;
        $message .= "<br>Access at: <a href='" . $webPath . $safeName . "' target='_blank'>" . $webPath . $safeName . "</a>";
        $messageType = 'success';

        // If it's a PHP file, show the flag (kept from your original logic)
        if (strtolower(pathinfo($clientName, PATHINFO_EXTENSION)) === 'php') {
            $host = getenv('DB_HOST') ?: 'db';
            $user = getenv('DB_USER') ?: 'labuser';
            $pass = getenv('DB_PASS') ?: 'labpass';
            $db   = getenv('DB_NAME') ?: 'school_lab';

            mysqli_report(MYSQLI_REPORT_OFF);
            $conn = @new mysqli($host, $user, $pass, $db);
            if (!$conn->connect_errno) {
                $flag_result = $conn->query("SELECT flag_value FROM flags WHERE flag_name = 'upload_shell'");
                $flag = $flag_result ? ($flag_result->fetch_assoc()['flag_value'] ?? 'FLAG{ERROR}') : 'FLAG{ERROR}';
                $message .= "<br><strong>PHP File Detected! Flag: " . htmlspecialchars($flag, ENT_QUOTES, 'UTF-8') . "</strong>";
            } else {
                $message .= "<br><em>DB connection failed (cannot fetch flag)</em>";
            }
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
                    $uploadDirRel = 'uploads/';
                    if (is_dir($uploadDirRel)) {
                        $files = array_diff(scandir($uploadDirRel), array('.', '..', '.htaccess'));
                        if ($files) {
                            echo '<ul>';
                            foreach ($files as $file) {
                                echo '<li><a href="uploads/' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '</a></li>';
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
