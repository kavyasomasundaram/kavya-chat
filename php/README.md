# Kavya Chat

A chat application using PHP and MySQL.

## Setup

1. Create a `.env` file in the root directory:
DB_HOST=your_db_host
DB_USER=your_db_user
DB_PASS=your_db_pass
DB_NAME=your_db_name
DB_PORT=your_db_port


2. Make sure `.env` and `php/certs/` are in `.gitignore`.

3. Docker setup:

```bash
docker build -t kavya-chat .
docker run -p 80:80 kavya-chat
## SSL Certificate

Place your `ca.pem` file in the `php/certs/` directory. Ensure this folder is ignored by Git.
