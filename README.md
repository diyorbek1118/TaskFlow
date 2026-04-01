# Universal Laravel Docker Configuration

Har qanday Laravel loyihasi uchun Docker konfiguratsiyasi.

## Foydalanish

### 1. Yangi loyiha uchun sozlash

```bash
# 1. .env faylini yaratish
cp .env.example .env

# 2. .env faylini tahrirlash (PROJECT_NAME, PROJECT_PATH, portlar)
nano .env
```

### 2. Mavjud Laravel loyihasini ulash

```bash
# Mavjud loyiha papkasini ko'rsatish
# .env faylida:
PROJECT_PATH=../mening-loyiham
```

### 3. Yangi Laravel loyihasi yaratish

```bash
# Loyiha papkasini yaratish va Laravel o'rnatish
mkdir laravel_project
docker compose run --rm app composer create-project laravel/laravel .
```

### 4. Ishga tushirish

```bash
# Containerlarni ishga tushirish
make up

# Yoki to'liq sozlash
make dev-setup
```

## Muhit o'zgaruvchilari (.env)

| O'zgaruvchi | Tavsif | Default |
|-------------|--------|---------|
| `PROJECT_NAME` | Container nomlari uchun | `laravel` |
| `PROJECT_PATH` | Laravel loyiha papkasi | `./laravel_project` |
| `PHP_VERSION` | PHP versiyasi (8.1, 8.2, 8.3) | `8.2` |
| `APP_PORT` | Web ilova porti | `8080` |
| `DB_PORT` | MySQL porti | `3306` |
| `MYSQL_VERSION` | MySQL versiyasi | `8.0` |
| `DB_DATABASE` | Database nomi | `laravel` |
| `DB_USERNAME` | Database foydalanuvchi | `laravel` |
| `DB_PASSWORD` | Database parol | `secret` |

## Mavjud komandalar (Makefile)

```bash
make up           # Containerlarni ishga tushirish
make down         # Containerlarni to'xtatish
make build        # Qayta build qilish
make restart      # Qayta ishga tushirish
make logs         # Loglarni ko'rish
make shell        # App containerga kirish
make db-shell     # Database ga kirish
make redis-shell  # Redis ga kirish
make composer-install  # Composer paketlarini o'rnatish
make npm-install  # NPM paketlarini o'rnatish
make migrate      # Migrationlarni ishga tushirish
make seed         # Seederlarni ishga tushirish
make test         # Testlarni ishga tushirish
make dev-setup    # To'liq sozlash
```

## Servislar

- **app** - PHP-FPM + Laravel
- **nginx** - Web server
- **mysql** - Database
- **redis** - Cache/Queue
- **queue** - Laravel queue worker

## Ko'p loyihalar uchun foydalanish

Har bir loyiha uchun alohida nusxa oling va `.env` faylida portlarni almashtiring:

```bash
# Loyiha 1
APP_PORT=8080
DB_PORT=3306

# Loyiha 2  
APP_PORT=8081
DB_PORT=3307
```
