# 1. Requisitos del servidor

Para que el proyecto se pueda instalar correctamente, los requisitos mínimos del servidor son:

1. PHP 7.2 o superior
2. php ext_imap
3. php ext_mysql
4. mySQL 
5. [Composer](https://getcomposer.org/)
6. nodejs  10 o superior
7. npm 6 o superior
8. ruby y ruby-sass

 # 2.  Instalación

## 2.1 Dependencias

El proyecto utiliza dependencias de [packagist](https://packagist.org/), y estas dependencias son instaladas con el siguiente comando

    composer install

El proyecto utiliza dependencias de npm como [grunt](https://gruntjs.com/) para compilar los archivos SASS y dependencias de [bower](https://bower.io/) como bootstrap, para hacer estos procesos se debe ejecutar el siguiente comando:

    npm install && npm run postinstall

En la raíz del proyecto hay un archivo llamado *.env.example*, este debe ser copiado y renombrado como *.env* 
Este archivo contiene todas las variables globales del proyecto, y serán explicadas a continuación.

## 2.2 Base de datos

Teniendo las credenciales de una base de datos vacía, ir al archivo *.env* y cambiar las siguientes variables:

> DB_HOST: Es el host de la base de datos, generalmente es *localhost*
>  
>   DB_DATABASE: Es el nombre de la base de datos
>   
>  DB_USERNAME: Es el nombre de usuario con permisos para acceder a la base de datos
>   
>   DB_PASSWORD: Es la contraseña del usuario con permisos para acceder a la base de datos

Una vez hecho esto, ubicarse en la raiz del proyecto y ejecutar el siguiente comando

    php core/migrate.php

**¡¡¡Este comando solo se debe usar en la fase de instalación, ya que elimina toda la información de la base de datos!!!**

Este comando hace dos cosas, la primera es crear las tablas en la base de datos configurada y la segunda ejecutar información por defecto, para acceder a la aplicación. 

> **Usuario por defecto:** admin@gmail.com
>
> **Contraseña: por defecto:** 1234

La ubicación de los archivos que continen las consultas a la base de datos y que se ejecutan en el mismo orden son ***app/database/migrations.php*** y ***app/database/seeders.php***

## 2.3 Servidor

El servidor debe estar apuntando a la carpeta *public*  y se tienen dos configuraciones para el archivo *.htaccess*

> Con redirección a https está en el archivo ***public/.htaccess.con.https***
>
> Sin redirección a https está en el archivo ***public/.htaccess.sin.https***

El archivo que se vaya a utilizar, debe ser renombrado a *.htaccess*

## 2.4 Correo Saliente

Teniendo las credenciales de correo o servidor SMTP que se va a usar para el envío de correos, se deben cambiar los siguientes valores en el archivo *.env*:

> SMTP_HOST: Es el host del servidor SMTP, para gmail es smtp.gmail.com
>  
> SMTP_USERNAME: Es el correo electrónico o usuario smtp
>   
>  SMTP_PASSWORD: Es la contraseña del usuario con permisos para acceder a la base de datos
>  
>  SMTP_PORT: Es el puerto del servidor SMTP, para gmail es 587
>  
>  SMTP_ENCRYPTION: Es la encriptación del SMTP, puede ser tls o ssl


## 2.5 Correo Entrante

Teniendo las credenciales IMAP del correo electrónico que se va a usar para recibir los correos, se deben cambiar los siguientes valores en el archivo *.env*:

> IMAP_HOST: Es el host del servidor IMAP
>  
> IMAP_PROTO: Es el protocolo que se va a usar para recibir correos. Puede ser pop3 o imap
>   
>  IMAP_ALIAS: Es el nombre que de quien recibe los mensajes
>  
>  IMAP_USERNAME: Es el correo electrónico del correo que recibe los mensajes.
>  
>  IMAP_PASSWORD: Es la contraseña del correo electrónico
>   
>   IMAP_PORT: Es el puerto del IMAP, por default es 995


***Nota:*** *Para que los correos entrantes y salientes funcionen, el email relacionado en la configuración de imap y de smtp debe tener activo pop e imap*

# 3. Estructura 

El proyecto está construido de la siguiente manera:
> app: Contiene la lógica de la aplicación
>> controllers: Contiene los controladores de la aplicación
>>
>> database: Queries base de datos iniciales
>>
>> views: Vistas en [twig](https://twig.symfony.com/)
>
> core: Contiene clases que se usan en toda la aplicación
>>cache: Almacena el cache de la aplicación
> 
> public: Son los archivos públicos 
>>bower_components: Librerías de bower
>>
>>dist: archivos, imagenes, fuentes, JS, etc ...
>>
>>img: Imágenes usadas en todo el proyecto
> 
> src: Contiene todos los archivos SASS
>
> uploads: Aqui se suben todos los archivos no públicos
>
> node_modules: Dependencias de npm
>
> vendor: Dependencias de packagist
 


# 4. Consideraciones finales

## 4.1 Cache
La aplicación está cacheada gracias a [twig](https://twig.symfony.com/), por lo que se debe dar permisos de lectura y escritura a la carpeta  **core/cache/**, si se realiza un cambio en el proyecto y este no se ve reflejado, se debe eliminar el contenido de esta carpeta.

## 4.2 Debug: 
Si se desea utilizar el modo desarrollador y ver los errores más a detalle se debe agregar la variable ***DEBUG=true*** al archivo ***.env***

## 4.3 Error "No se encuentra la clase": 
El proyecto trabaja con namespaces, si no se encuentran los namespaces, verifique que se encuentren en el autoload de composer.json, si se encuentran allí ejecute el siguiente comando:

    composer dumpautoload -o

Esto agregará los namespace faltantes al autoload de composer,
Si va a agregar una nueva carpeta que tenga clases con namespaces, agreguela al composer.json siguiendo [este](https://getcomposer.org/doc/01-basic-usage.md#autoloading) paso a paso y luego ejecute ***composer dumpautoload -o***

## 4.4 Estilos CSS: 
Cada vez que se cambien los estilos en en los archivos SASS ubicados en ***src/scss*** se debe ejecutar el siguiente comando: 

    grunt

Esto creará un archivo css en la carpeta *public/dist/css* llamado *style.css* y *style.css.map*
    

> Si tiene problemas a la hora de ejecutar el comando grunt, es porque falta instalar globalmente en el servidor Ruby y/o Sass, si pasa esto ejecutar el comando gem install sass