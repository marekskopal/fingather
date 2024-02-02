FROM node:20.11.0-alpine as build
WORKDIR /app

RUN npm install -g pnpm
RUN npm install -g @angular/cli

COPY ./package.json ./pnpm-lock.yaml ./
RUN pnpm install
COPY ./src /app/src
COPY ./angular.json ./tsconfig.app.json ./tsconfig.json ./tsconfig.spec.json ./
RUN npm run build-prod

FROM nginx:1.25.3-alpine as runtime
COPY --from=build /app/dist/fingather /usr/share/nginx/html
COPY ["./conf/default.conf","/etc/nginx/conf.d/default.conf"]
