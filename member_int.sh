#!/bin/bash
echo "*****************************************************************************"
echo "		********************************************                       "
echo "	        *  欢迎使用Shopex会员运营中心一键部署  *                       "
echo "		********************************************                       "
echo "                                                                             "
echo "	 注意事项：                                                                "
echo "	                                                                           "
echo "	       1.本程序仅适用于安装会员运营中心环境。                              "
echo "	       2.本程序会将本服务器上原来存在的涉及PHP和Mysql的包全部移除。        "
echo "	       3.本程序会将PHP和Java环境安装在一台服务器上如需分布式部署，请手工安装"
echo "                                                                             "
echo "                                                                             "
echo "*****************************************************************************"
TIP="input CRM web dir(such as your crm dir is /usr/local/crm ,just input crm!): "
read -p "$TIP" CRMDEMO

if [ -z "$CRMDEMO" ];then
	echo "you do not input CRM web dir name"
	exit 0
fi

echo $CRMDEMO
SERVICES='nginx mysqld redis php-fpm'
PAKNAME='ngx_openresty php-pdo php-xml php-pecl-imagick php-soap php php-fpm php-bcmath php-pecl-memcached php-pecl-igbinary php-common php-mysql php-mcrypt php-mbstring php-pecl-memcache php-pecl-redis php-gd php-cli php-xmlrpc php-pecl-mongo php-ZendGuardLoader mysql redis memcached'
#Disable SeLinux
setenforce 0
if [ -s /etc/selinux/config ]; then
    sed -i 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/selinux/config
    echo -e "\033[31m selinux is disabled,if you need,you must reboot.\033[0m"
fi


#Synchronization time
rm -rf /etc/localtime
ln -s /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

