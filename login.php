<?php
// Gestione del login lato server: verifica credenziali e imposta la sessione

$titoloPagina = 'Accedi';
require 'includes/header.php';
require_once 'includes/db.php';

$errore = '';

// POST: dati inviati dal form (meglio rispetto a GET perchè non espone i dati nell'URI)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $ricordami = isset($_POST['ricordami']);

    if ($username === '' || $password === '') {
        $errore = 'Inserisci username e password.';
    } else {
        // Connessione lecture (solo SELECT): verifica se l'utente esiste nel DB
        $con  = getDB('lecture');
        $stmt = mysqli_prepare($con, 'SELECT id, username, password, is_admin FROM utenti WHERE username = ?');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $usernameDB, $passwordDB, $isAdmin);

        $utenteTrovato = false;
        if (mysqli_stmt_fetch($stmt)) {
            $utenteTrovato = true;
        }
        mysqli_stmt_close($stmt);

        $passwordOk = false;
        if ($utenteTrovato) {
            // password_verify confronta la password in chiaro con l'hash bcrypt nel DB
            if (password_verify($password, $passwordDB)) {
                $passwordOk = true;
            } elseif ($passwordDB === $password) {
                // Fallback per utenti già presenti nel db con password in chiaro
                $passwordOk = true;
            }
        }

        if ($passwordOk) {
            // Rigenera l'ID di sessione 
            session_regenerate_id(true);
            $_SESSION['utente']    = $usernameDB;
            $_SESSION['utente_id'] = (int)$id;
            $_SESSION['is_admin']  = (bool)$isAdmin;

            // Cookie "ricordami"
            if ($ricordami) {
                setcookie('ultimo_username', $username, time() + 72 * 3600, '/', '', false, true);
            } else {
                setcookie('ultimo_username', '', time() - 3600, '/');
            }

            header('Location: home.php');
            exit;
        } else {
            $errore = 'Username o password non corretti.';
        }
    }
}

// Precompila il campo username dal cookie se l'utente non è loggato
$usernamePrefill = '';
if (!isset($_SESSION['utente']) && isset($_COOKIE['ultimo_username'])) {
    $usernamePrefill = htmlspecialchars($_COOKIE['ultimo_username']);
}
?>

<h1 class="sr-only">Accedi</h1>
<div class="form-container">
    <h2>Accedi</h2>

    <?php if ($errore): ?>
        <div class="messaggio-pagina errore" role="alert"><?= htmlspecialchars($errore) ?></div>
    <?php endif; ?>

    <!-- Form con method POST -->
    <form id="form-login" method="post" action="login.php" novalidate>
        <div class="campo-form">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   value="<?= $usernamePrefill ?>"
                   autocomplete="username" required>
            <span class="errore-campo" id="errore-username"></span>
        </div>

        <div class="campo-form">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   autocomplete="current-password" required>
            <span class="errore-campo" id="errore-password"></span>
        </div>

        <div class="checkbox-gruppo">
            <input type="checkbox" id="ricordami" name="ricordami">
            <label for="ricordami">Salva il mio username in un cookie per 72 ore</label>
            <p class="info-cookie">
                Verrà salvato un cookie sul tuo dispositivo contenente solo il tuo username.
                Nessuna password viene memorizzata.
            </p>
        </div>

        <button type="submit" id="btn-login" class="btn-submit">Accedi</button>
    </form>

    <p class="link-alternativo">
        Non hai un account? <a href="registrazione.php">Registrati</a>
    </p>
</div>

<!-- Validazione client-side: evita di inviare al server dati con formato errato o assenti -->
<script src="js/login.js"></script>

<?php require 'includes/footer.php'; ?>
