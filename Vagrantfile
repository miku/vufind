# -*- mode: ruby -*-
# vi: set ft=ruby :
#
# A setup and provisioning script for VuFind 2.3.1 based on
# https://vufind.org/wiki/vufind2:installation_ubuntu#configuring_and_starting_vufind.
#
# Run
#
#    $ vagrant up
#
# and wait about fifteen minutes. When the setup is done,
# login into your VM and start VuFind by hand.
#
#    $ vagrant ssh
#    vagrant@vagrant:~$ cd /usr/local/vufind2/
#    vagrant@vagrant:/usr/local/vufind2$ sudo ./vufind.sh start
#
# Then visit
#
#     http://localhost:8000/vufind/Install/Home
#
# and adjust your settings (e.g. MySQL: root/admin and NoILS).
#

$script = <<SCRIPT
apt-get -y install apache2
a2enmod rewrite
/etc/init.d/apache2 force-reload

debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password password admin'
debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password_again password admin'
apt-get install -q --yes mysql-server-5.5

apt-get -y install php5 php5-dev php-pear php5-json php5-ldap php5-mcrypt php5-mysql php5-xsl php5-intl php5-gd
apt-get -y install vim default-jdk

cd /tmp
wget "http://downloads.sourceforge.net/vufind/vufind-2.3.1.tar.gz?use_mirror=osdn" -O vufind-2.3.1.tar.gz
tar xzvf vufind-2.3.1.tar.gz
mv vufind-2.3.1 /usr/local/vufind2

cd /usr/local/vufind2
php install.php --use-defaults

chown -R www-data:www-data /usr/local/vufind2/local/cache
chown -R www-data:www-data /usr/local/vufind2/local/config
mkdir /usr/local/vufind2/local/cache/cli
chmod 777 /usr/local/vufind2/local/cache/cli
ln -fs /usr/local/vufind2/local/httpd-vufind.conf /etc/apache2/conf-enabled/vufind2.conf
/etc/init.d/apache2 reload

echo export JAVA_HOME="/usr/lib/jvm/default-java" >> /etc/profile
echo export VUFIND_HOME="/usr/local/vufind2" >> /etc/profile
echo export VUFIND_LOCAL_DIR="/usr/local/vufind2/local" >> /etc/profile
source /etc/profile

php5enmod mcrypt
service apache2 restart

date > /etc/vagrant_provisioned_at

echo "Go to: http://localhost:8000/vufind/Install/Home"
SCRIPT

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "chef/ubuntu-14.10"
  config.vm.provision "shell", inline: $script

  config.vm.network "forwarded_port", guest: 80, host: 8000
  config.vm.network "forwarded_port", guest: 8080, host: 8080

  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--memory", "1024"]
  end
end