#iptables config
cat > /etc/sysconfig/iptables << 'EOF'
# Firewall configuration written by system-config-securitylevel
# Manual customization of this file is not recommended.
*filter
:INPUT ACCEPT [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
:RH-Firewall-1-INPUT - [0:0]
-A INPUT -j RH-Firewall-1-INPUT
-A FORWARD -j RH-Firewall-1-INPUT
-A RH-Firewall-1-INPUT -i lo -j ACCEPT
-A RH-Firewall-1-INPUT -p icmp --icmp-type any -j ACCEPT
-A RH-Firewall-1-INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
-A RH-Firewall-1-INPUT -m state --state NEW -m tcp -p tcp --dport 21 -j ACCEPT
-A RH-Firewall-1-INPUT -m state --state NEW -m tcp -p tcp --dport 22 -j ACCEPT
-A RH-Firewall-1-INPUT -m state --state NEW -m tcp -p tcp --dport 80 -j ACCEPT
-A RH-Firewall-1-INPUT -m state --state NEW -m tcp -p tcp --dport 443 -j ACCEPT
-A RH-Firewall-1-INPUT -j REJECT --reject-with icmp-host-prohibited
COMMIT
EOF
iptables-restore < /etc/sysconfig/iptables
service iptables save
service iptables restart

# modprobe config
modprobe ip_conntrack_ftp
if [ $? -eq 0 ]; then
    sed -i "/modprobe ip_conntrack_ftp/d" /etc/rc.d/rc.local
    echo "modprobe ip_conntrack_ftp" >> /etc/rc.d/rc.local
fi
modprobe ip_nat_ftp
if [ $? -eq 0 ]; then
    sed -i "/modprobe ip_nat_ftp/d" /etc/rc.d/rc.local
    echo "modprobe ip_nat_ftp" >> /etc/rc.d/rc.local
fi
modprobe bridge
if [ $? -eq 0 ]; then
    sed -i "/modprobe bridge/d" /etc/rc.d/rc.local
    echo "modprobe bridge" >> /etc/rc.d/rc.local
fi
# limit config
cat > /etc/security/limits.conf <<'EOF'
*               soft    nofile          65532
*               hard    nofile          65532
EOF

cat >/etc/security/limits.d/90-nproc.conf <<'EOF'
*          soft    nproc     65532
root       soft    nproc     unlimited
EOF

#dns  config
cp /etc/resolv.conf /etc/resolv.conf.bak
cat >/etc/resolv.conf <<'EOF'
nameserver 223.5.5.5
nameserver 223.6.6.6
EOF

killall yum
echo -e "\033[31m cleanning old rpm pkg ... \033[0m"
# clean old php pkg
echo 
yum remove php* -y > /dev/null 2>&1 || true
yum remove mysql mysql-server -y > /dev/null 2>&1 || true
yum install wget -y > /dev/null 2>&1 || true

cd /etc/yum.repos.d/
if [ -z /etc/yum.repos.d/shopex-lnmp.repo ];then
	wget http://mirrors.shopex.cn/shopex/shopex-lnmp/shopex-lnmp.repo
else
	mv shopex-lnmp.repo shopex-lnmp.repo.bak
	wget http://mirrors.shopex.cn/shopex/shopex-lnmp/shopex-lnmp.repo
fi > /dev/null 2>&1
cd - > /dev/null 2>&1
yum install epel-release yum-plugin-fastestmirror -y > /dev/null 2>&1 || true

for e in $PAKNAME
do 
	rpm -q $e &> /dev/null 
	[ $? -ne 0 ] && UNPKG="$UNPKG $e"  
done 
[ -n "$UNPKG" ] && yum install $UNPKG -y  
echo "Dependent Packages install OK...." 

yum install jdk1.8.0_40 -y   > /dev/null 2>&1
# java config 
if [ -z /usr/local/java ];then
	ln -s /usr/java/jdk1.8.0_40/ /usr/local/java
fi

grep "JAVA_HOME" /etc/profile
if [ $? -ne 0 ]; then
	echo "export JAVA_HOME=/usr/local/java" >> /etc/profile
	echo 'export PATH=$JAVA_HOME/bin:$PATH' >> /etc/profile
	source /etc/profile
fi
# php config
if [ -f /etc/php.ini ];then
	sed -i 's#;date.timezone =#date.timezone = Asia/Shanghai#g' /etc/php.ini 
	sed -i 's#display_errors = Off#display_errors = On#g' /etc/php.ini
fi

if [ -f /etc/php-fpm.d/www.conf ];then
	sed -i 's#pm.max_children = 50#pm.max_children = 128#g' /etc/php-fpm.d/www.conf
	sed -i 's#pm.start_servers = 5#pm.start_servers = 15#g' /etc/php-fpm.d/www.conf
	sed -i 's#pm.min_spare_servers = 5#pm.min_spare_servers = 15#g' /etc/php-fpm.d/www.conf
	sed -i 's#pm = dynamic#pm = static#g' /etc/php-fpm.d/www.conf
	sed -i 's#user  www www;#user  apache apache;#g' /usr/local/nginx/conf/nginx.conf
fi


CRMTARFILE=(`ls crm-*.tar.gz 2>/dev/null`)
CRMTARFILE1=${CRMTARFILE[0]}

if [ -z  $CRMTARFILE1 ];then
        echo "not CRM install file"
        exit 127
fi

# config httpd path
if [ -d /data/httpd/$CRMDEMO ]; then
	mv /data/httpd/$CRMDEMO /data/httpd/$CRMDEMO.bak
	mkdir -p /data/httpd/
	tar xf $CRMTARFILE1 -C /data/httpd/
	if [ "/data/httpd/$CRMDEMO" != "/data/httpd/crm" ];then
		mv /data/httpd/crm   /data/httpd/$CRMDEMO
	fi
	chown -R apache.apache /data/httpd/$CRMDEMO
	chown -R apache.apache /data/httpd/
	# install java_app
	cd /data/httpd/$CRMDEMO/java/
	javafile='middleware.tar.gz'
	install -m755 -d /data/java_app
        tar xf $javafile -C /data/java_app
        chmod u+x /data/java_app/initShopexCRM.sh
        cd /data/java_app && nohup  /data/java_app/initShopexCRM.sh  &
else
	mkdir -p /data/httpd/
	tar xf $CRMTARFILE1 -C /data/httpd/
	if [ "/data/httpd/$CRMDEMO" != "/data/httpd/crm" ];then
		mv /data/httpd/crm   /data/httpd/$CRMDEMO
	fi
	chown -R apache.apache /data/httpd/$CRMDEMO
	chown -R apache.apache /data/httpd/
	# install java_app
	cd /data/httpd/$CRMDEMO/java/
	javafile='middleware.tar.gz'
	install -m755 -d /data/java_app
        tar xf  $javafile -C /data/java_app
        chmod u+x /data/java_app/initShopexCRM.sh
	cd /data/java_app &&  nohup  /data/java_app/initShopexCRM.sh  &
fi

# config php zend
if [ -f /etc/php.d/Zend.ini ];then
        grep "zend_loader.license_path ='/usr/local/php/etc/183.zl'" /etc/php.d/Zend.ini >>/dev/null
        if [ 0 = $? ];then
                echo 'zend_loader.license_path ='/data/httpd/$CRMDEMO/config/developer.zl'' >> /etc/php.d/Zend.ini
                sed -i '/;zend_loader.license_path/d' /etc/php.d/Zend.ini
        fi
fi

# install crm-demo config file
cat > /usr/local/nginx/conf/vhosts/default.conf <<EOF
server
{
    listen       80 default;
    server_name  _;
    index index.html index.htm index.php;
    root  /data/httpd/$CRMDEMO;


    location ~ (public\/*|themes\/*|demo\/*)
    {
       access_log off;
    }
    location ~ .*\.php.*
    {
        include php_fcgi.conf;
        include pathinfo.conf;
    }

    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$
    {
        expires      30d;
    }

    location ~ .*\.(js|css)?$
    {
        expires      1h;
    }
    access_log /var/log/nginx/access.log;
}

EOF

for i in $SERVICES
do
        service $i restart
        chkconfig $i on
done


sleep 1

declare -a closelist
closelist=(
avahi-daemon
bluetooth
cups
firstboot
ip6tables
isdn
pcscd
rhnsd
yum-updatesd
pcscd
)

for((count=0,i=0;count<${#closelist[@]};i++))
do
    /sbin/chkconfig --list | grep ${closelist[i]}
    if [ $? -eq 0 ]; then
        cmd="/sbin/chkconfig ${closelist[i]} --level 3 off"
        echo $cmd
        `$cmd`
        /sbin/service ${closelist[i]} stop
    fi
    let count+=1
done > /dev/null 2>&1

grep "unset MAILCHECK" /etc/profile
if [ $? -ne 0 ]; then
    sed -i "/unset MAILCHECK/d" /etc/profile
    echo "unset MAILCHECK"  >> /etc/profile
fi
