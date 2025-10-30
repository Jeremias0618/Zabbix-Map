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
apt install -y php8.1 php8.1-cli php8.1-common libapache2-mod-php8.1 php8.1-pgsql php8.1-curl

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

# Verificación de cURL
if php -m | grep -qi curl; then
  msg "Extensión cURL instalada"
else
  warning_msg "Extensión cURL no detectada"
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

# --- 5) UFW (Firewall) ---
msg "Instalando y configurando UFW (Firewall)..."
apt install -y ufw

# Reglas iniciales
ufw allow 10050/tcp comment 'Zabbix Agent'
ufw allow 10051/tcp comment 'Zabbix Server'
ufw allow 5432/tcp comment 'PostgreSQL'

# Políticas predeterminadas
ufw default deny incoming
ufw default allow outgoing

# Reglas restringidas por subred
ufw allow from 10.80.80.0/24 to any port 22 proto tcp comment 'SSH desde red local'
ufw allow from 10.80.80.0/24 to any port 80 proto tcp comment 'HTTP desde red local'
ufw allow from 10.80.80.0/24 to any port 443 proto tcp comment 'HTTPS desde red local'

# Deshabilitar IPv6 en UFW para evitar reglas v6 (ya se desactivó IPv6 del sistema)
if grep -q "^IPV6=" /etc/default/ufw; then
  sed -i 's/^IPV6=.*/IPV6=no/' /etc/default/ufw
else
  echo 'IPV6=no' >> /etc/default/ufw
fi

# Activar UFW
ufw --force enable

# Restringir ICMP
if [ -f "/etc/ufw/before.rules" ]; then
  cp /etc/ufw/before.rules /etc/ufw/before.rules.backup
  if grep -q "-A ufw-before-input -p icmp --icmp-type echo-request -j ACCEPT" /etc/ufw/before.rules; then
    tmpfile=$(mktemp)
    awk 'BEGIN{inserted=0}
      {
        if ($0 ~ /^# ok icmp codes for INPUT/ && inserted==0) {
          print $0
          print "-A ufw-before-input -s 10.80.80.0/24 -p icmp --icmp-type echo-request -j ACCEPT"
          print "-A ufw-before-input -p icmp --icmp-type echo-request -j DROP"
          inserted=1
          next
        }
        if ($0 == "-A ufw-before-input -p icmp --icmp-type echo-request -j ACCEPT") {
          next
        }
        print $0
      }' /etc/ufw/before.rules > "$tmpfile" && mv "$tmpfile" /etc/ufw/before.rules
  else
    # Insertar reglas si no existe la línea estándar
    if grep -q "^# ok icmp codes for INPUT" /etc/ufw/before.rules; then
      sed -i '/^# ok icmp codes for INPUT/a -A ufw-before-input -s 10.80.80.0\/24 -p icmp --icmp-type echo-request -j ACCEPT\n-A ufw-before-input -p icmp --icmp-type echo-request -j DROP' /etc/ufw/before.rules
    fi
  fi
  ufw reload || true
else
  warning_msg "No se encontró /etc/ufw/before.rules para ajustar ICMP"
fi

ufw status verbose || true

msg "✅ Instalación básica terminada. Componentes listos: IPv6 desactivado, Apache, PHP 8.1 (PDO), PostgreSQL."
exit 0