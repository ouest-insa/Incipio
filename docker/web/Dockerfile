FROM php:7.1-apache

# PHP extensions
ENV APCU_VERSION 5.1.11
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN buildDeps=" \
        libicu-dev \
        zlib1g-dev \
    " \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        $buildDeps \
        libicu57 \
        zlib1g \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install \
        intl \
        mbstring \
        pdo_mysql \
        zip \
    && apt-get purge -y --auto-remove $buildDeps
RUN pecl install \
        apcu-$APCU_VERSION \
    && docker-php-ext-enable --ini-name 05-opcache.ini \
        opcache \
    && docker-php-ext-enable --ini-name 20-apcu.ini \
        apcu

# Apache config
RUN a2enmod rewrite headers
ADD docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# PHP config
ADD docker/php/php.ini /usr/local/etc/php/php.ini

# Install Git
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git

# Add the application
ADD . /app
WORKDIR /app

# Install composer
RUN ./docker/composer.sh \
    && mv composer.phar /usr/bin/composer \
    && composer global require "hirak/prestissimo:^0.3"

RUN \
    # Remove var directory if it's accidentally included
    (rm -rf var || true) \
    # Create the var sub-directories
    && mkdir -p var/cache var/logs var/sessions \
    # Install dependencies
    && composer install --optimize-autoloader --no-scripts \
    # Fixes permissions issues in non-dev mode
    && chown -R www-data . var/cache var/logs var/sessions

#Install phantomjs
RUN apt-get install -y  bzip2 fontconfig wget \
    && wget https://raw.github.com/n7consulting/phantomjs-build/master/phantomjs-2.1.1-linux-x86_64.tar.bz2 \
    && tar xvjf phantomjs-2.1.1-linux-x86_64.tar.bz2 \
    && mv phantomjs-2.1.1-linux-x86_64 /usr/local/bin \
    && ln -sf /usr/local/bin/phantomjs-2.1.1-linux-x86_64/bin/phantomjs /usr/local/bin \
    && phantomjs --version \
    && apt-get -y autoremove wget bzip2 \
    && rm -rf /var/lib/apt/lists/*

CMD ["/app/docker/start.sh"]
