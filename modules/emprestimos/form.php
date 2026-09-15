<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$usuarios = $pdo->query(
    "SELECT id, nome FROM usuarios WHERE tipo = 'aluno' AND status = 'ativo' ORDER BY nome"
)->fetchAll();

$livros = $pdo->query(
    "SELECT id, titulo, quantidade_disponivel
     FROM livros
     WHERE quantidade_disponivel > 0
     ORDER BY titulo"
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO:
    // 1. Validar usuário e livro.
    // 2. Verificar disponibilidade.
    // 3. INSERT em emprestimos.
    // 4. UPDATE livros SET quantidade_disponivel = quantidade_disponivel - 1.
    // 5. Use transação (beginTransaction/commit/rollBack).
    $mensagem = 'Agora implemente a regra de empréstimo.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo empréstimo</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">
    <div class="card">
        <h1>Novo empréstimo</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="alert"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Aluno</label>
            <select name="usuario_id" required>
                <option value="">Selecione</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['id'] ?>">
                        <?= htmlspecialchars($usuario['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Livro</label>
            <select name="livro_id" required>
                <option value="">Selecione</option>
                <?php foreach ($livros as $livro): ?>
                    <option value="<?= $livro['id'] ?>">
                        <?= htmlspecialchars($livro['titulo']) ?>
                        — <?= $livro['quantidade_disponivel'] ?> disponível(is)
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Data prevista para devolução</label>
            <input type="date" name="data_devolucao_prevista" required>

            <button type="submit">Registrar empréstimo</button>
        </form>
    </div>
</main>
</body>
</html>
