<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/config/api.php';
require_once dirname(__DIR__) . '/includes/auth_api.php';
require_once dirname(__DIR__) . '/includes/rbac.php';

// Só acessível com sessão válida
api_require_login();

$user = api_user();
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code   = trim($_POST['code'] ?? '');
    $secret = trim($_POST['secret'] ?? '');

    $res = ApiClient::post('/auth/mfa/setup', [
        'secret' => $secret,
        'codigo' => $code,
    ]);

    if (!empty($res['sucesso'])) {
        flash_set('success', 'MFA ativado com sucesso.');
        redirect(base_url('usuarios/perfil.php'));
    }
    $erro = $res['erro'] ?? 'Código inválido. Confira o app autenticador.';
}

// Busca QR Code e secret da API
$setupRes = ApiClient::get('/auth/mfa/setup');
$qrUrl    = $setupRes['dados']['qr_url']  ?? '';
$secret   = $setupRes['dados']['secret']  ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar MFA · OneCheck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(asset_url('css/style.css')) ?>" rel="stylesheet">
</head>
<body class="app-body">
<div class="login-wrap">
    <div class="card shadow-lg border-0" style="width:100%;max-width:480px;">
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 mb-1 text-center">Configurar MFA</h1>
            <p class="text-muted text-center small mb-4">
                Escaneie o QR Code no Google Authenticator ou Authy e informe o código gerado.
            </p>
            <?php if ($erro): ?>
            <div class="alert alert-danger py-2"><?= e($erro) ?></div>
            <?php endif; ?>
            <?php if ($qrUrl): ?>
            <div class="text-center mb-3">
                <img src="<?= e($qrUrl) ?>" alt="QR Code MFA" width="200" height="200" class="border rounded">
            </div>
            <p class="small text-muted text-center mb-3">
                Chave manual: <code><?= e($secret) ?></code>
            </p>
            <?php else: ?>
            <div class="alert alert-warning">Não foi possível carregar o QR Code. Verifique a API.</div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="secret" value="<?= e($secret) ?>">
                <div class="mb-3">
                    <label class="form-label" for="code">Código de verificação</label>
                    <input type="text" class="form-control text-center" id="code" name="code"
                           inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Ativar MFA</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
