# Bonus: Load balancing round-robin vs sticky sessions

## Description

In this additional step we have to adapt our load balancer to distribute HTTP requests in a round-robin fashion to the dynamic server nodes. To accomplish that, we will use the notion of sticky session.

All sources of this part of the lab can be found in [docker-images/apache-reverse-proxy/](../docker-images/apache-reverse-proxy/) folder.

## Implementation

### Dockerfile

To make it possible, we just have to enable an other module of apache2 witch is called `headers`. This module will allow us to set cookies and track the information across the docker nodes.

```dockerfile
FROM php:7.2-apache

RUN apt-get update && \
  apt-get install -y vim

# Copy custom version of apache2-foreground script
COPY apache2-foreground /usr/local/bin/

COPY templates/ /var/apache2/templates
COPY conf/ /etc/apache2

RUN a2enmod proxy proxy_http proxy_balancer lbmethod_byrequests headers
RUN a2ensite 000-* 001-*
```

### config_load_balancer-sticky.php

We created a new php script based on `config_load_balancer.php` done in additional step 1 of the lab. We add the line that start by `Header`. With that, the script is ready to track our information.

```php
<?php
	$dynamic_app_1 = getenv('DYNAMIC_APP_1');
	$dynamic_app_2 = getenv('DYNAMIC_APP_2');
	$static_app_1 = getenv('STATIC_APP_1');
	$static_app_2 = getenv('STATIC_APP_2');
?>
<VirtualHost *:80>
	ServerName demo.res.ch

	Header add Set-Cookie "ROUTEID=.%{BALANCER_WORKER_ROUTE}e; path=/" env=BALANCER_ROUTE_CHANGED
	
	<Proxy "balancer://joke">
		BalancerMember 'http://<?php echo "$dynamic_app_1" ?>'
		BalancerMember 'http://<?php echo "$dynamic_app_2" ?>'
	</Proxy>

	<Proxy "balancer://static">
		BalancerMember 'http://<?php echo "$static_app_1" ?>'
		BalancerMember 'http://<?php echo "$static_app_2" ?>'
	</Proxy>

	ProxyPass '/api/joke/' 'balancer://joke/'
	ProxyPassReverse '/api/joke/' 'balancer://joke/'
	
	ProxyPass '/' 'balancer://static/'
	ProxyPassReverse '/' 'balancer://static/'
</VirtualHost>
```

### apache2-foreground

Now that we have created a new php script, we have to edit the `apache2-foreground` script and ask him to execute `config-load-balancer-sticky.php` script:

```bash
# Add setup for RES lab
echo "Setup for the RES lab..."
echo "Static app URL 1: $STATIC_APP_1"
echo "Static app URL 2: $STATIC_APP_2"
echo "Dynamic app URL 1: $DYNAMIC_APP_1"
echo "Dynamic app URL 2: $DYNAMIC_APP_2"
# Call our php script and add result to 001-reverse-proxy.conf file
# !!! BE CAREFUL This line must be adapted acording to the step of the lab !!!
php /var/apache2/templates/config-load-balancer-sticky.php > /etc/apache2/sites-available/001-reverse-proxy.conf
```

## Usage

Be aware that you have to rebuild the reverse proxy image and restart a new container.

To make the test, you can open a browser and go to `demo.res.ch:8080`. Now you can open the logs of the static containers and see that only one container responded to the request. But if you open the dynamic container's logs, you will see that the load balancing work.

Second test, you can open more tab of your browser and go on the web site. Here again, only one server has accept the requests.

Last test, open a second window and go on the web site. You will now see that the other server works.