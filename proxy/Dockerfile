FROM nginx:1.29.8-alpine

ARG ADMINER_USER
ARG ADMINER_PASSWORD

RUN \
	# https://tools.ietf.org/html/rfc7919
	# https://github.com/mozilla/ssl-config-generator/blob/master/docs/ffdhe2048.txt
	wget -q https://ssl-config.mozilla.org/ffdhe2048.txt -O /etc/ssl/dhparam.pem

RUN \
	HASH=$(printf '%s' "$ADMINER_PASSWORD" | openssl passwd -apr1 -stdin) && \
	printf '%s:%s\n' "$ADMINER_USER" "$HASH" > /etc/nginx/conf.d/.htpasswd

COPY ["./conf/default.conf.template","/etc/nginx/templates/default.conf.template"]