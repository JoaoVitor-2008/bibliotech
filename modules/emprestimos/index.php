<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$emprestimos = $pdo->query(
    "SELECT e.id, u.nome AS usuario, l.titulo AS livro,
            e.data_retirada, e.data_devolucao_prevista,
            e.data_devolucao_real, e.status
     FROM emprestimos e
     INNER JOIN usuarios u ON u.id = e.usuario_id
     INNER JOIN livros l ON l.id = e.livro_id
     ORDER BY e.id DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empréstimos — BiblioTech</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">
    <div class="page-title">
        <h1>Empréstimos</h1>
        <a class="button" href="form.php">Novo empréstimo</a>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Aluno</th>
                    <th>Livro</th>
                    <th>Retirada</th>
                    <th>Prevista</th>
                    <th>Real</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($emprestimos as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['usuario']) ?></td>
                    <td><?= htmlspecialchars($item['livro']) ?></td>
                    <td><?= htmlspecialchars($item['data_retirada']) ?></td>
                    <td><?= htmlspecialchars($item['data_devolucao_prevista']) ?></td>
                    <td><?= htmlspecialchars($item['data_devolucao_real'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($item['status']) ?></td>
                    <td>
                        <?php if ($item['status'] === 'ativo' || $item['status'] === 'atrasado'): ?>
                            <a href="devolver.php?id=<?= $item['id'] ?>">Registrar devolução</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
