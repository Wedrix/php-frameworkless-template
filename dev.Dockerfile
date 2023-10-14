FROM ubuntu:22.04

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update -yqq && apt-get install -yqq \
	software-properties-common build-essential 

RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php > /dev/null
RUN apt-get update -yqq && apt-get install -yqq \
    php8.2-dev php8.2-curl php8.2-pgsql composer php-pear pkg-config libevent-dev librabbitmq-dev uuid-dev curl

RUN printf "\n" | curl 'https://pecl.php.net/get/event-3.0.8.tgz' -o event-3.0.8.tgz && \
    pecl install event-3.0.8.tgz && \
    rm -rf event-3.0.8.tgz && \
    rm -rf /tmp/pear && \
    echo "extension=event.so" > /etc/php/8.2/cli/conf.d/event.ini

RUN printf "\n" | curl 'https://pecl.php.net/get/redis-5.3.7.tgz' -o redis-5.3.7.tgz && \
    pecl install redis-5.3.7.tgz && \
    rm -rf redis-5.3.7.tgz && \
    rm -rf /tmp/pear && \
    echo "extension=redis.so" > /etc/php/8.2/cli/conf.d/redis.ini

RUN printf "\n" | curl 'https://pecl.php.net/get/uuid-1.2.0.tgz' -o uuid-1.2.0.tgz && \
    pecl install uuid-1.2.0.tgz && \
    rm -rf uuid-1.2.0.tgz && \
    rm -rf /tmp/pear && \
    echo "extension=uuid.so" > /etc/php/8.2/cli/conf.d/uuid.ini

RUN printf "\n" | curl 'https://pecl.php.net/get/amqp-2.0.0.tgz' -o amqp-2.0.0.tgz && \
    pecl install amqp-2.0.0.tgz && \
    rm -rf amqp-2.0.0.tgz && \
    rm -rf /tmp/pear && \
    echo "extension=amqp.so" > /etc/php/8.2/cli/conf.d/amqp.ini

COPY php.ini /etc/php/8.2/cli/php.ini

WORKDIR /var/www

CMD php bin/server start