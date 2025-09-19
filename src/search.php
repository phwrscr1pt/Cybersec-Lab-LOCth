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

$teachers = [];
$error = '';

if (isset($_GET['tid'])) {
    $tid = $_GET['tid'];
    
    // VULNERABLE: Direct string interpolation - UNION injection possible
    $query = "SELECT teacher_id, name, department FROM teachers WHERE teacher_id = '$tid'";
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $teachers[] = $row;
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
    <title>Ban Nong Ngu Hao University - Teacher Directory</title>
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
                        <li><a href="student.php">Courses</a></li>
                        <li><a href="upload.php">Upload</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <h2>Teacher Directory Search</h2>
                <p>Search for teachers by Teacher ID</p>

                <form method="GET">
                    <div class="form-group">
                        <label for="tid">Teacher ID:</label>
                        <input type="text" id="tid" name="tid" value="<?php echo htmlspecialchars($_GET['tid'] ?? ''); ?>" placeholder="e.g., T001">
                    </div>
                    <button type="submit" class="btn mt-4">Search</button>
                </form>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($teachers): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Teacher ID</th>
                                <th>Name</th>
                                <th>Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teachers as $teacher): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($teacher['teacher_id']); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['department']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif (isset($_GET['tid']) && !$error): ?>
                    <div class="alert alert-warning mt-4">No teacher found for ID: <?php echo htmlspecialchars($_GET['tid']); ?></div>
                <?php endif; ?>
                <!-- <div class="alert alert-warning" style="margin-top: 2rem;">
                    <strong>SQL Injection Hint:</strong>
                    <p>Try: <code>' UNION SELECT username,password,role FROM users-- -</code></p>
                    <p>This query returns 3 columns (teacher_id, name, department), perfect for UNION attacks!</p>
                </div> -->
                <div style="margin-top: 2rem;">
                    <h3>Available Teachers:</h3>
                    <p>T001, T002, T003, T004, T005, T006</p>
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
