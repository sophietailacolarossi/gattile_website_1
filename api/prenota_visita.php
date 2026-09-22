<?php
/**
 * API: registra una prenotazione di visita conoscitiva, validazione lato server.
 * Riceve via POST: data_visita, ora_visita, gatti_ids (JSON array di id).
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { //se la sessione non era attiva la avvio
    session_start();
}

// Solo utenti autenticati e non amministratori
if (!isset($_SESSION['utente']) || $_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['successo' => false, 'errore' => 'Accesso non autorizzato.']);
    exit;
}

require_once '../includes/db.php';

$utenteId   = (int)$_SESSION['utente_id'];
$dataVisita = trim($_POST['data_visita'] ?? '');
$oraVisita  = trim($_POST['ora_visita'] ?? '');
$gattiIds   = json_decode($_POST['gatti_ids'] ?? '[]', true);

//validazione del formato di data e ora

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataVisita)) {
    http_response_code(400);
    echo json_encode(['successo' => false, 'errore' => 'Data non valida.']);
    exit;
}

if (!in_array($oraVisita, ['09:00','10:00','11:00','14:00','15:00','16:00'])) {
    http_response_code(400);
    echo json_encode(['successo' => false, 'errore' => 'Orario non valido.']);
    exit;
}

if (strtotime($dataVisita) <= strtotime('today')) { //se la data di visita è gia passata invio messaggio di errore
    http_response_code(400);
    echo json_encode(['successo' => false, 'errore' => 'La data deve essere successiva a oggi.']);
    exit;
}

if (empty($gattiIds) || !is_array($gattiIds)) { //non ho selezionato nessun gatto: invio messaggio di errore
    http_response_code(400);
    echo json_encode(['successo' => false, 'errore' => 'Seleziona almeno un gatto.']);
    exit;
}

$dataOra = $dataVisita . ' ' . $oraVisita . ':00';
$con     = getDB('modifier'); //accesso come modificatore

// Inserisce la prenotazione
$stmt = mysqli_prepare($con, 'INSERT INTO prenotazioni_visite (utente_id, data_ora) VALUES (?, ?)');
mysqli_stmt_bind_param($stmt, 'is', $utenteId, $dataOra);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    http_response_code(500); //errore lato server
    echo json_encode(['successo' => false, 'errore' => 'Errore durante la prenotazione. Riprova.']);
    exit;
}

$prenotazioneId = (int)mysqli_insert_id($con); //prendo l'id della visita, generato dal db
mysqli_stmt_close($stmt);

// Associa i gatti selezionati alla visita
$stmtGatto = mysqli_prepare($con, 'INSERT INTO visita_gatti (prenotazione_id, gatto_id) VALUES (?, ?)');

foreach ($gattiIds as $gattoId) {
    $gattoId = (int)$gattoId; 
    if ($gattoId > 0) {
        mysqli_stmt_bind_param($stmtGatto, 'ii', $prenotazioneId, $gattoId);
        if (!mysqli_stmt_execute($stmtGatto)) {
            mysqli_stmt_close($stmtGatto);
            http_response_code(500);
            echo json_encode(['successo' => false, 'errore' => 'Errore durante la prenotazione. Riprova.']);
            exit;
        }
    }
}

mysqli_stmt_close($stmtGatto);
echo json_encode(['successo' => true, 'messaggio' => 'Visita prenotata con successo!']); //invio il messaggio di successo
