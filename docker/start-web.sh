#!/bin/bash
set -e

# Render injeta a porta via $PORT; internamente o Apache escuta nessa porta
PORT="${PORT:-80}"

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Garante que mod_rewrite está ativo
a2enmod rewrite headers 2>/dev/null || true

exec apache2-foreground
