<?php
// --- session ---
if (session_status() === PHP_SESSION_NONE) session_start();

/*
  TRAINING VERSION: "มีวาลิเดชันแต่ยังพอเจาะได้"
  - ปิดการ execute ใน uploads? -> (เจตนา) เปิดให้ execute เฉพาะนามสกุล PHP ทั่วไป รวมถึง .pht / .phar
  - บล็อก .php, .php3, .php5, .php7, .phtml, .phps "โดยตั้งใจลืม" .pht / .phar
  - เช็ค MIME แบบเชื่อ $_FILES['type'] (แก้ได้ด้วย Burp)
  - จำกัดขนาดไฟล์
  - ถ้าอัปโหลดไฟล์ที่ execute ได้สำเร็จ จะแสดง FLAG
*/

// ---------------- Config (for lab) ----------------
const MAX_UPLOAD_BYTES = 5 * 1024 * 1024; // 5MB

// “Blacklist” ที่ตั้งใจพลาด: ไม่มี pht / phar
$BLOCKED_EXTS = ['php','php3','php4','php5','php7','php8','phtml','phps'];

// “ไฟล์ที่อนุญาต” (แค่ไว้สร้างภาพลวงตา ฝั่ง MIME เราเชื่อ header จาก client ได้)
$SOFT_ALLOWED_MIME = [
  'image/png','image/jpeg','image/gif','image/webp','application/pdf'
];

// นามสกุลที่ Apache/PHP จะ execute (เปิดใน .htaccess ด้านล่าง)
$EXECUTABLE_EXTS = ['php','phtml','php3','pht','php5','php7','phar'];

// ---------------- Paths ----------------
$uploadDir = __DIR__ . '/uploads/';
$webBase   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$webPath   = ($webBase === '' ? '' : $webBase) . '/uploads/';

// เตรียมโฟลเดอร์
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

// .htaccess (ตั้งใจเปิด execute ให้ตระกูล PHP รวมทั้ง .pht/.phar)
$htaccess = $uploadDir . '.htaccess';
if (!file_exists($htaccess)) {
  @file_put_contents($htaccess, <<<HT
Options +Indexes
<IfModule mod_authz_core.c>
  Require all granted
</IfModule>

# LAB ONLY: Execute PHP inside /uploads (รวม .pht, .phar)
AddType application/x-httpd-php .php .phtml .php3 .pht .php5 .php7 .phar
<FilesMatch "\\.(php|phtml|php3|pht|php5|php7|phar)$">
  SetHandler application/x-httpd-php
</FilesMatch>
HT);
  @chmod($htaccess, 0666);
}

// ---------------- Helpers ----------------
function sanitize_name(string $name): string {
  $name = basename($name);
  $name = preg_replace('/[^\w.\-]+/u', '_', $name);
  return ltrim($name, '.') ?: 'file';
}

function ext_of(string $filename): string {
  return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function random_name_with_ext(string $ext): string {
  return bin2hex(random_bytes(6)) . '.' . strtolower($ext);
}

// ---------------- Handle upload ----------------
$msg = ''; $type = ''; $extra = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  $f = $_FILES['file'];

  if ($f['error'] !== UPLOAD_ERR_OK) {
    $msg = "Upload error (code {$f['error']}).";
    $type = 'error';
  } elseif ($f['size'] <= 0 || $f['size'] > MAX_UPLOAD_BYTES) {
    $msg = 'File too large.';
    $type = 'error';
  } else {
    $original = sanitize_name($f['name']);
    $ext = ext_of($original);

    // 1) บล็อก .php/.phtml/... (ตั้งใจ "ลืม" .pht/.phar)
    if (in_array($ext, $BLOCKED_EXTS, true)) {
      $msg = "This file type is not allowed.";
      $type = 'error';
    } else {
      // 2) ตรวจ MIME แบบอ่อน (เชื่อค่า client)
      $clientMime = $_FILES['file']['type'] ?? 'application/octet-stream';
      if (!in_array($clientMime, $SOFT_ALLOWED_MIME, true)) {
        $msg = "MIME type not allowed.";
        $type = 'error';
      } else {
        // 3) ใช้ชื่อสุ่ม (คงนามสกุลเดิมไว้เพื่อให้ .pht/.phar ทำงานได้)
        $saveName = random_name_with_ext($ext);
        $dest = $uploadDir . $saveName;

        if (is_uploaded_file($f['tmp_name']) && move_uploaded_file($f['tmp_name'], $dest)) {
          @chmod($dest, 0666);
          $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
          $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
          $url    = $scheme . '://' . $host . $webPath . rawurlencode($saveName);

          $msg  = "File uploaded successfully: " . htmlspecialchars($original) . " → " . htmlspecialchars($saveName);
          $type = 'success';
          $extra = "<br>Access at: <a href=\"" . htmlspecialchars($url) . "\" target=\"_blank\">" . htmlspecialchars($url) . "</a>";

          // ถ้าเป็นไฟล์ที่ execute ได้ (เช่น .pht/.phar) → โชว์ FLAG
          if (in_array($ext, $EXECUTABLE_EXTS, true)) {
            // ตัวอย่างดึง flag จาก DB/หรือฮาร์ดโค้ดก็ได้
            $flag = "FLAG{UPLOAD_BYPASS_VIA_PHT_AND_FAKE_MIME}";
            $extra .= "<br><strong>Flag: " . htmlspecialchars($flag) . "</strong>";
          }

        } else {
          $msg = 'Upload failed! Check permissions or enctype.';
          $type = 'error';
        }
      }
    }
  }
}
?>
<!-- ===== View (ใส่ UI ของคุณได้ตามปกติ) ===== -->
<link rel="stylesheet" href="assets/css/modern-school.css">
<div class="card">
  <h3>Upload (Training)</h3>
  <?php if ($msg): ?>
    <div class="alert <?= $type === 'success' ? 'alert-success':'alert-danger' ?>">
      <?= $msg ?><?= $extra ?>
    </div>
  <?php endif; ?>

  <form class="form mt-4" method="post" enctype="multipart/form-data" autocomplete="off">
    <div class="mb-4">
      <label for="f">Choose file</label>
      <input id="f" type="file" name="file" class="input" required>
      <div class="help">Allowed (UI): png, jpg, gif, webp, pdf · Max 5MB</div>
    </div>
    <button class="btn">Upload</button>
  </form>
</div>

<script>
// Client-side validation (กันตรงๆ แบบพื้นๆ — ข้ามได้ด้วย Burp)
document.querySelector('form').addEventListener('submit', (e) => {
  const f = document.getElementById('f');
  if (!f.files[0]) return;

  const name = f.files[0].name.toLowerCase();
  const ok = /\.(png|jpe?g|gif|webp|pdf)$/.test(name); // ตั้งใจไม่ครอบคลุม .pht/.phar
  if (!ok) {
    e.preventDefault();
    alert('This file type is not allowed (client check).');
  }
});
</script>

