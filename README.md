# Flower reservation system . simple
0 composer install
1 vim .env set db
2 php bin/console doctrine:database:create (create db)
3 php bin/console make:migration (init sql)
4 php bin/console doctrine:migrations:migrate(run sql)
5 cd public && php -S 0.0.0.0:8080 (set server)
6 backend localhost:8080;frontend localhost:8080/auth/login



Learn symfony by 2022.04 
