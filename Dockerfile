FROM docker.io/bitnami/laravel:9

COPY ./my-project /app

# This IP for DB_HOST is not real
ENV DB_HOST=36.185.164.47
ENV DB_PORT=3306
ENV DB_USERNAME=hut
ENV DB_DATABASE=inventario

EXPOSE 8000
