<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/config/api.php';
require_once dirname(__DIR__) . '/includes/auth_api.php';
require_once dirname(__DIR__) . '/includes/rbac.php';

api_require_login();

$user = api_user();
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code   = trim($_POST['code']   ?? '');
    $secret = trim($_POST['secret'] ?? '');

    $res = ApiClient::post('/auth/mfa/setup', [
        'secret' => $secret,
        'codigo' => $code,
    ]);

    if (!empty($res['sucesso'])) {
        // Atualizar sessão para refletir MFA ativo
        $_SESSION['user']['mfa_ativo']   = true;
        $_SESSION['user']['mfa_enabled'] = true;
        flash_set('success', 'MFA ativado com sucesso!');
        redirect(base_url('usuarios/perfil.php'));
    }
    $erro = $res['erro'] ?? ($res['message'] ?? 'Código inválido. Confira o app autenticador.');
}

// Busca QR Code e secret da API
$setupRes = ApiClient::get('/auth/mfa/setup');
$dados    = $setupRes['dados'] ?? [];

// A API pode retornar o campo com nomes diferentes
$qrUrl  = $dados['qr_url']       ?? ($dados['qr_code_url'] ?? ($dados['qrcode_url'] ?? ($dados['qr'] ?? '')));
$secret = $dados['secret']        ?? ($dados['totp_secret'] ?? ($dados['mfa_secret'] ?? ''));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar MFA · OneCheck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset_url('css/style.css')) ?>" rel="stylesheet">
    <style>.brand-one{color:#fff}.brand-check{color:#22c55e}</style>
</head>
<body class="app-body">
<div class="login-wrap">
    <div class="login-card" style="max-width:480px">
        <div class="login-logo mb-2">
            <span style="font-size:24px;font-weight:700;letter-spacing:-.5px">
                <span class="brand-one">One</span><span class="brand-check">Check</span>
            </span>
        </div>
        <h5 class="text-center mb-1" style="color:#fff">Configurar autenticação em 2 fatores</h5>
        <p class="text-center mb-4" style="font-size:13px;color:#6b7fa3">
            Escaneie o QR Code no Google Authenticator ou Authy e informe o código gerado.
        </p>

        <?php if ($erro): ?>
        <div class="alert alert-danger py-2 mb-3"><?= e($erro) ?></div>
        <?php endif; ?>

        <?php if ($qrUrl): ?>
        <div class="text-center mb-3">
            <img src="<?= e($qrUrl) ?>" alt="QR Code MFA" width="200" height="200"
                 style="border:1px solid #2a3347;border-radius:8px;background:#fff;padding:8px">
        </div>
        <p class="text-center mb-4" style="font-size:12px;color:#6b7fa3">
            Chave manual: <code style="color:#4f8ef7"><?= e($secret) ?></code>
        </p>
        <?php else: ?>
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Não foi possível carregar o QR Code.
            <?php if (!empty($setupRes['erro'])): ?>
            <br><small><?= e($setupRes['erro']) ?></small>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="secret" value="<?= e($secret) ?>">
            <div class="mb-3">
                <label class="form-label" for="code">
                    <i class="bi bi-key me-1"></i>Código de 6 dígitos
                </label>
                <input type="text" class="form-control text-center" id="code" name="code"
                       inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                       required autofocus placeholder="000000">
            </div>
            <button type="submit" class="btn btn-primary w-100" style="padding:10px">
                <i class="bi bi-shield-check me-1"></i>Ativar MFA
            </button>
        </form>

        <p class="text-center mt-3 mb-0">
            <a href="<?= e(base_url('usuarios/perfil.php')) ?>" style="font-size:12px">
                ← Voltar ao perfil
            </a>
        </p>
    </div>
</div>
</body>
</html>
