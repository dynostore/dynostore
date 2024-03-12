FROM postgres:12

ADD ./schema-sql/pub_sub.sql /docker-entrypoint-initdb.d/pub_sub.sql

VOLUME psql-pubsub:/var/lib/postgresql/data
