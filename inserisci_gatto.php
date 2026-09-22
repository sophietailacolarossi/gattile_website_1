<?php

// Validazione server-side: ripete i controlli già fatti in JS lato client,
// necessaria perché la validazione client-side è bypassabile 


$titoloPagina = 'Inserisci gatto';
require 'includes/header.php';
require_once 'includes/db.php';

// Accesso riservato agli amministratori: 403 Forbidden per chiunque non sia admin
if (!isset($_SESSION['utente']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo '<div class="messaggio-pagina errore">Accesso negato. Questa pagina è riservata agli amministratori.</div>';
    require 'includes/footer.php';
    exit;
}

$errore   = '';
$successo = '';

// POST: dati del form ricevuti; GET: mostra solo il form vuoto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descrizione = trim($_POST['descrizione'] ?? '');
    $peso = $_POST['peso'] ?? '';
    $colore_mantello = trim($_POST['colore_mantello'] ?? '');
    $lunghezza_pelo = trim($_POST['lunghezza_pelo'] ?? '');
    $razza = trim($_POST['razza'] ?? '');
    $colore_occhi = trim($_POST['colore_occhi'] ?? '');
    $eta = $_POST['eta'] ?? '';
    $sesso = $_POST['sesso'] ?? '';
    $data_arrivo = $_POST['data_arrivo'] ?? '';

    // Validazione server-side: raccoglie tutti gli errori 
    $errori = [];
    if ($nome === '')                                              $errori[] = 'Il nome è obbligatorio.';
    if ($descrizione === '')                                       $errori[] = 'La descrizione è obbligatoria.';
    if (!is_numeric($peso) || $peso <= 0)                         $errori[] = 'Il peso deve essere un numero positivo.';
    if ($colore_mantello === '')                                   $errori[] = 'Il colore del mantello è obbligatorio.';
    if (!in_array($lunghezza_pelo, ['Corto', 'Medio', 'Lungo']))  $errori[] = 'Lunghezza pelo non valida.';
    if ($razza === '')                                             $errori[] = 'La razza è obbligatoria.';
    if ($colore_occhi === '')                                      $errori[] = 'Il colore degli occhi è obbligatorio.';
    if (!ctype_digit((string)$eta) || (int)$eta < 0)              $errori[] = "L'età deve essere un numero intero non negativo, esprimerla in mesi.";
    if (!in_array($sesso, ['M', 'F']))                            $errori[] = 'Il sesso non è valido.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_arrivo))      $errori[] = 'Inserire la data in formato YYYY-MM-DD';

    if (empty($errori)) {
        $con       = getDB('modifier');
        $pesoFloat = (float)$peso; // cast a float: nel DB la colonna è decimal
        $etaInt    = (int)$eta;    // cast a int: nel DB la colonna è int

        // Query parametrica per evitare SQL injection
        // tipi: s=nome, s=descrizione, d=peso, s=colore_mantello, s=lunghezza_pelo, s=razza, s=colore_occhi, i=eta, s=sesso, s=data_arrivo
        $stmt = mysqli_prepare($con,
            'INSERT INTO gatti (nome, descrizione, peso, colore_mantello, lunghezza_pelo, razza, colore_occhi, eta, sesso, data_arrivo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        mysqli_stmt_bind_param($stmt, 'ssdssssiss',                                  
            $nome, $descrizione, $pesoFloat, $colore_mantello,
            $lunghezza_pelo, $razza, $colore_occhi, $etaInt, $sesso, $data_arrivo
        );

        if (!mysqli_stmt_execute($stmt)) {
            $errore = 'Errore durante il salvataggio. Riprova più tardi.';
        } else {
            $successo = 'Gatto "' . htmlspecialchars($nome) . '" inserito correttamente!';
        }

        mysqli_stmt_close($stmt);
    } else {
        $errore = '<ul><li>' . implode('</li><li>', $errori) . '</li></ul>'; //lista dei possibili errori catturati
    }
}
?>

