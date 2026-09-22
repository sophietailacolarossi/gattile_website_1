<?php
/**
 * API: registra l'iscrizione dell'utente ai turni di volontariato selezionati.
 * Riceve via POST fasce_orarie (array di stringhe datetime).
 * Controlla server-side che ogni fascia non superi il limite di 2 volontari.
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { //se la sessione non è avviata la avvio
    session_start();
}

if (!isset($_SESSION['utente'])) { //controllo che l'utente sia loggato
    http_response_code(403);
    echo json_encode(['successo' => false, 'errore' => 'Devi essere autenticato.']);
    exit;
}

require_once '../includes/db.php';

$utenteId = (int)$_SESSION['utente_id']; 
$fasceRaw = $_POST['fasce_orarie'] ?? [];

if (!is_array($fasceRaw) || empty($fasceRaw)) {
    http_response_code(400);
    echo json_encode(['successo' => false, 'errore' => 'Seleziona almeno una fascia oraria.']);
    exit;
}

// Validazione formato fasce
$fasce = [];
foreach ($fasceRaw as $f) {
    $f = trim($f);
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $f)) {
        $fasce[] = $f;
    }
}

if (empty($fasce)) {
    http_response_code(400);
    echo json_encode(['successo' => false, 'errore' => 'Fasce orarie non valide.']);
    exit;
}

$con = getDB('modifier'); //accesso come modificatore

// Controlla server-side che nessuna fascia sia già al completo e che l'utente non sia già iscritto
$stmtConta = mysqli_prepare($con,
    'SELECT COUNT(*), SUM(utente_id = ?) FROM turni_volontariato WHERE fascia_oraria = ?');
$fasceOccupate = [];
$fasceGiaIscritto = [];

foreach ($fasce as $fascia) { 
    mysqli_stmt_bind_param($stmtConta, 'is', $utenteId, $fascia);
    mysqli_stmt_execute($stmtConta);
    mysqli_stmt_bind_result($stmtConta, $totale, $giaIscritto);
    mysqli_stmt_fetch($stmtConta);
    mysqli_stmt_reset($stmtConta);

    if ((int)$giaIscritto > 0) {
        $fasceGiaIscritto[] = $fascia; //inserisco tutte le fasce in cui l'utente è gia iscritto per mostrale a video
    }
    if ((int)$totale >= 2) {
        $fasceOccupate[] = $fascia; //inserisco tutte le fasce occupate durante la prenotazioen
    }
}
mysqli_stmt_close($stmtConta);

//se l'utente ha selezionato delle fasce a cui è già iscritto o una fascia è stata occupata invio un messaggio in formato json
if (!empty($fasceGiaIscritto)) {
    http_response_code(409);
    echo json_encode([
        'successo'       => false,
        'codice_errore'  => 'GIA_ISCRITTO',
        'fasce_occupate' => $fasceGiaIscritto,
    ]);
    exit;
}

if (!empty($fasceOccupate)) {
    http_response_code(409);
    echo json_encode([
        'successo'       => false,
        'codice_errore'  => 'FASCIA_PIENA',
        'errore'         => 'Le seguenti fasce sono già al completo: ' . implode(', ', $fasceOccupate),
        'fasce_occupate' => $fasceOccupate,
    ]);
    exit;
}

// Tutte le fasce sono libere e l'utente non è già iscritto: inserisce nel DB
// Il UNIQUE KEY su (utente_id, fascia_oraria) garantisce l'unicità a livello di DB
$stmtInserisci = mysqli_prepare($con, 'INSERT INTO turni_volontariato (utente_id, fascia_oraria) VALUES (?, ?)');

foreach ($fasce as $fascia) {
    mysqli_stmt_bind_param($stmtInserisci, 'is', $utenteId, $fascia);
    mysqli_stmt_execute($stmtInserisci);
}

mysqli_stmt_close($stmtInserisci);
echo json_encode(['successo' => true, 'messaggio' => 'Iscrizione ai turni completata con successo!']); //invio il messaggio di successo 
