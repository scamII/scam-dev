# scam-dev2 WordPress Theme Project

## Project Overview
WordPress hybrid theme with Tailwind CSS, built with wp-scripts and webpack. Modern development setup with Docker containerization.

## Technology Stack
- **Language**: PHP (WordPress theme), JavaScript (ES6+), SCSS/CSS
- **Build Tool**: wp-scripts, webpack 5
- **CSS Framework**: Tailwind CSS v4.1.0
- **Package Manager**: npm
- **Container**: Docker + Docker Compose
- **Database**: MySQL 8.0
- **Web Server**: Apache 2.4 (via WordPress image)
- **Runtime**: Node.js v24

## Project Structure
```
scam-dev2/
├── assets/
│   ├── js/                  # Source JavaScript files
│   │   └── main.js          # Main JavaScript entry
│   └── scss/                # Source SCSS files
│       ├── style.css        # Theme styles
│       └── editor.css       # Block editor styles
├── build/                   # Compiled CSS/JS (generated)
├── wp-content/              # WordPress content (mounted)
├── Dockerfile               # Container image definition
├── docker-compose.yml       # Multi-container orchestration
├── package.json             # npm dependencies & scripts
├── webpack.config.js        # Webpack configuration
├── postcss.config.js        # PostCSS/Tailwind configuration
├── Makefile                 # Development commands
├── .env                     # Environment variables
├── .dockerignore            # Docker build context exclusions
├── start.sh                 # Automated setup script
├── *.php                    # Theme template files
└── README.md                # Documentation

```

## Key Files Explanation

### Docker Setup
- **Dockerfile**: Multi-stage WordPress image with Node.js LTS installed
- **docker-compose.yml**: Defines WordPress service and MySQL service with volumes, ports, environment
- **.env**: Configuration for database and WordPress (loaded by compose)
- **.dockerignore**: Excludes node_modules, build artifacts from Docker context

### Development
- **package.json**: npm scripts (start, build, lint:css, lint:js, format)
- **webpack.config.js**: Asset bundling with wp-scripts
- **postcss.config.js**: PostCSS plugins for Tailwind CSS
- **Makefile**: Development task shortcuts

### Automation
- **start.sh**: One-command setup with health checks

## npm Scripts
```
npm start       # Dev server with hot reload (HMR)
npm run build   # Production build (CSS/JS compilation)
npm run lint:css     # CSS linting with StyleLint
npm run lint:js      # JavaScript linting with ESLint
npm run format       # Code formatting with Prettier
```

## Docker Commands

### Quick Start
```bash
./start.sh              # Full setup with checks
make up                 # Start containers
make down               # Stop containers
make logs               # View logs
```

### Development
```bash
make start              # Dev server (hot reload)
make build              # Build assets
make install            # Install dependencies
make lint-js            # Lint JavaScript
make lint-css           # Lint CSS
make format             # Format code
```

### Docker Management
```bash
make shell              # Access WordPress container
make mysql-shell        # Access MySQL CLI
make rebuild            # Rebuild images
make clean              # Remove all containers/volumes
```

## Service Configuration

### WordPress Service
- **Image**: Custom built from Dockerfile (wordpress:latest + Node.js)
- **Port**: 8080 (localhost:8080 → container:80)
- **Volumes**: 
  - wordpress_data: /var/www/html (WordPress installation)
  - Theme mounted: /var/www/html/wp-content/themes/scam-dev/
- **Environment**: WORDPRESS_DB_*, WORDPRESS_DEBUG
- **Health Check**: HTTP GET on /wp-admin/admin-ajax.php

### MySQL Service
- **Image**: mysql:8.0
- **Port**: 3306 (localhost:3306 → container:3306)
- **Volumes**: mysql_data: /var/lib/mysql (persisted database)
- **Environment**: MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD, MYSQL_ROOT_PASSWORD
- **Health Check**: mysqladmin ping

## Environment Variables (.env)
```
WORDPRESS_DB_HOST=mysql:3306
WORDPRESS_DB_NAME=wordpress
WORDPRESS_DB_USER=wordpress
WORDPRESS_DB_PASSWORD=wordpress
WORDPRESS_DEBUG=true
MYSQL_ROOT_PASSWORD=root
COMPOSE_PROJECT_NAME=scam-dev2
```

## Data Persistence
- **Database**: Persists in named volume `scam-dev2_mysql_data`
- **WordPress**: Persists in named volume `scam-dev2_wordpress_data`
- **Command to preserve data**: Use `docker compose down` (NOT `docker compose down -v`)
- **Command to reset**: Use `docker compose down -v` to remove volumes

## Common Workflows

### Fresh Setup
```bash
./start.sh
```

### Development Cycle
```bash
make up                 # Start if not running
make start              # Dev server with HMR
# Edit files in assets/js/, assets/scss/
# Browser auto-refreshes (handled by wp-scripts)
```

### Production Build
```bash
make build              # Creates /build directory with minified assets
docker compose down     # Stop containers (keeps data)
docker compose up -d    # Restart with production build
```

### Debugging
```bash
make logs               # Tail container logs
make shell              # Direct container access
make mysql-shell        # Direct MySQL access
docker exec scam-dev-wordpress npm list  # Check installed packages
```

## Key Dependencies
- @wordpress/scripts: Build toolchain
- @tailwindcss/postcss: Tailwind CSS framework
- autoprefixer: CSS vendor prefixing
- postcss: CSS transformation
- webpack: Module bundling
- wp-coding-standards/wpcs: PHP linting (Composer)

## Important Notes
1. **Node/npm versions**: Only required in Docker container (v24, v11)
2. **Volume mounts**: Theme files are mounted from host → container
3. **Hot reload**: Handled by wp-scripts/webpack-dev-server
4. **Database**: Survives container restarts (stored in Docker volume)
5. **Build artifacts**: Kept in /build directory for production use

## Troubleshooting

### Containers won't start
```bash
make clean              # Remove all containers/volumes
./start.sh              # Full reset
```

### npm dependencies issues
```bash
docker exec scam-dev-wordpress npm ci --prefix /var/www/html/wp-content/themes/scam-dev
```

### Database connection errors
```bash
make mysql-shell        # Check MySQL connectivity
```

### Port conflicts (8080 or 3306 in use)
Edit docker-compose.yml ports section and restart

## For AI/CLI Integration
- Project root: `/home/scam/work/scam-dev2/`
- Config files: `.env`, `docker-compose.yml`, `Dockerfile`, `Makefile`
- Source code: `assets/` directory
- Build output: `build/` directory (generated)
- Docker volumes: Named volumes (mysql_data, wordpress_data)
- Theme path in container: `/var/www/html/wp-content/themes/scam-dev/`
