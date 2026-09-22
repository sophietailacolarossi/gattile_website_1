/**
 * Form di prenotazione visita 
 * RICEZIONE SELEZIONE DA REACT:
 *    React emette un CustomEvent('gattiSelezionati') a ogni click su una card, e js
 *    lo ascolta con un Listener.
 * VALIDAZIONE E INVIO DEL FORM:
 *    Valida data, orario e selezione dei gatti; invia via fetch POST a prenota_visita.php.
 */

(function () {
    'use strict';

    const form = document.getElementById('form-prenotazione');
    const inputGattiIds = document.getElementById('gatti-ids');
    const listaGatti = document.getElementById('lista-gatti-selezionati');
    const riepilogo = document.getElementById('riepilogo-selezione');
    const msgPrenotazione = document.getElementById('msg-prenotazione');
    const inDataVisita = document.getElementById('data-visita');
    const inOraVisita = document.getElementById('ora-visita');

    if (!form) return;

    let gattiSelezionati = [];

    // Ascolta il CustomEvent da React
    document.addEventListener('gattiSelezionati', function (e) {
        gattiSelezionati = e.detail.gatti || [];
        aggiornaRiepilogo();
    });

    function aggiornaRiepilogo() {  //gestisco il riepilogo testuale visibile nel form quando si selezionano i gatti
        if (gattiSelezionati.length === 0) {
            riepilogo.classList.add('nascosto');
            inputGattiIds.value = '[]';
        } else {
            riepilogo.classList.remove('nascosto');
            listaGatti.innerHTML = '';
            gattiSelezionati.forEach(function (g) {
                const li = document.createElement('li');
                li.textContent = g.nome + ' (' + g.razza + ', ' + g.eta + ' mesi)';
                listaGatti.appendChild(li);
            });
            inputGattiIds.value = JSON.stringify(gattiSelezionati.map(function (g) { return g.id; }));
        }
    }

    function mostraErrore(el, spanId, msg) {  //funziona mostraErrore utilizzata anche negli altri file js
        const span = document.getElementById(spanId);
        if (span) span.textContent = msg;
        if (msg) el.classList.add('invalido');
        else     el.classList.remove('invalido');
    }

    function mostraMessaggio(tipo, testo) {   //mostra il messaggio che invita a selezionare i gatti
        msgPrenotazione.className = 'messaggio-pagina ' + tipo;
        msgPrenotazione.textContent = testo;
        msgPrenotazione.classList.remove('nascosto');
    }

    //lunghezza selezione, validazione data e ora di visita
    function validaForm() {
        let ok = true;

        if (gattiSelezionati.length === 0) {
            mostraMessaggio('errore', 'Seleziona almeno un gatto dalla lista sopra.');
            ok = false;
        }

        if (!inDataVisita.value) {
            mostraErrore(inDataVisita, 'errore-data-visita', 'Seleziona una data per la visita.');
            ok = false;
        } else {
            mostraErrore(inDataVisita, 'errore-data-visita', '');
        }

        if (!inOraVisita.value) {
            mostraErrore(inOraVisita, 'errore-ora-visita', 'Seleziona un orario.');
            ok = false;
        } else {
            mostraErrore(inOraVisita, 'errore-ora-visita', '');
        }

        return ok;
    }

    //gestione del comportamento al submit del form
    form.addEventListener('submit', async function (e) { //async permette di usare await all'interno del listener
        e.preventDefault();

        if (!validaForm()) return;

        const btn = form.querySelector('#btn-prenota'); //disattivo il button durante l'invio del form
        btn.disabled = true;
        btn.textContent = 'Prenotazione in corso…';

        const dati = new FormData(form);  //formdata contiene i dati del form, utilizzato con il metodo POST

        try {
            const res = await fetch('api/prenota_visita.php', { //invia i dati a prenota_visita.php per permette la registrazione della visita
                method: 'POST',
                body: dati,
            });
            const risposta = await res.json();

            if (risposta.successo) {
                mostraMessaggio('successo', risposta.messaggio);
                form.reset();
                gattiSelezionati = []; //svuoto i gatti selezionati e nascondo il riepilogo
                aggiornaRiepilogo();
            } else {
                mostraMessaggio('errore', risposta.errore || 'Errore sconosciuto.');
            }
        } catch (errore) {
            mostraMessaggio('errore', 'Errore di comunicazione con il server. Riprova.');
        } finally {
            btn.disabled = false;  //riattivo il button e lo riempio con del testo
            btn.textContent = 'Prenota visita';
        }
    });

    inDataVisita.addEventListener('blur', function () { //se esco dal campo senza averlo compilato mi dice di scegliere la data
        if (!this.value) {
            mostraErrore(this, 'errore-data-visita', 'Seleziona una data per la visita.');
        } else {
            mostraErrore(this, 'errore-data-visita', '');
        }
    });

    inOraVisita.addEventListener('change', function () { //idem per l'orario
        if (!this.value) {
            mostraErrore(this, 'errore-ora-visita', 'Seleziona un orario.');
        } else {
            mostraErrore(this, 'errore-ora-visita', '');
        }
    });
}());
