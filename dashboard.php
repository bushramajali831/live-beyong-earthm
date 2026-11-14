<?php
session_start();
require 'db.php';

if (!isset($_SESSION['farmer_id']) && !isset($_COOKIE['remember_farmer'])) {
    header("Location: index.php");
    exit();
}

$farmer_id = $_SESSION['farmer_id'] ?? $_COOKIE['remember_farmer'];
$stmt = $conn->prepare("SELECT username, email FROM farmers WHERE id = ?");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$farmer = $stmt->get_result()->fetch_assoc();

if (isset($_GET['logout'])) {
    session_destroy();
    setcookie("remember_farmer", "", time() - 3600, "/");
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المزارع | Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="card" style="text-align: center;">
    <button type="button" class="lang-btn" onclick="toggleLanguage()" data-i18n="langBtnText">English</button>

    <h1>
        <span data-i18n="welcomeTitle">أهلاً بك يا مزارع، </span>
        <?php echo htmlspecialchars($farmer['username']); ?> 🚜
    </h1>
    
    <p data-i18n="welcomeSub">تم تسجيل دخولك تلقائياً بنجاح.</p>
    
    <p>
        <strong data-i18n="registeredEmail">البريد المسجل: </strong>
        <?php echo htmlspecialchars($farmer['email']); ?>
    </p>
    
    <a href="?logout=true" class="logout-btn" data-i18n="logoutBtn">تسجيل الخروج</a>
</div>

<script src="script.js"></script>
</body>
</html>