FROM nginx:1.19.2

RUN apt-get update
COPY ./nginx/pto.conf /etc/nginx/conf.d/default.conf
RUN mkdir -p /etc/nginx/certs/
COPY ./nginx/dev.pto-it.alliedhealthmedia.devlocal-key.pem /etc/nginx/certs
COPY ./nginx/dev.pto-it.alliedhealthmedia.devlocal.pem /etc/nginx/certs