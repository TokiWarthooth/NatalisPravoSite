# Настройка Webhook для автоматического деплоя

Этот метод проще, чем SSH через GitHub Actions, и не требует настройки секретов.

## Установка на сервере

### 1. Скопируйте файлы на сервер

Файлы уже в репозитории:
- `deploy.sh` - скрипт деплоя
- `webhook_server.py` - webhook сервер

### 2. Сделайте скрипт исполняемым

```bash
cd /var/www/NatalisPravoSite
chmod +x deploy.sh
chmod +x webhook_server.py
```

### 3. Установите Python3 (если еще нет)

```bash
apt install python3 -y
```

### 4. Запустите webhook сервер

#### Вариант А: Запуск вручную (для теста)

```bash
cd /var/www/NatalisPravoSite
python3 webhook_server.py
```

Сервер запустится на порту 9000.

#### Вариант Б: Запуск как systemd сервис (рекомендуется)

Создайте файл сервиса:

```bash
nano /etc/systemd/system/webhook-deploy.service
```

Содержимое:

```ini
[Unit]
Description=Webhook Deploy Server
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/var/www/NatalisPravoSite
ExecStart=/usr/bin/python3 /var/www/NatalisPravoSite/webhook_server.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Запустите сервис:

```bash
systemctl daemon-reload
systemctl enable webhook-deploy
systemctl start webhook-deploy
systemctl status webhook-deploy
```

### 5. Откройте порт 9000 в файрволе

```bash
# Если используется ufw
ufw allow 9000/tcp

# Если используется iptables
iptables -A INPUT -p tcp --dport 9000 -j ACCEPT
```

### 6. Проверьте, что webhook работает

```bash
# С сервера
curl -X POST http://localhost:9000/deploy

# С вашего компьютера
curl -X POST http://194.87.200.201:9000/deploy
```

Должен запуститься процесс деплоя.

## Как это работает

1. Вы делаете `git push origin main`
2. GitHub Actions отправляет POST запрос на `http://194.87.200.201:9000/deploy`
3. Webhook сервер получает запрос и запускает `deploy.sh`
4. Скрипт `deploy.sh` делает:
   - `git pull origin main`
   - `docker-compose down`
   - `docker-compose up -d --build`
   - Устанавливает права
   - Очищает старые образы

## Преимущества этого метода

- ✅ Не нужны SSH ключи в GitHub
- ✅ Не нужны GitHub Secrets
- ✅ Простая настройка
- ✅ Легко отлаживать (логи на сервере)
- ✅ Можно запускать деплой вручную: `bash deploy.sh`

## Ручной деплой

Если нужно задеплоить вручную без GitHub:

```bash
cd /var/www/NatalisPravoSite
bash deploy.sh
```

## Логи

Смотреть логи webhook сервера:

```bash
# Если запущен как сервис
journalctl -u webhook-deploy -f

# Если запущен вручную
# Логи будут в терминале где запущен python3 webhook_server.py
```

## Безопасность

Для продакшена рекомендуется:

1. Добавить проверку секретного токена в `webhook_server.py`
2. Использовать HTTPS (через Nginx reverse proxy)
3. Ограничить доступ к порту 9000 только с IP GitHub

Но для приватного репозитория текущая настройка достаточна.
