# Imagem customizada do MySQL que já embute o banco.sql com permissões corretas
FROM mysql:8.0
COPY banco.sql /docker-entrypoint-initdb.d/banco.sql
RUN chmod 644 /docker-entrypoint-initdb.d/banco.sql
