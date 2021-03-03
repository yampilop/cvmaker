# Filename: Dockerfile
FROM php:apache
MAINTAINER Juan Pablo Martí <yampilop@gmail.com>

# Environment variables
ENV HTML_PATH /var/www/html

# Expose ports
EXPOSE 80

# Copy files
WORKDIR ${HTML_PATH}
COPY html ./
