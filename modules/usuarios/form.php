


<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Impedir ID inválido
if (isset($_GET['id']) && (!$id || $id <= 0)) {
    header('Location: index.php');
    exit;
}

$usuario = [
    'nome' => '',
    'email' => '',
    'tipo' => 'aluno',
    'status' => 'ativo'
];

$mensagem = '';
$erro = false;

// Buscar usuário existente
if ($id) {

    $stmt = $pdo->prepare(
        'SELECT * FROM usuarios WHERE id = :id'
    );

    $stmt->execute(['id' => $id]);

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resultado) {
        die('Usuário não encontrado.');
    }

    $usuario = $resultado;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo = $_POST['tipo'] ?? 'aluno';
    $status = $_POST['status'] ?? 'ativo';

    $usuario = [
        'nome' => $nome,
        'email' => $email,
        'tipo' => $tipo,
        'status' => $status
    ];

    // Validar campos
    if (
        $nome === '' ||
        $email === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        (!$id && $senha === '')
    ) {

        $mensagem = 'Preencha os campos corretamente.';
        $erro = true;

    } elseif (
        !in_array($tipo, ['admin', 'professor', 'aluno'], true) ||
        !in_array($status, ['ativo', 'inativo'], true)
    ) {

        $mensagem = 'Tipo ou status inválido.';
        $erro = true;

    } else {

        // Verificar e-mail duplicado
        $sql = 'SELECT id FROM usuarios
                WHERE email = :email';

        if ($id) {
            $sql .= ' AND id != :id';
        }

        $stmt = $pdo->prepare($sql);

        $parametros = ['email' => $email];

        if ($id) {
            $parametros['id'] = $id;
        }

        $stmt->execute($parametros);

        if ($stmt->fetch()) {

            $mensagem = 'Este e-mail já está cadastrado.';
            $erro = true;

        } else {

            if ($id) {

                // UPDATE: editar usuário
                if ($senha !== '') {

                    // Atualizar também a senha
                    $senha_hash = password_hash(
                        $senha,
                        PASSWORD_DEFAULT
                    );

                    $sql = 'UPDATE usuarios SET
                            nome = :nome,
                            email = :email,
                            senha = :senha,
                            tipo = :tipo,
                            status = :status
                            WHERE id = :id';

                    $parametros = [
                        'nome' => $nome,
                        'email' => $email,
                        'senha' => $senha_hash,
                        'tipo' => $tipo,
                        'status' => $status,
                        'id' => $id
                    ];

                } else {

                    // Atualizar sem alterar a senha
                    $sql = 'UPDATE usuarios SET
                            nome = :nome,
                            email = :email,
                            tipo = :tipo,
                            status = :status
                            WHERE id = :id';

                    $parametros = [
                        'nome' => $nome,
                        'email' => $email,
                        'tipo' => $tipo,
                        'status' => $status,
                        'id' => $id
                    ];
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($parametros);

                $mensagem = 'Usuário atualizado com sucesso!';

            } else {

                // CREATE: cadastrar usuário
                $senha_hash = password_hash(
                    $senha,
                    PASSWORD_DEFAULT
                );

                $sql = 'INSERT INTO usuarios
                        (nome, email, senha, tipo, status)
                        VALUES
                        (:nome, :email, :senha, :tipo, :status)';

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    'nome' => $nome,
                    'email' => $email,
                    'senha' => $senha_hash,
                    'tipo' => $tipo,
                    'status' => $status
                ]);

                $mensagem = 'Usuário cadastrado com sucesso!';

                $usuario = [
                    'nome' => '',
                    'email' => '',
                    'tipo' => 'aluno',
                    'status' => 'ativo'
                ];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $id ? 'Editar' : 'Novo' ?> usuário</title>

    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">

    <div class="card">

        <h1><?= $id ? 'Editar usuário' : 'Novo usuário' ?></h1>

        <?php if ($mensagem !== ''): ?>
            <div class="alert <?= $erro ? 'error' : '' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <label>Nome</label>
            <input
                type="text"
                name="nome"
                value="<?= htmlspecialchars($usuario['nome']) ?>"
                required
            >

            <label>E-mail</label>
            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($usuario['email']) ?>"
                required
            >

            <label>Senha <?= $id ? '(deixe vazia para manter a atual)' : '' ?></label>
            <input
                type="password"
                name="senha"
                <?= $id ? '' : 'required' ?>
            >

            <label>Tipo</label>
            <select name="tipo" required>

                <option value="aluno"
                    <?= $usuario['tipo'] === 'aluno' ? 'selected' : '' ?>>
                    Aluno
                </option>

                <option value="professor"
                    <?= $usuario['tipo'] === 'professor' ? 'selected' : '' ?>>
                    Professor
                </option>

                <option value="admin"
                    <?= $usuario['tipo'] === 'admin' ? 'selected' : '' ?>>
                    Administrador
                </option>

            </select>

            <label>Status</label>
            <select name="status" required>

                <option value="ativo"
                    <?= $usuario['status'] === 'ativo' ? 'selected' : '' ?>>
                    Ativo
                </option>

                <option value="inativo"
                    <?= $usuario['status'] === 'inativo' ? 'selected' : '' ?>>
                    Inativo
                </option>

            </select>

            <button type="submit">Salvar</button>

        </form>

        <p>
            <a href="index.php">Voltar para usuários</a>
        </p>

    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

</body>
</html>
