#!/bin/bash

provisionar_vps(){

	echo "==> Checkar se a box do VPS_DEV foi gerada..."
	vps_dev=$(vagrant box list | grep "neoricalex/ubuntu" > /dev/null)
	if [ $? == "1" ];
	then
		echo "==> Checkar se a box base com o Ubuntu - VPS_BASE - foi gerada..."
		if [ ! -f "vagrant-libs/base.box" ];
		then
			echo "==> Provisionando o VPS_BASE..."
			#VAGRANT_VAGRANTFILE=Vagrantfile.VPS_BASE vagrant destroy -f
			VAGRANT_VAGRANTFILE=Vagrantfile.VPS_BASE vagrant up
			VAGRANT_VAGRANTFILE=Vagrantfile.VPS_BASE vagrant ssh<<EOF
#!/bin/bash

echo "Atualizar repositórios e pacotes..."

sudo bash -c 'cat > /etc/apt/sources.list' <<REPOSITORIOS
# deb cdrom:[Ubuntu 20.04 LTS _Focal Fossa_ - Release amd64 (20200423)]/ focal main restricted

# See http://help.ubuntu.com/community/UpgradeNotes for how to upgrade to
# newer versions of the distribution.
deb http://br.archive.ubuntu.com/ubuntu/ focal main restricted
deb-src http://br.archive.ubuntu.com/ubuntu/ focal main restricted

## Major bug fix updates produced after the final release of the
## distribution.
deb http://br.archive.ubuntu.com/ubuntu/ focal-updates main restricted
deb-src http://br.archive.ubuntu.com/ubuntu/ focal-updates main restricted

## N.B. software from this repository is ENTIRELY UNSUPPORTED by the Ubuntu
## team. Also, please note that software in universe WILL NOT receive any
## review or updates from the Ubuntu security team.
deb http://br.archive.ubuntu.com/ubuntu/ focal universe
deb-src http://br.archive.ubuntu.com/ubuntu/ focal universe
deb http://br.archive.ubuntu.com/ubuntu/ focal-updates universe
deb-src http://br.archive.ubuntu.com/ubuntu/ focal-updates universe

## N.B. software from this repository is ENTIRELY UNSUPPORTED by the Ubuntu 
## team, and may not be under a free licence. Please satisfy yourself as to 
## your rights to use the software. Also, please note that software in 
## multiverse WILL NOT receive any review or updates from the Ubuntu
## security team.
deb http://br.archive.ubuntu.com/ubuntu/ focal multiverse
deb-src http://br.archive.ubuntu.com/ubuntu/ focal multiverse
deb http://br.archive.ubuntu.com/ubuntu/ focal-updates multiverse
deb-src http://br.archive.ubuntu.com/ubuntu/ focal-updates multiverse

## N.B. software from this repository may not have been tested as
## extensively as that contained in the main release, although it includes
## newer versions of some applications which may provide useful features.
## Also, please note that software in backports WILL NOT receive any review
## or updates from the Ubuntu security team.
deb http://br.archive.ubuntu.com/ubuntu/ focal-backports main restricted universe multiverse
deb-src http://br.archive.ubuntu.com/ubuntu/ focal-backports main restricted universe multiverse

## Uncomment the following two lines to add software from Canonical's
## 'partner' repository.
## This software is not part of Ubuntu, but is offered by Canonical and the
## respective vendors as a service to Ubuntu users.
# deb http://archive.canonical.com/ubuntu focal partner
deb-src http://archive.canonical.com/ubuntu focal partner

deb http://security.ubuntu.com/ubuntu focal-security main restricted
deb-src http://security.ubuntu.com/ubuntu focal-security main restricted
deb http://security.ubuntu.com/ubuntu focal-security universe
deb-src http://security.ubuntu.com/ubuntu focal-security universe
deb http://security.ubuntu.com/ubuntu focal-security multiverse
deb-src http://security.ubuntu.com/ubuntu focal-security multiverse

# This system was installed using small removable media
# (e.g. netinst, live or single CD). The matching "deb cdrom"
# entries were disabled at the end of the installation process.
# For information about how to configure apt package sources,
# see the sources.list(5) manual.
REPOSITORIOS

sudo apt-get update
sudo apt-get -y upgrade
sudo apt-get -y dist-upgrade

echo "==> Instalar o Linux/Ubuntu base..."
sudo apt-get install linux-generic linux-headers-`uname -r` ubuntu-minimal dkms -y

echo "==> Instalar pacotes para desenvolvimento geral..."
sudo apt-get install -y build-essential checkinstall libreadline-gplv2-dev \
	libncursesw5-dev libssl-dev libsqlite3-dev tk-dev libgdbm-dev libc6-dev \
	libbz2-dev libffi-dev python3-pip unzip lsb-release software-properties-common \
	curl wget git rsync devscripts python-dev python3-venv

echo "==> Instalar pacotes para a criação da imagem ISO..."
sudo apt install -y \
	binutils \
	debootstrap \
	squashfs-tools \
	xorriso \
	grub-pc-bin \
	grub-efi-amd64-bin \
	unzip \
	mtools \
	whois \
	jq \
	moreutils \
	make

echo "==> Instalar os pacotes do kvm"
sudo apt install -y qemu-system qemu qemu-kvm qemu-utils qemu-block-extra \
					libvirt-daemon libvirt-daemon-system libvirt-clients \
					cpu-checker libguestfs-tools libosinfo-bin \
					bridge-utils dnsmasq-base ebtables libvirt-dev ruby-dev \
					ruby-libvirt libxslt-dev libxml2-dev zlib1g-dev       

echo "==> Instalar o VirtualBox"
echo "deb [arch=amd64] https://download.virtualbox.org/virtualbox/debian focal contrib" | sudo tee /etc/apt/sources.list.d/virtualbox.list
wget -q https://www.virtualbox.org/download/oracle_vbox_2016.asc -O- | sudo apt-key add -
wget -q https://www.virtualbox.org/download/oracle_vbox.asc -O- | sudo apt-key add -
sudo apt-get update
sudo apt-get install virtualbox-6.1 -y
sudo apt install -y virtualbox-guest-dkms #virtualbox-guest-x11
sudo apt install -y virtualbox-guest-additions-iso

echo "==> Instalar o Extension Pack do VirtualBox"
wget https://download.virtualbox.org/virtualbox/6.1.18/Oracle_VM_VirtualBox_Extension_Pack-6.1.18.vbox-extpack \
	-q --show-progress \
	--progress=bar:force:noscroll
sudo vboxmanage extpack install Oracle_VM_VirtualBox_Extension_Pack-6.1.18.vbox-extpack --accept-license=33d7284dc4a0ece381196fda3cfe2ed0e1e8e7ed7f27b9a9ebc4ee22e24bd23c
rm Oracle_VM_VirtualBox_Extension_Pack-6.1.18.vbox-extpack 

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

echo "==> Adicionar o usuário vagrant ao grupo docker"
sudo usermod -aG docker vagrant

echo "==> Adicionar o grupo kvm"
sudo groupadd kvm

echo "==> Adicionar o usuário vagrant ao grupo kvm"
sudo usermod -aG kvm vagrant

echo "==> Adicionar o usuário vagrant ao grupo libvirt"
sudo usermod -aG libvirt vagrant

echo "==> Iniciar o serviço KVM de forma automática"
sudo systemctl start libvirtd
sudo systemctl enable --now libvirtd

echo "==> Reiniciar o serviço libvirt"
sudo systemctl restart libvirtd.service

echo "==> Habilitar o IPv4 e IPv6 forwarding"
sudo sed -i "/net.ipv4.ip_forward=1/ s/# *//" /etc/sysctl.conf
sudo sed -i "/net.ipv6.conf.all.forwarding=1/ s/# *//" /etc/sysctl.conf

echo "==> Aplicar as mudanças"
sudo sysctl -p

echo "==> Download Vagrant & Instalar"
wget -nv https://releases.hashicorp.com/vagrant/2.2.14/vagrant_2.2.14_x86_64.deb
sudo dpkg -i vagrant_2.2.14_x86_64.deb
rm vagrant_2.2.14_x86_64.deb

echo "==> Instalar plugins do Vagrant"
vagrant plugin install vagrant-libvirt
vagrant plugin install vagrant-disksize # Só funciona no Virtualbox
vagrant plugin install vagrant-mutate

echo "==> Instalar Packer"
wget https://releases.hashicorp.com/packer/1.6.4/packer_1.6.4_linux_amd64.zip
unzip packer_1.6.4_linux_amd64.zip
sudo mv packer /usr/local/bin 
rm packer_1.6.4_linux_amd64.zip

echo "==> Remover entradas antigas do kernel na Grub..."
# REF: https://askubuntu.com/questions/176322/removing-old-kernel-entries-in-grub
sudo apt-get purge $( dpkg --list | grep -P -o "linux-image-\d\S+" | grep -v $(uname -r | grep -P -o ".+\d") ) -y	

echo "==> Removendo pacotes desnecessários"
sudo apt autoremove -y

echo "O VPS_BASE foi provisionado com sucesso!"
echo "Continuando..."
EOF

			echo "==> Reiniciando o VPS_BASE..."
			VAGRANT_VAGRANTFILE=Vagrantfile.VPS_BASE vagrant reload
			echo "==> Empacotando o VPS_BASE como VPS_DEV..."
			vagrant package --base VPS_BASE --output vagrant-libs/base.box

			usuario="$(whoami)@$(hostname | cut -d . -f 1-2)"
			if [ "$usuario" == "neo@desktop" ]; 
			then
				vagrant cloud auth login
				vagrant cloud publish \
					--box-version 0.0.1 \
					--release \
					--short-description "Um VPS baseado no ubuntu/focal64 para desenvolvimento do projeto NEORICALEX e NFDOS" \
					--version-description "Versão inicial" \
					neoricalex/ubuntu 0.0.1 virtualbox \
					vagrant-libs/base.box # --force --debug
				vagrant cloud auth logout
			else
				echo "[DEBUG] Para enviar a box base para a Vagrant Cloud tem que ter as credenciais. Continuando..."
			fi

			echo "==> Excluir o VPS_BASE pois não é mais necessário..."
			VAGRANT_VAGRANTFILE=Vagrantfile.VPS_BASE vagrant destroy -f
			echo "==> Excluir a box ubuntu/focal64 pois não é mais necessária..."
			vagrant box remove ubuntu/focal64 --provider virtualbox

			#echo "==> Excluir também a vagrant-libs/base.box para liberarmos espaço em disco..."
			#rm vagrant-libs/base.box


		fi
		echo "==> O VPS_BASE foi gerado e empacotado como VPS_DEV."

	fi
	echo "==> O VPS_DEV foi gerado."

	echo "==> O VPS_DEV baseado no neoricalex/ubuntu está agora pronto para ser executado."
	echo "==> Provisionando o VPS_DEV..."
	VAGRANT_VAGRANTFILE=Vagrantfile.VPS_DEV vagrant up
	echo "==> Reiniciando o VPS_DEV para as configurações ficarem ativas..."
	VAGRANT_VAGRANTFILE=Vagrantfile.VPS_DEV vagrant reload
}

entrar_vps(){

	echo "==> Entrando no VPS_DEV..."
	VAGRANT_VAGRANTFILE=Vagrantfile.VPS_DEV vagrant ssh<<EOF
#!/bin/bash

cd /neoricalex

make nfdos

cd .. 
EOF
}

if VAGRANT_VAGRANTFILE=Vagrantfile.VPS_DEV vagrant status | grep "not created" > /dev/null;
then

    provisionar_vps
	entrar_vps

elif VAGRANT_VAGRANTFILE=Vagrantfile.VPS_DEV vagrant status | grep "poweroff" > /dev/null;
then

	echo "==> [DEBUG] O VPS_DEV existe mas está com um status de desligado. Ligando o VPS_DEV..."
	VAGRANT_VAGRANTFILE=Vagrantfile.VPS_DEV vagrant up
	entrar_vps

elif VAGRANT_VAGRANTFILE=Vagrantfile.VPS_DEV vagrant status | grep "is running" > /dev/null;
then

	entrar_vps

else

    echo "==> [DEBUG] O VPS_DEV existe mas está com um status desconhecido."
    VAGRANT_VAGRANTFILE=Vagrantfile.VPS_DEV vagrant status 
	sleep 5

fi
