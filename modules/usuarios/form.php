
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

// Somente administrador e professor podem cadastrar ou editar
exigirPerfil(['admin', 'professor']);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Impedir ID inválido
if (isset($_GET['id']) && (!$id || $id <= 0)) {
    header('Location: index.php');
    exit;
}

$livro = [
    'titulo' => '',
    'autor' => '',
    'ano_publicacao' => '',
    'quantidade_total' => '',
    'quantidade_disponivel' => ''
];

$mensagem = '';

// Buscar livro existente
if ($id) {

    $stmt = $pdo->prepare(
        'SELECT * FROM livros WHERE id = :id'
    );

    $stmt->execute(['id' => $id]);

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resultado) {
        die('Livro não encontrado.');
    }

    $livro = $resultado;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo'] ?? '');
    $autor = trim($_POST['autor'] ?? '');

    $ano_publicacao = filter_input(
        INPUT_POST,
        'ano_publicacao',
        FILTER_VALIDATE_INT
    );

    $quantidade_total = filter_input(
        INPUT_POST,
        'quantidade_total',
        FILTER_VALIDATE_INT
    );

    $quantidade_disponivel = filter_input(
        INPUT_POST,
        'quantidade_disponivel',
        FILTER_VALIDATE_INT
    );

    // Validar campos
    if (
        $titulo === '' ||
        $autor === '' ||
        $ano_publicacao === false ||
        $ano_publicacao === null ||
        $ano_publicacao <= 0 ||
        $quantidade_total === false ||
        $quantidade_total === null ||
        $quantidade_total < 0 ||
        $quantidade_disponivel === false ||
        $quantidade_disponivel === null ||
        $quantidade_disponivel < 0
    ) {

        $mensagem = 'Preencha os campos corretamente.';

    } elseif ($quantidade_disponivel > $quantidade_total) {

        $mensagem = 'A quantidade disponível não pode ser maior que a quantidade total.';

    } else {

        if ($id) {

            // UPDATE: editar livro
            $sql = 'UPDATE livros SET
                    titulo = :titulo,
                    autor = :autor,
                    ano_publicacao = :ano_publicacao,
                    quantidade_total = :quantidade_total,
                    quantidade_disponivel = :quantidade_disponivel
                    WHERE id = :id';

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'titulo' => $titulo,
                'autor' => $autor,
                'ano_publicacao' => $ano_publicacao,
                'quantidade_total' => $quantidade_total,
                'quantidade_disponivel' => $quantidade_disponivel,
                'id' => $id
            ]);

            $mensagem = 'Livro atualizado com sucesso!';

        } else {

            // CREATE: cadastrar livro
            // Disponível começa igual ao total
            $quantidade_disponivel = $quantidade_total;

            $sql = 'INSERT INTO livros
                    (titulo, autor, ano_publicacao,
                     quantidade_total, quantidade_disponivel)
                    VALUES
                    (:titulo, :autor, :ano_publicacao,
                     :quantidade_total, :quantidade_disponivel)';

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'titulo' => $titulo,
                'autor' => $autor,
                'ano_publicacao' => $ano_publicacao,
                'quantidade_total' => $quantidade_total,
                'quantidade_disponivel' => $quantidade_disponivel
            ]);

            $mensagem = 'Livro cadastrado com sucesso!';

            $livro = [
                'titulo' => '',
                'autor' => '',
                'ano_publicacao' => '',
                'quantidade_total' => '',
                'quantidade_disponivel' => ''
            ];
        }

        if ($id) {
            $livro = [
                'titulo' => $titulo,
                'autor' => $autor,
                'ano_publicacao' => $ano_publicacao,
                'quantidade_total' => $quantidade_total,
                'quantidade_disponivel' => $quantidade_disponivel
            ];
        }
    }

    // Manter os dados preenchidos quando houver erro
    if ($mensagem === 'Preencha os campos corretamente.' ||
        $mensagem === 'A quantidade disponível não pode ser maior que a quantidade total.') {

        $livro = [
            'titulo' => $titulo,
            'autor' => $autor,
            'ano_publicacao' => $_POST['ano_publicacao'] ?? '',
            'quantidade_total' => $_POST['quantidade_total'] ?? '',
            'quantidade_disponivel' => $_POST['quantidade_disponivel'] ?? ''
        ];
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
                min="1"
                value="<?= htmlspecialchars($livro['ano_publicacao']) ?>"
                required
            >

            <label>Quantidade total</label>
            <input
                type="number"
                name="quantidade_total"
                min="0"
                value="<?= htmlspecialchars($livro['quantidade_total']) ?>"
                required
            >

            <?php if ($id): ?>

                <label>Quantidade disponível</label>
                <input
                    type="number"
                    name="quantidade_disponivel"
                    min="0"
                    value="<?= htmlspecialchars($livro['quantidade_disponivel']) ?>"
                    required
                >

            <?php endif; ?>

            <button type="submit">Salvar</button>

        </form>

        <p>
            <a href="index.php">Voltar para livros</a>
        </p>

    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

</body>
</html>




