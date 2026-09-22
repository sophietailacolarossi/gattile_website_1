<?php
/**
 * API: restituisce le fasce orarie disponibili con il numero di volontari già iscritti. Utilizzata da volontariato.js per disabilitare le fasce piene (>= 2 iscritti).
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/db.php';

// Genera tutte le combinazioni data+ora per i prossimi 7 giorni
$fasce = [];
$orari = ['09:00', '11:00', '14:00', '16:00'];

for ($giorno = 0; $giorno < 7; $giorno++) {
    // strtotime interpreta "+N days" e restituisce il timestamp; date lo formatta YYYY-MM-DD
    $data = date('Y-m-d', strtotime("+{$giorno} days"));
    foreach ($orari as $ora) {
        $fasce[] = $data . ' ' . $ora . ':00';
    }
}

// Connessione lecture: leggo solo il conteggio degli iscritti senza modificare il DB
$con  = getDB('lecture');
$stmt = mysqli_prepare($con, 'SELECT COUNT(*) FROM turni_volontariato WHERE fascia_oraria = ?');

if (!$stmt) {
    http_response_code(500); //errore lato server
    echo json_encode(['errore' => 'Errore nel recupero delle fasce orarie.']);
    exit;
}

//per ogni fascia controllo il numero di iscritti e lo inserisco in risultati
$risultati = [];
foreach ($fasce as $fascia) {  
    mysqli_stmt_bind_param($stmt, 's', $fascia);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $totale);
    mysqli_stmt_fetch($stmt);

    $risultati[] = [
        'fascia_oraria' => $fascia,
        'iscritti'      => (int)$totale,
        //i posti liberi non possono mai essere negativi
        'posti_liberi'  => max(0, 2 - (int)$totale),
    ];
}

mysqli_stmt_close($stmt);
echo json_encode($risultati, JSON_UNESCAPED_UNICODE); //restituisce le fasce orarie con il numero di iscritti e di posti liberi (codifica unicode)
