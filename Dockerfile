FROM nharrand/passoire:latest

RUN apt update && apt upgrade -y

COPY ./passoire /passoire

RUN chmod 777 /passoire/web/uploads

CMD ["/passoire/init.sh"]
