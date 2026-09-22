/**
 * Gestione turni di volontariato, gestisce validazione client side e costruzione delle fasce 
 */

(function () {
    'use strict';

    const griglia = document.getElementById('griglia-fasce');
    const form = document.getElementById('form-volontariato');
    const msgDiv = document.getElementById('msg-volontariato');
    const contenitorBtn = document.getElementById('contenitore-btn-turni');

    if (!griglia || !form) return;

    let fasceSelezionate = new Set();

    //formatto la data ricevuta dal form in modo che sia inseribile nel db
    function formattaFascia(datetimeStr) {
        const data = new Date(datetimeStr);
        const giorni = ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'];
        const giorno = giorni[data.getDay()];
        const dd = ('0' + data.getDate()).slice(-2);
        const mm = ('0' + (data.getMonth() + 1)).slice(-2);
        const hh = ('0' + data.getHours()).slice(-2);
        const min = ('0' + data.getMinutes()).slice(-2);
        return giorno + ' ' + dd + '/' + mm + ' – ' + hh + ':' + min;
    }

    function mostraMessaggio(tipo, testo) {   //messaggio per utenti non loggati/registrati
        msgDiv.className = 'messaggio-pagina ' + tipo;
        msgDiv.textContent = testo;
    }

    function nascondMessaggio() {
        msgDiv.classList.add('nascosto');
        msgDiv.textContent = '';
    }

    // Carica le fasce da get_turni.php e costruisce un bottone per ciascuna
    async function caricaFasce() {
        try {
            const res = await fetch('api/get_turni.php'); //estraggo i turni da get_turni.php
            if (!res.ok) throw new Error('Errore di rete.');
            const fasce = await res.json();

            griglia.innerHTML = '';
            contenitorBtn.classList.add('nascosto'); //svuoto la griglia e nascondo il bottone

            if (!Array.isArray(fasce) || fasce.length === 0) {
                griglia.innerHTML = '<p>Nessuna fascia disponibile al momento.</p>';
                return;
            }

            fasce.forEach(function (fascia) {
                const piena = fascia.posti_liberi === 0; //controllo se la fascia sia piena o meno
                const btn   = document.createElement('button'); //per ogni fascia oraria creo il button

                btn.type      = 'button';
                btn.className = 'fascia-btn' + (piena ? ' piena' : '');
                btn.disabled  = piena;
                btn.setAttribute('data-fascia', fascia.fascia_oraria);

                btn.innerHTML =                             
                    formattaFascia(fascia.fascia_oraria) +
                    '<span class="posti-rimasti">' +
                    (piena ? 'Al completo' : fascia.posti_liberi + ' posto/i libero/i') +
                    '</span>'; //inserisco il testo del button

                if (!piena) {
                    btn.addEventListener('click', function () { //gestisco selezione e deselezione
                        toggleFascia(this);
                    });
                }

                griglia.appendChild(btn);
            });
        } catch (errore) {
            griglia.innerHTML = '<p class="messaggio-pagina errore">Impossibile caricare le fasce orarie. Riprova.</p>';
        }
    }

    // Seleziona o deseleziona la fascia; mostra/nasconde il bottone di submit di conseguenza
    function toggleFascia(btn) {
        const fascia = btn.getAttribute('data-fascia');
        if (fasceSelezionate.has(fascia)) {
            fasceSelezionate.delete(fascia);
            btn.classList.remove('selezionata');
        } else {
            fasceSelezionate.add(fascia);
            btn.classList.add('selezionata');
        }
        if (fasceSelezionate.size === 0) {
            contenitorBtn.classList.add('nascosto');
        } else {
            contenitorBtn.classList.remove('nascosto');
        }
    }

    //gestisco il comportamento al submit della fascia
    form.addEventListener('submit', async function (e) {   
        e.preventDefault();
        nascondMessaggio();

        if (fasceSelezionate.size === 0) {
            mostraMessaggio('errore', 'Seleziona almeno una fascia oraria.');
            return;
        }

        const btn = document.getElementById('btn-iscriviti'); //all'invio disabilito il button e mostro un messaggio 
        btn.disabled = true;
        btn.textContent = 'Iscrizione in corso…';

        // FormData con le fasce selezionate
        const dati = new FormData();
        fasceSelezionate.forEach(function (f) {
            dati.append('fasce_orarie[]', f);
        });

        try {
            const res = await fetch('api/prenota_turno.php', { //invio le fasce selezionate a prenota_turno.php
                method: 'POST',
                body: dati,
            });
            const risposta = await res.json();

            if (risposta.successo) {
                mostraMessaggio('successo', risposta.messaggio);
                fasceSelezionate.clear();
                caricaFasce(); // aggiorna i posti disponibili
            } else {
                // Codici di errore: FASCIA_PIENA (turno esaurito) e GIA_ISCRITTO
                if (risposta.codice_errore === 'FASCIA_PIENA') {
                    mostraMessaggio('errore',
                        'Alcune fasce sono diventate al completo nel frattempo: ' +
                        risposta.fasce_occupate.map(formattaFascia).join(', ') +
                        '. Scegli un\'altra fascia oraria.');
                    caricaFasce();
                } else if (risposta.codice_errore === 'GIA_ISCRITTO') {
                    mostraMessaggio('errore',
                        'Sei già iscritto a: ' +
                        risposta.fasce_occupate.map(formattaFascia).join(', ') +
                        '. Si prega di selezionare un altro turno.');
                } else {
                    mostraMessaggio('errore', risposta.errore || 'Errore sconosciuto.');
                }
            }
        } catch (errore) {
            mostraMessaggio('errore', 'Errore di comunicazione con il server. Riprova.');
        } finally {
            btn.disabled = false;  //riattivo il button e cambio il messaggio
            btn.textContent = 'Iscriviti ai turni selezionati';
            contenitorBtn.classList.add('nascosto');
        }
    });

    // Avvia il caricamento delle fasce al caricamento della pagina
    caricaFasce();
}());
