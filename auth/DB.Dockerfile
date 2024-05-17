FROM postgres:12

ADD ./configure/ /configure/
RUN apt-get update 
RUN apt-get install -y python3 python3-dev
RUN apt-get install -y build-essential
RUN apt-get install -y libpq-dev libxml2-dev libxslt1-dev libldap2-dev libsasl2-dev libffi-dev libjpeg-dev zlib1g-dev
RUN apt-get install -y python3-pip
RUN pip3 install psycopg2 --break-system-packages

ADD ./schema-sql/auth.sql /docker-entrypoint-initdb.d/auth.sql
VOLUME psql-auth:/var/lib/postgresql/data
