<?php
// Parametri di connessione come costanti
define('DB_HOST', 'localhost');
define('DB_NAME', 'gattile_db');
define('DB_CHARSET', 'utf8mb4');

// Credenziali per i tre utenti DB con privilegi distinti:
define('DB_USER_LECTURE',     'lecture');
define('DB_PASS_LECTURE',     'P@ssw0rd!');

define('DB_USER_MODIFIER',    'modifier');
define('DB_PASS_MODIFIER',    'Str0ng#Admin9');

define('DB_USER_REGISTRATOR', 'registrator');
define('DB_PASS_REGISTRATOR', 'ToB31nsert?');

// Crea e restituisce la connessione al DB usando le credenziali 
function getDB(string $tipo = 'lecture') {
    switch ($tipo) {
        case 'modifier':
            $user = DB_USER_MODIFIER;
            $pass = DB_PASS_MODIFIER;
            break;
        case 'registrator':
            $user = DB_USER_REGISTRATOR;
            $pass = DB_PASS_REGISTRATOR;
            break;
        default:
            $user = DB_USER_LECTURE;
            $pass = DB_PASS_LECTURE;
    }

    $con = mysqli_connect(DB_HOST, $user, $pass, DB_NAME);

    if (!$con) {
        // 500 Internal Server Error: la connessione al DB è un errore lato server
        http_response_code(500);
        die(printf("<p>Errore di connessione al database: %s</p>\n", mysqli_connect_error()));
    }

    mysqli_set_charset($con, DB_CHARSET);
    return $con;
}
