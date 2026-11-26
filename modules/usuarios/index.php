
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

// Consultar usuários conforme o perfil
if ($_SESSION['tipo'] === 'professor') {
    $stmt = $pdo->prepare(
        "SELECT id, nome, email, tipo, status
         FROM usuarios
         WHERE tipo = 'aluno'
         ORDER BY nome"
    );
} else {
    $stmt = $pdo->prepare(
        "SELECT id, nome, email, tipo, status
         FROM usuarios
         ORDER BY nome"
    );
}

$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários — BiblioTech</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">

    <div class="page-title">
        <h1>
            <?= $_SESSION['tipo'] === 'professor' ? 'Alunos' : 'Usuários' ?>
        </h1>

        <a class="button" href="form.php">Novo usuário</a>
    </div>

    <div class="card">

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($usuarios) > 0): ?>

                    <?php foreach ($usuarios as $usuario): ?>

                        <tr>
                            <td>
                                <?= htmlspecialchars($usuario['nome']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($usuario['email']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($usuario['tipo']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($usuario['status']) ?>
                            </td>

                            <td>
                                <a href="form.php?id=<?= (int) $usuario['id'] ?>">
                                    Editar
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5">
                            Nenhum usuário cadastrado.
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
