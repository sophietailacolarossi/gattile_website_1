<?php
// Gestione della registrazione: validazione lato server e inserimento nel DB

$titoloPagina = 'Registrazione';
require 'includes/header.php';
require_once 'includes/db.php';

$errore   = '';
$successo = '';

//estraggo le variabili globali inviate con post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome'] ?? '');
    $cognome   = trim($_POST['cognome'] ?? '');
    $indirizzo = trim($_POST['indirizzo'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $conferma  = $_POST['conferma_password'] ?? '';

    $errori = [];

    if ($nome === '') $errori[] = 'Il nome è obbligatorio.';
    if ($cognome === '') $errori[] = 'Il cognome è obbligatorio.';
    if ($indirizzo === '') $errori[] = "L'indirizzo è obbligatorio.";

    // Espressioni regolari per validare username e password lato server
    if (!preg_match('/^[a-zA-Z]/', $username)) {
        $errori[] = 'Lo username deve iniziare con un carattere alfabetico.';
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/', $password)) {
        $errori[] = 'La password deve essere lunga da 8 a 16 caratteri e contenere almeno una maiuscola, una minuscola, un numero e un carattere speciale.';
    }

    if ($password !== $conferma) {
        $errori[] = 'Le password non coincidono.';
    }

    if (empty($errori)) {
        // password_hash con bcrypt: genera un hash irreversibile con salt, prevenendo attacchi dizionario
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Connessione lecture (solo SELECT): verifica che lo username non sia già presente
        $conCheck = getDB('lecture');
        $check = mysqli_prepare($conCheck, 'SELECT id FROM utenti WHERE username = ?');
        mysqli_stmt_bind_param($check, 's', $username);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $errore = 'Username già in uso. Sceglierne un altro.';
        } else {
            // Connessione registrator (solo INSERT su utenti): inserisce il nuovo utente
            $con  = getDB('registrator');
            // is_admin impostato a FALSE: nessun utente registrato può essere admin 
            $stmt = mysqli_prepare($con, 'INSERT INTO utenti (nome, cognome, indirizzo, username, password, is_admin) VALUES (?, ?, ?, ?, ?, FALSE)');
            mysqli_stmt_bind_param($stmt, 'sssss', $nome, $cognome, $indirizzo, $username, $passwordHash);

            if (!mysqli_stmt_execute($stmt)) {
                $errore = 'Errore durante la registrazione. Riprova più tardi.';
            } else {
                $successo = 'Registrazione avvenuta con successo! Puoi ora <a href="login.php">accedere</a>.';
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check);

    } else {
        // Mostro tutti gli errori in una lista: l'utente vede subito tutti i campi sbagliati
        $errore = '<ul><li>' . implode('</li><li>', $errori) . '</li></ul>';
    }
}
?>

<h1 class="sr-only">Registrazione</h1> <!-- solo per screen reader-->
<div class="form-container">
    <h2>Registrati</h2>

    <?php if ($successo): ?>
        <div class="messaggio-pagina successo" role="status"><?= $successo ?></div>
    <?php elseif ($errore): ?>
        <!-- Messaggio di errore server-side -->
        <div class="messaggio-pagina errore" role="alert"><?= $errore ?></div>
    <?php endif; ?>

    <?php if (!$successo): ?>
    <form id="form-registrazione" method="post" action="registrazione.php" novalidate>
        <div class="campo-form">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome"
                   value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
            <span class="errore-campo" id="errore-nome"></span>
        </div>

        <div class="campo-form">
            <label for="cognome">Cognome</label>
            <input type="text" id="cognome" name="cognome"
                   value="<?= htmlspecialchars($_POST['cognome'] ?? '') ?>" required>
            <span class="errore-campo" id="errore-cognome"></span>
        </div>

        <div class="campo-form">
            <label for="indirizzo">Indirizzo</label>
            <input type="text" id="indirizzo" name="indirizzo"
                   value="<?= htmlspecialchars($_POST['indirizzo'] ?? '') ?>" required>
            <span class="errore-campo" id="errore-indirizzo"></span>
        </div>

        <div class="campo-form">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                   autocomplete="username" required>
            <span class="errore-campo" id="errore-username"></span>
        </div>

        <div class="campo-form">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   autocomplete="new-password" required>
            <span class="errore-campo" id="errore-password"></span>
        </div>

        <div class="campo-form">
            <label for="conferma_password">Conferma password</label>
            <input type="password" id="conferma_password" name="conferma_password"
                   autocomplete="new-password" required>
            <span class="errore-campo" id="errore-conferma"></span>
        </div>

        <button type="submit" id="btn-registra" class="btn-submit">Registrati</button>
    </form>
    <?php endif; ?>

    <p class="link-alternativo">
        Hai già un account? <a href="login.php">Accedi</a>
    </p>
</div>

<script src="js/registrazione.js"></script>

<?php require 'includes/footer.php'; ?>
