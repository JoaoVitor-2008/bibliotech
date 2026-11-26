

<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$usuario = [
    'nome' => '',
    'email' => '',
    'tipo' => 'aluno',
    'status' => 'ativo'
];

$mensagem = '';
$erro = false;

// Processar cadastro
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

    // Validar campos obrigatórios
    if (
        $nome === '' ||
        $email === '' ||
        $senha === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $mensagem = 'Preencha todos os campos corretamente.';
        $erro = true;

    } elseif (
        !in_array($tipo, ['admin', 'professor', 'aluno'], true) ||
        !in_array($status, ['ativo', 'inativo'], true)
    ) {

        $mensagem = 'Tipo ou status inválido.';
        $erro = true;

    } else {

        // Verificar e-mail duplicado
        $stmt = $pdo->prepare(
            'SELECT id FROM usuarios WHERE email = :email'
        );

        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {

            $mensagem = 'Este e-mail já está cadastrado.';
            $erro = true;

        } else {

            // Proteger senha
            $senha_hash = password_hash(
                $senha,
                PASSWORD_DEFAULT
            );

            // Cadastrar usuário
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
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo usuário</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">

    <div class="card">

        <h1>Novo usuário</h1>

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

            <label>Senha</label>
            <input
                type="password"
                name="senha"
                required
            >

            <label>Tipo</label>
            <select name="tipo" required>
                <option value="aluno" <?= $usuario['tipo'] === 'aluno' ? 'selected' : '' ?>>
                    Aluno
                </option>

                <option value="professor" <?= $usuario['tipo'] === 'professor' ? 'selected' : '' ?>>
                    Professor
                </option>

                <option value="admin" <?= $usuario['tipo'] === 'admin' ? 'selected' : '' ?>>
                    Administrador
                </option>
            </select>

            <label>Status</label>
            <select name="status" required>
                <option value="ativo" <?= $usuario['status'] === 'ativo' ? 'selected' : '' ?>>
                    Ativo
                </option>

                <option value="inativo" <?= $usuario['status'] === 'inativo' ? 'selected' : '' ?>>
                    Inativo
                </option>
            </select>

            <button type="submit">Salvar usuário</button>

        </form>

        <p>
            <a href="index.php">Voltar para usuários</a>
        </p>

    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

</body>
</html>
