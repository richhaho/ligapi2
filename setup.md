1)
git clone git@bitbucket.org:steffengrell/ligapi2.git
2)
composer install
3) 
update .env database connection
4) Generate DB
symfony php bin/console d:d:c
5)
bin/console doctrine:migrations:migrate
6)
mkdir config/jwt
7)
openssl genrsa -out config/jwt/private.pem -aes256 4096
8)
Enter passphrase from .env
9)
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
10)
Enter passphrase from .env

**Addditional setup if needed**

A) Set directory permissions with setfacl:
https://symfony.com/doc/current/setup/file_permissions.html
B) Load fixtures
bin/console doctrine:fixtures:load
C) Prepare tests with
APP_ENV=test bin/console app:phpunit
