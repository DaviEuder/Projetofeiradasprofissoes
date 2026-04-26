FROM php:8.2-apache

# 1. Instala bibliotecas de desenvolvimento do PostgreSQL

RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Instala as extensões PHP (cria os arquivos .so)
RUN docker-php-ext-install pdo pdo_pgsql

# 3. ATIVA as extensões para garantir que elas sejam carregadas no runtime.
RUN docker-php-ext-enable pdo pdo_pgsql

# 4. Copia o código da sua aplicação para o diretório web do Apache
COPY . /var/www/html/
