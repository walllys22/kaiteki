<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Laravel Template Voyager Example

## Instalación
```
composer install
cp .env.example .env
php artisan example:install
sudo chmod -R 775 storage bootstrap/cache
chown -R www-data storage bootstrap/cache
```

## Versión de Laravel
Laravel Framework 10.0.0

## Requisistos
- php >= 8.1
- Extenciones **php-mbstring php-intl php-dom php-gd php-xml php-zip php-curl php-fpm php-mysql**


## Dockerfile
Crear en la Raiz del proyecto los siguientes archivos:
Dockerfile
unit.json

Ejecutar.
```
docker build -t example .
docker run -e DB_DATABASE=example -e DB_HOST=host.docker.internal -p 8000:8000 -t example
```
Ejemplo
```
docker run  -e DB_CONNECTION=mysql -e DB_HOST=host.docker.internal -e DB_PORT=3306 -e DB_DATABASE=example -e DB_USERNAME=root -e DB_CONNECTION_SOLUCION_DIGITAL=mysql -e DB_HOST_SOLUCION_DIGITAL=host.docker.internal -e DB_PORT_SOLUCION_DIGITAL=3306 -e DB_DATABASE_SOLUCION_DIGITAL=soluciondigital -e DB_USERNAME_SOLUCION_DIGITAL=root -p 8000:8000 -t example
```
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Laravel Template Voyager Example

## Instalación
```
composer install
cp .env.example .env
php artisan example:install
sudo chmod -R 775 storage bootstrap/cache
chown -R www-data storage bootstrap/cache
```

## Versión de Laravel
Laravel Framework 10.0.0

## Requisistos
- php >= 8.1
- Extenciones **php-mbstring php-intl php-dom php-gd php-xml php-zip php-curl php-fpm php-mysql**


## Dockerfile
Crear en la Raiz del proyecto los siguientes archivos:
Dockerfile
unit.json

Ejecutar.
```
docker build -t example .
docker run -e DB_DATABASE=example -e DB_HOST=host.docker.internal -p 8000:8000 -t example
```
Ejemplo
```
docker run  -e DB_CONNECTION=mysql -e DB_HOST=host.docker.internal -e DB_PORT=3306 -e DB_DATABASE=example -e DB_USERNAME=root -e DB_CONNECTION_SOLUCION_DIGITAL=mysql -e DB_HOST_SOLUCION_DIGITAL=host.docker.internal -e DB_PORT_SOLUCION_DIGITAL=3306 -e DB_DATABASE_SOLUCION_DIGITAL=soluciondigital -e DB_USERNAME_SOLUCION_DIGITAL=root -p 8000:8000 -t example
```


## Configuración de Nginx (nginx.conf o tu sitio)
```sh
client_max_body_size 300M;
client_body_timeout 300s;
client_header_timeout 300s;
keepalive_timeout 300s;
send_timeout 300s;
fastcgi_read_timeout 300s;
fastcgi_send_timeout 300s;
fastcgi_connect_timeout 300s;
proxy_read_timeout 300s;
```
## Configuración de PHP (php.ini)
```sh
upload_max_filesize = 300M
post_max_size = 300M
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
```
## Configuración específica para tu sitio (en server block)
```sh
server {
    listen 80;
    server_name tu-dominio.com;
    
    # Configuración para uploads grandes
    client_max_body_size 300M;
    client_body_timeout 300s;
    client_header_timeout 300s;
    
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_read_timeout 300s;
        fastcgi_send_timeout 300s;
        fastcgi_connect_timeout 300s;
    }
    
    # O si usas PHP-FPM en puerto
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_read_timeout 300s;
    }
}
```

