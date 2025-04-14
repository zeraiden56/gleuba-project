# Etapa de build
FROM node:20 AS build
WORKDIR /app
COPY . .
RUN npm install && npm run build --prod

# Etapa de deploy
FROM nginx:alpine
COPY --from=build /app/dist/ibge-dashboard /usr/share/nginx/html
EXPOSE 80
