FROM openjdk:alpine AS build-environment

ARG ANT_VERSION=1.10.14
ARG ANT_RELEASE=apache-ant-$ANT_VERSION
ARG ANT_BINARY=$ANT_RELEASE-bin.tar.gz
ARG ANT_BINARY_URL=https://dlcdn.apache.org/ant/binaries/$ANT_BINARY

RUN apk update \
    && apk upgrade \
    && apk add wget \
    && apk add git \
    && apk add tar

RUN mkdir -p /opt
RUN wget $ANT_BINARY_URL -P /opt

RUN tar -zxvf /opt/$ANT_BINARY -C /opt
RUN mv /opt/$ANT_RELEASE /opt/ant
RUN rm -f /opt/$ANT_BINARY

ENV ANT_HOME=/opt/ant
ENV PATH="${PATH}:${ANT_HOME}/bin"



FROM build-environment AS build

ARG GIT_REPO_TAG=1.3.1
ARG GIT_REPO_NAME=xml-micro-exchange-php
ARG GIT_REPO_URL=https://github.com/seanox
ARG WORKSPACE=/workspace
ARG RELEASE_NAME=seanox-xmex
ARG BUILD_TIMESTAMP=00000000-00000000

# Should prevent the caching of this layer/stage
RUN echo $BUILD_TIMESTAMP > /tmp/cachebust

WORKDIR $WORKSPACE
RUN git clone --branch $GIT_REPO_TAG $GIT_REPO_URL/$GIT_REPO_NAME.git

# Optionally, the local sources from the host are used if necessary
# COPY . $WORKSPACE/$GIT_REPO_NAME

RUN ant -f $GIT_REPO_NAME/development/build.xml release \
    && mkdir $GIT_REPO_NAME/release/$RELEASE_NAME \
    && unzip $GIT_REPO_NAME/release/$RELEASE_NAME-*.zip -d $GIT_REPO_NAME/release/$RELEASE_NAME



FROM alpine:3 AS runtime

ARG GIT_REPO_NAME=xml-micro-exchange-php
ARG WORKSPACE=/workspace
ARG RELEASE_NAME=seanox-xmex
ARG BUILD_DIR=$WORKSPACE/$GIT_REPO_NAME/release/$RELEASE_NAME
ARG SETUP_DIR=$WORKSPACE/$GIT_REPO_NAME/docker
ARG APPLICATION_DIR=/usr/local/xmex

ENV XMEX_SERVER_NAME="localhost"
ENV XMEX_DEBUG_MODE=""
ENV XMEX_CONTAINER_MODE="on"
ENV XMEX_STORAGE_DIRECTORY=""
ENV XMEX_STORAGE_QUANTITY=""
ENV XMEX_STORAGE_SPACE=""
ENV XMEX_STORAGE_EXPIRATION=""
ENV XMEX_STORAGE_REVISION_TYPE=""
ENV XMEX_URI_XPATH_DELIMITER=""

RUN apk update \
    && apk upgrade \
    && apk add apache2 apache2-utils \
    && apk add logrotate \
    && apk add php83 php83-apache2 php83-xsl php83-simplexml

RUN sed -i "s/^\s*CustomLog/#CustomLog/" /etc/apache2/httpd.conf
RUN echo "IncludeOptional /usr/local/xmex/conf.d/*.conf" >> /etc/apache2/httpd.conf
RUN mkdir -p $APPLICATION_DIR/data
COPY --from=build $SETUP_DIR /
COPY --from=build $BUILD_DIR $APPLICATION_DIR
RUN chown -R apache:apache $APPLICATION_DIR

EXPOSE 80
WORKDIR /usr/local/xmex
ENTRYPOINT [ "/usr/sbin/httpd" ]
CMD [ "-D", "FOREGROUND"]
