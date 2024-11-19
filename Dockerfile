FROM nharrand/passoire:latest

COPY ./passoire /passoire

CMD ["/passoire/init.sh"]
