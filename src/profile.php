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

// VULNERABLE: No authorization check - IDOR
$sid = $_GET['sid'] ?? '0001';

$query = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $sid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $error = "Student not found";
    $student = null;
} else {
    $student = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberTech University - Student Profile</title>
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
                        <li><a href="student.php">Courses</a></li>
                        <li><a href="search.php">Teachers</a></li>
                        <li><a href="upload.php">Upload</a></li>
                        <li><a href="login.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($student): ?>
                <div class="card">
                    <h2>Student Profile</h2>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h3>
                        <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
                        <p><strong>Class:</strong> <?php echo htmlspecialchars($student['class']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                    </div>

                    <div class="alert alert-warning" style="margin-top: 2rem;">
                        <strong>IDOR Vulnerability Demo:</strong>
                        <p>Try changing the 'sid' parameter in the URL to access other student profiles (e.g., ?sid=0002, ?sid=0003, etc.)</p>
                        <p>This demonstrates an Insecure Direct Object Reference where authorization is not properly checked.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Cyber