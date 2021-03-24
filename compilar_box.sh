#!/bin/bash

provisionar_vps(){
    VAGRANT_VAGRANTFILE=Vagrantfile_Virtualbox vagrant up
    VAGRANT_VAGRANTFILE=Vagrantfile_Virtualbox vagrant reload
}
iniciar_vps(){
    VAGRANT_VAGRANTFILE=Vagrantfile_Virtualbox vagrant ssh <<EOF
#!/bin/bash

cd /vagrant
make iso
cd ..
EOF

}

echo "==> Iniciando o VPS_DEV..."

if vagrant status | grep "not created" > /dev/null; then
    provisionar_vps
    iniciar_vps
elif vagrant status | grep "is running" > /dev/null; then
    iniciar_vps
else
    echo "[DEBUG] O VPS_DEV existe mas está com um status diferente..."
    vagrant status
    sleep 5
fi
