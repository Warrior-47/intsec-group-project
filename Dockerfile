FROM nharrand/passoire:latest

RUN apt update && apt upgrade -y

# Updating application code
COPY ./passoire /passoire

# Hardening apache2 config
COPY ./config/apache2.conf /etc/apache2/apache2.conf

# Hardening SSH config
RUN sed -i 's/#PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config && mkdir /home/passoire/.ssh

# Ensuring only passoire user can ssh with only key based auth
COPY ./authorized_keys /home/passoire/.ssh/authorized_keys

# Ensuring ownership of code
RUN chown -R passoire /passoire

CMD ["/passoire/init.sh"]