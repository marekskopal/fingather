FROM node:18-alpine as build
WORKDIR /app

RUN npm install -g @angular/cli

COPY ./package.json .
RUN npm install
COPY . .
RUN npm run build-prod

FROM nginx as runtime
COPY --from=build /app/dist/fingather /usr/share/nginx/html
COPY ["./conf/default.conf","/etc/nginx/conf.d/default.conf"]
