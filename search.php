
<?php
$conn = new mysqli("localhost", "root", "", "waf_shop");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Loi ket noi database.");
}

$keyword = trim($_GET["keyword"] ?? "");
$products = [];

if ($keyword !== "") {
    $stmt = $conn->prepare(
        "SELECT * FROM products WHERE name LIKE ?"
    );
    $search = "%" . $keyword . "%";
    $stmt->bind_param("s", $search);
    $stmt->execute();

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tìm kiếm - WAF Shop</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#f1f5f9;color:#1e293b}
header{background:#172554;color:white;padding:24px 8%;box-shadow:0 3px 10px #0002}
header h1{margin:0;font-size:30px}
nav{margin-top:18px;display:flex;flex-wrap:wrap;gap:24px}
nav a{color:white;text-decoration:none}
nav a:hover{color:#93c5fd}
main{max-width:1000px;margin:35px auto;padding:0 20px}
h2{color:#172554}
.search-box,.product{background:white;padding:24px;border-radius:14px;margin-bottom:20px;border:1px solid #e2e8f0;box-shadow:0 4px 12px #0f172a0d}
.search-box form{display:flex;gap:10px;flex-wrap:wrap}
input{flex:1;min-width:180px;padding:13px;border:1px solid #cbd5e1;border-radius:8px;font-size:15px}
button{padding:13px 22px;background:#1d4ed8;color:white;border:0;border-radius:8px;cursor:pointer;font-weight:bold}
button:hover{background:#1e40af}
.product h3{margin-top:0}
.price{color:#dc2626;font-weight:bold;font-size:18px}
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
    <h2>Tìm kiếm sản phẩm</h2>
    <section class="search-box">
        <form method="GET" action="search.php">
            <input type="text" name="keyword"
                placeholder="Nhập tên sản phẩm..."
                value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Tìm kiếm</button>
        </form>
    </section>

    <?php if ($keyword !== ""): ?>
        <h2>Kết quả tìm kiếm</h2>
        <?php if ($products): ?>
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <h3><?= htmlspecialchars($product["name"], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="price"><?= number_format((float)$product["price"], 0, ",", ".") ?> VNĐ</p>
                    <p><?= htmlspecialchars($product["description"] ?? "", ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Không tìm thấy sản phẩm phù hợp.</p>
        <?php endif; ?>
    <?php endif; ?>
</main>

<footer>WAF Shop - Đồ án chuyên ngành An toàn thông tin</footer>
</body>
</html>
<?php $conn->close(); ?>
