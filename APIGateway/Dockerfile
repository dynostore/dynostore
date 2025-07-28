FROM python:3.11


ADD requirements.txt /install/requirements.txt
WORKDIR /install

RUN pip install -r requirements.txt

#RUN pip install git+https://github.com/dynostore/D-rex.git

# Install Gunicorn
RUN pip install gunicorn

ADD app /app
WORKDIR /app


# #run service
# EXPOSE 5000
# CMD ["python", "main.py"]

# Expose the port Gunicorn will run on
EXPOSE 80

# Run Gunicorn server
CMD ["gunicorn", "--bind", "0.0.0.0:80", "main:app", "--workers", "4", "--threads", "2", "--access-logfile", "-", "--error-logfile", "-", "--log-level", "info"]
