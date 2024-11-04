#!/bin/bash

# Detecta a distribuição do sistema operacional
if [ -f /etc/os-release ]; then
    . /etc/os-release
    DISTRO=$ID
else
    echo "Sistema operacional não suportado."
    exit 1
fi

echo "Distribuição detectada: $DISTRO"

# Função para instalar o Git
install_git() {
    echo "Instalando o Git..."
    if [ "$DISTRO" == "debian" ] || [ "$DISTRO" == "ubuntu" ]; then
        apt update
        apt install -y git
    elif [ "$DISTRO" == "centos" ] || [ "$DISTRO" == "rocky" ] || [ "$DISTRO" == "rhel" ]; then
        dnf install -y git
    else
        echo "Distribuição não suportada para a instalação do Git."
    fi
}

# Função para instalar o PHP 8.3
install_php() {
    echo "Instalando o PHP 8.3..."
    if [ "$DISTRO" == "debian" ] || [ "$DISTRO" == "ubuntu" ]; then
        apt update
        apt install -y software-properties-common
        add-apt-repository -y ppa:ondrej/php
        apt update
        apt install -y php8.3 php8.3-json
    elif [ "$DISTRO" == "centos" ] || [ "$DISTRO" == "rocky" ] || [ "$DISTRO" == "rhel" ]; then
        dnf install -y epel-release
        dnf install -y dnf-utils
        dnf module reset php -y
        dnf module enable php:remi-8.3 -y
        dnf install -y php php-json
    else
        echo "Distribuição não suportada para a instalação do PHP 8.3."
    fi
}

# Executa as funções de instalação
install_git
install_php

# Clona o repositório na mesma pasta onde o script está
SCRIPT_DIR=$(dirname "$0")
echo "Clonando o repositório no diretório do script: $SCRIPT_DIR"
git clone https://github.com/dereckleme/ip_rand_command.git "$SCRIPT_DIR/ip_rand_command"

# Executa o Composer install usando o composer.phar na pasta ip_rand_command
echo "Executando 'composer install' na pasta ip_rand_command com composer.phar..."
cd "$SCRIPT_DIR/ip_rand_command" || exit
php composer.phar install

echo "Instalação, clonagem e dependências concluídas."
