<?php
$titoloPagina = 'I nostri gatti';
require 'includes/header.php';

$loggato   = isset($_SESSION['utente']);
$isAdmin   = $loggato && $_SESSION['is_admin'];
$utenteId  = $loggato ? (int)$_SESSION['utente_id'] : 0;
?>

<h1 class="sezione-titolo"><img src="img/zampetta.png" alt="" aria-hidden="true" class="icona-titolo"> I nostri gatti</h1>

<!--check sullo stato dell'utente per gestire l'accesso -->
<?php if ($loggato && !$isAdmin): ?>
    <div class="info-selezione">
        <img src="img/lampadina.png" alt="" aria-hidden="true" class="icona-titolo"> Clicca sulle card dei gatti per selezionarli, poi compila il form in basso per prenotare una visita conoscitiva.
    </div>
<?php elseif (!$loggato): ?>
    <div class="info-selezione">
        <img src="img/info.png" alt="" aria-hidden="true" class="icona-titolo"> Per prenotare una visita devi prima <a href="login.php">accedere</a> o <a href="registrazione.php">registrarti</a>.
    </div>
<?php endif; ?>

<!-- Ancoraggio per componente React -->
<div id="react-gatti-root">
    <div class="loading">
        <div class="spinner"></div>
        <p>Caricamento gatti in corso…</p>
    </div>
</div>

<?php if ($loggato && !$isAdmin): ?>
<!-- Form prenotazione visita -->
<section class="form-prenotazione mt-3" id="sezione-prenotazione">
    <h2><img src="img/calendar.png" alt="" class="icona-titolo"> Prenota una visita conoscitiva</h2>
    <p class="testo-intro">
        Seleziona i gatti dalla lista sopra, poi scegli data e ora della visita.
    </p>

    <div id="riepilogo-selezione" class="nascosto" aria-live="polite">
        <span>Gatti selezionati:</span>
        <ul class="gatti-selezionati-lista" id="lista-gatti-selezionati"></ul>
    </div>

    <div id="msg-prenotazione" role="alert" aria-live="assertive"></div>

    <form id="form-prenotazione" method="post" action="api/prenota_visita.php" novalidate>
        <input type="hidden" id="gatti-ids" name="gatti_ids" value="">

        <div class="campo-form">
            <label for="data-visita">Data della visita</label>
            <input type="date" id="data-visita" name="data_visita"
                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
            <span class="errore-campo" id="errore-data-visita"></span>
        </div>

        <div class="campo-form">
            <label for="ora-visita">Ora della visita</label>
            <select id="ora-visita" name="ora_visita" required>
                <option value="">-- Seleziona orario --</option>
                <option value="09:00">09:00</option>
                <option value="10:00">10:00</option>
                <option value="11:00">11:00</option>
                <option value="14:00">14:00</option>
                <option value="15:00">15:00</option>
                <option value="16:00">16:00</option>
            </select>
            <span class="errore-campo" id="errore-ora-visita"></span>
        </div>

        <button type="submit" id="btn-prenota" class="btn-submit">Prenota visita</button>
    </form>
</section>

<!-- messaggio da mostrare in caso l'utente non sia loggato-->
<?php elseif (!$loggato): ?>
<div class="avviso-auth mt-3">
    <p>Per prenotare una visita conoscitiva devi essere registrato e aver effettuato l'accesso.</p>
    <a href="login.php" class="btn-submit">Accedi</a>
    <a href="registrazione.php" class="btn-submit">Registrati</a>
</div>
<?php endif; ?>

<!-- React via CDN  -->
<script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<!-- Contesto di autenticazione dal server PHP che passa a React -->
<script>
    window.GATTILE_CONFIG = {
        loggato: <?= $loggato ? 'true' : 'false' ?>,
        isAdmin: <?= $isAdmin ? 'true' : 'false' ?>,
        apiUrl: 'api/get_gatti.php'
    };
</script>

<script type="text/babel">
/**
 * Componente React per la visualizzazione, ordinamento e selezione dei gatti.
 * Recupera i gatti da get_gatti.php tramite fetch asincrona.
 * Se l'utente è autenticato come utente normale, consente la selezione che
 * viene comunicata al form Vanilla JS tramite CustomEvent sul document.
 */

const { useState, useEffect } = React;

