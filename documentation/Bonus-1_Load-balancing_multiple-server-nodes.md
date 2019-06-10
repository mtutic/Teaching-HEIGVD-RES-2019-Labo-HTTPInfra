# Bonus: Management UI

## Description

In this additional step we have to extend the reverse proxy configuration to support **load balancing**. The idea is that the load balancer distributes HTTP requests between multiple static server nodes and multiple dynamic server nodes.

All sources of this part of the lab can be found in [docker-images/apache-reverse-proxy/](../docker-images/apache-reverse-proxy/) folder.

## Implementation

### Dockerfile

To extend the reverse proxy to support **load balancing** we have added `proxy_balancer` and `lbmethod_byrequests` modules:

```dockerfile
FROM php:7.2-apache

RUN apt-get update && \
  apt-get install -y vim

# Copy custom version of apache2-foreground script
COPY apache2-foreground /usr/local/bin/

COPY templates/ /var/apache2/templates
COPY conf/ /etc/apache2

RUN a2enmod proxy proxy_http proxy_balancer lbmethod_byrequests
RUN a2ensite 000-* 001-*
```

### config_load_balancer.php

We created a new php script based on `config_template.php` done in step 5 of the lab. In this case, we will have two **static** server nodes and two **dynamic** server nodes. 
So, our php script must handle four ip addresses (for the four server nodes). We edited apache config and added `Proxy` tags:

```php
<?php
	$dynamic_app_1 = getenv('DYNAMIC_APP_1');
	$dynamic_app_2 = getenv('DYNAMIC_APP_2');
	$static_app_1 = getenv('STATIC_APP_1');
	$static_app_2 = getenv('STATIC_APP_2');
?>
<VirtualHost *:80>
	ServerName demo.res.ch

	<Proxy "balancer://joke">
		BalancerMember 'http://<?php echo "$dynamic_app_1" ?>/'
		BalancerMember 'http://<?php echo "$dynamic_app_2" ?>/'
	</Proxy>

	<Proxy "balancer://static">
		BalancerMember 'http://<?php echo "$static_app_1" ?>/'
		BalancerMember 'http://<?php echo "$static_app_2" ?>/'
	</Proxy>

	ProxyPass '/api/joke/' 'balancer://joke'
	ProxyPassReverse '/api/joke/' 'balancer://joke'
	
	ProxyPass '/' 'balancer://static'
	ProxyPassReverse '/' 'balancer://static'
</VirtualHost>
```

### apache2-foreground

Now that we have created a new php script, we have to edit the `apache2-foreground` script and ask him to execute `config-load-balancer.php` script:

```bash
# Add setup for RES lab
echo "Setup for the RES lab..."
echo "Static app URL 1: $STATIC_APP_1"
echo "Static app URL 2: $STATIC_APP_2"
echo "Dynamic app URL 1: $DYNAMIC_APP_1"
echo "Dynamic app URL 2: $DYNAMIC_APP_2"
# Call our php script and add result to 001-reverse-proxy.conf file
php /var/apache2/templates/config-load-balancer.php > /etc/apache2/sites-available/001-reverse-proxy.conf
```

### dynamic server node

To know which dynamic server node will send a joke, we have added some logs in [this file](../docker-images/express-image/src/index.js):

```javascript
app.get('/', (req, res) => {
  console.log('sending a joke');  // Added line
  res.send(oneLinerJoke.getRandomJoke());
});
```

## Usage

First of all, create two static server nodes with:
* `docker run -d --name apache_static_1 res/apache_php`
* `docker run -d --name apache_static_2 res/apache_php`

Then, build the new image with `docker build -t res/express_joke .` (in `docker-images/express-image` folder) and run two dynamic server nodes with:
* `docker run -d --name express_dynamic_1 res/express_joke`
* `docker run -d --name express_dynamic_2 res/express_joke`

Get all ip addresses of this four containers with `docker inspect <container_name> | grep -i ipaddr`.

Then build the new image with `docker build -t res/apache_rp .` (in `docker-images/apache-reverse-proxy` folder) and run the reverse proxy container with this command:
* `docker run -d -e STATIC_APP_1=<static_node_1_ip>:80 -e STATIC_APP_2=<static_node_2_ip>:80 -e DYNAMIC_APP_1=<dynamic_node_1_ip>:3000 -e DYNAMIC_APP_2=<dynamic_node_2_ip>:3000 --name apache_rp -p 8080:80 res/apache_rp`

To verify that the load balancer works, we can log the two dynamic server nodes with `docker logs -f <container_name>` into two different terminals. We can see that both of these nodes prints `sending a new joke` on terminal. It means that the load balancer works properly.

We can check that the load balancer works with static server nodes as well by killing one with `docker kill apache_static_1`. Our website is still accessible at `http://demo.res.ch:8080` (even though it may take some time).