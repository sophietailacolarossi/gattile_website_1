<?php
// Costruzione della home: inserisce i due gatti più recenti tramite query al DB

$titoloPagina = 'Home';
require 'includes/header.php';
require_once 'includes/db.php';

// Connessione in modalità lecture (solo SELECT), estraggo gli ultimi due gatti per data di arrivo
$con    = getDB('lecture');
$result = mysqli_query($con, 'SELECT id, nome, descrizione, razza, eta, peso, sesso, colore_mantello, lunghezza_pelo, colore_occhi, data_arrivo FROM gatti ORDER BY data_arrivo DESC LIMIT 2');  // LIMIT 2: prendo gli ultimi due gatti arrivati

$nuoviArrivi = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $nuoviArrivi[] = $row;
    }
    mysqli_free_result($result);
}
// Se la query fallisce (es. problema di connessione), $nuoviArrivi resta vuoto:
// la sezione "nuovi arrivi" mostrerà il messaggio di fallback invece di un errore fatale
?>

<section class="hero">
    <h1><img src="img/icon.png" alt="" aria-hidden="true" class="icona-titolo"> Benvenuto alla Casa del Gatto</h1>
    <p>Ogni anno, centinaia di gatti vengono abbandonati o nascono in strada, necessitando di cure e di una famiglia. Allo stesso tempo, molte persone desiderano accogliere un felino o dedicare il proprio tempo come volontari.
       Questo sito nasce per facilitare le adozioni e organizzare il supporto attivo alla struttura ospitante.</p>
    <a href="gatti.php" class="btn-principale">Scopri i nostri gatti</a>
</section>

<!-- Sezione nuovi arrivi: card generate dal DB -->
<section>
    <h2 class="sezione-titolo"> Nuovi arrivi</h2>
    <?php if (empty($nuoviArrivi)): ?>
        <p>Nessun gatto presente al momento.</p>
    <?php else: ?>
        <div class="info-box nuovi-arrivi">
            <?php foreach ($nuoviArrivi as $gatto): ?>
                <div class="card-gatto">
                    <img src="img/placeholder2.png" alt="Foto di <?= htmlspecialchars($gatto['nome']) ?>">
                    <div class="card-body">
                        <h3><?= htmlspecialchars($gatto['nome']) ?></h3>
                        <div class="badge-gruppo">
                            <span class="badge"><img src="img/zampetta.png" alt="" aria-hidden="true" class="icona-badge"><span class="badge-label">Razza:</span> <?= htmlspecialchars($gatto['razza']) ?></span>
                            <span class="badge"><img src="img/eta.png" alt="" aria-hidden="true" class="icona-badge"><span class="badge-label">Età:</span> <?= (int)$gatto['eta'] ?> mesi</span>
                            <span class="badge"><img src="img/peso.png" alt="" aria-hidden="true" class="icona-badge"><span class="badge-label">Peso:</span> <?= htmlspecialchars(number_format((float)$gatto['peso'], 1)) ?> kg</span>
                            <span class="badge"><img src="<?= $gatto['sesso'] === 'M' ? 'img/maschio.png' : 'img/femmina.png' ?>" alt="" aria-hidden="true" class="icona-badge"><span class="badge-label">Sesso:</span> <?= $gatto['sesso'] === 'M' ? 'Maschio' : 'Femmina' ?></span>
                            <span class="badge"><img src="img/colore_pelo.png" alt="" aria-hidden="true" class="icona-badge"><span class="badge-label">Mantello:</span> <?= htmlspecialchars($gatto['colore_mantello']) ?></span>
                            <span class="badge"><img src="img/lunghezza_pelo.png" alt="" aria-hidden="true" class="icona-badge"><span class="badge-label">Pelo:</span> <?= htmlspecialchars($gatto['lunghezza_pelo']) ?></span>
                            <span class="badge"><img src="img/occhi.png" alt="" aria-hidden="true" class="icona-badge"><span class="badge-label">Occhi:</span> <?= htmlspecialchars($gatto['colore_occhi']) ?></span>
                        </div>
                        <p class="descrizione"><?= htmlspecialchars($gatto['descrizione']) ?></p>
                        <p class="data-arrivo">
                            Arrivato il <?= date('d/m/Y', strtotime($gatto['data_arrivo'])) ?>
                        </p>
                        <a href="gatti.php" class="link-scopri-piu-card" aria-label="Scopri di più su <?= htmlspecialchars($gatto['nome']) ?>">Scopri di più</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Sezione di navigazione -->
<section class="mt-3">
    <h2 class="sezione-titolo"> Cosa puoi fare</h2>
    <div class="info-box">
        <div class="card-gatto card-info">
            <div class="card-body">
                <h3><img src="img/zampetta.png" alt="" aria-hidden="true" class="icona-titolo"> Adotta un gatto</h3>
                <p class="descrizione">Sfoglia la lista dei nostri amici a quattro zampe, scegli quello che fa per te e
                   prenota una visita conoscitiva in struttura.</p>
                <a href="gatti.php" class="btn-card" >Vai alla lista</a>
            </div>
        </div>
        <div class="card-gatto card-info">
            <div class="card-body">
                <h3><img src="img/zampetta.png" alt="" aria-hidden="true" class="icona-titolo"> Fai il volontario</h3>
                <p class="descrizione">Offri il tuo tempo per aiutarci a prenderci cura dei gatti.
                   Scegli le fasce orarie che preferisci tra quelle disponibili.</p>
                <a href="volontariato.php" class="btn-card">Scopri come</a>
            </div>
        </div>
        <div class="card-gatto card-info">
            <div class="card-body">
                <h3><img src="img/zampetta.png" alt="" aria-hidden="true" class="icona-titolo"> Registrati</h3>
                <p class="descrizione">Crea un account per poter prenotare visite e iscriverti ai turni
                   di volontariato. La registrazione è gratuita e immediata.</p>
                <a href="registrazione.php" class="btn-card">Registrati ora</a>
            </div>
        </div>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
