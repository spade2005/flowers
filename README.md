# Flower reservation system . simple
1. composer install
2. vim .env set db
3. php bin/console doctrine:database:create (create db)
4. php bin/console make:migration (init sql)
5. php bin/console doctrine:migrations:migrate(run sql)
6. cd public && php -S 0.0.0.0:8080 (set server)
7. backend localhost:8080;frontend localhost:8080/auth/login



Learn symfony by 2022.04 
