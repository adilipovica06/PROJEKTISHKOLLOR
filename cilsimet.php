
<?php
session_start();

if (!isset($_SESSION['settings'])) {
    $_SESSION['settings'] = [
        'business_name' => 'Inventari',
        'email' => 'admin@inventari.com',
        'phone' => '',
        'address' => '',
        'currency' => 'EUR',
        'theme' => 'light',
        'notifications' => 'all',
        'low_stock' => 5,
        'backup' => true
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'save') {
        $_SESSION['settings']['business_name'] =
            trim($_POST['business_name'] ?? 'Inventari');

        $_SESSION['settings']['email'] =
            trim($_POST['email'] ?? '');

        $_SESSION['settings']['phone'] =
            trim($_POST['phone'] ?? '');

        $_SESSION['settings']['address'] =
            trim($_POST['address'] ?? '');

        $_SESSION['settings']['currency'] =
            in_array($_POST['currency'] ?? '', ['EUR', 'USD', 'CHF'])
            ? $_POST['currency'] : 'EUR';

        $_SESSION['settings']['theme'] =
            in_array($_POST['theme'] ?? '', ['light', 'dark'])
            ? $_POST['theme'] : 'light';

        $_SESSION['settings']['notifications'] =
            in_array($_POST['notifications'] ?? '', ['all', 'important', 'off'])
            ? $_POST['notifications'] : 'all';

        $_SESSION['settings']['low_stock'] =
            max(0, min(10000, (int)($_POST['low_stock'] ?? 5)));

        $_SESSION['settings']['backup'] =
            isset($_POST['backup']);

        $message = 'Cilësimet u ruajtën me sukses!';
    }

    if ($action === 'import' && isset($_FILES['file'])) {
        if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $data = json_decode(
                file_get_contents($_FILES['file']['tmp_name']),
                true
            );

            if (is_array($data)) {
                foreach ($_SESSION['settings'] as $key => $value) {
                    if (array_key_exists($key, $data)) {
                        $_SESSION['settings'][$key] = $data[$key];
                    }
                }

                $message = 'Cilësimet u importuan!';
            } else {
                $message = 'Skedari nuk është JSON i vlefshëm.';
            }
        }
    }
}

$s = $_SESSION['settings'];

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cilësimet - Inventari</title>

<style>
:root {
    --navy: #050B1F;
    --navy-blue: #0A2A66;
    --blue: #2563EB;
    --bright-blue: #3B82F6;
    --white: #FFFFFF;
    --gray: #F3F4F6;
    --text: #10264D;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: var(--gray);
    color: var(--text);
    transition: 0.2s;
}

body.dark {
    background: var(--navy);
    color: var(--white);
}

.sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    padding: 25px 15px;
    background: var(--navy);
    color: white;
}

.logo {
    font-size: 25px;
    font-weight: bold;
    margin: 5px 10px 35px;
}

.sidebar a {
    display: block;
    padding: 14px;
    margin-bottom: 8px;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}

.sidebar a:hover,
.sidebar a.active {
    background: var(--blue);
}

main {
    margin-left: 240px;
    padding: 30px;
}

header {
    background: var(--navy);
    color: white;
    padding: 20px 25px;
    border-radius: 12px;
    margin-bottom: 25px;
}

header h1 {
    margin: 0 0 8px;
}

header p {
    margin: 0;
    color: #CBD8F2;
}

.message {
    padding: 14px;
    background: #D1FAE5;
    color: #065F46;
    border-radius: 8px;
    margin-bottom: 20px;
}

.card {
    background: white;
    padding: 25px;
    margin-bottom: 20px;
    border-radius: 12px;
    border: 1px solid #DFE8F7;
    box-shadow: 0 3px 12px #10264D0A;
}

.dark .card {
    background: var(--navy-blue);
    color: white;
    border-color: #24477D;
}

