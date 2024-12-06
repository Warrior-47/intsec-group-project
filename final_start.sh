#!/bin/bash

DOCKER_IMAGE="samuelegarzon/group39_intsec:latest"
CONTAINER_NAME="passoire-final"

HTTP_PORT=8080
SSH_PORT=8081
CRYPTO_PORT=3002

echo "Removing existing container and image..."
docker stop passoire && docker rm passoire
docker stop $CONTAINER_NAME && docker rm $CONTAINER_NAME
docker rmi $DOCKER_IMAGE

echo "Running final image from Dockerhub..."
docker run -d --pull "always" --name $CONTAINER_NAME -p $HTTP_PORT:80 -p $SSH_PORT:22 -p $CRYPTO_PORT:3002 -e HOST="localhost" $DOCKER_IMAGE

echo -e "\nContainer is up and running."
echo "Container name: $CONTAINER_NAME, HTTP port: $HTTP_PORT, SSH port: $SSH_PORT, Crypto port: $CRYPTO_PORT"
echo "To tail container logs run: docker logs -f $CONTAINER_NAME"