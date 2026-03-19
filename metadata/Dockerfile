FROM python:3.11-slim

# Arguments defined in docker-compose.yml
ARG user=appuser
ARG uid=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Create system user
RUN useradd -G root -u $uid -m -d /home/$user $user

# Set working directory
WORKDIR /var/www

# Copy the python application files
COPY python_app/ /var/www/

# Install Python requirements
RUN pip install --no-cache-dir -r requirements.txt

# Change permissions to the user
RUN chown -R $user:$user /var/www

USER $user

EXPOSE 80

# Run FastAPI server
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "80"]
