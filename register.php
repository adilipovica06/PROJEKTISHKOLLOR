
<?php
session_start();
require_once "Database.php";

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST["fullname"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (
        $fullname === "" ||
        $username === "" ||
        $email === "" ||
        $password === "" ||
        $confirmPassword === ""
    ) {
        $error = "Plotëso të gjitha fushat.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Shkruaj një email të vlefshëm.";
    } elseif (strlen($username) < 3) {
        $error = "Username duhet të ketë së paku 3 karaktere.";
    } elseif (strlen($password) < 8) {
        $error = "Fjalëkalimi duhet të ketë së paku 8 karaktere.";
    } elseif ($password !== $confirmPassword) {
        $error = "Fjalëkalimet nuk përputhen.";
    } else {
        try {
            $check = $pdo->prepare(
                "SELECT id FROM users
                 WHERE email = :email OR username = :username
                 LIMIT 1"
            );

            $check->execute([
                "email" => $email,
                "username" => $username
            ]);

            if ($check->fetch()) {
                $error = "Email ose username ekziston tashmë.";
            } else {
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $pdo->prepare(
                    "INSERT INTO users (username, email, password)
                     VALUES (:username, :email, :password)"
                );

                $stmt->execute([
                    "username" => $username,
                    "email" => $email,
                    "password" => $hashedPassword
                ]);

                header("Location: login.php?registered=1");
                exit;
            }

        } catch (PDOException $e) {
            $error = "Ndodhi një gabim gjatë regjistrimit.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regjistrohu | Inventari.io</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>

<main class="register-page">

    <section class="welcome">
        <a href="login.php" class="logo">
            Inventari<span>.io</span>
        </a>

        <div class="welcome-content">
            <span class="tag">SMART INVENTORY MANAGEMENT</span>

            <h1>Fillo të menaxhosh.<br>
                <span>Rrit biznesin tënd.</span>
            </h1>

            <p>
                Krijo llogarinë tënde në Inventari.io
                dhe menaxho produktet, stokun dhe
                aktivitetin e biznesit në një vend.
            </p>

            <div class="feature">
                <span class="feature-icon">01</span>
                <div>
                    <h3>Menaxhim i lehtë</h3>
                    <p>Organizo produktet në mënyrë profesionale.</p>
                </div>
            </div>

            <div class="feature">
                <span class="feature-icon">02</span>
                <div>
                    <h3>Kontroll i stokut</h3>
                    <p>Mbaj nën kontroll produktet dhe sasitë.</p>
                </div>
            </div>

            <div class="feature">
                <span class="feature-icon">03</span>
                <div>
                    <h3>Raporte të qarta</h3>
                    <p>Ndiq aktivitetin e biznesit tënd.</p>
                </div>
            </div>
        </div>

        <p class="footer-text">
            Inventari.io — Menaxho më lehtë.
        </p>
    </section>

    <section class="form-section">
        <div class="register-card">

            <div class="mobile-logo">
                Inventari<span>.io</span>
            </div>

            <p class="welcome-back">KRIJO LLOGARINË TËNDE</p>
            <h2>Regjistrohu</h2>
            <p class="subtitle">
                Plotëso të dhënat për të filluar.
            </p>

            <?php if ($error !== ""): ?>
                <div class="error" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">

                <label for="fullname">Emri dhe mbiemri</label>
                <input
                    type="text"
                    id="fullname"
                    name="fullname"
                    placeholder="Shkruaj emrin dhe mbiemrin"
                    value="<?= htmlspecialchars($_POST["fullname"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    autocomplete="name"
                    required
                >

                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Zgjidh një username"
                    value="<?= htmlspecialchars($_POST["username"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    autocomplete="username"
                    required
                >

                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Shkruaj emailin"
                    value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    autocomplete="email"
                    required
                >

                <label for="password">Fjalëkalimi</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimum 8 karaktere"
                    autocomplete="new-password"
                    required
                >

                <label for="confirm_password">Konfirmo fjalëkalimin</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Shkruaj përsëri fjalëkalimin"
                    autocomplete="new-password"
                    required
                >

                <button type="submit" class="register-button">
                    Krijo llogarinë <span>→</span>
                </button>

            </form>

            <div class="login-link">
                Ke tashmë llogari?
                <a href="login.php">Hyr këtu</a>
            </div>

        </div>
    </section>

</main>

</body>
</html>
