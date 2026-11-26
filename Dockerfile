
# ==========================================================
# SISTEMA DE CONTROLE HOSPITALAR
# CONFIGURAÇÃO DE HOSPEDAGEM NO RENDER
# PHP 8.3 + APACHE
# ==========================================================

FROM php:8.3-apache

# Instala extensões necessárias para o banco de dados
RUN apt-get update && apt-get install -y \
    && docker-php-ext-install pdo_mysql mysqli \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Configura a pasta pública do sistema
RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

# Permite o funcionamento do .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' \
    /etc/apache2/apache2.conf

# Configura a porta utilizada pelo Render
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' \
    /etc/apache2/sites-available/000-default.conf

# Copia os arquivos do projeto
COPY . /var/www/html/

# Porta HTTP do Render
EXPOSE 10000

# Inicia o servidor Apache
CMD ["apache2-foreground"]
