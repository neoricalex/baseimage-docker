#!/bin/bash

if ! vagrant box list | grep "libvirt" > /dev/null; then
    echo "[DEBUG] O ubuntu/bionic64 libvirt não existe. Criando ele..."
    vagrant box add ubuntu/bionic64
    vagrant mutate ubuntu/bionic64 libvirt
fi
if vagrant status | grep "not created" > /dev/null; then
    iniciar_vps
elif vagrant status | grep "is running" > /dev/null; then
    echo "[DEBUG] O VPS_DEV existe e está ligado. Destruir e começar de novo?"
    vagrant destroy
    iniciar_vps
elif vagrant status | grep "poweroff" > /dev/null; then
    echo "[DEBUG] O VPS_DEV existe mas está desligado. Destruir e começar de novo..."
    vagrant destroy -f
    iniciar_vps
else
    echo "[DEBUG] O VPS_DEV existe mas está com um status diferente..."
    vagrant status
    sleep 5
fi