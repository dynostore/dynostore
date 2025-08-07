FROM python:3.11


ADD requirements.txt /install/requirements.txt
WORKDIR /install

RUN pip install -r requirements.txt

#RUN pip install git+https://github.com/dynostore/D-rex.git

ADD app /app
WORKDIR /app


# #run service
# EXPOSE 5000
# CMD ["python", "main.py"]

# Expose the port Gunicorn will run on
EXPOSE 80


CMD ["hypercorn", "main:app", "--bind", "0.0.0.0:80"]
