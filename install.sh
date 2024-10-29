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
        sudo apt update
        sudo apt install -y git
    elif [ "$DISTRO" == "centos" ] || [ "$DISTRO" == "rocky" ] || [ "$DISTRO" == "rhel" ]; then
        sudo dnf install -y git
    else
        echo "Distribuição não suportada para a instalação do Git."
    fi
}

# Função para instalar o PHP 8.3
install_php() {
    echo "Instalando o PHP 8.3..."
    if [ "$DISTRO" == "debian" ] || [ "$DISTRO" == "ubuntu" ]; then
        sudo apt update
        sudo apt install -y software-properties-common
        sudo add-apt-repository -y ppa:ondrej/php
        sudo apt update
        sudo apt install -y php8.3
    elif [ "$DISTRO" == "centos" ] || [ "$DISTRO" == "rocky" ] || [ "$DISTRO" == "rhel" ]; then
        sudo dnf install -y epel-release
        sudo dnf install -y dnf-utils
        sudo dnf module reset php -y
        sudo dnf module install php:8.3 -y
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
git clone git@github.com:dereckleme/ip_rand_command.git "$SCRIPT_DIR/ip_rand_command"

echo "Instalação e clonagem concluídas."
