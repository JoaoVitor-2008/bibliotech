<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

// TODO:
// 1. Buscar o empréstimo.
// 2. Verificar se ainda está ativo/atrasado.
// 3. Registrar a data atual.
// 4. Alterar status para devolvido.
// 5. Aumentar quantidade_disponivel em 1.
// 6. Fazer tudo em uma transação.

$mensagem = 'Implemente aqui a devolução do livro.';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Devolução</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">
    <div class="card">
        <h1>Registrar devolução</h1>
        <div class="alert"><?= htmlspecialchars($mensagem) ?></div>
        <a class="button" href="index.php">Voltar</a>
    </div>
</main>
</body>
</html>
