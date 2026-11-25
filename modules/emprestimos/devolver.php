
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

$mensagem = '';

try {
    // Inicia a transação
    $pdo->beginTransaction();

    // 1. Buscar o empréstimo e bloquear o registro
    $sql = "SELECT id, livro_id, status
            FROM emprestimos
            WHERE id = :id
            FOR UPDATE";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    $emprestimo = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Verificar se o empréstimo existe
    if (!$emprestimo) {
        throw new Exception('Empréstimo não encontrado.');
    }

    // 3. Verificar se ainda pode ser devolvido
    if (!in_array($emprestimo['status'], ['ativo', 'atrasado'], true)) {
        throw new Exception('Este empréstimo já foi devolvido.');
    }

    // 4. Registrar a data real e alterar o status
    $sql = "UPDATE emprestimos
            SET data_devolucao_real = :data,
                status = 'devolvido'
            WHERE id = :id
              AND status IN ('ativo', 'atrasado')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'data' => date('Y-m-d'),
        'id' => $id
    ]);

    // Garantir que o empréstimo foi atualizado
    if ($stmt->rowCount() !== 1) {
        throw new Exception('Não foi possível registrar a devolução.');
    }

    // 5. Aumentar a quantidade disponível do livro
    $sql = "UPDATE livros
            SET quantidade_disponivel = quantidade_disponivel + 1
            WHERE id = :livro_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'livro_id' => $emprestimo['livro_id']
    ]);

    if ($stmt->rowCount() !== 1) {
        throw new Exception('Não foi possível atualizar a quantidade do livro.');
    }

    // 6. Confirmar as alterações
    $pdo->commit();

    $mensagem = 'Devolução registrada com sucesso!';

} catch (Throwable $erro) {

    // Desfazer alterações caso aconteça algum erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $mensagem = $erro->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devolução</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">
    <div class="card">
        <h1>Registrar devolução</h1>

        <div class="alert">
            <?= htmlspecialchars($mensagem) ?>
        </div>

        <a class="button" href="index.php">Voltar</a>
    </div>
</main>

</body>
</html>
