# Step 5: Dynamic reverse proxy configuration

## Description

The aim of this step is to get rid of hard-coded IP addresses in reverse proxy configuration. We will therefore use the environment variables with `-e` option of Docker `run` command. The idea is to specify ip addresses of static and dynamic web server containers when we are running a new reverse proxy container.

All sources of this part of the lab can be found in [docker-images/apache-reverse-proxy/](../docker-images/apache-reverse-proxy/) folder.

## Implementation

### apache2-foreground

As we saw in step 3 of the lab, our Dockerfile is based on `php:7.2-apache` base image. If we inspect this base image [here](https://github.com/docker-library/php/blob/master/7.2/stretch/apache/Dockerfile), we can see at the end `COPY apache2-foreground /usr/local/bin/` instruction. This means that `apache2-foreground` script (can be found [here](https://github.com/docker-library/php/blob/master/7.2/stretch/apache/apache2-foreground)) will be copied to `/usr/local/bin` folder of the container and will be executed (`CMD ["apache2-foreground"]`).

What we will do is to make our custom `apache2-foreground` script. So we created the `apache2-foreground` file, kept the original content and added those lines:

```bash
# Add setup for RES lab
echo "Setup for the RES lab..."
echo "Static app URL: $STATIC_APP"
echo "Dynamic app URL: $DYNAMIC_APP"
# Call our php script and add result to 001-reverse-proxy.conf file
php /var/apache2/templates/config-template.php > /etc/apache2/sites-available/001-reverse-proxy.conf
```

It will execute `config-template.php` script and the result will be added to `001-reverse-proxy.conf` file. This apache config file is now dynamic because it uses `STATIC_APP` and `DYNAMIC_APP` environment variables. Let's see now how does this php script work.

### config-template

```php
<?php
  $dynamic_app = getenv('DYNAMIC_APP');
  $static_app = getenv('STATIC_APP');
?>
<VirtualHost *:80>
	ServerName demo.res.ch

	ProxyPass '/api/joke/' 'http://<?php echo "$dynamic_app" ?>/'
	ProxyPassReverse '/api/joke/' 'http://<?php echo "$dynamic_app" ?>/'
	
	ProxyPass '/' 'http://<?php echo "$static_app" ?>/'
	ProxyPassReverse '/' 'http://<?php echo "$static_app" ?>/'
</VirtualHost>
```

We can see that there are no more hard-coded ip addresses. They were replaced by `DYNAMIC_APP` and `STATIC_APP` environment variables. Of course, these must be speciefied with `-e` option when we will create the reverse proxy container.

### Dockerfile

```dockerfile
FROM php:7.2-apache

RUN apt-get update && \
  apt-get install -y vim

# Copy custom version of apache2-foreground script
COPY apache2-foreground /usr/local/bin/

COPY templates/ /var/apache2/templates
COPY conf/ /etc/apache2

RUN a2enmod proxy proxy_http
RUN a2ensite 000-* 001-*
```

Two lines were added to the Dockerfile. `COPY apache2-foreground /usr/local/bin/` used to replace the original `apache2-foreground` script and `COPY templates/ /var/apache2/templates` that will copy our php script to docker filesystem. Like we have seen, this php script is executed by our custom `apache2-foreground` script.

## Usage

To be sure to have different ip addresses than the previous steps of the lab, we will create some containers with `docker run -d res/apache_php` and `docker run -d res/express_joke`commands.
Then we will run two containers with a name (for static and dynamic http server) :
* `docker run -d --name apache_static res/apache_php`
* `docker run -d --name express_dynamic res/express_joke`

Now, we have to get ip addresses of these two containers. This can be done with these instructions:
* `docker inspect apache_static | grep -i ipaddr`
* `docker inspect epxress_dynamic | grep -i ipadd`

Finaly, the reverse proxy container is created with:
* `docker run -d -e STATIC_APP=<apache_static_ip>:80 -e DYNAMIC_APP=<express_dynamic_ip>:3000 --name apache_rp -p 8080:80 res/apache_rp`

The website is still accessible at `http://demo.res.ch:8080`.