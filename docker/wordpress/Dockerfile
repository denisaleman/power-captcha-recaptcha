FROM wordpress:6.5.3-php8.1-apache

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN if [ "$USER_ID" != "33" ] || [ "$GROUP_ID" != "33" ]; then \
    usermod -u ${USER_ID} www-data && \
    groupmod -g ${GROUP_ID} www-data && \
    chown -R www-data:www-data /var/www; \
fi