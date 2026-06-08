<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/config/api.php';

// Tenta revogar token na API
if (!empty($_SESSION['api_refresh_token'])) {
    try {
        ApiClient::post('/auth/logout', ['refresh_token' => $_SESSION['api_refresh_token']]);
    } catch (Throwable) {
        // ignora — faz logout local mesmo se a API falhar
    }
}

// Apaga cookie de sessão
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Destrói sessão completamente
$_SESSION = [];
session_destroy();

redirect(base_url('public/login.php?logout=1'));
