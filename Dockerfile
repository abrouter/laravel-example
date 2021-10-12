FROM webdevops/php-nginx:8.0	 as laravel-example

ARG BUILD_ID=0
ARG VERSION=0.0.1
ARG CONSUL_TEMPLATE_VERSION=0.19.5

ENV BUILD_ID=${BUILD_ID} \
    APPLICATION_VERSION=${VERSION} \
    DEBIAN_FRONTEND=noninteractive

COPY . /app

ADD 	 https://releases.hashicorp.com/consul-template/${CONSUL_TEMPLATE_VERSION}/consul-template_${CONSUL_TEMPLATE_VERSION}_linux_amd64.zip /usr/bin/
RUN 	 unzip /usr/bin/consul-template_${CONSUL_TEMPLATE_VERSION}_linux_amd64.zip && \
    	 mv consul-template /usr/local/bin/consul-template && \
    	 rm -rf /usr/bin/consul-template_${CONSUL_TEMPLATE_VERSION}_linux_amd64.zip

RUN mkdir -p /entrypoint.d \
    && cp -R /app/docker/provision/entrypoint.d/* /entrypoint.d/ \
#    && cp -R /app/docker/config/supervisor/conf.d/* /opt/docker/etc/supervisor.d/ \
    && mkdir -p /opt/docker/etc/php/7.1/conf.d \
    && cp /app/docker/config/php/app.ini /opt/docker/etc/php/7.1/conf.d/app.ini \
    && cp /app/docker/config/nginx/sites-enabled/app1.conf /opt/docker/etc/nginx/vhost.conf \
    && cp  /app/docker/config/php/fpm/application.conf /opt/docker/etc/php/fpm/pool.d/application.conf

RUN bash /app/docker/provision/after-build.sh

EXPOSE 80

VOLUME ["/app/storage/logs", "/var/log/nginx", "/var/log/php"]

WORKDIR /app