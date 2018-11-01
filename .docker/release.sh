#!/usr/bin/env bash
##
# Build and push images to Dockerhub.
#

# Namespace for the image.
DOCKERHUB_NAMESPACE=${DOCKERHUB_NAMESPACE:-govcms8}
# Docker image edge tag.
IMAGE_TAG_EDGE=${IMAGE_TAG_EDGE:-beta}

# Path prefix to Dockerfiles extension that is used as a name of the service.
FILE_EXTENSION_PREFIX=${FILE_EXTENSION_PREFIX:-.docker/Dockerfile.}

for file in $(echo $FILE_EXTENSION_PREFIX"*"); do
    service=${file/$FILE_EXTENSION_PREFIX/}

    echo "==> Releasing \"$service\" image for service \"$DOCKERHUB_NAMESPACE/$service\""
    docker pull $DOCKERHUB_NAMESPACE/$service:$IMAGE_TAG_EDGE
    docker tag $DOCKERHUB_NAMESPACE/$service:$IMAGE_TAG_EDGE $DOCKERHUB_NAMESPACE/$service:latest

    echo "==> Tagging and pushing \"$service\" image to $DOCKERHUB_NAMESPACE/$service:latest"
    docker push $DOCKERHUB_NAMESPACE/$service:latest
done
