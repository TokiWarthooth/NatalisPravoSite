# Руководство по развертыванию сайта на TimeWeb Cloud

## Архитектура
- Сервер: TimeWeb Cloud
- Проект: Docker (Nginx + PHP + PostgreSQL)
- Домен: reg.ru
- CI/CD: GitHub Actions
- SSL: Let's Encrypt (Certbot)

## Шаг 1: Подготовка сервера

### 1.1. Подключитесь к серверу
```bash
ssh root@5324181-eo34940.twc1.net
```

### 1.2. Установите необходимые пакеты
```bash
# Обновите систему
apt update && apt upgrade -y

# Установите Docker и Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Установите Docker Compose
apt install docker-compose -y

# Установите Git
apt install git -y

# Установите Nginx (для reverse proxy)
apt install nginx -y

# Установите Certbot для SSL
apt install certbot python3-certbot-nginx -y
```

### 1.3. Проверьте занятые порты
```bash
# Проверьте, какие порты заняты
sudo netstat -tulpn | grep LISTEN

# Ваш бот, скорее всего, использует один из портов
# Нам нужно выбрать свободный порт для сайта (например, 8080)
```

## Шаг 2: Настройка домена на reg.ru

### 2.1. Получите IP-адрес сервера
```bash
curl ifconfig.me
# Или
hostname -I
```

### 2.2. Настройте DNS на reg.ru
1. Войдите в личный кабинет reg.ru
2. Перейдите в управление доменом
3. Найдите раздел "DNS-серверы и управление зоной"
4. Добавьте A-записи:
   - `@` (корень домена) → IP вашего сервера
   - `www` → IP вашего сервера

Пример:
```
Тип    Имя    Значение              TTL
A      @      ВАШ_IP_АДРЕС          3600
A      www    ВАШ_IP_АДРЕС          3600
```

**Важно:** DNS-изменения могут занять от 15 минут до 24 часов.

## Шаг 3: Клонирование проекта на сервер

```bash
# Создайте директорию для проектов
mkdir -p /var/www
cd /var/www

# Клонируйте репозиторий
git clone https://github.com/TokiWarthooth/NatalisPravoSite.git
cd NatalisPravoSite

# Создайте .env файл (если нужно)
cp .env.example .env
nano .env
```

## Шаг 4: Настройка Nginx как Reverse Proxy

### 4.1. Создайте конфигурацию Nginx для сайта
```bash
nano /etc/nginx/sites-available/natalispravo
```

Содержимое файла:
```nginx
server {
    listen 80;
    server_name ваш-домен.ru www.ваш-домен.ru;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### 4.2. Активируйте конфигурацию
```bash
# Создайте символическую ссылку
ln -s /etc/nginx/sites-available/natalispravo /etc/nginx/sites-enabled/

# Проверьте конфигурацию
nginx -t

# Перезапустите Nginx
systemctl restart nginx
```

## Шаг 5: Запуск Docker-контейнеров

```bash
cd /var/www/NatalisPravoSite

# Измените порт в docker-compose.yml на 8080 (если он занят, выберите другой)
nano docker-compose.yml
# Измените строку:
# ports:
#   - "8080:80"

# Запустите контейнеры
docker-compose up -d

# Проверьте статус
docker-compose ps

# Посмотрите логи
docker-compose logs -f
```

## Шаг 6: Установка SSL-сертификата (Let's Encrypt)

```bash
# Остановите Nginx временно
systemctl stop nginx

# Получите сертификат
certbot certonly --standalone -d ваш-домен.ru -d www.ваш-домен.ru

# Или если Nginx запущен, используйте:
certbot --nginx -d ваш-домен.ru -d www.ваш-домен.ru

# Запустите Nginx обратно
systemctl start nginx
```

### 6.1. Обновите конфигурацию Nginx для HTTPS
```bash
nano /etc/nginx/sites-available/natalispravo
```

Обновленная конфигурация:
```nginx
# Редирект с HTTP на HTTPS
server {
    listen 80;
    server_name ваш-домен.ru www.ваш-домен.ru;
    return 301 https://$server_name$request_uri;
}

