#!/bin/bash

DOCKER_IMAGE="samuelegarzon/group39_intsec:latest"
GROUP_SECRET="4d852eea1a2dce9ea7f1222f56e3b2e0c9b05224"

echo ""
echo "Building Docker Image with name $DOCKER_IMAGE"
docker build -t $DOCKER_IMAGE .

echo ""
echo "Running Container with name passoire"
docker run -e GROUP_SECRET=$GROUP_SECRET -d --name passoire -p 8080:80 -p 8081:22 -p 3002:3002 -e HOST="localhost" $DOCKER_IMAGE