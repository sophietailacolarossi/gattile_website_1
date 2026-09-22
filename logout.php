<?php
// Svuota le variabili di sessione e distrugge la sessione sul server
session_start();
session_unset();
session_destroy();

// Il cookie "ricordami" NON viene cancellato: servirà per precompilare il prossimo accesso
header('Location: home.php');
exit;