// Card gatto
function CardGatto({ gatto, selezionabile, selezionata, nuovo, onToggle }) {
    const handleClick = () => {
        if (selezionabile) onToggle(gatto);
    };

    // Navigazione da tastiera con Invio e Spazio che replicano il click
    const handleKeyDown = (e) => {
        if (!selezionabile) return;
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault(); // evita lo scroll pagina che Spazio farebbe di default
            onToggle(gatto);
        }
    };

    //costruzione della classe per la card gatto
    const classi = [
        'card-gatto',
        selezionabile ? 'selezionabile' : '',
        selezionata ? 'selezionata' : '',
    ].join(' ').replace(/\s+/g, ' ').trim();

    //gestione della navigazione da tastiera e costruzione della card
    return (
        <div className={classi} onClick={handleClick} onKeyDown={handleKeyDown}
             role={selezionabile ? 'button' : undefined}
             tabIndex={selezionabile ? 0 : undefined}
             aria-pressed={selezionabile ? selezionata : undefined}
             title={selezionabile ? (selezionata ? 'Clicca per deselezionare' : 'Clicca per selezionare') : ''}>
            {nuovo && <span className="badge-nuovo">Nuovo</span>}

            {/* Placeholder fisso in attesa di future foto reali */}
            <img src="img/placeholder2.png" alt={`Foto di ${gatto.nome}`} />  
            <div className="card-body">
                <h2>{gatto.nome}</h2>
                <div className="badge-gruppo">
                    <span className="badge"><img src="img/zampetta.png" alt="" aria-hidden="true" className="icona-badge" /><span className="badge-label">Razza:</span> {gatto.razza}</span>
                    <span className="badge"><img src="img/eta.png" alt="" aria-hidden="true" className="icona-badge" /><span className="badge-label">Età:</span> {gatto.eta} mesi</span>
                    <span className="badge"><img src="img/peso.png" alt="" aria-hidden="true" className="icona-badge" /><span className="badge-label">Peso:</span> {Number(gatto.peso).toFixed(1)} kg</span>
                    <span className="badge"><img src={gatto.sesso === 'M' ? 'img/maschio.png' : 'img/femmina.png'} alt="" aria-hidden="true" className="icona-badge" /><span className="badge-label">Sesso:</span> {gatto.sesso === 'M' ? 'Maschio' : 'Femmina'}</span>
                    <span className="badge"><img src="img/colore_pelo.png" alt="" aria-hidden="true" className="icona-badge" /><span className="badge-label">Mantello:</span> {gatto.colore_mantello}</span>
                    <span className="badge"><img src="img/lunghezza_pelo.png" alt="" aria-hidden="true" className="icona-badge" /><span className="badge-label">Pelo:</span> {gatto.lunghezza_pelo}</span>
                    <span className="badge"><img src="img/occhi.png" alt="" aria-hidden="true" className="icona-badge" /><span className="badge-label">Occhi:</span> {gatto.colore_occhi}</span>
                </div>
                <p className="descrizione">{gatto.descrizione}</p>
                <p className="data-arrivo">
                    Arrivato il {new Date(gatto.data_arrivo).toLocaleDateString('it-IT')}
                </p>
                {selezionata && <p className="badge-selezionato">✓ Selezionato</p>}
            </div>
        </div>
    );
}

