#!/bin/bash

# Скрипт автоматического деплоя
# Запускается на сервере

echo "🚀 Starting deployment..."

# Переход в директорию проекта
cd /var/www/NatalisPravoSite || exit

# Получение последних изменений
echo "📥 Pulling latest changes from GitHub..."
git pull origin main

# Остановка контейнеров
echo "🛑 Stopping containers..."
docker-compose -f docker-compose.prod.yml down

# Сборка и запуск контейнеров
echo "🔨 Building and starting containers..."
docker-compose -f docker-compose.prod.yml up -d --build

# Установка прав на директории
echo "🔐 Setting permissions..."
docker exec natalispravosite_php_1 chown -R www-data:www-data /var/www/html/var /var/www/html/vendor 2>/dev/null || true
docker exec natalispravosite_php_1 chmod -R 775 /var/www/html/var 2>/dev/null || true

# Проверка статуса
echo "✅ Checking container status..."
docker-compose -f docker-compose.prod.yml ps

# Очистка неиспользуемых образов
echo "🧹 Cleaning up unused images..."
docker system prune -f

echo "✨ Deployment completed successfully!"
