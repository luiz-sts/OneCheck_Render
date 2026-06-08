<?php
/** @var string $pageTitle */
$user = api_user();
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
            <span class="d-none d-sm-inline" style="font-size:12px;color:var(--oc-muted)"><?= e($user['nome'] ?? '') ?></span>
            <a class="btn btn-sm btn-outline-light" href="<?= e(base_url('public/logout.php')) ?>">
                <i class="bi bi-box-arrow-right me-1"></i>Sair
            </a>
        </div>
    </div>
</nav>
<main class="container py-4">
