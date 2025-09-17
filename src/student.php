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

$courses = [];
$error = '';

if (isset($_GET['cid'])) {
    $cid = $_GET['cid'];
    
    // Special course that sets HttpOnly cookie
    if ($cid === '0087') {
        $flag_result = $conn->query("SELECT flag_value FROM flags WHERE flag_name = 'course_cookie'");
        $flag = $flag_result->fetch_assoc()['flag_value'] ?? 'FLAG{ERROR}';
        setcookie('LABFLAG', $flag, time() + 3600, '/', '', false, true); // HttpOnly
    }
    
    // VULNERABLE: Direct string interpolation
    $query = "SELECT course_id, name, teacher_id FROM courses WHERE course_id = '$cid'";
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    } else {
        $error = "Query error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberTech University - Course Search</title>
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
                        <li><a href="login.php">Portal</a></li>
                        <li><a href="search.php">Teachers</a></li>
                        <li><a href="upload.php">Upload</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <h2>Course Search</h2>
                <p>Search for courses by Course ID</p>

                <form method="GET">
                    <div class="form-group">
                        <label for="cid">Course ID:</label>
                        <input type="text" id="cid" name="cid" value="<?php echo htmlspecialchars($_GET['cid'] ?? ''); ?>" placeholder="e.g., 0001">
                    </div>
                    <button type="submit" class="btn">Search</button>
                </form>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($courses): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Course ID</th>
                                <th>Course Name</th>
                                <th>Teacher ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                                    <td><?php echo htmlspecialchars($course['name']); ?></td>
                                    <td><?php echo htmlspecialchars($course['teacher_id']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif (isset($_GET['cid']) && !$error): ?>
                    <div class="alert alert-warning">No courses found for ID: <?php echo htmlspecialchars($_GET['cid']); ?></div>
                <?php endif; ?>

                <div class="alert alert-warning" style="margin-top: 2rem;">
                    <strong>Vulnerability Hint:</strong>
                    <p>This search is vulnerable to SQL injection. Try searching for course ID: <code>0087</code></p>
                    <p>When you find the special course, check your HTTP response headers/cookies in Burp Suite or browser dev tools!</p>
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
