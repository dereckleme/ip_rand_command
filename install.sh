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

# Função para instalar o Composer
install_composer() {
    echo "Instalando o Composer..."
    EXPECTED_SIGNATURE="$(curl -sS https://composer.github.io/installer.sig)"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
        >&2 echo 'Erro: Assinatura do installer do Composer inválida.'
        rm composer-setup.php
        exit 1
    fi

    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    RESULT=$?
    rm composer-setup.php
    if [ $RESULT -eq 0 ]; then
        echo "Composer instalado com sucesso!"
    else
        echo "Erro ao instalar o Composer."
    fi
}

# Executa as funções de instalação
install_git
install_php
install_composer

# Clona o repositório na mesma pasta onde o script está
SCRIPT_DIR=$(dirname "$0")
echo "Clonando o repositório no diretório do script: $SCRIPT_DIR"
git clone https://github.com/dereckleme/ip_rand_command.git "$SCRIPT_DIR/ip_rand_command"

# Executa o Composer install dentro da pasta ip_rand_command
echo "Executando 'composer install' na pasta ip_rand_command..."
cd "$SCRIPT_DIR/ip_rand_command" || exit
composer install

echo "Instalação, clonagem e dependências concluídas."
