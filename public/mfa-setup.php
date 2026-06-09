<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/config/api.php';
require_once dirname(__DIR__) . '/includes/auth_api.php';

api_require_login();

$erro    = '';
$sucesso = '';

// Buscar URI do autenticador
$setupRes   = ApiClient::get('/auth/mfa/setup');
$dados      = $setupRes['dados'] ?? [];
$otpauthUri = $dados['otpauth_uri'] ?? '';

// Extrair secret da URI (secret=XXXXX)
$secret = '';
if ($otpauthUri && preg_match('/secret=([A-Z2-7]+)/i', $otpauthUri, $m)) {
    $secret = $m[1];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if (strlen($code) !== 6 || !ctype_digit($code)) {
        $erro = 'Informe um código de 6 dígitos.';
    } else {
        $res = ApiClient::post('/auth/mfa/setup', [
            'codigo' => $code,
            'code'   => $code, // alguns endpoints aceitam 'code'
        ]);

        if (!empty($res['sucesso'])) {
            // Atualizar sessão
            $_SESSION['user']['mfa_ativo']   = true;
            $_SESSION['user']['mfa_enabled'] = true;
            flash_set('success', 'MFA ativado com sucesso! A partir do próximo login será solicitado.');
            redirect(base_url('usuarios/perfil.php'));
        } else {
            $erro = $res['erro'] ?? ($res['message'] ?? 'Código inválido. Tente novamente.');
        }
    }
}

// URL do QR Code via API do Google Charts (gera QR a partir da URI)
$qrCodeUrl = '';
if ($otpauthUri) {
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpauthUri);
}
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
    <style>
        .brand-one  { color: #fff; }
        .brand-check{ color: #22c55e; }
    </style>
</head>
<body class="app-body">
<div class="login-wrap">
    <div class="login-card" style="max-width:500px">

        <div class="login-logo mb-2">
            <span style="font-size:24px;font-weight:700;letter-spacing:-.5px">
                <span class="brand-one">One</span><span class="brand-check">Check</span>
            </span>
        </div>

        <h5 class="text-center mb-1" style="color:#fff">
            <i class="bi bi-shield-lock me-2" style="color:#4f8ef7"></i>Autenticação em 2 fatores
        </h5>
        <p class="text-center mb-4" style="font-size:13px;color:#6b7fa3">
            Configure o MFA para proteger sua conta
        </p>

        <?php if ($erro): ?>
        <div class="alert alert-danger py-2 mb-3"><?= e($erro) ?></div>
        <?php endif; ?>

        <?php if (!$otpauthUri): ?>
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Não foi possível carregar as informações de configuração.
            <?php if (!empty($setupRes['erro'])): ?>
            <br><small><?= e($setupRes['erro']) ?></small>
            <?php endif; ?>
        </div>
        <?php else: ?>

        <!-- Passo 1 -->
        <div class="mb-3 p-3" style="background:#1e2535;border-radius:10px">
            <p class="mb-2" style="font-size:13px;color:#c8d4f0;font-weight:500">
                <span class="badge bg-primary me-2">1</span>
                Abra o <strong>Google Authenticator</strong> ou <strong>Authy</strong>
            </p>
            <p class="mb-0" style="font-size:12px;color:#6b7fa3">
                Toque em <strong>"+"</strong> → <strong>"Escanear QR Code"</strong> e aponte para o código abaixo
            </p>
        </div>

        <!-- QR Code -->
        <div class="text-center mb-3">
            <img src="<?= e($qrCodeUrl) ?>"
                 alt="QR Code para autenticador"
                 width="200" height="200"
                 style="border-radius:10px;background:#fff;padding:8px;border:1px solid #2a3347">
        </div>

        <!-- Chave manual -->
        <?php if ($secret): ?>
        <div class="mb-4 text-center">
            <p style="font-size:11px;color:#6b7fa3;margin-bottom:4px">Ou insira a chave manualmente:</p>
            <code style="background:#1e2535;color:#4f8ef7;padding:6px 12px;border-radius:6px;font-size:13px;letter-spacing:.1em">
                <?= e($secret) ?>
            </code>
        </div>
        <?php endif; ?>

        <!-- Passo 2 -->
        <div class="mb-3 p-3" style="background:#1e2535;border-radius:10px">
            <p class="mb-0" style="font-size:13px;color:#c8d4f0;font-weight:500">
                <span class="badge bg-primary me-2">2</span>
                Informe o código de <strong>6 dígitos</strong> gerado pelo app
            </p>
        </div>

        <form method="post" autocomplete="off">
            <div class="mb-3">
                <input type="text" class="form-control form-control-lg text-center"
                       name="code" id="code"
                       inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                       required autofocus placeholder="000000"
                       style="font-size:24px;letter-spacing:.3em">
            </div>
            <button type="submit" class="btn btn-primary w-100" style="padding:10px;font-size:14px">
                <i class="bi bi-shield-check me-1"></i>Ativar MFA
            </button>
        </form>

        <?php endif; ?>

        <p class="text-center mt-3 mb-0">
            <a href="<?= e(base_url('usuarios/perfil.php')) ?>" style="font-size:12px;color:#6b7fa3">
                ← Voltar ao perfil
            </a>
        </p>
    </div>
</div>
</body>
</html>
