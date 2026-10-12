
<?php
session_start();

$conn = new mysqli("localhost", "root", "", "waf_shop");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Loi ket noi database: " . $conn->connect_error);
}

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["product_id"])) {

    $id = filter_input(INPUT_POST, "product_id", FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION["cart"][$id] =
                ($_SESSION["cart"][$id] ?? 0) + 1;
        }

        $stmt->close();
    }

    header("Location: index.php");
    exit;
}

$result = $conn->query("SELECT * FROM products");

$icons = [
    "Dien thoai" => "📱",
    "Laptop" => "💻",
    "Tai nghe" => "🎧",
    "Ban phim" => "⌨️"
];

$cartCount = array_sum($_SESSION["cart"]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>WAF Shop - Cửa hàng công nghệ</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    color: #1e293b;
}

header {
    background: #172554;
    color: white;
    padding: 24px 8%;
    box-shadow: 0 3px 10px rgba(0,0,0,.12);
}

header h1 {
    margin: 0;
    font-size: 30px;
    letter-spacing: 1px;
}

nav {
    margin-top: 18px;
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
}

nav a {
    color: white;
    text-decoration: none;
    font-size: 15px;
    transition: color .2s;
}

nav a:hover {
    color: #93c5fd;
}

main {
    max-width: 1100px;
    margin: 35px auto;
    padding: 0 20px;
}

.banner {
    background: linear-gradient(135deg, #dbeafe, #fff);
    padding: 38px;
    border-radius: 14px;
    margin-bottom: 35px;
    border: 1px solid #dbeafe;
}

.banner h2 {
    color: #172554;
    font-size: 28px;
    margin-top: 0;
}

.banner p {
    color: #475569;
    line-height: 1.7;
}

main > h2 {
    margin-bottom: 22px;
    color: #172554;
}

.products {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 24px;
}

.product {
    background: white;
    padding: 28px 22px;
    border-radius: 14px;
    text-align: center;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(15,23,42,.05);
    transition: transform .2s, box-shadow .2s;
}

.product:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(15,23,42,.12);
}

.emoji {
    font-size: 65px;
    margin-bottom: 15px;
}

.product h3 {
    font-size: 19px;
    margin: 12px 0;
    color: #1e293b;
}

.product p {
    line-height: 1.6;
    color: #64748b;
}

.price {
    color: #dc2626 !important;
    font-size: 19px;
    font-weight: bold;
    margin: 15px 0;
}

.buy-button {
    width: 100%;
    background: #1d4ed8;
    color: white;
    border: none;
    padding: 13px 16px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: background .2s;
}

.buy-button:hover {
    background: #1e40af;
}

.buy-button:active {
    transform: scale(.98);
}

footer {
    margin-top: 50px;
    text-align: center;
    padding: 28px 15px;
    background: #172554;
    color: #cbd5e1;
}

@media (max-width: 600px) {
    header {
        padding: 22px;
    }

    .banner {
        padding: 25px;
    }

    .banner h2 {
        font-size: 23px;
    }

    .products {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>
<header>
    <h1>WAF SHOP</h1>
    <nav>
        <a href="index.php">Trang chủ</a>
        <a href="search.php">Tìm kiếm</a>
        <a href="login.php">Đăng nhập</a>
        <a href="cart.php">Giỏ hàng (<?= (int)$cartCount ?>)</a>
    </nav>
</header>

<main>
    <section class="banner">
        <h2>Chào mừng đến với WAF Shop</h2>
        <p>Khám phá các sản phẩm công nghệ với giá cả hợp lý.</p>
    </section>

    <h2>Sản phẩm nổi bật</h2>

    <section class="products">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product">
                    <div class="emoji">
                        <?= $icons[$row["name"]] ?? "🛍️" ?>
                    </div>

                    <h3>
                        <?= htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8") ?>
                    </h3>

                    <p class="price">
                        <?= number_format((float)$row["price"], 0, ",", ".") ?> VNĐ
                    </p>

                    <p>
                        <?= htmlspecialchars($row["description"] ?? "", ENT_QUOTES, "UTF-8") ?>
                    </p>

                    <form method="POST" action="index.php">
                        <input type="hidden" name="product_id"
                            value="<?= (int)$row["id"] ?>">

                        <button class="buy-button" type="submit">
                            Thêm vào giỏ hàng
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Chưa có sản phẩm nào.</p>
        <?php endif; ?>
    </section>
</main>

<footer>
    WAF Shop - Đồ án chuyên ngành An toàn thông tin
</footer>
</body>
</html>

<?php $conn->close(); ?>
