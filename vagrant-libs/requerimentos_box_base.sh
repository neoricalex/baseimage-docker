#!/bin/bash

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
    virtualbox virtualbox-guest-dkms virtualbox-guest-additions-iso \
	bridge-utils dnsmasq-base ebtables libvirt-daemon-system libvirt-clients \
    libvirt-dev qemu-kvm qemu-utils ruby-dev \
    ruby-libvirt libxslt-dev libxml2-dev zlib1g-dev	
