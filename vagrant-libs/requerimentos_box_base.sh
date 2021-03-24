#!/bin/bash

echo "==> Instalar os requerimentos da box..."
if [ ! -f ".requerimentos_box_base.box" ]; 
then
	#echo "==> Criar a pasta compartilhada ..."
	#sudo umount -a -t vboxsf 
	#mkdir -p /home/vagrant/src
	#sudo mount.vboxsf -o relatime,user,exec,dev,noauto vagrant /vagrant

	echo "==> Atualizar os repositórios do $HOSTNAME ..."
	sudo apt update && sudo apt upgrade -y

	echo "==> Instalar os pacotes base no $HOSTNAME..."
	sudo apt-get install -y \
		linux-generic \
		linux-headers-`uname -r` \
		ubuntu-minimal \
		dkms \
		autoconf \
		build-essential \
		make \
		virtualbox virtualbox-guest-dkms virtualbox-guest-additions-iso

	echo "==> Adicionar o grupo kvm"
	sudo groupadd kvm

	echo "==> Adicionar o usuário vagrant ao grupo kvm"
	sudo usermod -aG kvm vagrant

	echo "==> Instalar os pacotes do kvm"
	sudo apt install -y qemu-system qemu qemu-kvm qemu-utils qemu-block-extra \
						libvirt-daemon libvirt-daemon-system libvirt-clients \
						cpu-checker libguestfs-tools libosinfo-bin \
						bridge-utils dnsmasq-base ebtables libvirt-dev ruby-dev \
						ruby-libvirt libxslt-dev libxml2-dev zlib1g-dev	

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

    echo "==> Removendo pacotes do Ubuntu desnecessários"
    sudo apt autoremove -y
    touch .requerimentos_box_base.box
fi
