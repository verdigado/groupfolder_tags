FROM nextcloud

RUN apt-get update && apt-get install -y --no-install-recommends jq

COPY custom-entrypoint.sh /custom-entrypoint.sh
COPY after-install.sh /after-install.sh

RUN chmod +x /custom-entrypoint.sh
RUN chmod +x /after-install.sh

RUN echo "opcache.revalidate_freq=0" > /usr/local/etc/php/conf.d/z-gitpod.ini

ENTRYPOINT ["/custom-entrypoint.sh"]

CMD ["/after-install.sh"]