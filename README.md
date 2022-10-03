# Contenedorización y despliegue en Kubernetes de una API en Laravel

Veremos cómo crear una API en Laravel usando un entorno de desarrollo en Docker. El entorno estará formado por el framework Laravel y una base de datos MySQL. A continuación veremos cómo empaquetarla en una imagen Docker, que subiremos a Docker Hub. Por último, la desplegaremos en un cluster de Kubernetes y veremos cómo realizar una actualización.

## Endpoints de la API

* `GET api/product`
* `GET api/product/{id}`
* `POST api/product`
* `PUT api/product/{id}`
* `DELETE api/product/{id}`

## Modelo

* `id: integer (autoincrement)`
* `barcode: string`
* `product: string`
* `description: string`
* `stock: integer`
* `price: float`
* `timestamps`

## Preparación del entorno

Bitnami propociona un [`docker-compose.yml`](https://github.com/bitnami/bitnami-docker-laravel/blob/master/docker-compose.yml) para desplegar MariaDB y Laravel.
   
   Lanzar con `docker-compose up -d`. 

Esto crea:
   * Dos servicios: 
     * `mariadb`: Servicio MariaDB
     * `myapp`: Servicio Laravel
   * Un proyecto nuevo denominado `my-project`

Para este tutorial usaremos un [`docker-compose.yml`](https://github.com/ualmtorres/laravel-inventory-api/blob/master/docker-compose.yml) adaptado para trabajar con variables de entorno en un archivo [`.env`](https://github.com/ualmtorres/laravel-inventory-api/blob/master/.env).

> **NOTA**
> Excluiremos el `.env` del control de versiones del [`.gitignore`](https://gist.github.com/ualmtorres/9d97317b97afaa188cc52d6d08084ef5) y usaremos en su puesto un [`.env.example`](https://gist.github.com/ualmtorres/218d175806ac8bdc1b1304cf0e9f4a13) para que de las indicaciones de configuración.


## Ejecución de comandos Laravel

Ejecutamos los comandos Laravel a través del servicio Laravel del `docker compose`. En nuestro ejemplo el servicio Laravel se denomina `myapp`

* Comando `php artisan`:
    
    `docker-compose exec myapp php artisan …`
* Comando `composer`:
  
    `docker-compose exec myapp composer …`

## Preparación de la base de datos

* Creación de una migración para una tabla `Productos` con el comando siguiente: 

    `docker-compose exec myapp php artisan make:migration create_products_table`
* Configurar la [migración de la tabla de productos](https://gist.github.com/ualmtorres/d440a496d3562d0e92a34727cb78c228) en `<proyecto>/database/migrations`
* Ejecutar la migración con el comando siguiente:

    `docker-compose exec myapp php artisan migrate`

## Preparación del modelo y el controlador

* Crear modelo y controlador

    `docker-compose exec myapp php artisan make:model Product -c`

* Programar el [controlador](https://gist.github.com/ualmtorres/2c92fe219534f50701358b3b38683092)  en `<proyecto>/app/Http/Controllers`
* Programar el [modelo](https://gist.github.com/ualmtorres/0df1575c095330736bb519f2eb874173) en `<proyecto>/app/Models`
* Añadir las [rutas](https://gist.github.com/ualmtorres/4dabff3a333d267650c69820edc30e1f) en `<proyecto>/routes/api.php`

Ya estará disponible la API en `localhost:8000/api/product`.

## Creación de la imagen

A partir de este [Dockerfile](https://github.com/ualmtorres/laravel-inventory-api/blob/master/Dockerfile) generaremos la imagen con este comando

    docker build -t ualmtorres/laravel-inventario-api:v0 .

A continuación, subiremos la imagen a Docker Hub con este comando

    docker push ualmtorres/laravel-inventario-api:v0

## Despliegue en Kubernetes

Partimos de un cluster Kubernetes creado y de una instancia MySQL corriendo con la base de datos `inventario` ya creada y con la tabla de productos creada.

Usaremos 3 objetos Kubernetes para el despliegue:

* Objeto _secret_: [mysql-secret.yaml](https://gist.github.com/ualmtorres/ff00364e75bcbe7fa529628335e0587f)
  Contiene las variables de entorno `DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD, DB_DATABASE`. 
  
  > **NOTA**
  > En un objeto _secret_ los valores van codificados en base64. Para codificarlos lo haremos con este comando

  > `echo -n '<valor-a-codificar' | base64`

  > Por ejemplo, `echo -n '123' | base64` produce el valor `MTIz`

* Objeto _deployment_: [inventario-api-deployment.yaml](https://gist.github.com/ualmtorres/dc991d47e726cd4e28440ee78160db82)
* Objeto _service_: [inventario-api-service.yaml](https://gist.github.com/ualmtorres/c0b29c23764d664226cb8670c44d4ff5)

Desplegaremos estos tres objetos con `kubectl apply -f <filename>`

## Actualización del despliegue de Kubernetes

Actualizaremos el despliegue por dos motivos: actualización del código de la API o cambio de credenciales en la base de datos.

* Actualización del código de la API
    1. Crear una nueva imagen local de la API con una etiqueta nueva (p.e. `ualmtorres/laravel-inventario-api:v0.1`) mediante 

        `docker build -t ualmtorres/laravel-inventario-api:v0.1 .`

    1. Subir la imagen al registro de imágenes con `docker push` mediante

        `docker push ualmtorres/laravel-inventario-api:v0.1`

    1. Modificar el archivo de _deployment_ actualizando la versión de la imagen de la API
    1. Redesplegar el archivo de _deployment_
* Cambio de credenciales en la base de datos
    1. Modificar el archivo _secret_ con las nuevas credenciales
    2. Redesplegar el archivo _secret_
    3. Reiniciar el despliegue con el comando siguiente para que se actualicen los pods con las nuevas credenciales

        `kubectl rollout restart deployment inventario-api`

## Enlaces de interés

* [docker-compose.yml para MariaDB y Laravel de Bitnami](https://github.com/bitnami/bitnami-docker-laravel/blob/master/docker-compose.yml)