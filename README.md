# Symfony Blog Platform

Полнофункциональная блог-платформа на Symfony 7+ с использованием современного стека технологий.

## 🎯 Цель проекта

Разработка блог-платформы для прокачки навыков работы с современными технологиями веб-разработки.

## 🛠 Технологический стек

- **PHP** 8.2+
- **Symfony** 7.x
- **MySQL** 8.0+
- **Redis** (кеш, сессии, очереди)
- **Elasticsearch** (полнотекстовый поиск)
- **Docker** + Docker Compose
- **Nginx**
- **Twig** (шаблонизатор)

## 📋 Требования

- Docker >= 20.10
- Docker Compose >= 2.0
- Make

## 🚀 Быстрый старт
```bash
# 1. Клонировать проект
git clone https://gitlab.com/umbrellait-narine-melkonyan/pdp.git
cd pdp

# 2. Создать .env файл
cp .env.example .env

# 3. Запустить 
make up

# 4. Установить зависимости
make composer-install

# 5. Bыполнить миграции

make migration-migrate
```

## 🌐 Доступ к приложению

### Development:
- **Web**: http://localhost:8080
- **MySQL**: localhost:3307
- **Redis**: localhost:6379
- **Elasticsearch**: http://localhost:9200

### Production:
- **Web**: http://localhost
- **MySQL**: localhost:3306

## 📦 Основные команды
```bash
make help              # Показать все команды

# Development
make dev-up            # Запустить
make dev-down          # Остановить
make dev-restart       # Перезапустить
make dev-logs          # Показать логи

# Production
make prod-up           # Запустить
make prod-down         # Остановить
make prod-restart      # Перезапустить
make prod-logs         # Показать логи

# Default (docker-compose.yml)
make up                # Запустить
make down              # Остановить
make restart           # Перезапустить
make logs              # Показать логи

# Symfony
make cache-clear       # Очистить кеш
make composer-update   # Обновить зависимости

# База данных
make migration-migrate # Выполнить миграции

```

## ⚙️ Конфигурация

Измените в `.env`:
```bash
# Порты Development
NGINX_PORT_DEV=8080
MYSQL_PORT_DEV=3307

# Порты Production
NGINX_PORT_PROD=80
MYSQL_PORT_PROD=3306

# Порты Default
NGINX_PORT=8080
MYSQL_PORT=3307

# База данных
DB_NAME=symfony_blog
DB_USER=symfony
DB_PASSWORD=symfony
```

## 🐳 Docker окружения

### Default (docker-compose.yml)
- Для быстрого тестирования
- PHP с volume mounting
- Порты из `.env` (NGINX_PORT, MYSQL_PORT)

### Development (docker-compose.dev.yml)
- PHP с Xdebug
- Volume mounting для hot reload
- Порты: 8080 (web), 3307 (mysql)

### Production (docker-compose.prod.yml)
- PHP оптимизирован
- Без Xdebug
- Auto restart
- Порты: 80 (web), 3306 (mysql)

## 📝 Работа без Make
```bash
# Default
docker compose up -d --build
docker compose down

# Development
docker compose -f docker-compose.dev.yml up -d --build
docker compose -f docker-compose.dev.yml down

# Production
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml down

# Symfony команды
docker compose exec php php bin/console cache:clear
```

## 🔍 Troubleshooting
```bash
# Проверить логи
make logs

# Полная очистка
make clean
make up
```

## 📚 Структура проекта
```
pdp/
├── .env                       # Переменные окружения (не коммитится)
├── .env.example              # Пример переменных окружения
├── docker-compose.yml        # Docker Compose по умолчанию
├── docker-compose.dev.yml    # Docker Compose для разработки
├── docker-compose.prod.yml   # Docker Compose для продакшена
├── Makefile                  # Команды для управления
├── README.md                 # Документация
├── docker/
│   ├── nginx/
│   │   └── default.conf     # Конфигурация Nginx
│   └── php/
│       └── Dockerfile       # Dockerfile для PHP
└── src/                     # Symfony приложение
    ├── bin/
    ├── config/
    ├── public/
    ├── src/
    ├── templates/
    └── var/
```

