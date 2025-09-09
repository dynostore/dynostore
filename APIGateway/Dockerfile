FROM python:3.12


ADD requirements.txt /install/requirements.txt
WORKDIR /install

RUN pip install -r requirements.txt


ADD app /app
WORKDIR /app


EXPOSE 80


CMD ["hypercorn", "main:app", "--bind", "0.0.0.0:80"]
