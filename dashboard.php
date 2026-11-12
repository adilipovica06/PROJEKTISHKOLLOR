
<?php
session_start();
require_once __DIR__ . "/Database.php";

// Kontrollo nëse përdoruesi është kyçur
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Kontrollo nëse tabela ekziston
function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return (bool) $stmt->fetchColumn();
}

// Merr numrin e rreshtave nga tabela
function safeCount(PDO $pdo, string $table): int {
    if (!tableExists($pdo, $table)) {
        return 0;
    }

    return (int) $pdo->query(
        "SELECT COUNT(*) FROM `$table`"
    )->fetchColumn();
}

// Formati i çmimeve
function money($value): string {
    return "€" . number_format((float)$value, 2);
}

$totalProducts = 0;
$totalOrders = 0;
$totalCustomers = 0;
$totalRevenue = 0;

$lowStock = [];
$recentOrders = [];
$topProducts = [];
$dbWarning = null;

try {
    $totalProducts = safeCount($pdo, "products");
    $totalOrders = safeCount($pdo, "orders");
    $totalCustomers = safeCount($pdo, "customers");

    // Porositë
    if (tableExists($pdo, "orders")) {
        $cols = $pdo->query(
            "SHOW COLUMNS FROM `orders`"
        )->fetchAll(PDO::FETCH_COLUMN);

        if (in_array("total", $cols, true)) {
            $totalRevenue = (float)$pdo->query(
                "SELECT COALESCE(SUM(total), 0) FROM `orders`"
            )->fetchColumn();
        }

        if (
            in_array("customer_name", $cols, true) &&
            in_array("created_at", $cols, true) &&
            in_array("status", $cols, true) &&
            in_array("total", $cols, true)
        ) {
            $recentOrders = $pdo->query(
                "SELECT id, customer_name, total, status, created_at
                 FROM `orders`
                 ORDER BY created_at DESC
                 LIMIT 5"
            )->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Produktet
    if (tableExists($pdo, "products")) {
        $cols = $pdo->query(
            "SHOW COLUMNS FROM `products`"
        )->fetchAll(PDO::FETCH_COLUMN);

        if (
            in_array("quantity", $cols, true) &&
            in_array("name", $cols, true)
        ) {
            $lowStock = $pdo->query(
                "SELECT name, quantity
                 FROM `products`
                 WHERE quantity <= 5
                 ORDER BY quantity ASC
                 LIMIT 5"
            )->fetchAll(PDO::FETCH_ASSOC);
        }

        if (
            in_array("sold_count", $cols, true) &&
            in_array("name", $cols, true)
        ) {
            $topProducts = $pdo->query(
                "SELECT name, sold_count
                 FROM `products`
                 ORDER BY sold_count DESC
                 LIMIT 3"
            )->fetchAll(PDO::FETCH_ASSOC);
        }
    }

} catch (PDOException $e) {
    $dbWarning = "Disa të dhëna nuk mund të lexohen. Kontrollo databazën.";
}

$username = htmlspecialchars(
    $_SESSION["username"] ?? "Admin",
    ENT_QUOTES,
    "UTF-8"
);

// Klasat e statusit të porosive
function statusClass(string $status): string {
    $s = mb_strtolower($status);

    if (
        str_contains($s, "përfund") ||
        str_contains($s, "perfund")
    ) {
        return "done";
    }

    if (str_contains($s, "prit")) {
        return "waiting";
    }

    return "processing";
}
?>

<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Inventari.io</title>

    <link rel="stylesheet" href="dashboard.css">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

<div class="app-shell">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">

        <a class="brand" href="dashboard.php">
            <span class="brand-mark">
                <i data-lucide="box"></i>
            </span>

            <span>
                Inventari<span class="brand-blue">.io</span>
            </span>
        </a>

        <nav class="nav-menu">

            <a class="nav-link active" href="dashboard.php">
                <i data-lucide="layout-dashboard"></i>
                Dashboard
            </a>

            <a class="nav-link" href="invertari.html">
                <i data-lucide="package"></i>
                Produktet
            </a>

            <a class="nav-link" href="porosite.php">
                <i data-lucide="shopping-cart"></i>
                Porositë
            </a>

            <a class="nav-link" href="customers.php">
                <i data-lucide="users"></i>
                Klientët
            </a>

            <a class="nav-link" href="reports.php">
                <i data-lucide="chart-no-axes-column"></i>
                Raportet
            </a>

            <a class="nav-link" href="cilsimet.php">
                <i data-lucide="settings"></i>
                Cilësimet
            </a>

        </nav>

        <a class="logout-link" href="logout.php">
            <i data-lucide="log-out"></i>
            Dil nga llogaria
        </a>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- TOPBAR -->
        <header class="topbar">

            <button class="mobile-menu" id="menuButton">
                <i data-lucide="menu"></i>
            </button>

            <label class="search-box">
                <i data-lucide="search"></i>

                <input
                    type="search"
                    id="searchInput"
                    placeholder="Kërko këtu..."
                >
            </label>

            <div class="top-actions">

                <button class="icon-button">
                    <i data-lucide="bell"></i>
                    <span class="notification-dot"></span>
                </button>

                <div class="admin-chip">
                    <span class="avatar">
                        <?= strtoupper(mb_substr($username, 0, 1)) ?>
                    </span>

                    <span><?= $username ?></span>
                </div>

            </div>

        </header>

        <!-- WELCOME -->
        <section class="welcome-banner">

            <div>
                <p class="eyebrow">PANELI KRYESOR</p>

                <h1>
                    Mirë se erdhe, <?= $username ?>! 👋
                </h1>

                <p>
                    Ja një përmbledhje e përgjithshme e biznesit tënd.
                </p>
            </div>

            <div class="banner-art">
                <i data-lucide="boxes"></i>
            </div>

        </section>

        <?php if ($dbWarning): ?>
            <div class="notice">
                <?= htmlspecialchars($dbWarning) ?>
            </div>
        <?php endif; ?>

        <!-- STATISTIKAT -->
        <section class="stats-grid">

            <article class="stat-card">

                <div class="stat-icon blue">
                    <i data-lucide="package"></i>
                </div>

                <div>
                    <p>Gjithsej produktet</p>
                    <strong><?= number_format($totalProducts) ?></strong>
                    <small>Produkte në inventar</small>
                </div>

            </article>

            <article class="stat-card">

                <div class="stat-icon violet">
                    <i data-lucide="shopping-cart"></i>
                </div>

                <div>
                    <p>Porositë totale</p>
                    <strong><?= number_format($totalOrders) ?></strong>
                    <small>Porosi të regjistruara</small>
                </div>

            </article>

            <article class="stat-card">

                <div class="stat-icon cyan">
                    <i data-lucide="users"></i>
                </div>

                <div>
                    <p>Klientët</p>
                    <strong><?= number_format($totalCustomers) ?></strong>
                    <small>Klientë të regjistruar</small>
                </div>

            </article>

            <article class="stat-card">

                <div class="stat-icon green">
                    <i data-lucide="wallet"></i>
                </div>

                <div>
                    <p>Të ardhurat</p>
                    <strong><?= money($totalRevenue) ?></strong>
                    <small>Shuma e porosive</small>
                </div>

            </article>

        </section>

        <!-- CONTENT GRID -->
        <section class="content-grid">

            <!-- GRAFIKU -->
            <article class="panel activity-panel">

                <div class="panel-heading">

                    <div>
                        <span class="heading-icon">
                            <i data-lucide="chart-no-axes-combined"></i>
                        </span>

                        <h2>Aktiviteti i biznesit</h2>
                    </div>

                    <span class="period-label">
                        Përmbledhje
                    </span>

                </div>

                <div class="chart-placeholder">

                    <div class="chart-y-labels">
                        <span>200</span>
                        <span>150</span>
                        <span>100</span>
                        <span>50</span>
                        <span>0</span>
                    </div>

                    <div class="chart-area">

                        <div class="chart-grid-lines">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>

                        <svg viewBox="0 0 700 190"
                             preserveAspectRatio="none">

                            <defs>
                                <linearGradient id="chartFill" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0"
                                          stop-color="#2878f0"
                                          stop-opacity=".25"/>

                                    <stop offset="1"
                                          stop-color="#2878f0"
                                          stop-opacity="0"/>
                                </linearGradient>
                            </defs>

                            <path
                                d="M0 150 C55 130 65 135 115 110 S185 130 235 95 S300 115 355 75 S430 105 475 65 S540 95 585 58 S650 68 700 20 L700 190 L0 190Z"
                                fill="url(#chartFill)"
                            />

                            <path
                                d="M0 150 C55 130 65 135 115 110 S185 130 235 95 S300 115 355 75 S430 105 475 65 S540 95 585 58 S650 68 700 20"
                                fill="none"
                                stroke="#1769e8"
                                stroke-width="4"
                            />

                        </svg>

                        <div class="chart-x-labels">
                            <span>1 Tet</span>
                            <span>7 Tet</span>
                            <span>14 Tet</span>
                            <span>21 Tet</span>
                            <span>30 Tet</span>
                        </div>

                    </div>

                </div>

                <p class="chart-note">
                    Grafik demonstrues i aktivitetit.
                </p>

            </article>

            <!-- POROSITË E FUNDIT -->
            <article class="panel orders-panel">

                <div class="panel-heading">

                    <div>
                        <span class="heading-icon">
                            <i data-lucide="shopping-bag"></i>
                        </span>

                        <h2>Porositë e fundit</h2>
                    </div>

                    <a href="orders.php" class="text-link">
                        Shiko të gjitha →
                    </a>

                </div>

                <?php if ($recentOrders): ?>

                    <div class="table-wrap">

                        <table>

                            <thead>
                                <tr>
                                    <th>Porosia</th>
                                    <th>Klienti</th>
                                    <th>Totali</th>
                                    <th>Statusi</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($recentOrders as $order): ?>

                                    <tr>

                                        <td>
                                            #<?= (int)$order["id"] ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($order["customer_name"]) ?>
                                        </td>

                                        <td>
                                            <?= money($order["total"]) ?>
                                        </td>

                                        <td>
                                            <span class="status <?= statusClass((string)$order["status"]) ?>">
                                                <?= htmlspecialchars($order["status"]) ?>
                                            </span>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty-state">
                        <i data-lucide="inbox"></i>

                        <strong>
                            Nuk ka porosi për t'u shfaqur
                        </strong>

                        <span>
                            Porositë e reja do të shfaqen këtu.
                        </span>
                    </div>

                <?php endif; ?>

            </article>

            <!-- PRODUKTET MË TË SHITURA -->
            <article class="panel list-panel">

                <div class="panel-heading">
                    <h2>Produktet më të shitura</h2>
                </div>

                <?php if ($topProducts): ?>

                    <?php foreach ($topProducts as $index => $product): ?>

                        <div class="simple-row">

                            <span class="rank">
                                <?= $index + 1 ?>
                            </span>

                            <div class="row-main">

                                <strong>
                                    <?= htmlspecialchars($product["name"]) ?>
                                </strong>

                                <small>
                                    <?= (int)$product["sold_count"] ?> shitje
                                </small>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-small">
                        Të dhënat e shitjeve do të shfaqen këtu.
                    </div>

                <?php endif; ?>

            </article>

            <!-- STOKU I ULËT -->
            <article class="panel list-panel">

                <div class="panel-heading">
                    <h2>Stoku i ulët</h2>
                </div>

                <?php if ($lowStock): ?>

                    <?php foreach ($lowStock as $product): ?>

                        <div class="simple-row">

                            <div class="row-main">

                                <strong>
                                    <?= htmlspecialchars($product["name"]) ?>
                                </strong>

                                <small>
                                    <?= (int)$product["quantity"] ?> copë në stok
                                </small>

                            </div>

                            <span class="status low">
                                I ulët
                            </span>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-small">
                        Nuk ka produkte me stok të ulët.
                    </div>

                <?php endif; ?>

            </article>

            <!-- PROMO -->
            <article class="promo-panel">

                <div class="promo-copy">

                    <span class="rocket">🚀</span>

                    <h2>Rrit biznesin tënd!</h2>

                    <p>
                        Menaxho produktet dhe stokun në një vend të vetëm.
                    </p>

                    <a href="products.php" class="primary-button">
                        Menaxho produktet →
                    </a>

                </div>

                <i class="promo-art" data-lucide="boxes"></i>

            </article>

        </section>

        <footer class="footer">
            © <?= date("Y") ?> Inventari.io · Të gjitha të drejtat e rezervuara.
        </footer>

    </main>

</div>

<!-- JAVASCRIPT BRENDA PHP -->
<script>

    // Menuja për telefon
    const menuButton = document.getElementById("menuButton");
    const sidebar = document.getElementById("sidebar");

    menuButton?.addEventListener("click", () => {
        sidebar?.classList.toggle("open");
    });

    // Kërkimi në porosi
    const searchInput = document.getElementById("searchInput");

    searchInput?.addEventListener("input", (event) => {

        const query = event.target.value.toLocaleLowerCase("sq");

        document.querySelectorAll(".orders-panel tbody tr").forEach((row) => {

            row.hidden = !row.textContent
                .toLocaleLowerCase("sq")
                .includes(query);

        });

    });

    // Aktivizimi i ikonave
    lucide.createIcons();

</script>

</body>
</html>
