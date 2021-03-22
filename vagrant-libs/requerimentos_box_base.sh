#!/bin/bash

mkdir -p /pasta_compartilhada
mount.vboxsf -o "uid=1000,gid=1000,dev,exec,rw" pasta_compartilhada /pasta_compartilhada

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