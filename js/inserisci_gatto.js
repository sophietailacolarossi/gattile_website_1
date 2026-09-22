/**
 * Validazione client-side del form di inserimento gatto.
 * function isola lo scope per evitare conflitti con altri script sulla window.
 */

(function () {
    'use strict';

    const form = document.getElementById('form-inserisci-gatto');
    if (!form) return;

    const inNome = document.getElementById('nome');
    const inDescrizione = document.getElementById('descrizione');
    const inPeso = document.getElementById('peso');
    const inEta = document.getElementById('eta');
    const inSesso = document.getElementById('sesso');
    const inLunghezzaPelo = document.getElementById('lunghezza_pelo');
    const inColoreMantello = document.getElementById('colore_mantello');
    const inColoreOcchi = document.getElementById('colore_occhi');
    const inRazza = document.getElementById('razza');
    const inDataArrivo  = document.getElementById('data_arrivo');

    // Aggiunge/rimuove 'invalido' e scrive il messaggio nello <span>; riutilizzabile per ogni campo (SRP)
    function mostraErrore(inputEl, spanId, messaggio) {
        const span = document.getElementById(spanId);
        if (span) span.textContent = messaggio;
        if (messaggio) {
            inputEl.classList.add('invalido');
        } else {
            inputEl.classList.remove('invalido');
        }
    }

    // Verifica che i campi obbligatori non siano vuoti
    function validaObbligatorio(el, spanId, etichetta) {
        if (el.value.trim() === '') {
            mostraErrore(el, spanId, etichetta + ' è obbligatorio/a.');
            return false;
        }
        mostraErrore(el, spanId, '');
        return true;
    }


    function validaPeso() {
        const val = parseFloat(inPeso.value);
        if (isNaN(val) || val <= 0 || val > 20) {
            mostraErrore(inPeso, 'errore-peso', 'Inserisci un peso valido (tra 0.1 e 20 kg).');
            return false;
        }
        mostraErrore(inPeso, 'errore-peso', '');
        return true;
    }

    function validaEta() {
        const val = parseInt(inEta.value, 10);
        if (isNaN(val) || val < 0 || val > 300) {
            mostraErrore(inEta, 'errore-eta', "Inserisci un'età valida in mesi (0-300).");
            return false;
        }
        mostraErrore(inEta, 'errore-eta', '');
        return true;
    }

    function validaSelezione(el, spanId, etichetta) {
        if (el.value === '') {
            mostraErrore(el, spanId, 'Seleziona ' + etichetta + '.');
            return false;
        }
        mostraErrore(el, spanId, '');
        return true;
    }

    // Listener blur/change: segnalano l'errore appena l'utente esce dal campo
    inNome.addEventListener('blur', () =>
        validaObbligatorio(inNome, 'errore-nome', 'Il nome'));

    inDescrizione.addEventListener('blur', () =>
        validaObbligatorio(inDescrizione, 'errore-descrizione', 'La descrizione'));

    inPeso.addEventListener('blur', validaPeso);
    inEta.addEventListener('blur', validaEta);

    inSesso.addEventListener('change', () =>
        validaSelezione(inSesso, 'errore-sesso', 'il sesso'));

    inLunghezzaPelo.addEventListener('change', () =>
        validaSelezione(inLunghezzaPelo, 'errore-lunghezza-pelo', 'la lunghezza del pelo'));

    inColoreMantello.addEventListener('blur', () =>
        validaObbligatorio(inColoreMantello, 'errore-colore-mantello', 'Il colore del mantello'));

    inColoreOcchi.addEventListener('blur', () =>
        validaObbligatorio(inColoreOcchi, 'errore-colore-occhi', 'Il colore degli occhi'));

    inRazza.addEventListener('blur', () =>
        validaObbligatorio(inRazza, 'errore-razza', 'La razza'));

    inDataArrivo.addEventListener('blur', () =>
        validaObbligatorio(inDataArrivo, 'errore-data-arrivo', 'La data di arrivo'));

    // Validazione completa al submit: blocca l'invio se anche un solo campo è invalido
    form.addEventListener('submit', function (e) {
        const ok = validaObbligatorio(inNome, 'errore-nome', 'Il nome')
            & validaObbligatorio(inDescrizione, 'errore-descrizione', 'La descrizione')
            & validaPeso()
            & validaEta()
            & validaSelezione(inSesso, 'errore-sesso', 'il sesso')
            & validaSelezione(inLunghezzaPelo, 'errore-lunghezza-pelo', 'la lunghezza del pelo')
            & validaObbligatorio(inColoreMantello, 'errore-colore-mantello', 'Il colore del mantello')
            & validaObbligatorio(inColoreOcchi, 'errore-colore-occhi', 'Il colore degli occhi')
            & validaObbligatorio(inRazza, 'errore-razza', 'La razza')
            & validaObbligatorio(inDataArrivo, 'errore-data-arrivo', 'La data di arrivo');

        if (!ok) {
            e.preventDefault();
        }
    });
}());
