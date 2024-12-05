FROM nharrand/passoire:latest

RUN apt update && apt upgrade -y
RUN apt install -y sudo gosu

# Updating application code
COPY ./passoire /passoire

# Installing necessary nodejs packages
WORKDIR /passoire/crypto-helper

RUN npm install

WORKDIR /passoire

# Hardening apache2 config
COPY ./config/apache2.conf /etc/apache2/apache2.conf

# Hardening SSH config
RUN sed -i 's/#PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config && mkdir /home/passoire/.ssh

# Hardening mysql config
COPY ./config/mysql.cnf /etc/mysql/mysql.cnf
RUN chown mysql:mysql /etc/mysql/mysql.cnf && chmod 640 /etc/mysql/mysql.cnf

# Ensuring only passoire user can ssh with only key based auth
COPY ./authorized_keys /home/passoire/.ssh/authorized_keys

# Ensuring ownership of code
RUN chown -R www-data /passoire/web
RUN chmod -R 750 /passoire/web

ENTRYPOINT ["/passoire/init.sh"]