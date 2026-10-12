
<?php
session_start();

$conn = new mysqli("localhost", "root", "", "waf_shop");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Loi ket noi database.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_SESSION["user_id"])) {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT id, username, password FROM users WHERE username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && hash("sha256", $password) === $user["password"]) {
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        header("Location: login.php");
        exit;
    } else {
        $error = "Sai tên đăng nhập hoặc mật khẩu!";
    }

    $stmt->close();
}

$loggedIn = isset($_SESSION["user_id"]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng nhập - WAF Shop</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#f1f5f9;color:#1e293b}
header{background:#172554;color:white;padding:24px 8%;box-shadow:0 3px 10px #0002}
header h1{margin:0;font-size:30px}
nav{margin-top:18px;display:flex;flex-wrap:wrap;gap:24px}
nav a{color:white;text-decoration:none}
nav a:hover{color:#93c5fd}
main{max-width:460px;margin:45px auto;padding:30px;background:white;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 8px 25px #0f172a12}
h2{color:#172554;margin-top:0}
label{display:block;margin-top:16px;font-weight:bold}
input{width:100%;padding:13px;margin-top:8px;border:1px solid #cbd5e1;border-radius:8px;font-size:15px}
button{width:100%;padding:13px;margin-top:20px;background:#1d4ed8;color:white;border:0;border-radius:8px;font-weight:bold;cursor:pointer}
button:hover{background:#1e40af}
.error{color:#dc2626;background:#fef2f2;padding:12px;border-radius:8px}
.success{color:#15803d}
footer{margin-top:50px;text-align:center;padding:28px;background:#172554;color:#cbd5e1}
</style>
</head>
<body>
<header>
    <h1>WAF SHOP</h1>
    <nav>
        <a href="index.php">Trang chủ</a>
        <a href="search.php">Tìm kiếm</a>
        <a href="login.php">Đăng nhập</a>
        <a href="cart.php">Giỏ hàng</a>
    </nav>
</header>

<main>
<?php if ($loggedIn): ?>
    <h2>Tài khoản của bạn</h2>
    <p class="success">
        Xin chào, <?= htmlspecialchars($_SESSION["username"], ENT_QUOTES, 'UTF-8') ?>!
    </p>
    <form method="POST" action="logout.php">
        <button type="submit">Đăng xuất</button>
    </form>
<?php else: ?>
    <h2>Đăng nhập</h2>

    <?php if ($error !== ""): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="username">Tên đăng nhập</label>
        <input id="username" type="text" name="username"
            maxlength="50" autocomplete="username" required>

        <label for="password">Mật khẩu</label>
        <input id="password" type="password" name="password"
            autocomplete="current-password" required>

        <button type="submit">Đăng nhập</button>
    </form>
<?php endif; ?>
</main>

<footer>WAF Shop - Đồ án chuyên ngành An toàn thông tin</footer>
</body>
</html>
<?php $conn->close(); ?>
