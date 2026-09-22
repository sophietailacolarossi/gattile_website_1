/**
 * Validazione client-side del form di login.
 * Evita di inviare al server dati con formato errato o assenti,
 * alleggerendo il carico e migliorando la sicurezza.
 */

// function() isola lo scope; funzioni come mostraErrore non diventano globali su window
(function () {
    'use strict';

    const form     = document.getElementById('form-login');
    const username = document.getElementById('username');
    const password = document.getElementById('password');

    // Scrive il messaggio nello <span> e aggiunge/rimuove la classe "invalido"
    function mostraErrore(inputEl, spanId, messaggio) {
        const span = document.getElementById(spanId);
        if (span) span.textContent = messaggio;
        if (messaggio) {
            inputEl.classList.add('invalido');
        } else {
            inputEl.classList.remove('invalido');
        }
    }

    //valido username e password assicurandomi non siano vuoti

    function validaUsername() {
        const val = username.value.trim();
        if (val === '') {
            mostraErrore(username, 'errore-username', 'Lo username è obbligatorio.');
            return false;
        }
        mostraErrore(username, 'errore-username', '');
        return true;
    }

    function validaPassword() {
        const val = password.value;
        if (val === '') {
            mostraErrore(password, 'errore-password', 'La password è obbligatoria.');
            return false;
        }
        mostraErrore(password, 'errore-password', '');
        return true;
    }

    // Al submit valida entrambi i campi; se fallisce blocca il comportamento di default
    form.addEventListener('submit', function (e) {
        const okUser = validaUsername();
        const okPass = validaPassword();
        if (!okUser || !okPass) {
            e.preventDefault();
        }
    });
}());
