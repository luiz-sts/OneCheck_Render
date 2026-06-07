<?php
declare(strict_types=1);

// Ponto de entrada raiz — redireciona direto para o login
// ONECHECK_BASE_PATH vazio no Render: URL será /public/login.php
$base = rtrim((string)(getenv('ONECHECK_BASE_PATH') ?: ''), '/');
header('Location: ' . $base . '/public/login.php');
exit;
