FROM php:8.2.0-apache

#update
RUN apt-get update && apt-get upgrade -y
