


<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

// Somente administrador e professor podem acessar
exigirPerfil(['admin', 'professor']);

$perfil = $_SESSION['tipo'];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Impedir ID inválido
if (isset($_GET['id']) && (!$id || $id <= 0)) {
    header('Location: ../../acesso_negado.php');
    exit;
}

$usuario = [
    'nome' => '',
    'email' => '',
    'tipo' => 'aluno',
    'status' => 'ativo'
];

$mensagem = '';

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

    // Professor só pode editar alunos
    if ($perfil === 'professor' && $resultado['tipo'] !== 'aluno') {
        header('Location: ../../acesso_negado.php');
        exit;
    }

    $usuario = $resultado;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Professor só pode trabalhar com alunos
    $tipo = ($perfil === 'professor')
        ? 'aluno'
        : ($_POST['tipo'] ?? 'aluno');

    $status = $_POST['status'] ?? 'ativo';
    $senha = $_POST['senha'] ?? '';

    // Validar campos obrigatórios
    if (
        empty($nome) ||
        empty($email) ||
        (!$id && empty($senha))
    ) {

        $mensagem = 'Preencha todos os campos obrigatórios.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensagem = 'Digite um e-mail válido.';

    } elseif (!in_array($tipo, ['aluno', 'professor', 'admin'], true)) {

        $mensagem = 'Tipo de usuário inválido.';

    } elseif (!in_array($status, ['ativo', 'bloqueado'], true)) {

        $mensagem = 'Status inválido.';

    } else {

        // Verificar se o e-mail já existe
        $sql = 'SELECT id FROM usuarios WHERE email = :email';

        if ($id) {
            $sql .= ' AND id != :id';
        }

        $stmt = $pdo->prepare($sql);

        $dados = ['email' => $email];

        if ($id) {
            $dados['id'] = $id;
        }

        $stmt->execute($dados);

        if ($stmt->fetch()) {

            $mensagem = 'Este e-mail já está cadastrado.';

        } else {

            if ($id) {

                // UPDATE: editar usuário existente
                if (!empty($senha)) {

                    $senhaHash = password_hash(
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

                    $dados = [
                        'nome' => $nome,
                        'email' => $email,
                        'senha' => $senhaHash,
                        'tipo' => $tipo,
                        'status' => $status,
                        'id' => $id
                    ];

                } else {

                    $sql = 'UPDATE usuarios SET
                            nome = :nome,
                            email = :email,
                            tipo = :tipo,
                            status = :status
                            WHERE id = :id';

                    $dados = [
                        'nome' => $nome,
                        'email' => $email,
                        'tipo' => $tipo,
                        'status' => $status,
                        'id' => $id
                    ];
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($dados);

                $mensagem = 'Usuário atualizado com sucesso!';

                $usuario['nome'] = $nome;
                $usuario['email'] = $email;
                $usuario['tipo'] = $tipo;
                $usuario['status'] = $status;

            } else {

                // CREATE: cadastrar novo usuário
                $senhaHash = password_hash(
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
                    'senha' => $senhaHash,
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

    // Manter dados preenchidos quando houver erro
    if (
        $mensagem !== '' &&
        $mensagem !== 'Usuário cadastrado com sucesso!'
    ) {
        $usuario['nome'] = $nome;
        $usuario['email'] = $email;
        $usuario['tipo'] = $tipo;
        $usuario['status'] = $status;
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

        <?php if (!empty($mensagem)): ?>
            <div class="alert">
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

            <label>
                Senha
                <?= $id ? '(deixe vazia para não alterar)' : '' ?>
            </label>

            <input
                type="password"
                name="senha"
                <?= $id ? '' : 'required' ?>
            >

            <label>Tipo</label>

            <?php if ($perfil === 'admin'): ?>

                <select name="tipo">

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

            <?php else: ?>

                <input type="hidden" name="tipo" value="aluno">
                <input type="text" value="Aluno" disabled>

            <?php endif; ?>

            <label>Status</label>

            <select name="status">

                <option value="ativo"
                    <?= $usuario['status'] === 'ativo' ? 'selected' : '' ?>>
                    Ativo
                </option>

                <option value="bloqueado"
                    <?= $usuario['status'] === 'bloqueado' ? 'selected' : '' ?>>
                    Bloqueado
                </option>

            </select>

            <button type="submit">Salvar</button>

        </form>

    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

</body>
</html>

