# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Symfony 7 web application and Telegram bot for the Ingress game community in Ecuador. Manages agents, badges/medals, events, and challenges.

- **PHP 8.2+** with Symfony 7.x (MicroKernel)
- **PostgreSQL 16** via Docker (docker-compose.yaml)
- **Frontend:** Stimulus JS, Bootstrap 5, Leaflet maps, FullCalendar

## Commands

### Development Setup
```bash
composer install
yarn && yarn dev
docker-compose up -d              # PostgreSQL database
symfony server:start -d
```

### Testing
```bash
make tests                        # Full CI: PHPUnit + PHPStan + Rector
symfony php vendor/bin/phpunit    # Run tests only
symfony php vendor/bin/phpunit --filter=TestName  # Single test
vendor/bin/phpstan analyse        # Static analysis (level 7)
vendor/bin/rector process --dry-run  # Check code modernization
```

### Database
```bash
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load
```

### Translations
```bash
composer translate                # Extract translations (en, es, de)
```

### Update Badge Data
```bash
bin/console app:update:badgedata  # Update medal images and CSS
```

## Architecture

### Layered Structure
```
Controller → Service → Repository → Entity
```
- **Controllers** (`src/Controller/`): Thin HTTP handlers delegating to services
- **Services** (`src/Service/`): Business logic (MedalChecker, EventHelper, TelegramBotHelper, etc.)
- **Repositories** (`src/Repository/`): Doctrine data access with custom queries
- **Entities** (`src/Entity/`): Doctrine ORM entities with PHP 8 attributes

### Key Components
- `src/BotCommand/`: Telegram bot commands (Start, Agents, Cite, Guides)
- `src/Command/`: Console commands for scheduled tasks
- `src/EventListener/`, `src/EventSubscriber/`: Event-driven architecture
- `templates/emails/`: Email templates using Inky + CSS inliner

### Main Entities
- **Agent**: Primary entity linked to User, Faction, AgentStat, MapGroup
- **Event/Challenge**: Game events and user challenges
- **AgentStat**: Historical agent statistics

## Code Quality

- **PHPStan level 7** with Symfony and Doctrine extensions
- **Rector** for code modernization (Symfony 6.4 ruleset)
- **Cognitive complexity limits**: 50 per class, 8 per function

## External Integrations

- Google OAuth (authentication)
- Telegram Bot API (via boshurik/telegram-bot-bundle)
- Firebase Cloud Messaging (push notifications)
- Google SMTP (email via Symfony Mailer)

## Adding New Badges

1. Add badge "code" in `src/Service/MedalChecker.php`
2. Run `bin/console app:update:badgedata` to update images/CSS
