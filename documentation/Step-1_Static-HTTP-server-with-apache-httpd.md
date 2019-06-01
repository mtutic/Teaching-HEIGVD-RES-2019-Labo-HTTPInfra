# Step 1: Static HTTP server with apache httpd

## Description

The aim of this step is to set up a web server in a Docker container. The idea is to quickly implement a **static** website using free templates that can be found on the internet. In this case, we used a bootstrap template.

All sources of this part of the lab can be found in [docker-images/apache-php-image/](../docker-images/apache-php-image/) folder.

## Implementation

Dockerfile: 

```
FROM php:7.2-apache

COPY content/ /var/www/html/
```

To build the web server, we used the `php:7.2-apache` base image to assemble our own image. Then we copied the contents of the `content/` folder into `/var/www/html` folder of the container filesystem (DocumentRoot configured in Apache settings). We have kept default settings of the Apache server but we can edit them in the `/etc/apache2/` directory of the container filesystem.

## Usage

First of all, we have to build the docker image by running `docker build -t res/apache_php .`. Then we built a container by running `docker run -d -p 8080:80 res/apache_php`.

Finally we can access our website from a browser at `http://localhost:8080`.