<?php

declare(strict_types=1);

return [
    'admin' => ['*'],
        'vistoriador' => [
        'dashboard', 'imoveis.view', 'vistorias', 'problemas',
    ],
        'locatario' => [
        'locatario.portal', 'locatario.checklist', 'locatario.problemas',
    ],
];