# HTTPS конфигурация
server {
    listen 443 ssl http2;
    server_name ваш-домен.ru www.ваш-домен.ru;

    ssl_certificate /etc/letsencrypt/live/ваш-домен.ru/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ваш-домен.ru/privkey.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
# Проверьте конфигурацию
nginx -t

# Перезапустите Nginx
systemctl restart nginx
```

### 6.2. Настройте автообновление сертификата
```bash
# Certbot автоматически добавляет cron job, проверьте:
systemctl status certbot.timer

# Или добавьте вручную в crontab
crontab -e
# Добавьте строку:
0 0 * * * certbot renew --quiet
```

## Шаг 7: Настройка GitHub Actions для автодеплоя

### 7.1. Создайте SSH ключ для деплоя
На сервере:
```bash
# Создайте нового пользователя для деплоя (опционально)
adduser deploy
usermod -aG docker deploy

# Или используйте root (не рекомендуется для продакшена)
# Создайте SSH ключ
ssh-keygen -t ed25519 -C "github-deploy"

# Добавьте публичный ключ в authorized_keys
cat ~/.ssh/id_ed25519.pub >> ~/.ssh/authorized_keys

# Скопируйте приватный ключ (понадобится для GitHub Secrets)
cat ~/.ssh/id_ed25519
```

### 7.2. Добавьте секреты в GitHub
1. Перейдите в репозиторий: https://github.com/TokiWarthooth/NatalisPravoSite
2. Settings → Secrets and variables → Actions
3. Добавьте секреты:
   - `SSH_PRIVATE_KEY` - приватный ключ SSH
   - `SERVER_HOST` - IP или домен сервера
   - `SERVER_USER` - пользователь (root или deploy)
   - `SERVER_PATH` - путь к проекту (/var/www/NatalisPravoSite)

### 7.3. Создайте GitHub Actions workflow
Создайте файл `.github/workflows/deploy.yml` в репозитории:

```yaml
name: Deploy to Server

on:
  push:
    branches: [ main ]
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Checkout code
      uses: actions/checkout@v3
    
    - name: Deploy to server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.SERVER_HOST }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SSH_PRIVATE_KEY }}
        script: |
          cd ${{ secrets.SERVER_PATH }}
          git pull origin main
          docker-compose down
          docker-compose up -d --build
          docker-compose ps
```

## Шаг 8: Проверка и тестирование

```bash
# Проверьте, что контейнеры запущены
docker-compose ps

# Проверьте логи
docker-compose logs -f

# Проверьте Nginx
systemctl status nginx

# Проверьте SSL
curl -I https://ваш-домен.ru

# Проверьте сайт в браузере
# https://ваш-домен.ru
```

## Шаг 9: Мониторинг и обслуживание

### 9.1. Настройте логирование
```bash
# Логи Docker
docker-compose logs -f --tail=100

# Логи Nginx
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### 9.2. Резервное копирование базы данных
```bash
# Создайте скрипт для бэкапа
nano /root/backup-db.sh
```

Содержимое:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker exec natalispravo-db-1 pg_dump -U symfony symfony_db > /var/backups/db_backup_$DATE.sql
# Удалить бэкапы старше 7 дней
find /var/backups -name "db_backup_*.sql" -mtime +7 -delete
```

```bash
chmod +x /root/backup-db.sh

# Добавьте в crontab
crontab -e
# Добавьте: ежедневный бэкап в 2 часа ночи
0 2 * * * /root/backup-db.sh
```

## Полезные команды

```bash
# Перезапуск контейнеров
docker-compose restart

# Остановка контейнеров
docker-compose down

# Просмотр логов
docker-compose logs -f

# Обновление проекта
cd /var/www/NatalisPravoSite
git pull
docker-compose up -d --build

# Проверка портов
netstat -tulpn | grep LISTEN

# Проверка процессов Docker
docker ps -a

# Очистка неиспользуемых образов
docker system prune -a
```

## Troubleshooting

### Проблема: Порт 8080 занят
```bash
# Найдите процесс
sudo lsof -i :8080
# Или
sudo netstat -tulpn | grep 8080

# Измените порт в docker-compose.yml на другой (например, 8081)
```

### Проблема: Nginx не запускается
```bash
# Проверьте конфигурацию
nginx -t

# Проверьте логи
tail -f /var/log/nginx/error.log
```

### Проблема: Docker контейнеры не запускаются
```bash
# Проверьте логи
docker-compose logs

# Проверьте, что порты не заняты
docker-compose down
docker-compose up -d
```

### Проблема: SSL сертификат не работает
```bash
# Проверьте сертификат
certbot certificates

# Обновите сертификат
certbot renew --dry-run
```

## Контакты и поддержка

- GitHub: https://github.com/TokiWarthooth/NatalisPravoSite
- Документация Certbot: https://certbot.eff.org/
- Документация Docker: https://docs.docker.com/
- Документация Nginx: https://nginx.org/ru/docs/
