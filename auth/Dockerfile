FROM ddomizzi/apache:php

# ARG DEBIAN_FRONTEND=noninteractive

# RUN apt-get update && apt-get install -y \
# 	mailutils \
# 	ssmtp \
# 	&& apt-get clean

# COPY ./configs/ssmtp.conf /etc/ssmtp/ssmtp.conf
# COPY ./configs/revaliases /etc/ssmtp/revaliases
# COPY ./configs/php.ini /etc/php/7.0/apache2/php.ini

# RUN chmod 555 /etc/ssmtp/ssmtp.conf
# RUN chown root:mail /etc/ssmtp/ssmtp.conf
# RUN a2enmod headers
# RUN service apache2 restart
#RUN echo "127.0.0.1 noreply.domain.com $(hostname)" >> /etc/hosts
#RUN echo 'sendmail_path = "/usr/sbin/ssmtp -t"' > /usr/local/etc/php/conf.d/mail.ini

#RUN chmod 666 /var/www/html/log/skycds.log

COPY ./auth/ /var/www/html/
