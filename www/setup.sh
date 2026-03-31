#!/bin/bash
# setup-min.sh - Versão minimalista

docker-compose down -v
docker-compose up -d
sleep 20

docker exec pixbuy_db mysql -uroot -proot123 -e "CREATE TABLE pixbuy_db.usuarios (id INT AUTO_INCREMENT PRIMARY KEY, usuario VARCHAR(100) UNIQUE, senha VARCHAR(255));"
docker exec pixbuy_db mysql -uroot -proot123 -e "CREATE TABLE pixbuy_db.config_pix (id INT AUTO_INCREMENT PRIMARY KEY, chave_pix VARCHAR(255), nome_titular VARCHAR(255), cidade VARCHAR(255));"

docker exec pixbuy_db mysql -uroot -proot123 -e "INSERT INTO pixbuy_db.usuarios (usuario, senha) VALUES ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');"
docker exec pixbuy_db mysql -uroot -proot123 -e "INSERT INTO pixbuy_db.config_pix (chave_pix, nome_titular, cidade) VALUES ('+5599991313341', 'JO B PIMENTEL', 'CODO');"

echo "✅ Pronto! Acesse: http://localhost:8080/admin/login.php (admin/admin123)"