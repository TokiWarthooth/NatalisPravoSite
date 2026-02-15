# Простой сайт на Symfony

Простой сайт на 5 страниц с API, построенный на Symfony 7, PHP 8.3, PostgreSQL и Docker.

## Структура проекта

- **Главная** (`/`) - Главная страница с демонстрацией API
- **О нас** (`/about`) - Информация о компании
- **Услуги** (`/services`) - Описание услуг
- **Портфолио** (`/portfolio`) - Примеры работ
- **Контакты** (`/contact`) - Контактная информация с формой обратной связи

## API Endpoints

- `GET /api/status` - Статус API
- `GET /api/pages` - Список всех страниц
- `POST /api/contact` - Отправка контактной формы

## Запуск проекта

### Требования
- Docker
- Docker Compose

### Установка и запуск

1. Клонируйте репозиторий
2. Запустите проект:

```bash
docker-compose up --build
```

3. Сайт будет доступен по адресу: http://localhost:8080

### Полезные команды

```bash
# Запуск в фоновом режиме
docker-compose up -d

# Остановка контейнеров
docker-compose down

# Просмотр логов
docker-compose logs -f

# Выполнение команд в контейнере
docker-compose exec php bash
```

## Технологии

- **PHP 8.3** - Язык программирования
- **Symfony 7.0** - Веб-фреймворк
- **PostgreSQL 17** - База данных
- **Nginx** - Веб-сервер
- **Docker** - Контейнеризация
- **Bootstrap 5** - CSS фреймворк
- **Twig** - Шаблонизатор

## Структура файлов

```
├── config/                 # Конфигурация Symfony
├── public/                 # Публичные файлы
├── src/
│   ├── Controller/         # Контроллеры
│   └── Kernel.php         # Ядро приложения
├── templates/              # Шаблоны Twig
├── docker-compose.yml      # Docker Compose конфигурация
├── Dockerfile             # Docker образ
└── composer.json          # Зависимости PHP
```