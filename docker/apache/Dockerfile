FROM httpd:2.4-alpine

RUN sed -i \
    -e 's/^#LoadModule proxy_module/LoadModule proxy_module/' \
    -e 's/^#LoadModule proxy_fcgi_module/LoadModule proxy_fcgi_module/' \
    -e 's/^#LoadModule rewrite_module/LoadModule rewrite_module/' \
    /usr/local/apache2/conf/httpd.conf

COPY httpd.conf /usr/local/apache2/conf/extra/wacdo-httpd.conf
COPY vhost.conf /usr/local/apache2/conf/extra/wacdo-vhost.conf

RUN printf '\nInclude conf/extra/wacdo-httpd.conf\nInclude conf/extra/wacdo-vhost.conf\n' >> /usr/local/apache2/conf/httpd.conf

EXPOSE 80
