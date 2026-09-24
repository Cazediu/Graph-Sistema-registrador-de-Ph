# Usa uma imagem oficial do PHP com servidor Apache embutido
FROM php:8.2-apache

# Instala e habilita a extensão mysqli, necessária para o PHP falar com o banco de dados
RUN docker-php-ext-install mysqli
RUN docker-php-ext-enable mysqli

# Habilita módulo do Apache que pode ser útil para URLs (mod_rewrite)
RUN a2enmod rewrite

# Permite que o .htaccess funcione (AllowOverride All) e define ServerName
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copia os arquivos da raiz do projeto (como configurado no context do compose) para o servidor
COPY . /var/www/html/

# Ajusta as permissões de pastas para garantir que o Apache possa ler os arquivos
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

