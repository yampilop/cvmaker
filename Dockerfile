# Filename: Dockerfile
FROM php:apache
MAINTAINER Juan Pablo Martí <yampilop@gmail.com>

# Expose ports
EXPOSE 80

# Copy files
WORKDIR /var/www/html
COPY html ./

VOLUME /var/www/html/data
