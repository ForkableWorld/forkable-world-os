FROM curedao/php-8.1-apache-buster-xdebug-opcache-composer-pgsql:latest as base

ARG COMMIT_SHA 
ARG APP_ENV 
ARG BRANCH_NAME 
ARG BUILD_ID 
ARG APP_URL 
ARG UNIT_DOPPLER_TOKEN 
ARG STAGING_DOPPLER_TOKEN 
ARG TEST_FOLDER 
ARG CYPRESS 

ENV APP_ENV=${APP_ENV:-testing}
ENV COMMIT_SHA=${COMMIT_SHA}
ENV BRANCH_NAME=${BRANCH_NAME}
ENV BUILD_ID=${BUILD_ID}
ENV APP_URL=${APP_URL}
ENV STAGING_DOPPLER_TOKEN=${STAGING_DOPPLER_TOKEN}
ENV UNIT_DOPPLER_TOKEN=${UNIT_DOPPLER_TOKEN}
ENV TEST_FOLDER=${TEST_FOLDER}
ENV CYPRESS=${CYPRESS}
#RUN apt-get update && apt-get install -y libfontconfig Moved to base image

COPY --chown=www-data:www-data . .
COPY config/etc/* /etc/
RUN echo "BRANCH_NAME is ${BRANCH_NAME} and APP_ENV is ${APP_ENV}"
RUN echo "COMMIT_SHA is ${COMMIT_SHA}"
COPY start.sh /usr/local/bin/start
RUN chmod a+x /usr/local/bin/start
RUN cd /var/www/html/ && chmod a+x deploy.sh && ./deploy.sh

CMD ["/usr/local/bin/start"]
