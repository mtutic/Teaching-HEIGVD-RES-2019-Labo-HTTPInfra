# Step 3: Reverse proxy with apache (static configuration)

## Description

The aim of this step is to set up a reverse proxy in a Docker container. In order to do this, we use Apache web server and its various modules. The purpose of this server is to redirect a request to the correct internal server and 

In this case, we will have three Docker containers :
* Static HTTP server : used in step 1 of the lab.
* Dynamic HTTP server : used in step 2 of the lab.
* Reverse proxy server : configured for this step of the lab.

The reverse proxy has to redirect requests either to the static server or the dynamic server and then ressources are returned to the client by the reverse proxy too.

All sources of this part of the lab can be found in [docker-images/apache-reverse-proxy/](../docker-images/apache-reverse-proxy/) folder.

## Implementation

### Dockerfile

```dockerfile
FROM php:7.2-apache

COPY conf/ /etc/apache2

RUN a2enmod proxy proxy_http
RUN a2ensite 000-* 001-*
```

To build the reverse proxy server, we use the `php:7.2-apache` base image to assemble our own image. Then we copied the contents of the `conf/` folder into `/etc/apache2` folder of the container. Finally, we enable the `proxy` and `proxy_http` modules, needed to use Apache as a reverse proxy, and we enable two sites.

### Sites-available

All sites are defined by config files in the sites-available folder. Here we have two config files :

`000-default.conf`:

```apacheconf
<VirtualHost *:80>
</VirtualHost>
```
 
 This config file is used as a default virtual host. Here we do nothing, we're not returning some content to the client or making some redirections. It's used to make the access to our servers more strict.

 `001-default.conf`:

 ```apacheconf
 <VirtualHost *:80>
	ServerName demo.res.ch

	#ErrorLog ${APACHE_LOG_DIR}/error.log
	#CustomLog ${APACHE_LOG_DIR}/access.log combined

	ProxyPass "/api/joke/" "http://172.17.0.3:3000/"
	ProxyPassReverse "/api/joke/" "http://172.17.0.3:3000/"
	
	ProxyPass "/" "http://172.17.0.2:80/"
	ProxyPassReverse "/" "http://172.17.0.2:80/"
</VirtualHost>
 ```

 First of all, this virtual host defines the server name. It gives the name that the server uses to identify itself. So, in order for the client to have access to the servers, he must access with the `demo.res.ch` address.

 Then we define two reverse proxy rules. The first one is redirecting `/api/joke/` to `http://172.17.0.3:3000/` (corresponds to the dynamic server container that generates a joke). The second one is redirecting `/` to `http://172.17.0.2:80/` (corresponds to the static server container).

 :warning: We can see that ip addresses are hard coded. This is not a good thing. It involves that every time we start containers for static and dynamic http server, we have to be sure that `172.17.0.2` is ip address of the **static** web server container and `172.17.0.3` is the ip address of the **dynamic** web server container.

 Like we said, the client can access to the server from a browser at `demo.res.ch`. This address is added to `Host` header. So, we have to add `127.0.0.1 demo.res.ch` line in client's `/etc/hosts` file.

## Usage

First, we have to run the static and dynamic web server with these commands (order is important):
* `docker run -d --name apache_static res/apache_php`
* `docker run -d --name express_dynamic res/express_joke`

For more details, go back to step 1 and step 2 of the lab. At this point, both of these containers cannot be access by the host because there is no port mapping.

Then, to set up the reverse proxy container, we have to build the docker image by running `docker build -t res/apache_rp .`. After that we run a container with `docker run -d -p 8080:80 res/apache_rp`.

Finally we can access (from a browser) the static web server at `http://demo.res.ch:8080` and the dynamic web server at `http://demo.res.ch:8080/api/joke/`.
