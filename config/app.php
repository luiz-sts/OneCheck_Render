<?php
declare(strict_types=1);

/**
 * Configuração geral da aplicação.
 * No Render, defina ONECHECK_BASE_PATH="" (string vazia) para servir da raiz.
 * Em XAMPP local, use ONECHECK_BASE_PATH="/onecheck".
 */
return [
    'base_path' => getenv('ONECHECK_BASE_PATH') !== false
        ? getenv('ONECHECK_BASE_PATH')
        : '',          // padrão raiz — correto para Docker/Render
    'name' => 'OneCheck',
];
