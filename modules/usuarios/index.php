

<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor', 'aluno']);

$sql = 'SELECT id, titulo, autor, ano_publicacao,
               quantidade_total, quantidade_disponivel
        FROM livros
        ORDER BY titulo';

$stmt = $pdo->prepare($sql);
$stmt->execute();

$livros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livros — BiblioTech</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">

    <div class="page-title">
        <h1>Livros</h1>

        <?php if (in_array($_SESSION['tipo'], ['admin', 'professor'], true)): ?>
            <a class="button" href="form.php">Novo livro</a>
        <?php endif; ?>
    </div>

    <div class="card">

        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Ano</th>
                    <th>Quantidade total</th>
                    <th>Quantidade disponível</th>

                    <?php if (in_array($_SESSION['tipo'], ['admin', 'professor'], true)): ?>
                        <th>Ações</th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>

                <?php if (count($livros) > 0): ?>

                    <?php foreach ($livros as $livro): ?>

                        <tr>
                            <td><?= htmlspecialchars($livro['titulo']) ?></td>
                            <td><?= htmlspecialchars($livro['autor']) ?></td>
                            <td><?= htmlspecialchars($livro['ano_publicacao']) ?></td>
                            <td><?= htmlspecialchars($livro['quantidade_total']) ?></td>
                            <td><?= htmlspecialchars($livro['quantidade_disponivel']) ?></td>

                            <?php if (in_array($_SESSION['tipo'], ['admin', 'professor'], true)): ?>
                                <td>
                                    <a href="form.php?id=<?= $livro['id'] ?>">
                                        Editar
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="<?= in_array($_SESSION['tipo'], ['admin', 'professor'], true) ? 6 : 5 ?>">
                            Nenhum livro cadastrado.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>
        </table>

    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

</body>
</html>
