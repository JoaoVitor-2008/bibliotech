
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$mensagem = '';

// Buscar somente alunos ativos
$usuarios = $pdo->query(
    "SELECT id, nome
     FROM usuarios
     WHERE tipo = 'aluno' AND status = 'ativo'
     ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

// Buscar livros, inclusive os indisponíveis
$livros = $pdo->query(
    "SELECT id, titulo, quantidade_disponivel
     FROM livros
     ORDER BY titulo"
)->fetchAll(PDO::FETCH_ASSOC);

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario_id = filter_var(
        $_POST['usuario_id'] ?? '',
        FILTER_VALIDATE_INT
    );

    $livro_id = filter_var(
        $_POST['livro_id'] ?? '',
        FILTER_VALIDATE_INT
    );

    $data_devolucao_prevista = $_POST['data_devolucao_prevista'] ?? '';

    // Validar os campos
    $dataValida = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $data_devolucao_prevista
    );

    $errosData = DateTimeImmutable::getLastErrors();

    if (
        !$dataValida ||
        ($errosData !== false &&
            ($errosData['warning_count'] > 0 || $errosData['error_count'] > 0)) ||
        $dataValida->format('Y-m-d') !== $data_devolucao_prevista
    ) {
        $mensagem = 'Informe uma data de devolução válida.';

    } elseif (
        !$usuario_id || $usuario_id <= 0 ||
        !$livro_id || $livro_id <= 0
    ) {
        $mensagem = 'Selecione um aluno e um livro válidos.';

    } else {

        try {

            // Iniciar transação
            $pdo->beginTransaction();

            // Verificar se o aluno existe e está ativo
            $stmt = $pdo->prepare(
                "SELECT id
                 FROM usuarios
                 WHERE id = :id
                 AND tipo = 'aluno'
                 AND status = 'ativo'"
            );

            $stmt->execute(['id' => $usuario_id]);

            if (!$stmt->fetch()) {
                throw new Exception('Aluno inválido ou inativo.');
            }

            // Buscar livro e bloquear sua linha durante a transação
            $stmt = $pdo->prepare(
                'SELECT id, quantidade_disponivel
                 FROM livros
                 WHERE id = :id
                 FOR UPDATE'
            );

            $stmt->execute(['id' => $livro_id]);

            $livro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$livro) {
                throw new Exception('Livro não encontrado.');
            }

            // Verificar disponibilidade
            if ($livro['quantidade_disponivel'] <= 0) {
                throw new Exception('Livro indisponível para empréstimo.');
            }

            // Registrar a data atual
            $data_retirada = date('Y-m-d');

            // Criar empréstimo
            $sql = "INSERT INTO emprestimos
                    (usuario_id, livro_id, data_retirada,
                     data_devolucao_prevista, status)
                    VALUES
                    (:usuario_id, :livro_id, :data_retirada,
                     :data_devolucao_prevista, :status)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'usuario_id' => $usuario_id,
                'livro_id' => $livro_id,
                'data_retirada' => $data_retirada,
                'data_devolucao_prevista' => $data_devolucao_prevista,
                'status' => 'ativo'
            ]);

            // Diminuir quantidade disponível em 1
            $stmt = $pdo->prepare(
                'UPDATE livros
                 SET quantidade_disponivel = quantidade_disponivel - 1
                 WHERE id = :id
                 AND quantidade_disponivel > 0'
            );

            $stmt->execute(['id' => $livro_id]);

            if ($stmt->rowCount() !== 1) {
                throw new Exception('Não foi possível atualizar a quantidade do livro.');
            }

            // Confirmar as duas operações
            $pdo->commit();

            $mensagem = 'Empréstimo registrado com sucesso!';

        } catch (Throwable $erro) {

            // Desfazer tudo em caso de erro
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $mensagem = $erro->getMessage();
        }

        // Atualizar a lista de livros após o empréstimo
        $livros = $pdo->query(
            "SELECT id, titulo, quantidade_disponivel
             FROM livros
             ORDER BY titulo"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}

require_once __DIR__ . '/../../includes/header.php';
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

<main class="container">

    <div class="card">

        <h1>Novo empréstimo</h1>

        <?php if ($mensagem !== ''): ?>
            <div class="alert">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <label>Aluno</label>

            <select name="usuario_id" required>
                <option value="">Selecione</option>

                <?php foreach ($usuarios as $usuario): ?>

                    <option
                        value="<?= $usuario['id'] ?>"
                        <?= (string)($usuario_id ?? '') === (string)$usuario['id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($usuario['nome']) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <label>Livro</label>

            <select name="livro_id" required>
                <option value="">Selecione</option>

                <?php foreach ($livros as $livro): ?>

                    <option
                        value="<?= $livro['id'] ?>"
                        <?= (string)($livro_id ?? '') === (string)$livro['id'] ? 'selected' : '' ?>
                        <?= $livro['quantidade_disponivel'] <= 0 ? 'disabled' : '' ?>
                    >
                        <?= htmlspecialchars($livro['titulo']) ?>
                        — <?= $livro['quantidade_disponivel'] ?> disponível(is)
                    </option>

                <?php endforeach; ?>

            </select>

            <label>Data prevista para devolução</label>

            <input
                type="date"
                name="data_devolucao_prevista"
                value="<?= htmlspecialchars($data_devolucao_prevista ?? '') ?>"
                required
            >

            <button type="submit">Registrar empréstimo</button>

        </form>

        <p>
            <a href="index.php">Voltar para empréstimos</a>
        </p>

    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

</body>
</html>
