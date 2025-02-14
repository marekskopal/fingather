FROM nginx:1.27.4-alpine

RUN \
	# https://tools.ietf.org/html/rfc7919
	# https://github.com/mozilla/ssl-config-generator/blob/master/docs/ffdhe2048.txt
	wget -q https://ssl-config.mozilla.org/ffdhe2048.txt -O /etc/ssl/dhparam.pem

COPY ["./conf/default.conf.template","/etc/nginx/templates/default.conf.template"]