<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="topbar">
    <a class="brand" href="../dashboard/index.php">📚 BiblioTech</a>

    <nav>
        <a href="../dashboard/index.php">Dashboard</a>

        <?php if (in_array($_SESSION['tipo'] ?? '', ['admin', 'professor'], true)): ?>
            <a href="../usuarios/index.php">Usuários</a>
            <a href="../livros/index.php">Livros</a>
            <a href="../emprestimos/index.php">Empréstimos</a>
        <?php else: ?>
            <a href="../livros/index.php">Livros</a>
            <a href="../emprestimos/meus.php">Meus empréstimos</a>
        <?php endif; ?>

        <a href="../../logout.php">Sair</a>
    </nav>
</header>
