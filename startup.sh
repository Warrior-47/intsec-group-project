#!/bin/bash

# The point of this script is to give a convenient way to the rest of the group to start the project container
# without having to worry about the details of the Docker commands. This is done to save time and avoid errors
# and focus more on the project itself.

DOCKER_IMAGE="samuelegarzon/group39_intsec:latest"
GROUP_SECRET="<secret_sent_by_prof>"
CONTAINER_NAME="passoire"

echo -e "\nChecking if container already exists..."
container_id=$(docker ps -aqf "name=^${CONTAINER_NAME}$")

if [ $container_id != "" ]; then
    echo -e "\nContainer exists. Stopping and removing container..."
    docker stop $CONTAINER_NAME && docker rm $CONTAINER_NAME

    echo -e "\nRemoving existing image..."
    docker rmi $DOCKER_IMAGE

    echo -e "\nContainer and image removed."
else
    echo -e "Container does not exist."
fi

echo -e "\nBuilding docker image with name $DOCKER_IMAGE"
docker build -t $DOCKER_IMAGE .

if [[ $? -eq 0 ]]; then
    echo -e "\nRunning container with name $CONTAINER_NAME"
    docker run -e GROUP_SECRET=$GROUP_SECRET -d --name $CONTAINER_NAME -p 8080:80 -p 8081:22 -p 3002:3002 -e HOST="localhost" $DOCKER_IMAGE
    echo -e "\nContainer is up and running."
else
    echo -e "\nFailed to build image. See error above."
fi