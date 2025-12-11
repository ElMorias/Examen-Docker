
# Despliegue con Docker Compose — Proxy inverso + servicios PHP
Este proyecto implementa una arquitectura de microservicios hecho con **Docker Compose**. El objetivo es desplegar un entorno que sirva dos dominios distintos a través de un **Proxy Inverso (Nginx)**, con balanceo de cargas y peristencia de datos

## Arquitectura del Sistema

El despliegue se compone de los siguientes servicios definidos en `docker-compose.yml`:

1.  **Proxy Inverso (`nginx`)**:
    * Actúa como punto de entrada único (Puerto 80).
    * Redirige el tráfico basándose en el nombre de dominio (`server_name`) hacia el servicio correspondiente.
    * Gestiona el balanceo de carga hacia el clúster de encuestas.

2.  **Servicio Encuesta (`php-apache`)**:
    * Responde al dominio `www.freedomforLinares.com`.
    * **Replicación:** 3 contenedores activos.
    * **Balanceo con Pesos:** Configurado para que el contenedor principal reciba **3 veces más carga** (`weight=3`) que las otras réplicas (`weight=1`)
    * **Persistencia:** Utiliza un volumen compartido para almacenar los votos.

3.  **Servicio Chiste (`php-apache`)**:
    * Responde al dominio `www.chiquito.com`.
    * Servicio simple que devuelve un chiste aleatorio en cada petición

---

## Revision de Dockerfiles y docker-compose

### 1. Dockerfiles de chistes y encuestas

Para el Dockerfile del programa de chistes tenemos la siguiente estructura:

    FROM php:7.4-apache

    COPY . /var/www/html/ 

Solo necesitamos crear el contenedor con php + apache , y hacer un COPY de la ruta donde se encuentra nuestro index.php (En este caso en la misma ruta del Dockerfile)  

Para el Dockerfile de la encuesta, la estructura es parecida pero añadimos otra cosa: 

    FROM php:7.4-apache

    COPY . /var/www/html/

    RUN chown -R www-data:www-data /var/www/html    

En este caso, solo por si acaso, ponemos que al crear el contenedor, le de permisos para poder escribir en la carpeta donde esta el archivo .txt de los  datos.

### Docker-compose.yml

Vamos a desglosar el archivo:

    services:
        proxy:
            image: nginx:alpine
            container_name: proxy-nginx
            ports:
            - "80:80"
            volumes:
            - ./nginx/default.conf:/etc/nginx/conf.d/default.conf

Primero creamos el proxy, Usamos la imagen de niginx:alpine (es la mas usada), vamos a ponerle a todos los servicios un nombre para tenerlo mas ordenado, y tiene que cargar además el archivo de configuracion que esta en la ruta /nginx, donde configuramos la carga de las réplicas de la encuesta, y los nombres de los servidores

    chiste:
    build: ./chiste #el Dockerfile esta ahi
    container_name: chiste-app

Para los chistes, solo tenemos que hacer el build con el Dockerfile de la carpeta /chistes, y le ponemos nombre al contenedor.

    --- ENCUESTA 1, tiene mas carga ---
     encuesta1:
    build: ./encuesta
    hostname: encuesta_1_X3
    volumes:
      - ./datos:/var/www/html/data



    --- ENCUESTA 2 ---
    encuesta2:
    build: ./encuesta
    hostname: encuesta_normal_2
    volumes:
        - ./datos:/var/www/html/data


    --- ENCUESTA 3 ---
    encuesta3:
    build: ./encuesta
    hostname: encuesta_normal_3
    volumes:
        - ./datos:/var/www/html/data


Para los tres servicios de encuesta es lo mismo, creamos los servicios con el Dockerfile  de /encuesta, y ademas le añandimos un hostname, para que luego podamos diferenciar que nos esta saliendo, y veamos el balanceo de carga. Tambien le añadimos como volumen la capeta de datos, que es donde vamos a guardar el archivo con los datos de la votacion.


### Archivo default.conf
Este archivo es el que contiene la cofiguracion del proxy

    upstream backend_encuesta {
    server encuesta1:80 weight=3;
    server encuesta2:80 weight=1;
    server encuesta3:80 weight=1;
    }

Aqui es donde configuramos el peso de cada una de las replicas, de asignamso a la encuesa 1 un peso de 3, mientras que a las otras dos un peso de 1. Con esto deberia de aparecer la encueta 1 mas veces que las demas.

    # 1. Configuración para el dominio de la encuesta
    server {
        listen 80;
        server_name www.freedomforLinares.com;

        location / {
            proxy_pass http://backend_encuesta;
        }
    }

    # 2. Configuración para el dominio de los chistes
    server {
        listen 80;
        server_name www.chiquito.com;

        location / {
            proxy_pass http://chiste:80;
        }
    }

Aqui tenemos la configuracion de los dominios, para el sevicio de la encuesta, le ponemos el server_name ( luego hay que cambiarlo en hosts), y lo que hacemos es que ubicacion se pase a backend_encuesta, que lo tenemos declarado arriba y es el que se encarga del balance de las encuestas.

Para la configuracion de los chistes, solo añadimos el nombre del servidor y le decimos a quien le pasa el trabajo.

---

## Requisitos y Configuración Previa

Para ejecutarlo, tenemos que simular los dominios mediante el archivo `hosts` del sistema.

### 1. Configuración de DNS Local (Hosts)

Hay que añadir al archivo `hosts` para lo siguiente que apunten a tu máquina local.

    **Añade estas lineas al final del archivo:**

    127.0.0.1    www.freedomforLinares.com

    127.0.0.1    www.chiquito.com

* **Ruta en Windows:** `C:\Windows\System32\drivers\etc\hosts`
Si pide permisos para modificar el archivo: 

Opcion 1 -> Abrir bloc de notas como Administrador y abrir el archivo.
                                            
Opcion 2 -> Con VsCode al guardar aparece la opcion de dar permisos.

---

## Ejecución

* En la raiz del proyecto, donde debe estar el `docker-compose.yml` ejecutamos:

    `docker compose up -d --build`

Usamos --build para asegurar que las imagenes se construyen con los últimos cambios del codigo.

---

## Guía de Pruebas y Verificación
Vamos a verificar quien todo funciona como debe.

1. ** Servicio "Chiste"**:

    Verificamos que el proxy redirige el dominio correcto y el PHP funciona.

    * Abrimos el navegador y entramos en: http://www.chiquito.com
    * Debe aparecer un chiste.
    * Recargamos la página (F5) varias veces. El chiste debe cambiar aleatoriamente.

2. **Prueba de Balanceo de Carga con Pesos del servicio de encuesta**:

    Verificamos que la replica 1 recibe mas trafico que la 2 y la 3.

    * Entramos en: http://www.freedomforLinares.com
    * Confirmamos que la pagina carga y que vemos la encuesta y los votos
    * Si miramos el texto rojo que indica "Esta es la:"., debe mostrar el hostname del contenedor  que se esta usando.
    * Si recargamos la pagina varias veces, vemos que el contenedor identificado como encuesta_1_X3 aparece con mas frecuencia que las otras replicas. Esto confirma que el weight=3 en Nginx está funcionando.

3. **Prueba de Persistencia y Lógica PHP**:

    Verificamos que los votos se guardan y comparten entre contenedores.

    * En la página de la encuesta, pulsamos botón "SI" o "NO" y vemos los resultados.
    * Aunque cambie la replica, se recargue la pagina o se apague y se vuelva a encender el contenedor, se deben de mantener la cantidad de votos.

---
