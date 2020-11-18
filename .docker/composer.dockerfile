FROM composer:2.0.4

RUN mkdir -p ~/.ssh && ssh-keyscan github.com >> ~/.ssh/known_hosts