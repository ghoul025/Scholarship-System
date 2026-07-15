<?php
// admin/mail_all_scholars.php
// Temporary script to email all registered scholars using PHPMailer

session_start();
require '../config.php';
require '../vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Restrict to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch all scholars with email addresses
$stmt = $conn->query("SELECT id, first_name, last_name, email FROM scholars WHERE email IS NOT NULL AND email <> ''");
$scholars = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sent = 0;
$failed = 0;
$errors = [];

foreach ($scholars as $scholar) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration for Gmail (copied from approve_application.php)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME; // From config.php
        $mail->Password = SMTP_PASSWORD; // From config.php
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Recipients
        $mail->setFrom(SMTP_USERNAME, 'Scholarship System');
        $mail->addAddress($scholar['email'], $scholar['first_name'] . ' ' . $scholar['last_name']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Scholarship Survey Request';
        $full_name = trim($scholar['first_name'] . ' ' . $scholar['last_name']);
        $survey_link = 'https://docs.google.com/forms/d/e/1FAIpQLSczVyp-C02auO2usJPDhjrrbHO27vLl5sXdo7elQI1BNM_kdg/viewform?usp=preview';
        $mail->Body =
            "<p>Dear {$full_name},</p>"
            . "<p>We kindly ask you to answer our short survey as part of our ongoing efforts to improve the scholarship program. It will only take less than 5 minutes of your time.</p>"
            . "<p><a href='{$survey_link}' target='_blank' style='color: #1a73e8; font-weight: bold;'>Click here to answer the survey</a></p>"
            . "<p>Your feedback is important to us. Thank you for your participation!</p>"
            . "<p>Best regards,<br>Scholarship System Team</p>";
        $mail->AltBody = "Dear {$full_name},\n\nWe kindly ask you to answer our short survey as part of our ongoing efforts to improve the scholarship program. It will only take less than 5 minutes of your time.\n\nSurvey link: {$survey_link}\n\nYour feedback is important to us. Thank you for your participation!\n\nBest regards,\nScholarship System Team";

        $mail->send();
        $sent++;
    } catch (Exception $e) {
        $failed++;
        $errors[] = $scholar['email'] . ': ' . $mail->ErrorInfo;
    }
}

// Output summary
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mail All Scholars - Result</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Mail All Scholars - Result</h2>
    <p>Sent: <?= $sent ?> | Failed: <?= $failed ?></p>
    <?php if ($failed > 0): ?>
        <h3>Failed Emails:</h3>
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
