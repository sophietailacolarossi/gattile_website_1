<?php
/**
 * API: restituisce la lista di tutti i gatti in formato JSON, utilizzata dal componente React nella pagina gatti.php.
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/db.php';

// Connessione lecture: leggo senza modificare il DB
$con    = getDB('lecture');
// Elenco esplicito delle colonne invece di SELECT *: limita ulteriormente il rischio di SQL injection
$result = mysqli_query($con,
    'SELECT id, nome, descrizione, peso, colore_mantello, lunghezza_pelo,
            razza, colore_occhi, eta, sesso, data_arrivo
     FROM gatti
     ORDER BY data_arrivo DESC'
);

if (!$result) {
    http_response_code(500); //errore lato server
    echo json_encode(['errore' => 'Errore nel recupero dei dati.']);
    exit;
}

$gatti = [];
while ($row = mysqli_fetch_assoc($result)) {
    $gatti[] = $row;
}
mysqli_free_result($result);

echo json_encode($gatti, JSON_UNESCAPED_UNICODE);
