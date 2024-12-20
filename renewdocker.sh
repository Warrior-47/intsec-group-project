#!/bin/bash

docker stop $(docker ps -aq) && docker rm $(docker ps -aq)
# docker rmi $(docker images -q)

rm -rf d_files 2> /dev/null
rm encrypted 2> /dev/null

if [ $# -ne 0 ]; then
    docker run -d --name attack -e HOST="localhost" -p 8080:80 -p 3002:3002 -p 8081:22 $1
fi