<h1 class="sr-only">Inserisci nuovo gatto</h1>  <!--visibile solo da screen reader-->
<div class="form-container wide">
    <h2>Inserisci nuovo gatto</h2>

    <?php if ($successo): ?>
        <div class="messaggio-pagina successo" role="status"><?= $successo ?></div>
    <?php elseif ($errore): ?>
        <div class="messaggio-pagina errore" role="alert"><?= $errore ?></div>
    <?php endif; ?>

    <?php if (!$successo): ?>
        <!-- In caso di errore il form viene ristampato con i valori già inseriti -->
        <form id="form-inserisci-gatto" method="post" action="inserisci_gatto.php" novalidate>
            
            <div class="campo-form">
                <label for="nome">Nome del gatto</label>
                <input type="text" id="nome" name="nome"
                    value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                <span class="errore-campo" id="errore-nome"></span>
            </div>

            <div class="campo-form">
                <label for="descrizione">Descrizione (carattere e storia)</label>
                <textarea id="descrizione" name="descrizione" rows="4" required><?= htmlspecialchars($_POST['descrizione'] ?? '') ?></textarea>
                <span class="errore-campo" id="errore-descrizione"></span>
            </div>

            <div class="form-row">
                <div class="campo-form">
                    <label for="peso">Peso (in kg)</label>
                    <input type="number" id="peso" name="peso" min="0.1" max="20" step="0.01"
                        value="<?= htmlspecialchars($_POST['peso'] ?? '') ?>" required>
                    <span class="errore-campo" id="errore-peso"></span>
                </div>

                <div class="campo-form">
                    <label for="eta">Età (mesi)</label>
                    <input type="number" id="eta" name="eta" min="0" max="300"
                        value="<?= htmlspecialchars($_POST['eta'] ?? '') ?>" required>
                    <span class="errore-campo" id="errore-eta"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="campo-form">
                    <label for="sesso">Sesso</label>
                    <select id="sesso" name="sesso" required>
                        <option value="">-- Seleziona --</option>
                        <option value="M" <?= (($_POST['sesso'] ?? '') === 'M') ? 'selected' : '' ?>>Maschio</option>   <!-- Ripropone il valore scelto in precedenza se il form viene ristampato dopo un errore -->
                        <option value="F" <?= (($_POST['sesso'] ?? '') === 'F') ? 'selected' : '' ?>>Femmina</option>
                    </select>
                    <span class="errore-campo" id="errore-sesso"></span>
                </div>

                <div class="campo-form">
                    <label for="lunghezza_pelo">Lunghezza pelo</label>
                    <select id="lunghezza_pelo" name="lunghezza_pelo" required>
                        <option value="">-- Seleziona --</option>
                        <option value="Corto" <?= (($_POST['lunghezza_pelo'] ?? '') === 'Corto') ? 'selected' : '' ?>>Corto</option>
                        <option value="Medio" <?= (($_POST['lunghezza_pelo'] ?? '') === 'Medio') ? 'selected' : '' ?>>Medio</option>
                        <option value="Lungo" <?= (($_POST['lunghezza_pelo'] ?? '') === 'Lungo') ? 'selected' : '' ?>>Lungo</option>
                    </select>
                    <span class="errore-campo" id="errore-lunghezza-pelo"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="campo-form">
                    <label for="colore_mantello">Colore mantello</label>
                    <input type="text" id="colore_mantello" name="colore_mantello"
                        value="<?= htmlspecialchars($_POST['colore_mantello'] ?? '') ?>" required>
                    <span class="errore-campo" id="errore-colore-mantello"></span>
                </div>

                <div class="campo-form">
                    <label for="colore_occhi">Colore occhi</label>
                    <input type="text" id="colore_occhi" name="colore_occhi"
                        value="<?= htmlspecialchars($_POST['colore_occhi'] ?? '') ?>" required>
                    <span class="errore-campo" id="errore-colore-occhi"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="campo-form">
                    <label for="razza">Razza</label>
                    <input type="text" id="razza" name="razza"
                        value="<?= htmlspecialchars($_POST['razza'] ?? '') ?>" required>
                    <span class="errore-campo" id="errore-razza"></span>
                </div>

                <div class="campo-form">
                    <label for="data_arrivo">Data di arrivo</label>
                    <input type="date" id="data_arrivo" name="data_arrivo"
                        value="<?= htmlspecialchars($_POST['data_arrivo'] ?? '') ?>" required>
                    <span class="errore-campo" id="errore-data-arrivo"></span>
                </div>
            </div>

            <button type="submit" id="btn-inserisci" class="btn-submit">Inserisci gatto</button>
        </form>
    <?php endif; ?>
</div>

<!-- aggancio allo script per la validazione del form-->
<script src="js/inserisci_gatto.js"></script>

<?php require 'includes/footer.php'; ?>
