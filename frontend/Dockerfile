FROM node:22.17.0-alpine AS build
WORKDIR /app

RUN npm install -g pnpm
RUN npm install -g @angular/cli

COPY ./package.json ./pnpm-lock.yaml ./
RUN pnpm install --reporter=silent
COPY ./src /app/src
COPY ./angular.json ./tsconfig.app.json ./tsconfig.json ./tsconfig.spec.json ./
RUN npm run build-prod

FROM nginx:1.29.0-alpine AS runtime
COPY --from=build /app/dist/fingather/browser /usr/share/nginx/html
COPY ["./conf/default.conf","/etc/nginx/conf.d/default.conf"]
