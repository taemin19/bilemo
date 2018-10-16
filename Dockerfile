ARG PHP_VERSION=7.2.8

# Development build
FROM php:${PHP_VERSION}-fpm-alpine as base

ENV WORKPATH /var/www/bilemo
ENV COMPOSER_ALLOW_SUPERUSER 1

# System requirements / PHP extensions
RUN set -eux \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        curl \
	    git \
		icu-dev \
		make \
		zlib-dev \
    && docker-php-ext-install \
        intl \
        json \
        opcache \
        pdo_mysql \
        zip \
    && pecl install \
        apcu \
        redis \
    && pecl clear-cache \
    && docker-php-ext-enable \
        apcu \
        redis \
    && apk del .build-deps

COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Blackfire (Docker approach) & Blackfire Player
RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/alpine/amd64/$version \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
    && mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
    && mkdir -p /tmp/blackfire \
    && curl -A "Docker" -L https://blackfire.io/api/v1/releases/client/linux_static/amd64 | tar zxp -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
    && rm -Rf /tmp/blackfire

RUN mkdir -p ${WORKPATH}

RUN rm -rf ${WORKDIR}/vendor \
    && ls -l ${WORKDIR}

RUN mkdir -p \
		${WORKDIR}/var/cache \
		${WORKDIR}/var/logs \
		${WORKDIR}/var/sessions \
	&& chown -R www-data ${WORKDIR}/var \
	&& chown -R www-data /tmp/

RUN chown www-data:www-data -R ${WORKPATH}

WORKDIR ${WORKPATH}

COPY . ./

EXPOSE 9000

CMD ["php-fpm"]
