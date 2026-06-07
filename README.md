# OneCheck

Sistema web em **PHP + Bootstrap** para gestão de imóveis, vistorias com fotos, contratos e problemas.  
Toda comunicação com o banco de dados é feita via **API PHP externa** (`ONECHECK_API_URL`).

## Deploy no Render

### Pré-requisitos
- API PHP rodando (ex: `http://3.145.6.22:8000`)
- Repositório Git com este projeto na raiz

### Passos
1. No Render Dashboard → **New +** → **Blueprint**
2. Aponte para o repositório — o `render.yaml` na raiz é detectado automaticamente
3. O único serviço `onecheck-web` será criado (Docker + PHP 8.2 + Apache)
4. Ajuste a variável de ambiente `ONECHECK_API_URL` para o endereço real da sua API
5. Acesse: `https://onecheck-web.onrender.com/public/login.php`

### Variáveis de ambiente (Render)
| Variável | Valor | Descrição |
|---|---|---|
| `ONECHECK_API_URL` | `http://3.145.6.22:8000` | URL base da API PHP |
| `ONECHECK_BASE_PATH` | `` (vazio) | Sem prefixo no Render |
| `ONECHECK_JWT_SECRET` | (gerado automático) | Chave JWT local |

### Desenvolvimento local (XAMPP)
1. Copie para `C:\xampp\htdocs\onecheck`
2. Defina `ONECHECK_BASE_PATH=/onecheck` no ambiente ou edite `config/app.php`
3. Acesse: `http://localhost/onecheck/public/login.php`

## Estrutura
```
onecheck/
├── api/              # Endpoints REST para app mobile (opcional)
├── assets/           # CSS, JS
├── config/           # api.php, app.php, session.php
├── contratos/        # Telas web de contratos
├── dashboard/
├── docker/           # apache.conf e start-web.sh
├── imoveis/
├── includes/         # bootstrap, auth_api, rbac, funções
├── problemas/
├── public/           # login, logout, mfa-verify (ponto de entrada)
├── usuarios/
├── vistorias/
├── Dockerfile.web
└── render.yaml
```
