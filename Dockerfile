FROM nharrand/passoire:latest

RUN apt update && apt upgrade -y

COPY ./passoire /passoire

COPY ./config/apache2.conf /etc/apache2/apache2.conf

RUN chown -R passoire /passoire

CMD ["/passoire/init.sh"]
