<?php
// Avvio la sessione se non è già attiva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Il titolo varia con la navigazione interna
$titoloPagina = $titoloPagina ?? 'Casa del Gatto';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Sophie Taila Colarossi">
    <meta name="description" content="Casa del Gatto: adotta un gatto o diventa volontario presso il nostro gattile.">
    <meta name="keywords" content="gatti, adozione, volontariato, gattile">
    <title><?= htmlspecialchars($titoloPagina) ?> – Casa del Gatto</title>
    <link rel="icon" type="image/png" href="img/icon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<!-- Link di accessibilità per la navigazione da tastiera -->
<a href="#contenuto-principale" class="skip-link">Vai al contenuto</a>
<header class="site-header">
    <div class="header-inner">
        <!-- Logo con collegamento alla home -->
        <div class="header-logo">
            <a href="home.php">
                <img src="img/icon.png" alt="Logo gatto" class="logo-icona">
                Casa del Gatto
            </a>
        </div>
        <!-- Navigazione principale -->
        <nav class="header-nav" aria-label="Navigazione principale">
            <a href="home.php">Home</a>
            <a href="gatti.php">I nostri amici pelosi</a>
            <a href="volontariato.php">Volontariato</a>
            <?php if (isset($_SESSION['utente']) && $_SESSION['is_admin']): ?>
                <a href="inserisci_gatto.php">Inserisci gatto</a>
            <?php endif; ?>
        </nav>
        <!-- Stato utente: se loggato mostra username e logout, altrimenti accedi/registrati -->
        <div class="header-user">
            <?php if (isset($_SESSION['utente'])): ?>
                <span class="username-label">Utente <?= htmlspecialchars($_SESSION['utente']) ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <span class="username-label not-logged">non loggato</span>
                <a href="login.php" class="btn-login">Accedi</a>
                <a href="registrazione.php" class="btn-register">Registrati</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="site-main" id="contenuto-principale">
