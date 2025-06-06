FROM python:3.11


ADD requirements.txt /install/requirements.txt
WORKDIR /install

RUN pip install -r requirements.txt

#RUN pip install git+https://github.com/dynostore/D-rex.git

ADD app /app
WORKDIR /app


#run service
EXPOSE 5000
CMD ["python", "main.py"]