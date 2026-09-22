/**
 * Validazione client-side del form di registrazione.
 * Complementare alla validazione server-side: evita richieste inutili con dati malformati.
 */

// function() isola lo scope per evitare conflitti tra script diversi sulla window
(function () {
    'use strict';

    const form = document.getElementById('form-registrazione');
    if (!form) return;   // se il form non è nel DOM (es. dopo registrazione riuscita), esco subito
    const inNome = document.getElementById('nome');
    const inCognome = document.getElementById('cognome');
    const inIndirizzo = document.getElementById('indirizzo');
    const inUsername = document.getElementById('username');
    const inPassword = document.getElementById('password');
    const inConferma = document.getElementById('conferma_password');


    // Espressioni regolari per formato password e username, speculari alla validazione PHP
    const PATTERN_PASSWORD = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/;
    const PATTERN_USERNAME = /^[a-zA-Z]/;

    // Aggiunge/rimuove 'invalido' e scrive il messaggio nello <span>
    function mostraErrore(inputEl, spanId, messaggio) {
        const span = document.getElementById(spanId);
        if (span){
            span.textContent = messaggio;
        }
        if (messaggio) {
            inputEl.classList.add('invalido');
        } else {
            inputEl.classList.remove('invalido');
        }
    }

    // Verifica che un campo obbligatorio non sia vuoto; usata per nome, cognome, indirizzo
    function validaObbligatorio(el, spanId, etichetta) {
        if (el.value.trim() === '') {
            mostraErrore(el, spanId, etichetta + ' è obbligatorio/a.');
            return false;
        }
        mostraErrore(el, spanId, '');
        return true;
    }

    function validaUsername() {
        const val = inUsername.value.trim();
        if (val === '') {
            mostraErrore(inUsername, 'errore-username', 'Lo username è obbligatorio.');
            return false;
        }
        if (!PATTERN_USERNAME.test(val)) {
            mostraErrore(inUsername, 'errore-username',
                'Lo username deve iniziare con carattere alfabetico');
            return false;
        }
        mostraErrore(inUsername, 'errore-username', '');
        return true;
    }

    function validaPassword() {
        const val = inPassword.value;
        if (val === '') {
            mostraErrore(inPassword, 'errore-password', 'La password è obbligatoria.');
            return false;
        }
        if (!PATTERN_PASSWORD.test(val)) {
            mostraErrore(inPassword, 'errore-password',
                'La password deve essere lunga 8-16 caratteri e contenere almeno una maiuscola, una minuscola, un numero e un carattere speciale.');
            return false;
        }
        mostraErrore(inPassword, 'errore-password', '');
        return true;
    }

    function validaConferma() { //controlla che campo conferma e password siano uguali
        if (inConferma.value === '') {
            mostraErrore(inConferma, 'errore-conferma', 'Conferma la password.');
            return false;
        }
        if (inPassword.value !== inConferma.value) {
            mostraErrore(inConferma, 'errore-conferma', 'Le password non coincidono.');
            return false;
        }
        mostraErrore(inConferma, 'errore-conferma', '');
        return true;
    }

    // Listener blur: valida il campo non appena l'utente esce, segnalando l'errore in modo tempestivo
    inNome.addEventListener('blur', () => validaObbligatorio(inNome, 'errore-nome', 'Il nome'));
    inCognome.addEventListener('blur', () => validaObbligatorio(inCognome, 'errore-cognome', 'Il cognome'));
    inIndirizzo.addEventListener('blur',() => validaObbligatorio(inIndirizzo, 'errore-indirizzo', "L'indirizzo"));
    inUsername.addEventListener('blur',  validaUsername);
    inPassword.addEventListener('blur',  validaPassword);
    inConferma.addEventListener('blur',  validaConferma);


    // Validazione completa al submit: blocca l'invio se anche un solo campo è invalido
    form.addEventListener('submit', function (e) {
        const ok = validaObbligatorio(inNome, 'errore-nome', 'Il nome')
            & validaObbligatorio(inCognome, 'errore-cognome', 'Il cognome')
            & validaObbligatorio(inIndirizzo, 'errore-indirizzo', "L'indirizzo")
            & validaUsername()
            & validaPassword()
            & validaConferma();

        if (!ok) {
            e.preventDefault();
        }
    });
}());