## PERMISO DE EJECUCIÓN EN PRODUCCIÓN
```sh
#!/bin/bash
# =============================================================================
# Hardening de permisos para Laravel + Voyager en producción
# Stack: Ubuntu 22.04 / 24.04 — Nginx — DigitalOcean
#
# Uso:
#   sudo bash laravel-voyager-permisos.sh [PROJECT_PATH] [DEPLOY_USER] [WEB_USER] [NGINX_CONF sites-available/demo_ventas]
#
# Ejemplo con config Nginx existente:
#   sudo bash laravel-voyager-permisos.sh /var/www/demo/ventas nachodevos www-data demo_ventas
#
# Ejemplo sin especificar config Nginx (solo genera snippets):
#   sudo bash laravel-voyager-permisos.sh /var/www/demo/ventas nachodevos www-data
#
# ⚠ SERVIDOR MULTIPROYECTO:
#   open_basedir NO se configura en php.ini global porque bloquearía
#   todos los otros proyectos. Se aplica por proyecto via fastcgi_param en Nginx.
# =============================================================================

set -euo pipefail

# ─────────────────────────────────────────────
# COLORES
# ─────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

ok()      { echo -e "${GREEN}  ✓${NC} $1"; }
warn()    { echo -e "${YELLOW}  ⚠${NC}  $1"; }
err()     { echo -e "${RED}  ✗ ERROR:${NC} $1"; exit 1; }
info()    { echo -e "${BLUE}  →${NC} $1"; }
section() { echo -e "\n${BOLD}${CYAN}══════════════════════════════════════${NC}"; \
            echo -e "${BOLD}${CYAN}  $1${NC}"; \
            echo -e "${BOLD}${CYAN}══════════════════════════════════════${NC}"; }
alert()   { echo -e "${RED}${BOLD}  !! ALERTA:${NC} $1"; }

# ─────────────────────────────────────────────
# PARÁMETROS
# ─────────────────────────────────────────────
PROJECT_PATH="${1:-/var/www/production/example}"
DEPLOY_USER="${2:-deployer}"
WEB_USER="${3:-www-data}"
NGINX_CONF="${4:-}"   # Nombre del archivo en sites-available (opcional)

# ─────────────────────────────────────────────
# BANNER
# ─────────────────────────────────────────────
echo ""
echo -e "${BOLD}${CYAN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}${CYAN}║   Laravel + Voyager — Hardening Producción   ║${NC}"
echo -e "${BOLD}${CYAN}║   Stack: Ubuntu · Nginx · DigitalOcean       ║${NC}"
echo -e "${BOLD}${CYAN}╚══════════════════════════════════════════════╝${NC}"
echo ""

# ═══════════════════════════════════════════════════════════
# 0. VALIDACIONES PREVIAS
# ═══════════════════════════════════════════════════════════
section "0. Validaciones previas"

[ "$(id -u)" -eq 0 ]      || err "Ejecutá el script como root o con sudo."
[ -z "$PROJECT_PATH" ]    && err "PROJECT_PATH está vacío."
[ "$PROJECT_PATH" = "/" ] && err "PROJECT_PATH no puede ser /."
[ -d "$PROJECT_PATH" ]    || err "El directorio '$PROJECT_PATH' no existe."

id "$DEPLOY_USER" &>/dev/null || err "El usuario '$DEPLOY_USER' no existe en el sistema."
id "$WEB_USER"    &>/dev/null || err "El usuario '$WEB_USER' no existe en el sistema."

[ -f "$PROJECT_PATH/artisan" ] || err "No parece un proyecto Laravel (artisan no encontrado)."

# Verificar Voyager
if [ ! -d "$PROJECT_PATH/vendor/tcg/voyager" ]; then
    warn "Voyager no encontrado en vendor/tcg/voyager."
    warn "Asegurate de haber ejecutado: composer install"
    read -rp "  ¿Continuar de todos modos? [s/N]: " CONT
    [[ "$CONT" =~ ^[sS]$ ]] || { echo "  Cancelado."; exit 0; }
fi

# Detectar versión PHP
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>/dev/null || echo "")
[ -z "$PHP_VERSION" ] && warn "PHP no detectado en el PATH del sistema."

ok "Directorio:   $PROJECT_PATH"
ok "Deploy user:  $DEPLOY_USER"
ok "Web user:     $WEB_USER"
ok "PHP version:  ${PHP_VERSION:-no detectada}"
if [ -n "$NGINX_CONF" ]; then
    ok "Nginx conf:   /etc/nginx/sites-available/$NGINX_CONF"
else
    warn "Nginx conf:   no especificada — snippets generados pero no inyectados automáticamente"
fi

# Detectar si DEPLOY_USER y WEB_USER son el mismo (ej: www-data www-data)
SAME_USER=false
if [ "$DEPLOY_USER" = "$WEB_USER" ]; then
    SAME_USER=true
    warn "DEPLOY_USER y WEB_USER son el mismo ($DEPLOY_USER) — modo single-user activado"
    warn "Los permisos de código fuente serán 644/755 en lugar de 640/750"
fi

echo ""
echo -e "${YELLOW}  ⚠  Se modificarán permisos y configuraciones en este servidor.${NC}"
read -rp "  ¿Continuar? [s/N]: " CONFIRM
[[ "$CONFIRM" =~ ^[sS]$ ]] || { echo "  Cancelado."; exit 0; }

# ═══════════════════════════════════════════════════════════
# 1. PROPIETARIO Y PERMISOS BASE
# ═══════════════════════════════════════════════════════════
section "1. Propietario y permisos base"

info "chown -R $DEPLOY_USER:$WEB_USER $PROJECT_PATH ..."
chown -R "$DEPLOY_USER":"$WEB_USER" "$PROJECT_PATH"
ok "Propietario: $DEPLOY_USER | Grupo: $WEB_USER"

find "$PROJECT_PATH" -type f -exec chmod 644 {} \;
find "$PROJECT_PATH" -type d -exec chmod 755 {} \;
ok "Archivos base: 644 | Directorios base: 755"

# ═══════════════════════════════════════════════════════════
# 2. DIRECTORIOS ESCRIBIBLES
# ═══════════════════════════════════════════════════════════
section "2. Directorios escribibles"

WRITABLE_DIRS=(
    "storage"
    "storage/app"
    "storage/app/public"
    "storage/framework"
    "storage/framework/cache"
    "storage/framework/cache/data"
    "storage/framework/sessions"
    "storage/framework/testing"
    "storage/framework/views"
    "storage/logs"
    "bootstrap/cache"
    "public/vendor/tcg/voyager/images"
    "storage/app/public/voyager/media"
    "storage/app/public/voyager/users"
)

for dir in "${WRITABLE_DIRS[@]}"; do
    FULL_PATH="$PROJECT_PATH/$dir"
    if [ ! -d "$FULL_PATH" ]; then
        mkdir -p "$FULL_PATH"
        chown "$DEPLOY_USER":"$WEB_USER" "$FULL_PATH"
    fi
    chmod 775 "$FULL_PATH"
    ok "$dir → 775"
done

# Laravel crea subdirectorios hash dentro de cache/data (ej: ad/65/...)
# Nos aseguramos que los existentes tengan permisos correctos
# y que www-data pueda crear nuevos subdirectorios dentro
info "Asegurando permisos recursivos en storage/framework/cache/data/ ..."
find "$PROJECT_PATH/storage/framework/cache" -type d -exec chown "$DEPLOY_USER":"$WEB_USER" {} \;
find "$PROJECT_PATH/storage/framework/cache" -type d -exec chmod 775 {} \;
ok "storage/framework/cache/** (dirs) → 775"

# ─────────────────────────────────────────────────────────
# FIX: Archivos existentes dentro de storage/ y bootstrap/cache
# El paso anterior (sección 1) pisa los archivos con 644 (sin escritura
# para el grupo). Esto rompe laravel.log, requests.log, etc. cuando
# www-data intenta escribir. Corregimos a 664 para todos los archivos
# dentro de los directorios escribibles.
# ─────────────────────────────────────────────────────────
info "Corrigiendo permisos de archivos dentro de storage/ ..."
find "$PROJECT_PATH/storage" -type f -exec chown "$DEPLOY_USER":"$WEB_USER" {} \;
find "$PROJECT_PATH/storage" -type f -exec chmod 664 {} \;
ok "storage/**/* → 664 (grupo puede escribir)"

info "Corrigiendo permisos de archivos dentro de bootstrap/cache/ ..."
find "$PROJECT_PATH/bootstrap/cache" -type f -exec chown "$DEPLOY_USER":"$WEB_USER" {} \; 2>/dev/null || true
find "$PROJECT_PATH/bootstrap/cache" -type f -exec chmod 664 {} \; 2>/dev/null || true
ok "bootstrap/cache/**/* → 664 (grupo puede escribir)"

# Asegurarse de que laravel.log y logs personalizados tengan los permisos correctos
for logfile in "$PROJECT_PATH/storage/logs/"*.log; do
    [ -f "$logfile" ] || continue
    chown "$DEPLOY_USER":"$WEB_USER" "$logfile"
    chmod 664 "$logfile"
    ok "$(basename "$logfile") → 664"
done

# Symlink storage → public/storage
if [ ! -L "$PROJECT_PATH/public/storage" ]; then
    info "Creando symlink public/storage → storage/app/public ..."
    sudo -u "$WEB_USER" php "$PROJECT_PATH/artisan" storage:link > /dev/null 2>&1 \
        && ok "Symlink creado" \
        || warn "Falló storage:link — ejecutalo manualmente: php artisan storage:link"
else
    ok "Symlink public/storage ya existe"
fi

# ═══════════════════════════════════════════════════════════
# 3. ARCHIVOS EJECUTABLES Y SENSIBLES
# ═══════════════════════════════════════════════════════════
section "3. Ejecutables y archivos sensibles"

chmod 754 "$PROJECT_PATH/artisan"
ok "artisan → 754"

for f in deploy.sh start.sh entrypoint.sh; do
    [ -f "$PROJECT_PATH/$f" ] && chmod 754 "$PROJECT_PATH/$f" && ok "$f → 754"
done

[ -f "$PROJECT_PATH/.env" ]         && chmod 640 "$PROJECT_PATH/.env"         && ok ".env → 640"
[ -f "$PROJECT_PATH/.env.example" ] && chmod 644 "$PROJECT_PATH/.env.example" && ok ".env.example → 644"
ls "$PROJECT_PATH/storage/oauth-"*.key &>/dev/null 2>&1 \
    && chmod 600 "$PROJECT_PATH"/storage/oauth-*.key && ok "oauth keys → 600"

# Directorios de código fuente — más restrictivos
# En modo single-user (www-data:www-data) usamos 644/755 para que el proceso
# pueda leer sus propios archivos sin problemas.
# En modo dual-user (nachodevos:www-data) usamos 640/750 para mayor seguridad.
PROTECTED_DIRS=("config" "database" "routes" "app" "resources")
for dir in "${PROTECTED_DIRS[@]}"; do
    FULL_PATH="$PROJECT_PATH/$dir"
    if [ -d "$FULL_PATH" ]; then
        if [ "$SAME_USER" = true ]; then
            find "$FULL_PATH" -type f -exec chmod 644 {} \;
            find "$FULL_PATH" -type d -exec chmod 755 {} \;
            ok "$dir → archivos 644 | dirs 755 (single-user)"
        else
            find "$FULL_PATH" -type f -exec chmod 640 {} \;
            find "$FULL_PATH" -type d -exec chmod 750 {} \;
            ok "$dir → archivos 640 | dirs 750 (dual-user)"
        fi
    fi
done

# ─────────────────────────────────────────────────────────
# FIX CRÍTICO: public/ debe ser 100% legible por Nginx (www-data)
# Esto incluye CSS, JS, imágenes, fuentes, assets con nombres hash, etc.
# Se aplica AL FINAL para que ningún paso anterior lo pise.
# ─────────────────────────────────────────────────────────
section "3b. Permisos de public/ (assets estáticos)"

info "Aplicando permisos correctos a todo public/ ..."
chown -R "$DEPLOY_USER":"$WEB_USER" "$PROJECT_PATH/public"
find "$PROJECT_PATH/public" -type f -exec chmod 644 {} \;
find "$PROJECT_PATH/public" -type d -exec chmod 755 {} \;
ok "public/ → archivos 644 | dirs 755 | grupo $WEB_USER"
ok "CSS, JS, imágenes, fuentes y assets hash → accesibles por Nginx"

# ═══════════════════════════════════════════════════════════
# 4. SNIPPETS DE NGINX — ANTI-RCE
# ═══════════════════════════════════════════════════════════
section "4. Snippets de Nginx — Anti-RCE"

NGINX_SNIPPET_DIR="/etc/nginx/snippets"
mkdir -p "$NGINX_SNIPPET_DIR"

# Snippet 1: bloqueo de PHP en uploads (quirúrgico, no interfiere con assets)
cat > "$NGINX_SNIPPET_DIR/voyager-no-php-uploads.conf" <<'NGINXEOF'
# Bloquear ejecución de PHP SOLO en storage/ (anti-RCE / anti-webshell)
# No toca public/ ni sus subdirectorios para no romper CSS/JS/imágenes
location ~* ^/storage/.+\.(php[0-9]?|phtml|phar|php3|php4|php5|php7|phps)$ {
    deny all;
    return 403;
}
location ~* ^/storage/.+\.(cgi|pl|py|asp|aspx|jsp|sh|bash|exe|dll)$ {
    deny all;
    return 403;
}
# Bloquear archivos ocultos (.git, .env accedido via URL, etc.)
location ~ /\.(?!well-known) {
    deny all;
    return 403;
}
NGINXEOF
ok "Snippet: voyager-no-php-uploads.conf"

# Snippet 2: bloqueo de archivos sensibles en la raíz
cat > "$NGINX_SNIPPET_DIR/voyager-block-sensitive.conf" <<'NGINXEOF'
# Bloquear acceso a archivos de configuración y desarrollo en la raíz
location ~* ^/(\.env|composer\.(json|lock)|package(-lock)?\.json|webpack\.mix\.js|Makefile|Dockerfile|docker-compose[^/]*|phpunit\.xml|\.phpunit[^/]*|\.travis\.yml|\.editorconfig|artisan)$ {
    deny all;
    return 403;
}
NGINXEOF
ok "Snippet: voyager-block-sensitive.conf"

# Snippet 3: headers de seguridad HTTP
cat > "$NGINX_SNIPPET_DIR/voyager-security-headers.conf" <<'NGINXEOF'
# Headers de seguridad HTTP
server_tokens off;
add_header X-Content-Type-Options    "nosniff"                        always;
add_header X-XSS-Protection          "1; mode=block"                  always;
add_header X-Frame-Options           "SAMEORIGIN"                     always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
add_header Referrer-Policy           "strict-origin-when-cross-origin" always;
add_header Permissions-Policy        "camera=(), microphone=(), geolocation=(), payment=()" always;
NGINXEOF
ok "Snippet: voyager-security-headers.conf"

# Inyectar snippets en config Nginx existente si se especificó
if [ -n "$NGINX_CONF" ]; then
    NGINX_CONF_PATH="/etc/nginx/sites-available/$NGINX_CONF"
    if [ -f "$NGINX_CONF_PATH" ]; then
        cp "$NGINX_CONF_PATH" "${NGINX_CONF_PATH}.bak.$(date +%Y%m%d_%H%M%S)"
        ok "Backup: ${NGINX_CONF_PATH}.bak.*"

        if grep -q "voyager-no-php-uploads" "$NGINX_CONF_PATH" && \
           grep -q "node_modules" "$NGINX_CONF_PATH"; then
            # Snippet viejo tenía node_modules/vendor bloqueados — reemplazar
            warn "Snippet desactualizado detectado — actualizando ..."
            # Eliminar includes viejos para reinyectar
            sed -i '/include snippets\/voyager-security-headers.conf/d' "$NGINX_CONF_PATH"
            sed -i '/include snippets\/voyager-no-php-uploads.conf/d' "$NGINX_CONF_PATH"
            sed -i '/include snippets\/voyager-block-sensitive.conf/d' "$NGINX_CONF_PATH"
        fi

        # ── Inyectar / actualizar directivas de tamaño y timeouts ──
        TIMEOUT_BLOCK="    client_max_body_size 300M;\n    client_body_timeout 300s;\n    client_header_timeout 300s;\n    fastcgi_read_timeout 300s;"

        # Si ya existen, actualizarlos; si no, inyectarlos
        if grep -q "client_max_body_size" "$NGINX_CONF_PATH"; then
            sed -i "s|client_max_body_size.*|client_max_body_size 300M;|"    "$NGINX_CONF_PATH"
            sed -i "s|client_body_timeout.*|client_body_timeout 300s;|"      "$NGINX_CONF_PATH"
            sed -i "s|client_header_timeout.*|client_header_timeout 300s;|"  "$NGINX_CONF_PATH"
            sed -i "s|fastcgi_read_timeout.*|fastcgi_read_timeout 300s;|"    "$NGINX_CONF_PATH"
            ok "Timeouts y client_max_body_size actualizados"
        else
            # Inyectar antes del primer add_header o antes del include de snippets
            if grep -q "add_header X-Frame-Options" "$NGINX_CONF_PATH"; then
                sed -i "/add_header X-Frame-Options/i\\${TIMEOUT_BLOCK}" "$NGINX_CONF_PATH"
                ok "Timeouts inyectados antes de add_header X-Frame-Options"
            elif grep -q "add_header X-Content-Type-Options" "$NGINX_CONF_PATH"; then
                sed -i "/add_header X-Content-Type-Options/i\\${TIMEOUT_BLOCK}" "$NGINX_CONF_PATH"
                ok "Timeouts inyectados antes de add_header X-Content-Type-Options"
            elif grep -q "charset utf-8;" "$NGINX_CONF_PATH"; then
                sed -i "/charset utf-8;/a\\${TIMEOUT_BLOCK}" "$NGINX_CONF_PATH"
                ok "Timeouts inyectados después de charset utf-8"
            elif grep -q "index index.php;" "$NGINX_CONF_PATH"; then
                sed -i "/index index\.php;/a\\${TIMEOUT_BLOCK}" "$NGINX_CONF_PATH"
                ok "Timeouts inyectados después de index index.php"
            else
                warn "No se pudo inyectar timeouts automáticamente — agregá manualmente dentro de server{}:"
                warn "  client_max_body_size 300M;"
                warn "  client_body_timeout 300s;"
                warn "  client_header_timeout 300s;"
                warn "  fastcgi_read_timeout 300s;"
            fi
        fi

        if grep -q "voyager-no-php-uploads" "$NGINX_CONF_PATH"; then
            ok "Snippets ya presentes en $NGINX_CONF — sin cambios"
        else
            # Inyectar después de "charset utf-8;" o "index index.php;"
            if grep -q "charset utf-8;" "$NGINX_CONF_PATH"; then
                sed -i '/charset utf-8;/a\    include snippets\/voyager-security-headers.conf;\n    include snippets\/voyager-no-php-uploads.conf;\n    include snippets\/voyager-block-sensitive.conf;' "$NGINX_CONF_PATH"
            elif grep -q "index index.php;" "$NGINX_CONF_PATH"; then
                sed -i '/index index\.php;/a\    include snippets\/voyager-security-headers.conf;\n    include snippets\/voyager-no-php-uploads.conf;\n    include snippets\/voyager-block-sensitive.conf;' "$NGINX_CONF_PATH"
            else
                warn "No se pudo inyectar automáticamente — agregá manualmente dentro del server{}:"
                warn "  include snippets/voyager-security-headers.conf;"
                warn "  include snippets/voyager-no-php-uploads.conf;"
                warn "  include snippets/voyager-block-sensitive.conf;"
            fi

            if nginx -t > /dev/null 2>&1; then
                ok "Config Nginx válida — snippets inyectados"
                systemctl reload nginx
                ok "Nginx recargado"
            else
                warn "nginx -t falló — restaurando backup"
                BACKUP=$(ls -t "${NGINX_CONF_PATH}.bak."* 2>/dev/null | head -1)
                [ -n "$BACKUP" ] && cp "$BACKUP" "$NGINX_CONF_PATH"
                warn "Backup restaurado — agregá los snippets manualmente"
            fi
        fi
    else
        warn "Config '$NGINX_CONF_PATH' no encontrada — snippets generados pero no inyectados"
    fi
fi

# Recargar Nginx siempre que los snippets fueron reescritos
# (los archivos .conf en /etc/nginx/snippets ya fueron actualizados arriba)
if nginx -t > /dev/null 2>&1; then
    systemctl reload nginx
    ok "Nginx recargado con snippets actualizados"
else
    warn "nginx -t falló — revisá la configuración manualmente: nginx -t"
fi

# ═══════════════════════════════════════════════════════════
# 5. HARDENING DE PHP (sin open_basedir global)
# ═══════════════════════════════════════════════════════════
section "5. Hardening de PHP"

PHP_FPM_INI=""
if [ -n "$PHP_VERSION" ]; then
    for candidate in \
        "/etc/php/$PHP_VERSION/fpm/php.ini" \
        "/etc/php/$PHP_VERSION/apache2/php.ini" \
        "/etc/php/php.ini" "/etc/php.ini"; do
        [ -f "$candidate" ] && PHP_FPM_INI="$candidate" && break
    done
fi

if [ -n "$PHP_FPM_INI" ]; then
    info "PHP.ini: $PHP_FPM_INI"
    cp "$PHP_FPM_INI" "${PHP_FPM_INI}.bak.$(date +%Y%m%d_%H%M%S)"
    ok "Backup: ${PHP_FPM_INI}.bak.*"

    # Funciones peligrosas usadas por webshells
    DANGEROUS_FUNCTIONS="system,exec,passthru,shell_exec,popen,proc_open,pcntl_exec,parse_ini_file,show_source,phpinfo,posix_kill,posix_mkfifo,posix_setpgid,posix_setsid,posix_setuid,posix_setgid,pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_signal,dl"

    if grep -q "^disable_functions" "$PHP_FPM_INI"; then
        sed -i "s|^disable_functions.*|disable_functions = $DANGEROUS_FUNCTIONS|" "$PHP_FPM_INI"
    else
        sed -i "/^\[PHP\]/a disable_functions = $DANGEROUS_FUNCTIONS" "$PHP_FPM_INI"
    fi
    ok "disable_functions configurado"

    # Solo settings que NO afectan rutas del filesystem
    # (open_basedir se omite intencionalmente — servidor multiproyecto)
    declare -A PHP_SETTINGS=(
        ["expose_php"]="Off"
        ["display_errors"]="Off"
        ["log_errors"]="On"
        ["allow_url_fopen"]="Off"
        ["allow_url_include"]="Off"
        ["session.cookie_httponly"]="1"
        ["session.cookie_secure"]="1"
        ["session.use_strict_mode"]="1"
        ["session.cookie_samesite"]="Lax"
        ["upload_max_filesize"]="50M"
        ["post_max_size"]="55M"
        ["max_file_uploads"]="20"
        ["max_execution_time"]="120"
        ["max_input_time"]="120"
        ["memory_limit"]="256M"
    )

    for key in "${!PHP_SETTINGS[@]}"; do
        value="${PHP_SETTINGS[$key]}"
        if grep -q "^$key" "$PHP_FPM_INI"; then
            sed -i "s|^$key.*|$key = $value|" "$PHP_FPM_INI"
        elif grep -q "^;$key" "$PHP_FPM_INI"; then
            sed -i "s|^;$key.*|$key = $value|" "$PHP_FPM_INI"
        else
            echo "$key = $value" >> "$PHP_FPM_INI"
        fi
        ok "$key = $value"
    done

    if systemctl is-active --quiet "php${PHP_VERSION}-fpm" 2>/dev/null; then
        systemctl reload "php${PHP_VERSION}-fpm"
        ok "php${PHP_VERSION}-fpm recargado"
    else
        warn "Recargá PHP-FPM manualmente: systemctl reload php${PHP_VERSION}-fpm"
    fi
else
    warn "php.ini no encontrado — configurá manualmente disable_functions y allow_url_include=Off"
fi

# ═══════════════════════════════════════════════════════════
# 6. CACHÉ DE LARAVEL
# ═══════════════════════════════════════════════════════════
section "6. Limpieza y optimización de caché"

ARTISAN_CMD="sudo -u $WEB_USER php $PROJECT_PATH/artisan"

for cmd in cache:clear config:clear route:clear view:clear; do
    $ARTISAN_CMD "$cmd" > /dev/null 2>&1 && ok "$cmd" || warn "$cmd falló"
done

for cmd in config:cache route:cache view:cache; do
    $ARTISAN_CMD "$cmd" > /dev/null 2>&1 && ok "$cmd" || warn "$cmd falló (revisá que .env esté completo)"
done

# ─────────────────────────────────────────────────────────
# FIX POST-CACHÉ: Artisan genera nuevos archivos en storage/framework/views
# y bootstrap/cache con el usuario www-data. Nos aseguramos que queden
# con el grupo correcto y permisos 664 para que DEPLOY_USER también pueda
# leer/escribir (necesario para redeploys sin sudo).
# ─────────────────────────────────────────────────────────
info "Re-ajustando permisos post-caché de artisan ..."
find "$PROJECT_PATH/storage" -type d -exec chown "$DEPLOY_USER":"$WEB_USER" {} \;
find "$PROJECT_PATH/storage" -type d -exec chmod 775 {} \;
find "$PROJECT_PATH/storage" -type f -exec chown "$DEPLOY_USER":"$WEB_USER" {} \;
find "$PROJECT_PATH/storage" -type f -exec chmod 664 {} \;
find "$PROJECT_PATH/bootstrap/cache" -type d -exec chown "$DEPLOY_USER":"$WEB_USER" {} \; 2>/dev/null || true
find "$PROJECT_PATH/bootstrap/cache" -type d -exec chmod 775 {} \; 2>/dev/null || true
find "$PROJECT_PATH/bootstrap/cache" -type f -exec chown "$DEPLOY_USER":"$WEB_USER" {} \; 2>/dev/null || true
find "$PROJECT_PATH/bootstrap/cache" -type f -exec chmod 664 {} \; 2>/dev/null || true
ok "Permisos post-caché normalizados (dirs 775, archivos 664)"

# ═══════════════════════════════════════════════════════════
# 7. ESCANEO DE WEBSHELLS
# ═══════════════════════════════════════════════════════════
section "7. Escaneo de webshells"

info "Buscando PHP en storage/ y public/ (excluyendo vistas Blade compiladas)..."
SUSPICIOUS=$(find "$PROJECT_PATH/storage" "$PROJECT_PATH/public" \
    -name "*.php" \
    ! -name "index.php" \
    ! -path "*/storage/framework/views/*" \
    2>/dev/null || true)

if [ -z "$SUSPICIOUS" ]; then
    ok "Sin PHP sospechoso"
else
    alert "Archivos PHP encontrados — revisalos manualmente:"
    echo "$SUSPICIOUS" | while read -r f; do echo -e "     ${RED}$f${NC}"; done
fi

info "Buscando patrones de webshells en código..."
PATTERNS=("eval(base64_decode" "eval(\$_" "system(\$_" "exec(\$_" "passthru(\$_"
          "shell_exec(\$_" "assert(\$_" "preg_replace.*\/e" "FilesMan" "c99shell" "r57shell")
FOUND=0
for pattern in "${PATTERNS[@]}"; do
    MATCHES=$(grep -rl "$pattern" "$PROJECT_PATH" --include="*.php" \
              --exclude-dir=".git" --exclude-dir="vendor" 2>/dev/null || true)
    if [ -n "$MATCHES" ]; then
        alert "Patrón '$pattern':"
        echo "$MATCHES" | while read -r f; do echo -e "     ${RED}$f${NC}"; done
        FOUND=1
    fi
done
[ "$FOUND" -eq 0 ] && ok "Sin patrones de webshells detectados"

# ═══════════════════════════════════════════════════════════
# 8. VERIFICACIÓN FINAL
# ═══════════════════════════════════════════════════════════
section "8. Verificación final"

echo ""
info "World-writable fuera de storage/bootstrap/cache:"
WW=$(find "$PROJECT_PATH" -type f -perm /o=w \
     ! -path "*/storage/*" ! -path "*/bootstrap/cache/*" 2>/dev/null || true)
[ -z "$WW" ] && ok "Ninguno" || { alert "Encontrados:"; echo "$WW"; }

echo ""
info "Ejecutables en storage/:"
ES=$(find "$PROJECT_PATH/storage" -type f -perm /a=x 2>/dev/null || true)
[ -z "$ES" ] && ok "Ninguno" || { alert "Encontrados:"; echo "$ES"; }

echo ""
info "Permisos de .env:"
ls -la "$PROJECT_PATH/.env" 2>/dev/null || warn ".env no encontrado"

echo ""
info "Permisos de public/ (muestra primeros niveles):"
ls -la "$PROJECT_PATH/public/" 2>/dev/null || warn "public/ no encontrado"

# ═══════════════════════════════════════════════════════════
# RESUMEN FINAL
# ═══════════════════════════════════════════════════════════
echo ""
echo -e "${BOLD}${GREEN}╔════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}${GREEN}║        HARDENING COMPLETADO ✓                  ║${NC}"
echo -e "${BOLD}${GREEN}╚════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${BOLD}Aplicado:${NC}"
echo -e "  ✓ Permisos (deploy_user dueño, www-data grupo)"
echo -e "  ✓ storage/ y uploads de Voyager con 775"
echo -e "  ✓ Archivos dentro de storage/ con 664 (www-data puede escribir logs)"
echo -e "  ✓ bootstrap/cache/ archivos con 664"
if [ "$SAME_USER" = true ]; then
    echo -e "  ✓ Modo single-user ($DEPLOY_USER) — código fuente 644/755"
else
    echo -e "  ✓ Modo dual-user ($DEPLOY_USER + $WEB_USER) — código fuente 640/750"
fi
echo -e "  ✓ PHP bloqueado en uploads via Nginx (anti-RCE)"
echo -e "  ✓ disable_functions (anti-webshell)"
echo -e "  ✓ allow_url_include=Off (anti-RFI)"
echo -e "  ✓ client_max_body_size 300M + timeouts 300s en Nginx"
echo -e "  ✓ Escaneo de webshells"
echo -e "  ✓ open_basedir omitido del php.ini global (servidor multiproyecto)"
echo ""
echo -e "  ${BOLD}${YELLOW}open_basedir por proyecto (recomendado):${NC}"
echo -e "  Dentro del bloque 'location ~ \\.php\$' de cada config Nginx, agregá:"
echo -e "  ${BOLD}fastcgi_param PHP_VALUE \"open_basedir=$PROJECT_PATH:/tmp\";${NC}"
echo ""
echo -e "  ${BOLD}${YELLOW}Recomendaciones adicionales:${NC}"
echo -e "  ✦ UFW: ufw allow 22,80,443/tcp && ufw enable"
echo -e "  ✦ Habilitar fail2ban: apt install fail2ban"
echo -e "  ✦ Backups automáticos en DigitalOcean Snapshots"
echo -e "  ✦ Monitorear logs: tail -f /var/log/nginx/error.log"
echo ""
```#   k a i t e k i  
 #   k a i t e k i  
 #   k a i t e k i  
 