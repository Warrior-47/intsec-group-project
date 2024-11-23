FROM nharrand/passoire:latest

RUN apt update && apt upgrade -y

COPY ./passoire /passoire

RUN chown -R passoire /passoire

CMD ["/passoire/init.sh"]
