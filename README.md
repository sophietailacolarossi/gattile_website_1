# Casa del Gatto — gestionale web per un gattile

Applicazione web full-stack per la gestione di un gattile: catalogo dei gatti disponibili per l'adozione, prenotazione di visite conoscitive in struttura e iscrizione ai turni di volontariato, con area riservata e permessi differenziati.

Progetto sviluppato durante il corso di laurea in Ingegneria Gestionale (curriculum Informatica) — Politecnico di Torino.

## Funzionalità

- **Catalogo gatti** — schede con razza, età, peso, sesso, mantello, lunghezza del pelo, colore degli occhi e data di arrivo; in home compaiono automaticamente gli ultimi due arrivi.
- **Prenotazione visite** — l'utente registrato seleziona uno o più gatti e prenota una fascia oraria per conoscerli.
- **Turni di volontariato** — visualizzazione delle fasce disponibili e iscrizione, con controllo dei posti residui.
- **Autenticazione** — registrazione, login con sessione PHP e logout; la voce "Inserisci gatto" compare solo agli utenti amministratori.
- **Inserimento gatti** — form riservato agli amministratori per aggiungere nuovi ospiti alla struttura.

## Scelte tecniche

**Tre utenti di database con privilegi distinti.** Invece di un'unica utenza con pieni poteri, la connessione avviene con l'utente minimo necessario all'operazione: `lecture` in sola lettura per le pagine di consultazione, `registrator` per le scritture legate a registrazione e prenotazioni, `modifier` per le operazioni amministrative. La funzione `getDB($tipo)` in `includes/db.php` seleziona la connessione appropriata.

**Difesa dagli attacchi comuni.** Query parametrizzate contro le SQL injection e `htmlspecialchars()` su tutto l'output dinamico contro le XSS.

**Validazione doppia.** Controlli lato client in JavaScript per il feedback immediato, ripetuti lato server in PHP perché la validazione client non è una garanzia.

**Accessibilità.** Struttura HTML semantica, attributi `aria-label` sugli elementi interattivi, immagini decorative marcate `aria-hidden`, skip link per la navigazione da tastiera.

## Stack

| Livello | Tecnologie |
|---|---|
| Front-end | HTML5, CSS3, JavaScript (vanilla) |
| Back-end | PHP 8 |
| Database | MySQL / MariaDB |
| Ambiente | XAMPP (Apache + MySQL + phpMyAdmin) |

## Struttura del progetto

```
gattile_website/
├── api/                 endpoint PHP chiamati via fetch dal front-end
│   ├── get_gatti.php
│   ├── get_turni.php
│   ├── prenota_turno.php
│   └── prenota_visita.php
├── css/style.css
├── img/                 icone e immagini
├── includes/
│   ├── db.php           connessione al database (non versionato)
│   ├── header.php
│   └── footer.php
├── js/                  validazione form e chiamate asincrone
├── home.php
├── gatti.php            catalogo + prenotazione visita
├── volontariato.php     turni di volontariato
├── inserisci_gatto.php  area amministratore
├── login.php · registrazione.php · logout.php
└── database/gattile_db.sql
```

## Installazione in locale

1. Installa [XAMPP](https://www.apachefriends.org/) e avvia **Apache** e **MySQL** dal Control Panel.
2. Copia la cartella del progetto in `C:\xampp\htdocs\`.
3. Apri `http://localhost/phpmyadmin`, crea un database chiamato `gattile_db` e importa `database/gattile_db.sql`.
4. Crea i tre utenti MySQL (`lecture`, `registrator`, `modifier`) con i rispettivi privilegi.
5. Copia `includes/db.example.php` in `includes/db.php` e inserisci le password scelte al punto 4.
6. Apri `http://localhost/gattile_website/home.php`.

## Screenshot

<!-- Crea una cartella "docs" nel progetto, mettici gli screenshot e togli i commenti qui sotto -->
<!-- ![Home](docs/home.png) -->
<!-- ![Catalogo gatti](docs/gatti.png) -->
<!-- ![Volontariato](docs/volontariato.png) -->

## Autrice

**Sophie Taila Colarossi** — Ingegneria Gestionale, Politecnico di Torino
