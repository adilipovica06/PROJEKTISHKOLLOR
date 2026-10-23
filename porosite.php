
<?php
$porosite = [
    ["id" => "#1001", "klienti" => "Arti Krasniqi", "produkti" => "3 produkte", "data" => "18.10.2025", "shuma" => "€59.98", "statusi" => "Në pritje"],
    ["id" => "#1002", "klienti" => "Blerita Dervishi", "produkti" => "2 produkte", "data" => "17.10.2025", "shuma" => "€120.50", "statusi" => "Në proces"],
    ["id" => "#1003", "klienti" => "Kastrit Rexhepi", "produkti" => "1 produkt", "data" => "16.10.2025", "shuma" => "€89.99", "statusi" => "E përfunduar"],
    ["id" => "#1004", "klienti" => "Erona Berisha", "produkti" => "4 produkte", "data" => "15.10.2025", "shuma" => "€245.20", "statusi" => "Në pritje"],
    ["id" => "#1005", "klienti" => "Denton Lushi", "produkti" => "2 produkte", "data" => "14.10.2025", "shuma" => "€76.45", "statusi" => "Në proces"],
    ["id" => "#1006", "klienti" => "Aime Shala", "produkti" => "3 produkte", "data" => "13.10.2025", "shuma" => "€132.20", "statusi" => "E përfunduar"],
    ["id" => "#1007", "klienti" => "Flam Gashi", "produkti" => "2 produkte", "data" => "12.10.2025", "shuma" => "€49.99", "statusi" => "E përfunduar"]
];

$kerko = $_GET["kerko"] ?? "";
$statusi = $_GET["statusi"] ?? "";
$filtruar = array_filter($porosite, function ($p) use ($kerko, $statusi) {
    return (stripos($p["klienti"], $kerko) !== false ||
            stripos($p["id"], $kerko) !== false) &&
           ($statusi === "" || $p["statusi"] === $statusi);
});
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Porositë | Inventari.io</title>
    <link rel="stylesheet" href="Porosite.css">
</head>
<body>

<aside class="sidebar">
    <h2 class="logo"><span>⬡</span> Inventari.io</h2>

    <nav>
        <a href="#">▦ <span>Dashboard</span></a>
        <a href="#">⬡ <span>Produktet</span></a>
        <a href="porosite" class="active">🛒 <span>Porositë</span></a>
        <a href="#">♧ <span>Klientët</span></a>
        <a href="#">▤ <span>Raportet</span></a>
        <a href="cilsimet">⚙ <span>Cilësimet</span></a>
    </nav>

    <a href="#" class="logout">⇥ Dil nga llogaria</a>
</aside>

<main class="main">
    <header class="topbar">
        <input type="search" placeholder="⌕  Kërko porosi, klient, produkt...">
        <div class="admin">♧　 <b>A</b> Admin　⌄</div>
    </header>

    <section class="heading">
        <div>
            <h1>🛒 Porositë</h1>
            <p>Këtu shfaqen të gjitha porositë e regjistruara në sistem.</p>
        </div>
        <button onclick="document.getElementById('forma').classList.toggle('hidden')">
            + Shto porosi të re
        </button>
    </section>

    <form id="forma" class="new-order hidden" method="get">
        <h3>Kërko porosi</h3>
        <input name="kerko" placeholder="ID ose emri i klientit"
               value="<?= htmlspecialchars($kerko) ?>">
        <select name="statusi">
            <option value="">Të gjitha statuset</option>
            <option value="Në pritje">Në pritje</option>
            <option value="Në proces">Në proces</option>
            <option value="E përfunduar">E përfunduar</option>
        </select>
        <button type="submit">Filtro</button>
        <a href="porosite.php">Pastro filtrat</a>
    </form>

    <section class="stats">
        <div class="stat blue">
            <span>▤</span><p>Gjithsej porosi</p>
            <h2>24</h2><small>↑ 12% nga muaji kaluar</small>
        </div>
        <div class="stat yellow">
            <span>◷</span><p>Në pritje</p>
            <h2>6</h2><small>↑ 2% nga muaji kaluar</small>
        </div>
        <div class="stat cyan">
            <span>⚙</span><p>Në proces</p>
            <h2>5</h2><small>↑ 5% nga muaji kaluar</small>
        </div>
        <div class="stat green">
            <span>✓</span><p>Të përfunduara</p>
            <h2>13</h2><small>↑ 8% nga muaji kaluar</small>
        </div>
    </section>

    <section class="table-card">
        <form class="filters" method="get">
            <input type="search" name="kerko"
                   placeholder="⌕  Kërko porosi..."
                   value="<?= htmlspecialchars($kerko) ?>">

            <select name="statusi">
                <option value="">Të gjitha statuset</option>
                <option value="Në pritje" <?= $statusi === "Në pritje" ? "selected" : "" ?>>Në pritje</option>
                <option value="Në proces" <?= $statusi === "Në proces" ? "selected" : "" ?>>Në proces</option>
                <option value="E përfunduar" <?= $statusi === "E përfunduar" ? "selected" : "" ?>>E përfunduar</option>
            </select>

            <select>
                <option>Mujor</option>
                <option>Javor</option>
                <option>Vjetor</option>
            </select>

            <button type="submit">Kërko</button>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Klienti</th>
                        <th>Produkte</th>
                        <th>Data e porosisë</th>
                        <th>Shuma</th>
                        <th>Statusi</th>
                        <th>Veprimet</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filtruar as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p["id"]) ?></td>
                        <td>
                            <div class="customer">
                                <span class="avatar"><?= strtoupper(substr($p["klienti"], 0, 1)) ?></span>
                                <span>
                                    <strong><?= htmlspecialchars($p["klienti"]) ?></strong>
                                    <small>Klient i regjistruar</small>
                                </span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($p["produkti"]) ?></td>
                        <td><?= htmlspecialchars($p["data"]) ?></td>
                        <td><strong><?= htmlspecialchars($p["shuma"]) ?></strong></td>
                        <td>
                            <span class="status <?= $p["statusi"] === "Në pritje" ? "pending" : ($p["statusi"] === "Në proces" ? "processing" : "done") ?>">
                                <?= htmlspecialchars($p["statusi"]) ?>
                            </span>
                        </td>
                        <td><button class="view" onclick="alert('Porosia <?= $p['id'] ?>')">◉ Shiko</button></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($filtruar)): ?>
                    <tr><td colspan="7" class="empty">Nuk u gjet asnjë porosi.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <footer class="table-footer">
            <span>Duke shfaqur <?= count($filtruar) ?> nga <?= count($porosite) ?> porosi</span>
            <div><button disabled>‹</button> <button class="page">1</button> <button disabled>›</button></div>
        </footer>
    </section>
</main>
</body>
</html>
