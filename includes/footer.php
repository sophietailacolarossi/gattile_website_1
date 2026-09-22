</main>
<footer class="site-footer">
    <div class="footer-inner">
        <p>&copy; <?= date('Y') ?> Casa del Gatto – Torino</p>
        <p>Progetto del Politecnico di Torino, corso di Progettazione di Applicazioni Internet AA 2025-2026</p>
    </div>
</footer>
<?php
// URL completa della pagina corrente per stampa 
$protocolloPagina = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$urlPagina = $protocolloPagina . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<footer class="print-footer">
    <p>&copy; <?= date('Y') ?> Casa del Gatto – Torino</p>
    <p>Progetto del Politecnico di Torino, corso di Progettazione di Applicazioni Internet AA 2025-2026</p>
    <p><?= htmlspecialchars($urlPagina) ?></p>
</footer>
</body>
</html>