.card h2 {
    margin-top: 0;
    color: var(--blue);
    font-size: 20px;
}

.dark .card h2 {
    color: #93B7FF;
}

.grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    font-size: 14px;
}

input, select, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    color: #10264D;
}

.field {
    margin-bottom: 18px;
}

input[type="checkbox"] {
    width: auto;
    accent-color: var(--blue);
}

.check-label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 16px 0;
}

button, .button {
    display: inline-block;
    background: var(--blue);
    color: white;
    padding: 12px 18px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
}

button:hover, .button:hover {
    background: var(--bright-blue);
}

.secondary {
    background: white;
    color: var(--blue);
    border: 1px solid var(--blue);
}

.avatar {
    width: 60px;
    height: 60px;
    display: inline-grid;
    place-items: center;
    border-radius: 50%;
    background: var(--blue);
    color: white;
    font-size: 22px;
    font-weight: bold;
    margin-right: 12px;
}

.palette {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.swatch {
    width: 30px;
    height: 30px;
    border-radius: 7px;
}

.actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

@media (max-width: 750px) {
    .sidebar {
        position: static;
        width: 100%;
        height: auto;
    }

    .sidebar a {
        display: inline-block;
        padding: 10px;
    }

    main {
        margin-left: 0;
        padding: 15px;
    }

    .grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body class="<?= $s['theme'] === 'dark' ? 'dark' : '' ?>">

<aside class="sidebar">
    <div class="logo">⬡ Inventari</div>
    <a href="index.php">⌂ Kreu</a>
    <a href="index.php#products">◇ Produktet</a>
    <a href="index.php#notifications">♧ Njoftimet</a>
    <a href="#profile">♙ Profili im</a>
    <a href="#business">▤ Të dhënat e biznesit</a>
    <a href="#backup">☁ Backup & Import</a>
    <a href="#security" class="active">⚙ Cilësimet</a>
</aside>

<main>
<header>
    <h1>⚙ Cilësimet</h1>
    <p>Menaxho dhe personalizo webfaqen tënde Inventari.</p>
</header>

<?php if (!empty($message)): ?>
    <div class="message"><?= h($message) ?></div>
<?php endif; ?>

<form method="POST">
<input type="hidden" name="action" value="save">

<section class="card" id="profile">
    <h2>♙ Profili im</h2>

    <div>
        <span class="avatar">EL</span>
        <strong>Elisa</strong>
        <p>Administratore e inventarit</p>
    </div>

    <p>Email: <?= h($s['email']) ?></p>
    <p>Roli: Administrator</p>
</section>

<section class="card" id="notifications">
    <h2>♧ Njoftimet</h2>

    <div class="field">
        <label>Preferencat e njoftimeve</label>
        <select name="notifications">
            <option value="all" <?= $s['notifications']==='all'?'selected':'' ?>>
                Të gjitha njoftimet
            </option>
            <option value="important" <?= $s['notifications']==='important'?'selected':'' ?>>
                Vetëm të rëndësishmet
            </option>
            <option value="off" <?= $s['notifications']==='off'?'selected':'' ?>>
                Të çaktivizuara
            </option>
        </select>
    </div>

    <label class="check-label">
        <input type="checkbox" checked disabled>
        Njoftimet për stok të ulët
    </label>
</section>

<section class="card" id="appearance">
    <h2>◐ Pamja</h2>

    <div class="field">
        <label>Tema e webfaqes</label>
        <select name="theme">
            <option value="light" <?= $s['theme']==='light'?'selected':'' ?>>
                Light — e bardhë dhe blu
            </option>
            <option value="dark" <?= $s['theme']==='dark'?'selected':'' ?>>
                Dark — navy blue dhe blu
            </option>
        </select>
    </div>

    <div class="palette">
        <span class="swatch" style="background:#050B1F"></span>
        <span class="swatch" style="background:#0A2A66"></span>
        <span class="swatch" style="background:#2563EB"></span>
        <span class="swatch" style="background:#3B82F6"></span>
        <span class="swatch" style="background:#FFFFFF;border:1px solid #ddd"></span>
        <span>Paleta Navy Blue · Blue · White</span>
    </div>
</section>

<section class="card" id="business">
    <h2>▤ Të dhënat e biznesit</h2>

    <div class="grid">
        <div class="field">
            <label>Emri i biznesit</label>
            <input name="business_name" required maxlength="100"
                   value="<?= h($s['business_name']) ?>">
        </div>

        <div class="field">
            <label>Email</label>
            <input type="email" name="email"
                   value="<?= h($s['email']) ?>">
        </div>

        <div class="field">
            <label>Numri i telefonit</label>
            <input name="phone" value="<?= h($s['phone']) ?>">
        </div>

        <div class="field">
            <label>Monedha</label>
            <select name="currency">
                <option value="EUR" <?= $s['currency']==='EUR'?'selected':'' ?>>Euro (€)</option>
                <option value="USD" <?= $s['currency']==='USD'?'selected':'' ?>>Dollar ($)</option>
                <option value="CHF" <?= $s['currency']==='CHF'?'selected':'' ?>>Frang zviceran (CHF)</option>
            </select>
        </div>

        <div class="field" style="grid-column:1/-1">
            <label>Adresa e biznesit</label>
            <textarea name="address" rows="3"><?= h($s['address']) ?></textarea>
        </div>
    </div>
</section>

<section class="card" id="security">
    <h2>♢ Stoku dhe siguria</h2>

    <div class="field">
        <label>Pragu i stokut të ulët</label>
        <input type="number" name="low_stock" min="0" max="10000"
               value="<?= h($s['low_stock']) ?>">
        <small>Shfaq paralajmërim kur stoku arrin këtë numër ose më pak.</small>
    </div>

    <label class="check-label">
        <input type="checkbox" name="backup" <?= $s['backup']?'checked':'' ?>>
        Aktivizo kujtesën për backup
    </label>

    <p>🔒 Për siguri të plotë, përdor hyrje me fjalëkalim dhe databazë.</p>
</section>

<section class="card" id="backup">
    <h2>☁ Backup & Import</h2>
    <p>Shkarko një kopje rezervë të cilësimeve ose importo një skedar JSON.</p>

    <div class="actions">
        <a class="button" href="backup.php">Shkarko backup</a>
    </div>

    <div class="field" style="margin-top:20px">
        <label>Importo cilësimet (JSON)</label>
        <input type="file" id="importFile" accept=".json,application/json">
    </div>

    <button type="button" class="secondary" onclick="importSettings()">
        Importo cilësimet
    </button>
</section>

<div class="actions" style="margin-bottom:30px">
    <button type="submit">Ruaj cilësimet</button>
    <a class="button secondary" href="index.php">Kthehu te paneli</a>
</div>

</form>
</main>

<script>
async function importSettings() {
    const file = document.getElementById('importFile').files[0];

    if (!file) {
        alert('Zgjidh një skedar JSON fillimisht.');
        return;
    }

    try {
        const data = JSON.parse(await file.text());

        
        const allowed = [
            'business_name', 'email', 'phone', 'address',
            'currency', 'theme', 'notifications', 'low_stock', 'backup'
        ];

        for (const key of allowed) {
            if (Object.prototype.hasOwnProperty.call(data, key)) {
                const field = document.querySelector(
                    '[name="' + key + '"]'
                );

                if (field) {
                    if (field.type === 'checkbox') {
                        field.checked = Boolean(data[key]);
                    } else {
                        field.value = data[key];
                    }
                }
            }
        }

        alert('Të dhënat u ngarkuan në formular. Kliko "Ruaj cilësimet".');
    } catch (error) {
        alert('Skedari nuk është JSON i vlefshëm.');
    }
}
</script>

</body>
</html>
