<?php
/** @var string $pageTitle */
$user     = api_user();
$initials = api_initials();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Locatário') ?> · OneCheck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset_url('css/style.css')) ?>" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="app-body">
<nav class="navbar app-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= e(base_url('locatario/index.php')) ?>"
           style="font-weight:700;letter-spacing:-.3px;font-size:16px">
            <span class="brand-one">One</span><span class="brand-check">Check</span>
            <span style="font-weight:400;font-size:13px;color:var(--oc-muted);margin-left:8px">Locatário</span>
        </a>
        <div class="d-flex gap-2 align-items-center">
            <div class="dropdown">
                <button class="btn d-flex align-items-center gap-2 p-0 border-0 bg-transparent"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        style="cursor:pointer">
                    <span class="d-none d-sm-inline" style="font-size:12px;color:#6b7fa3">
                        <?= e($user['nome'] ?? '') ?>
                    </span>
                    <div class="oc-avatar"><?= e($initials) ?></div>
                    <i class="bi bi-chevron-down" style="font-size:10px;color:#6b7fa3"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width:210px">
                    <li>
                        <div class="px-3 py-2" style="border-bottom:0.5px solid #2a3347">
                            <div style="font-size:13px;color:#e2e8f0;font-weight:500"><?= e($user['nome'] ?? '') ?></div>
                            <div style="font-size:11px;color:#6b7fa3"><?= e($user['email'] ?? '') ?></div>
                            <div class="mt-1">
                                <span class="badge bg-success" style="font-size:10px">Locatário</span>
                                <?php if ($user['mfa_ativo'] ?? false): ?>
                                <span class="badge bg-success" style="font-size:10px">
                                    <i class="bi bi-shield-check"></i> MFA
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= e(base_url('usuarios/perfil.php')) ?>">
                            <i class="bi bi-person me-2"></i>Meu perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= e(base_url('usuarios/minha-senha.php')) ?>">
                            <i class="bi bi-key me-2"></i>Alterar senha
                        </a>
                    </li>
                    <?php if (!($user['mfa_ativo'] ?? false)): ?>
                    <li>
                        <a class="dropdown-item" href="<?= e(base_url('public/mfa-setup.php')) ?>">
                            <i class="bi bi-shield-lock me-2" style="color:#4f8ef7"></i>Configurar MFA
                        </a>
                    </li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider" style="border-color:#2a3347"></li>
                    <li>
                        <a class="dropdown-item" href="<?= e(base_url('public/logout.php')) ?>"
                           style="color:#f87171">
                            <i class="bi bi-box-arrow-right me-2"></i>Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<main class="container py-4">
