
<?php
session_start();

$conn = new mysqli("localhost", "root", "", "waf_shop");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Loi ket noi database.");
}

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["remove_id"])) {
    $id = filter_input(INPUT_POST, "remove_id", FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        unset($_SESSION["cart"][$id]);
    }

    header("Location: cart.php");
    exit;
}

$items = [];
$total = 0;

if (!empty($_SESSION["cart"])) {
    $stmt = $conn->prepare(
        "SELECT id, name, price FROM products WHERE id = ?"
    );

    foreach ($_SESSION["cart"] as $id => $quantity) {
        $id = (int)$id;
        $quantity = (int)$quantity;

        if ($id <= 0 || $quantity <= 0) {
            continue;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if ($product) {
            $product["quantity"] = $quantity;
            $product["subtotal"] = (float)$product["price"] * $quantity;
            $total += $product["subtotal"];
            $items[] = $product;
        }
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Giỏ hàng - WAF Shop</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#f1f5f9;color:#1e293b}
header{background:#172554;color:white;padding:24px 8%;box-shadow:0 3px 10px #0002}
header h1{margin:0;font-size:30px}
nav{margin-top:18px;display:flex;flex-wrap:wrap;gap:24px}
nav a{color:white;text-decoration:none}
nav a:hover{color:#93c5fd}
main{max-width:950px;margin:35px auto;padding:0 20px}
h2{color:#172554}
.item,.empty-cart{background:white;padding:24px;border-radius:14px;margin-bottom:18px;border:1px solid #e2e8f0;box-shadow:0 4px 12px #0f172a0d}
.item h3{margin-top:0}
.price{color:#dc2626;font-weight:bold;font-size:18px}
button{padding:10px 16px;background:#dc2626;color:white;border:0;border-radius:7px;cursor:pointer}
button:hover{background:#b91c1c}
.total{background:#dbeafe;padding:20px;border-radius:10px;color:#172554}
.continue{display:inline-block;margin-top:20px;padding:12px 18px;background:#1d4ed8;color:white;text-decoration:none;border-radius:8px}
.continue:hover{background:#1e40af}
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
    <h2>Giỏ hàng của bạn</h2>

    <?php if (empty($items)): ?>
        <div class="empty-cart">
            <p>Giỏ hàng đang trống.</p>
            <a class="continue" href="index.php">Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
            <div class="item">
                <h3><?= htmlspecialchars($item["name"], ENT_QUOTES, 'UTF-8') ?></h3>
                <p>Số lượng: <?= (int)$item["quantity"] ?></p>
                <p>Đơn giá:
                    <?= number_format((float)$item["price"], 0, ",", ".") ?> VNĐ
                </p>
                <p class="price">Thành tiền:
                    <?= number_format($item["subtotal"], 0, ",", ".") ?> VNĐ
                </p>

                <form method="POST" action="cart.php">
                    <input type="hidden" name="remove_id"
                        value="<?= (int)$item["id"] ?>">
                    <button type="submit">Xóa sản phẩm</button>
                </form>
            </div>
        <?php endforeach; ?>

        <div class="total">
            <h3>Tổng cộng: <?= number_format($total, 0, ",", ".") ?> VNĐ</h3>
        </div>
        <a class="continue" href="index.php">Tiếp tục mua hàng</a>
    <?php endif; ?>
</main>

<footer>WAF Shop - Đồ án chuyên ngành An toàn thông tin</footer>
</body>
</html>
<?php $conn->close(); ?>
