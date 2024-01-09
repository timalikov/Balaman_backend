FROM php:8.2-fpm as php


RUN apt-get update && apt-get install -y --no-install-recommends apt-utils \
\
# ZIP/Bzip/Archive
            libbz2-dev \
                zlib1g-dev \
                libzip-dev \
                zip unzip \
\
# IMAGES Imagick/Gd Dependencies
            libjpeg-dev \
		    libjpeg62-turbo-dev \
            libfreetype6-dev \
            libwebp-dev \
            libpng-dev \
            libmagickwand-dev \
            libmagickcore-dev \
\
#PostgreSQL DependencyLib
        libpq-dev \
\
# Another PHP Default Dependencies
        libmcrypt-dev \
        libxml++2.6-dev \
        libcurl3-dev \
        libxpm-dev \
\
# ******** Configure
# Configure GD
    && docker-php-ext-configure gd \
                 --with-jpeg \
                 --with-freetype \
	&& docker-php-ext-install gd \
\
#Configure Imagick
    && pecl install imagick \
    && docker-php-ext-enable imagick \
\
#Configure ZIP
    && docker-php-ext-configure zip \
	&& docker-php-ext-install zip \
\
#Configure Another Dependencies
	&& docker-php-ext-install \
	        curl \
	        xml\
	        sockets \
	        intl \
	        bcmath \
	        exif \
\
	        pdo \
	        pdo_pgsql \
	        pgsql \
	        mysqli \
	        pdo_mysql\
\
    && docker-php-ext-install soap \
# PCNTL Library Ifyou Need
#    && docker-php-ext-install pcntl \
\
        \
            &&  userdel -f www-data &&\
                if getent group www-data ; then groupdel www-data; fi &&\
                groupadd -g 1000 www-data &&\
                useradd -l -u 1000 -g www-data www-data &&\
                install -d -m 0755 -o www-data -g www-data /home/www-data &&\
                chown --changes --silent --no-dereference --recursive \
                      --from=33:33 1000:1000 \
                    /home/www-data \
        \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/* /var/cache/apk/*

# RUN su - www-data -c "composer global require hirak/prestissimo"

# ENV PATH=${PATH}:/home/www-data/.composer/vendor/bin

WORKDIR /var/www
COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


FROM php-base

# Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY ./php.ini /usr/local/etc/php/conf.d/php.ini

RUN apt-get update -y && \
    apt-get install -y openssh-client && \
    mkdir -p /home/www-data/.ssh && \
    chmod 777 /home/www-data/.ssh

RUN apt-get install -y jpegoptim optipng pngquant

RUN curl -LO https://deployer.org/releases/v6.4.7/deployer.phar && \
    mv deployer.phar /usr/local/bin/dep && \
    chmod +x /usr/local/bin/dep

WORKDIR /var/www

# USER www-data

ENV PORT=8000
ENTRYPOINT ["docker/entrypoint.sh"]