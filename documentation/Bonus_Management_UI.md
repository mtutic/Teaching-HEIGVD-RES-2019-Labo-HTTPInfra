# Bonus: Management UI

## Description

In this additional step we have to offer an User interface that allow administrator to control the Docker environment.

In order to do this, we have choose to work with `Portainer`. This tool is easily installed and really intuitive.

## Implementation

To add Portainer to your workspace you just have to run this commands :

```bash
docker volume create portainer_data
docker run -d -p 9000:9000 -v /var/run/docker.sock:/var/run/docker.sock -v portainer_data:/data portainer/portainer
```

Note that `-v /var/run/docker.sock:/var/run/docker.sock` option can only be used in Linux environment.

More information are available on the [Pontainer website](<https://www.portainer.io/>).

## Usage

To use your Pontainer interface you just have to connect on the port 9000 of your Docker environment (e.g. `localhost:9000` for windows if you use docker desktop).

Than the application will ask you to make an administrator account if you haven't already do it.

![login](/img/login.png)

After you get in the application, you can make the connection to your Docker environment. For that go on the `Local` section and click on "Connect".

![connect](/img/connect.png)

Than you will arrived in the User interface. You can click on the local endpoint to manage your local Docker environment.

![local](/img/local.png)

Then you will see every container and image you have. From this you can restart, stop, duplicate, etc. your them.

![container](/img/container.png)

Pontainer offer a lot of other functions. But for the moment, we just need what is presented here.