### backend

1 /opt/www/zhongyu/flowers/config/bundles.php
add bundle
```yaml
\App\Acme\BackendBundle\src\AcmeBackendBundle::class => ['all' => true],
```
2 /opt/www/zhongyu/flowers/config/routes.yaml
add backend
```yaml
backend:
  resource: ../src/Acme/BackendBundle/src/Controller/
  type: annotation
```
3  /opt/www/zhongyu/flowers/config/packages/twig.yaml
add tempaltes

```yaml
 paths:
   'templates': ~
   'src/Acme/BackendBundle/templates': ~
```



### doctrine 
- https://symfony.com/doc/current/doctrine.html
- https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/basic-mapping.html
- php bin/console doctrine:database:create
// create entity
1 php bin/console make:entity

// create table sql
2 php bin/console make:migration

// run sql .
3 php bin/console doctrine:migrations:migrate

//create controller
4 php bin/console make:controller ProductController

