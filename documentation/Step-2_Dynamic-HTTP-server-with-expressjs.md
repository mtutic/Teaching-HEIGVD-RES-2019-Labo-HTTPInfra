# Step 2: Dynamic HTTP server with express.js

## Description

The aim of this step is to set up a web application in a Docker container. The idea is to quickly implement a **dynamic** application that can give a response to a GET request. In this case, the application generates a random joke.

All sources of this part of the lab can be found in [docker-images/express-image/](../docker-images/express-image/) folder.

## Implementation

Dockerfile: 

```dockerfile
FROM node:10.16

COPY src /opt/app

CMD ["node", "opt/app/index.js"]
```

To build the application, we use the `node:10.16` base image to assemble our own image. Then we copied the contents of the `src/` folder into `/opt/app` folder of the container. Finally, we set the command that will be executed after starting a container with the following line `CMD ["node", "opt/app/index.js"]`.

To help us to generate random jokes, we use the `one-liner-joke` package and the `getRandomJoke()` method.

This application will send a random joke to the client who sends a `GET` request. This joke will be sent in a `JSON` payload which is splited into two parts : `body` that contains the joke and `tags` that contains an array of tags for this joke.

## Usage

First of all, we have to build the docker image by running `docker build -t res/express_joke .`. Then we run a container with `docker run -d -p 9090:3000 res/express_joke`.

Finally we can access our website from a browser at `http://localhost:9090`.