<?php
$titoloPagina = 'Volontariato';
require 'includes/header.php';

// Verifico se l'utente è loggato tramite $_SESSION
$loggato = isset($_SESSION['utente']);
?>

<h1 class="sezione-titolo">Turni di volontariato</h1>

<?php if (!$loggato): ?>
    <!-- Utente non autenticato: lo invito ad accedere o registrarsi -->
    <div class="avviso-auth">
        <p>Per iscriverti ai turni di volontariato devi essere registrato e aver effettuato l'accesso.</p>
        <a href="login.php" class="btn-submit">Accedi</a>
        <a href="registrazione.php" class="btn-submit">Registrati</a>
    </div>
<?php else: ?>

<!-- Prevenzione errori (Nielsen): informo subito l'utente sul limite di 2 volontari -->
<p class="testo-intro">
    Seleziona le fasce orarie in cui desideri prestare servizio. La struttura può accogliere al massimo
    2 volontari per ogni fascia oraria. Le fasce già al completo sono disabilitate.
</p>

<!-- role="alert" + aria: lo screen reader annuncia i messaggi di successo/errore -->
<div id="msg-volontariato" role="alert" aria-live="assertive"></div>

<form id="form-volontariato" method="post" action="api/prenota_turno.php" novalidate>
    <!-- La griglia viene popolata da volontariato.js; nel mentre lo spinner fornisce feedback durante il caricamento -->
    <div class="fasce-orarie-grid" id="griglia-fasce">
        <div class="loading">
            <div class="spinner"></div>
            <p>Caricamento fasce orarie…</p>
        </div>
    </div>

    <!-- Il bottone rimane nascosto finché JS non ha caricato le fasce e l'utente non ne seleziona una -->
    <div class="campo-form mt-2 nascosto" id="contenitore-btn-turni">
        <button type="submit" id="btn-iscriviti" class="btn-submit">Iscriviti ai turni selezionati</button>
    </div>
</form>

<!-- volontariato.js: carica le fasce da get_turni.php e gestisce selezione e invio -->
<script src="js/volontariato.js"></script>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>
