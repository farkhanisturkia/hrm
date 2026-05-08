FROM php:8.3-cli

LABEL maintainer="Taylor Otwell"

ARG WWWGROUP
ARG NODE_VERSION=20

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/local/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"

# timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip supervisor sqlite3 \
    libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev \
    libpq-dev dnsutils librsvg2-bin fswatch ffmpeg nano \
    && docker-php-ext-install \
        pdo_mysql mbstring exif pcntl bcmath gd intl zip pdo_pgsql \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug

# composer
RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

# nodejs
RUN curl -fsSL https://deb.nodesource.com/setup_$NODE_VERSION.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm pnpm bun

# database clients
RUN apt-get install -y default-mysql-client postgresql-client

# cleanup
RUN apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# user
RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

# git safe dir
RUN git config --global --add safe.directory /var/www/html

# copy config
COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /usr/local/etc/php/conf.d/99-sail.ini

RUN chmod +x /usr/local/bin/start-container

EXPOSE 80

ENTRYPOINT ["start-container"]