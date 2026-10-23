<?php
session_start();
require_once "Database.php";

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["login"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($login === "" || $password === "") {
        $error = "Plotëso të gjitha fushat.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, username, email, password
             FROM users
             WHERE email = :email OR username = :username
             LIMIT 1"
        );

        $stmt->execute([
            "email" => $login,
            "username" => $login
        ]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];

            header("Location: dashboard.php");
            exit;
        }

        $error = "Emaili ose fjalëkalimi është gabim.";
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Inventari.io</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<main class="login-page">
    <section class="welcome">
        <a class="logo" href="login.php">
            Inventari<span>.io</span>
        </a>

        <div class="welcome-content">
            <span class="tag">SMART INVENTORY MANAGEMENT</span>

            <h1>Menaxho inventarin.<br>
                <span>Kontrollo biznesin.</span>
            </h1>

            <p>
                Një mënyrë e thjeshtë dhe profesionale për të
                menaxhuar produktet, stokun dhe aktivitetin
                e biznesit tënd.
            </p>

            <div class="feature">
                <span class="feature-icon">01</span>
                <div>
                    <h3>Menaxhim i lehtë</h3>
                    <p>Mbaji produktet të organizuara në një vend.</p>
                </div>
            </div>

            <div class="feature">
                <span class="feature-icon">02</span>
                <div>
                    <h3>Kontroll i stokut</h3>
                    <p>Kontrollo sasitë dhe produktet në dispozicion.</p>
                </div>
            </div>

            <div class="feature">
                <span class="feature-icon">03</span>
                <div>
                    <h3>Raporte të qarta</h3>
                    <p>Organizo të dhënat dhe ndiq aktivitetin.</p>
                </div>
            </div>
        </div>

        <p class="footer-text">Inventari.io &mdash; Menaxho më lehtë.</p>
    </section>

    <section class="form-section">
        <div class="login-card">
            <div class="mobile-logo">
                Inventari<span>.io</span>
            </div>

            <div class="icon-box">↗</div>

            <p class="welcome-back">MIRË SE U KTHEVE</p>
            <h2>Hyr në llogarinë tënde</h2>
            <p class="subtitle">
                Shkruaj të dhënat për të vazhduar në Inventari.io.
            </p>

            <?php if ($error !== ""): ?>
                <div class="error" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <label for="login">Email ose Username</label>
                <input
                    type="text"
                    id="login"
                    name="login"
                    placeholder="Shkruaj emailin ose username"
                    autocomplete="username"
                    value="<?= htmlspecialchars($_POST["login"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    required
                >

                <div class="password-label">
                    <label for="password">Fjalëkalimi</label>
                </div>

                <div class="password-field">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Shkruaj fjalëkalimin"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" id="togglePassword"
                            aria-label="Shfaq fjalëkalimin">Shfaq</button>
                </div>

                <div class="form-options">
                    <span class="secure-note">Hyrje e sigurt</span>
                    <a href="#" id="forgotLink">Ke harruar fjalëkalimin?</a>
                </div>

                <button type="submit" class="login-button">
                    Log In <span>→</span>
                </button>
            </form>

            <div class="security-note">
                <span>●</span> Të dhënat e llogarisë tënde mbrohen.
            </div>
        </div>
    </section>
</main>

<script>
const password = document.getElementById("password");
const toggle = document.getElementById("togglePassword");

toggle.addEventListener("click", () => {
    const show = password.type === "password";
    password.type = show ? "text" : "password";
    toggle.textContent = show ? "Fshih" : "Shfaq";
    toggle.setAttribute(
        "aria-label",
        show ? "Fshih fjalëkalimin" : "Shfaq fjalëkalimin"
    );
});

document.getElementById("forgotLink").addEventListener("click", (event) => {
    event.preventDefault();
    alert("Funksioni i rikuperimit të fjalëkalimit nuk është konfiguruar ende.");
});
</script>

</body>
</html>
