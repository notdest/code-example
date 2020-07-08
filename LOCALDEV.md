# Установка локального окружения

## Linux
1. Установите Docker и docker-compose по инструкциям для вашего дистрибутива  https://docs.docker.com/engine/install/
   https://docs.docker.com/compose/install/

2. Склонируйте репозиторий проекта  

3. В корне проекта выполните команду  `docker-compose run parser composer install`

4. Откройте проект в PHPStorm и настройте использование docker-compose по инструкции https://www.jetbrains.com/help/phpstorm/docker.html#using-docker-compose

## Windows 10

1. Установите WSL2 https://docs.microsoft.com/en-us/windows/wsl/install-win10
   Возможно потребуется обновить Windows до версии version 2004 Build 19041

2. Установите Docker Desktop https://docs.docker.com/docker-for-windows/install/
   После установки, в настройках Docker Desktop включите параметр Settings -> General -> Expose daemon on tcp://localhost without TLS
   
3. Склонируйте репозиторий проекта  

4. В корне проекта выполните команду  `docker-compose run parser composer install`

4. Откройте проект в PHPStorm и настройте использование docker-compose по инструкции https://www.jetbrains.com/help/phpstorm/docker.html#using-docker-compose  
   Если возникает ошибка _docker-compose is not a valid Win32 application_, откройте настройках PHPStorm  
   **Settings -> Build, Execution, Deployment -> Docker -> Tools** и добавте расширение .exe к имени файла в поле **Docker Compose executable**

## Mac (но это не точно)
1. Установите Docker Desktop https://docs.docker.com/docker-for-windows/install/
   После установки, в настройках Docker Desktop включите параметр Settings -> General -> Expose daemon on tcp://localhost without TLS
      
2. Склонируйте репозиторий проекта  

3. В корне проекта выполните команду  `docker-compose run parser composer install`

4. Откройте проект в PHPStorm и настройте использование docker-compose по инструкции https://www.jetbrains.com/help/phpstorm/docker.html#using-docker-compose

# Запуск

В корне проекта выполните команду  `docker-compose up`
Проект будет доступен по адресу http://localhost:8787
   

