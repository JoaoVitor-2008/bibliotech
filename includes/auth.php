
<?php

// Iniciar sessão somente se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está autenticado
function exigirLogin(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /login.php');
        exit;
    }
}

// Verificar se o perfil possui autorização
function exigirPerfil(array $perfis): void
{
    exigirLogin();

    // Verificar se o perfil existe na sessão
    if (
        !isset($_SESSION['tipo']) ||
        !in_array($_SESSION['tipo'], $perfis, true)
    ) {
        header('Location: /acesso_negado.php');
        exit;
    }
}
