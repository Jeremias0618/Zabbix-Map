#!/bin/bash
# ================================================
#   Zabbix Map - Instalación básica (Ubuntu 22.04)
#   Autor: Yeremi Tantaraico
#   Email: yeremitantaraico@gmail.com
#   Versión: 1.0
#   Tareas: Update/Upgrade, desactivar IPv6, Apache, PHP 8.1 (PDO), PostgreSQL
# ================================================

# --- Funciones de mensaje ---
function msg() {
    echo -e "\n\033[1;32m[✔]\033[0m $1\n"
}

function error_msg() {
    echo -e "\n\033[1;31m[✗]\033[0m $1\n"
}

function warning_msg() {
    echo -e "\n\033[1;33m[⚠]\033[0m $1\n"
}

# --- Verificar que se ejecute como root ---
if [[ $EUID -ne 0 ]]; then
   error_msg "Este script debe ejecutarse como root. Usa: sudo ./install.sh"
   exit 1
fi

# === INICIO DE INSTALACIÓN ===
echo -e "\033[1;35m"
echo "                    Instalación básica para Zabbix Map"
echo -e "\033[0m"

msg "🚀 Iniciando instalación básica de Zabbix Map..."

# --- 0) Desactivar IPv6 ---
msg "Desactivando IPv6..."
echo -e "precedence ::ffff:0:0/96 100" | tee -a /etc/gai.conf >/dev/null
cat > /etc/sysctl.d/99-disable-ipv6.conf <<'EOF'
net.ipv6.conf.all.disable_ipv6 = 1
net.ipv6.conf.default.disable_ipv6 = 1
net.ipv6.conf.lo.disable_ipv6 = 1
EOF
sysctl -p /etc/sysctl.d/99-disable-ipv6.conf || warning_msg "No se pudo aplicar sysctl para IPv6, continúo..."

# --- 1) Update/Upgrade ---
msg "Actualizando paquetes (apt update && apt upgrade -y)..."
apt update && apt upgrade -y

# --- 2) Apache ---
msg "Instalando y habilitando Apache..."
apt install -y apache2
systemctl enable apache2
systemctl restart apache2

# --- 3) PHP 8.1 con PDO (PostgreSQL) ---
msg "Instalando PHP 8.1 y extensiones PDO para PostgreSQL..."
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.1 php8.1-cli php8.1-common libapache2-mod-php8.1 php8.1-pgsql

# Establecer PHP 8.1 como predeterminado
update-alternatives --set php /usr/bin/php8.1
a2enmod php8.1
systemctl restart apache2

# Verificación rápida de PHP y PDO_PGSQL
if php -m | grep -qi pdo; then
  msg "PDO habilitado en PHP"
else
  warning_msg "PDO no detectado en módulos de PHP"
fi
if php -m | grep -qi pdo_pgsql; then
  msg "Controlador PDO_PGSQL instalado"
else
  warning_msg "Controlador PDO_PGSQL no detectado"
fi

# --- 4) PostgreSQL ---
msg "Instalando PostgreSQL..."
apt install -y postgresql postgresql-contrib
systemctl enable postgresql
systemctl start postgresql
if systemctl is-active --quiet postgresql; then
    msg "PostgreSQL instalado y ejecutándose"
else
    error_msg "PostgreSQL no se inició correctamente"
fi

msg "✅ Instalación básica terminada. Componentes listos: IPv6 desactivado, Apache, PHP 8.1 (PDO), PostgreSQL."
exit 0