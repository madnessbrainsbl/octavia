FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    sudo \
    iputils-ping \
    net-tools \
    iproute2 \
    curl \
    wget \
    dos2unix \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install sockets \
    && docker-php-ext-enable sockets \
    && a2enmod rewrite

# Set ServerName to suppress Apache warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configure Apache to listen on port 3000
RUN sed -i 's/Listen 80/Listen 3000/' /etc/apache2/ports.conf && \
    sed -i 's/:80>/:3000>/' /etc/apache2/sites-available/000-default.conf

RUN echo "www-data ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

COPY hst /usr/local/bin/hst
RUN dos2unix /usr/local/bin/hst && \
    chmod +x /usr/local/bin/hst

WORKDIR /var/www/html

# Install bash for stub script
RUN apt-get update && apt-get install -y bash

# Copy toolkit_cli
COPY toolkit_cli /usr/local/bin/toolkit_cli
RUN chmod +x /usr/local/bin/toolkit_cli

# Create stub script for hst CLI
RUN echo '#!/bin/bash' > /usr/local/bin/hst && \
    echo 'case "$1" in' >> /usr/local/bin/hst && \
    echo '  flash)' >> /usr/local/bin/hst && \
    echo '    echo "Firmware flashed to controller $4 with IP $3"' >> /usr/local/bin/hst && \
    echo '    ;;' >> /usr/local/bin/hst && \
    echo '  export)' >> /usr/local/bin/hst && \
    echo '    /usr/local/bin/toolkit_cli export -f json "$2"' >> /usr/local/bin/hst && \
    echo '    ;;' >> /usr/local/bin/hst && \
    echo '  start)' >> /usr/local/bin/hst && \
    echo '    /usr/local/bin/toolkit_cli start "$2"' >> /usr/local/bin/hst && \
    echo '    ;;' >> /usr/local/bin/hst && \
    echo '  stop)' >> /usr/local/bin/hst && \
    echo '    echo "Miner stopped"' >> /usr/local/bin/hst && \
    echo '    ;;' >> /usr/local/bin/hst && \
    echo '  restart)' >> /usr/local/bin/hst && \
    echo '    echo "Miner restarted"' >> /usr/local/bin/hst && \
    echo '    ;;' >> /usr/local/bin/hst && \
    echo '  *)' >> /usr/local/bin/hst && \
    echo '    echo "Usage: hst [start|stop|restart|flash|export]"' >> /usr/local/bin/hst && \
    echo '    ;;' >> /usr/local/bin/hst && \
    echo 'esac' >> /usr/local/bin/hst && \
    chmod +x /usr/local/bin/hst

# Copy project files into Apache docroot
COPY . /var/www/html

# Increase PHP upload limits
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
 && sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/' /usr/local/etc/php/php.ini \
 && sed -i 's/post_max_size = .*/post_max_size = 50M/' /usr/local/etc/php/php.ini

EXPOSE 3000
CMD ["apache2-foreground"] 