// Componente React principale
function GattiApp() {
    const [gatti, setGatti] = useState([]);
    const [caricamento, setCaricamento] = useState(true);
    const [erroreApi, setErroreApi] = useState('');
    const [ricerca, setRicerca] = useState('');
    const [ordinamento, setOrdinamento] = useState('data_arrivo_desc');
    const [selezionati, setSelezionati] = useState([]);

    const loggato = window.GATTILE_CONFIG.loggato;
    const isAdmin = window.GATTILE_CONFIG.isAdmin;
    const apiUrl = window.GATTILE_CONFIG.apiUrl;
    const selezionabile = loggato && !isAdmin;

    // Caricamento dati da get_gatti.php
    useEffect(() => {
        fetch(apiUrl)
            .then(res => {
                if (!res.ok) throw new Error('Errore nel caricamento dei gatti.');
                return res.json();
            })
            .then(data => {
                setGatti(data);
                setCaricamento(false);
            })
            .catch(err => {
                setErroreApi(err.message);
                setCaricamento(false);
            });
    }, [apiUrl]);

    // Emette CustomEvent ogni volta che gattiSelezionati cambia
    // Il form Vanilla JS ascolta questo evento per recuperare i gatti selezionati
    useEffect(() => {
        const evento = new CustomEvent('gattiSelezionati', {
            detail: { gatti: selezionati },
        });
        document.dispatchEvent(evento);
    }, [selezionati]);

    // selezione/deselezione di un singolo gatto
    const toggleSelezione = (gatto) => {
        setSelezionati(prev => {
            const presente = prev.some(g => g.id === gatto.id);
            return presente ? prev.filter(g => g.id !== gatto.id) : [...prev, gatto];
        });
    };

    // Filtraggio per ricerca con testo
    const gattiFiltrați = gatti.filter(g => {
        if (!ricerca.trim()) return true;
        const q = ricerca.toLowerCase();
        return g.nome.toLowerCase().includes(q) || g.descrizione.toLowerCase().includes(q);
    });

    // Ordinamento
    const gattiOrdinati = [...gattiFiltrați].sort((a, b) => {
        switch (ordinamento) {
            case 'eta_asc':         return a.eta - b.eta;
            case 'eta_desc':        return b.eta - a.eta;
            case 'colore_pelo_asc': return a.colore_mantello.localeCompare(b.colore_mantello);
            case 'colore_pelo_desc': return b.colore_mantello.localeCompare(a.colore_mantello);
            case 'data_arrivo_asc': return new Date(a.data_arrivo) - new Date(b.data_arrivo);
            case 'data_arrivo_desc': return new Date(b.data_arrivo) - new Date(a.data_arrivo);
            default:                return 0;
        }
    });

    // Stessi due gatti mostrati come "nuovi arrivi" in home.php: get_gattii.php li restituisce
    // già ordinati per data_arrivo decrescente, quindi i primi due dell'array sono loro
    //tutto ciò per mettere il badge "nuovo" nella card
    const idNuoviArrivi = new Set(gatti.slice(0, 2).map(g => g.id));

    //spinner per il caricamento
    if (caricamento) {
        return (
            <div className="loading">
                <div className="spinner"></div>
                <p>Caricamento gatti in corso…</p>
            </div>
        );
    }

    //gestione di errori della fetch
    if (erroreApi) {
        return <div className="messaggio-pagina errore">{erroreApi}</div>;
    }

    return (
        <div>
            {/* Controlli di ordinamento e ricerca */}
            <div className="react-controlli">
                <div className="input-cerca-wrapper">
                    <img src="img/lente.png" alt="" aria-hidden="true" className="icona-cerca" />
                    <input
                        type="text"
                        id="cerca-gatto"
                        name="cerca-gatto"
                        placeholder="Cerca per nome o descrizione…"
                        value={ricerca}
                        onChange={e => setRicerca(e.target.value)}
                        aria-label="Cerca gatto"
                    />
                </div>
                <select
                    id="ordina-gatti"
                    name="ordina-gatti"
                    value={ordinamento}
                    onChange={e => setOrdinamento(e.target.value)}
                    aria-label="Ordina per"
                >
                    <option value="data_arrivo_desc">Arrivo: più recenti prima</option>
                    <option value="data_arrivo_asc">Arrivo: meno recenti prima</option>
                    <option value="eta_asc">Età: crescente</option>
                    <option value="eta_desc">Età: decrescente</option>
                    <option value="colore_pelo_asc">Colore pelo: A→Z</option>
                    <option value="colore_pelo_desc">Colore pelo: Z→A</option>
                </select>
                {selezionabile && selezionati.length > 0 && (
                    <span className="contatore-selezione">
                        {selezionati.length} selezionato/i
                    </span>
                )}
            </div>

            {/* Griglia delle card */}
            {gattiOrdinati.length === 0 ? (
                <p className="nessun-risultato">Nessun gatto corrisponde alla ricerca.</p>
            ) : (
                <div className="griglia-gatti">
                    {gattiOrdinati.map(gatto => (
                        <CardGatto
                            key={gatto.id}
                            gatto={gatto}
                            selezionabile={selezionabile}
                            selezionata={selezionati.some(g => g.id === gatto.id)}
                            nuovo={idNuoviArrivi.has(gatto.id)}
                            onToggle={toggleSelezione}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}

// Rendering del componente nella pagina PHP
const container = document.getElementById('react-gatti-root');
const root = ReactDOM.createRoot(container);
root.render(<GattiApp />);
</script>

<?php if ($loggato && !$isAdmin): ?>
<script src="js/gatti_form.js"></script>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
