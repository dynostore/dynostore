FROM python:3.11
ADD code /app
WORKDIR /app
RUN pip install -r requirements.txt

#run service
EXPOSE 5000
CMD ["python", "app.py"]