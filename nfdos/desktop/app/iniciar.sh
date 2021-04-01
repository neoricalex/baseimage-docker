#!/bin/bash

echo "==> Instalar pacotes para desenvolvimento geral..."
sudo apt-get install -y build-essential checkinstall libreadline-gplv2-dev \
	libncursesw5-dev libssl-dev libsqlite3-dev tk-dev libgdbm-dev libc6-dev \
	libbz2-dev libffi-dev python3-pip unzip lsb-release software-properties-common \
	curl wget git rsync devscripts python-dev python3-venv php-cli unzip \
	libz-dev libssl-dev libcurl4-gnutls-dev libexpat1-dev gettext cmake gcc build-dep 

	#qemu-user-static libvirt-bin 

echo "==> Instalar Docker..."
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
sudo apt-get update
sudo apt-cache policy docker-ce
sudo apt-get install -y docker-ce docker-compose
# Re-instalar docker-compose # WORKAROUND: https://github.com/docker/for-linux/issues/563
# docker build -t terraform-azure-vm . >> "free(): invalid pointer"
sudo apt-get remove -y golang-docker-credential-helpers
sudo curl -L "https://github.com/docker/compose/releases/download/1.25.5/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo echo '{"experimental": true}' > /etc/docker/daemon.json
sudo service docker restart

echo "==> Adicionar o usuário neo ao grupo docker"
sudo usermod -aG docker neo

echo "==> Instalar o Composer..."
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

#git config --global user.name "neoricalex"
#git config --global user.email "neo.webmaster.2@gmail.com"

#git clone --depth=1 https://github.com/neoricalex/backend.git
#composer create-project roots/bedrock site

# TODO: Trellis/Bedrock/Wordpress: https://www.youtube.com/watch?v=-pOKTtAfJ8M&ab_channel=WPCasts
# TODO Ainsible Docker Swarm: https://imasters.com.br/devsecops/cluster-de-docker-swarm-com-ansible

if [ ! -f "/etc/wireguard/wg0.conf" ]; then
	echo "==> Instalar Wireguard..."
	sudo apt install wireguard -y
	sudo cp /nfdos/vagrant-libs/ssh/digital-ocean/wireguard/cliente/wg0.conf /etc/wireguard/wg0.conf
	sleep 10
	sudo wg-quick up wg0
fi

echo "==> Remover entradas antigas do kernel na Grub..."
# REF: https://askubuntu.com/questions/176322/removing-old-kernel-entries-in-grub
sudo apt-get purge $( dpkg --list | grep -P -o "linux-image-\d\S+" | grep -v $(uname -r | grep -P -o ".+\d") ) -y	

echo "==> Removendo pacotes desnecessários"
sudo apt autoremove -y

#ip -o route get to 8.8.8.8 | sed -n 's/.*src \([0-9.]\+\).*/\1/p'

echo ""
echo "O NFDOS foi compilado com Sucesso!"
