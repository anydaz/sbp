FROM postgres:10-alpine
ENV POSTGRES_USER andy
ENV POSTGRES_PASSWORD 1234
ENV POSTGRES_DB sistemtoko
COPY .docker/init.sql /docker-entrypoint-initdb.d/