
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$livro = [
    'titulo' => '',
    'autor' => '',
    'ano_publicacao' => '',
    'quantidade_total' => 1,
    'quantidade_disponivel' => 1
];

$mensagem = '';
$livroEncontrado = true;

// Buscar livro para edição
if ($id) {
    $stmt = $pdo->prepare(
        'SELECT * FROM livros WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $livro = $resultado;
    } else {
        $livroEncontrado = false;
        $mensagem = 'Livro não encontrado.';
    }
}

// Receber dados do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $livroEncontrado) {

    $titulo = trim($_POST['titulo'] ?? '');
    $autor = trim($_POST['autor'] ?? '');
    $ano = trim($_POST['ano_publicacao'] ?? '');
    $total = filter_var(
        $_POST['quantidade_total'] ?? null,
        FILTER_VALIDATE_INT
    );

    // Na edição, recebe a quantidade disponível.
    // No cadastro, ela começa igual à quantidade total.
    if ($id) {
        $disponivel = filter_var(
            $_POST['quantidade_disponivel'] ?? null,
            FILTER_VALIDATE_INT
        );
    } else {
        $disponivel = $total;
    }

    // Manter os dados preenchidos no formulário
    $livro = [
        'titulo' => $titulo,
        'autor' => $autor,
        'ano_publicacao' => $ano,
        'quantidade_total' => $_POST['quantidade_total'] ?? '',
        'quantidade_disponivel' => $id
            ? ($_POST['quantidade_disponivel'] ?? '')
            : $total
    ];

    // Validar os dados
    if ($titulo === '' || $autor === '') {
        $mensagem = 'Preencha o título e o autor.';

    } elseif ($total === false || $total < 1) {
        $mensagem = 'A quantidade total deve ser pelo menos 1.';

    } elseif (
        $disponivel === false ||
        $disponivel < 0 ||
        $disponivel > $total
    ) {
        $mensagem = 'A quantidade disponível deve estar entre 0 e a quantidade total.';

    } elseif ($ano !== '' && filter_var($ano, FILTER_VALIDATE_INT) === false) {
        $mensagem = 'Informe um ano de publicação válido.';

    } else {

        $ano = $ano === '' ? null : (int) $ano;

        if ($id) {
            // UPDATE: alterar livro existente
            $sql = 'UPDATE livros SET
                        titulo = :titulo,
                        autor = :autor,
                        ano_publicacao = :ano,
                        quantidade_total = :total,
                        quantidade_disponivel = :disponivel
                    WHERE id = :id';

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'titulo' => $titulo,
                'autor' => $autor,
                'ano' => $ano,
                'total' => $total,
                'disponivel' => $disponivel,
                'id' => $id
            ]);

            $mensagem = 'Livro atualizado com sucesso!';

        } else {
            // INSERT: cadastrar livro novo
            $sql = 'INSERT INTO livros
                    (titulo, autor, ano_publicacao,
                     quantidade_total, quantidade_disponivel)
                    VALUES
                    (:titulo, :autor, :ano, :total, :disponivel)';

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'titulo' => $titulo,
                'autor' => $autor,
                'ano' => $ano,
                'total' => $total,
                'disponivel' => $total
            ]);

            $mensagem = 'Livro cadastrado com sucesso!';

            // Limpar formulário após cadastro
            $livro = [
                'titulo' => '',
                'autor' => '',
                'ano_publicacao' => '',
                'quantidade_total' => 1,
                'quantidade_disponivel' => 1
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Editar' : 'Novo' ?> livro</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">
    <div class="card">

        <h1><?= $id ? 'Editar livro' : 'Novo livro' ?></h1>

        <?php if ($mensagem !== ''): ?>
            <div class="alert">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <?php if ($livroEncontrado): ?>

            <form method="post">

                <label>Título</label>
                <input
                    type="text"
                    name="titulo"
                    value="<?= htmlspecialchars($livro['titulo']) ?>"
                    required
                >

                <label>Autor</label>
                <input
                    type="text"
                    name="autor"
                    value="<?= htmlspecialchars($livro['autor']) ?>"
                    required
                >

                <label>Ano de publicação</label>
                <input
                    type="number"
                    name="ano_publicacao"
                    value="<?= htmlspecialchars((string) $livro['ano_publicacao']) ?>"
                >

                <label>Quantidade total</label>
                <input
                    type="number"
                    name="quantidade_total"
                    min="1"
                    value="<?= htmlspecialchars((string) $livro['quantidade_total']) ?>"
                    required
                >

                <?php if ($id): ?>

                    <label>Quantidade disponível</label>
                    <input
                        type="number"
                        name="quantidade_disponivel"
                        min="0"
                        value="<?= htmlspecialchars((string) $livro['quantidade_disponivel']) ?>"
                        required
                    >

                <?php else: ?>

                    <p>A quantidade disponível começará igual à quantidade total.</p>

                <?php endif; ?>

                <button type="submit">Salvar</button>

            </form>

        <?php endif; ?>

    </div>
</main>

</body>
</html>
