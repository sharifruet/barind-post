# Barind Post - Bengali News Portal

A modern Bengali news portal built with CodeIgniter 4, featuring a comprehensive content management system with user roles, categories, tags, and featured content management.

## Features

### Public Site
- **Homepage** with featured and latest news
- **Category-based sections** for organized content browsing
- **Tag-based filtering** for related content discovery
- **Search functionality** across titles, subtitles, and content
- **Responsive design** for mobile and desktop viewing
- **Bengali language support** with proper UTF-8 encoding

### Admin Panel
- **User Management** with role-based access control
- **News Management** with rich text editing and image uploads
- **Category Management** for content organization
- **Tag Management** for content tagging and filtering
- **Featured Content** toggle for highlighting important articles
- **Image Management** with upload and selection capabilities

### Technical Features
- **CodeIgniter 4** framework for robust PHP development
- **MySQL Database** with proper UTF-8 support for Bengali text
- **Docker Support** for easy development and deployment
- **Role-based Authentication** system
- **File Upload** handling with security measures
- **SEO-friendly URLs** with slug-based routing

## Prerequisites

- **Docker** and **Docker Compose** (recommended for development)
- **PHP 8.1+** (if running locally)
- **MySQL 8.0+** (if running locally)
- **Composer** (if running locally)

## Quick Start with Docker

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd barind-post
   ```

2. **Start the application**:
   ```bash
   docker-compose up -d
   ```

3. **Access the application**:
   - **Public Site**: http://localhost
   - **Admin Panel**: http://localhost/admin
   - **Database**: localhost:3306 (root/toor)

4. **Default Admin Credentials**:
   - Check the database seeder or create an admin user through the registration process

## Local Development Setup

If you prefer to run without Docker:

1. **Install dependencies**:
   ```bash
   composer install
   ```

2. **Configure environment**:
   ```bash
   cp env .env
   # Edit .env with your database and application settings
   ```

3. **Set up database**:
   - Create a MySQL database
   - Import `dbscript.sql` to set up the schema
   - Update database credentials in `.env`

4. **Set permissions**:
   ```bash
   chmod -R 755 writable/
   ```

5. **Start development server**:
   ```bash
   php spark serve
   ```

## Project Structure

```
barind-post/
├── app/
│   ├── Controllers/          # Application controllers
│   │   ├── Admin.php        # Admin panel functionality
│   │   ├── PublicSite.php   # Public site functionality
│   │   └── Auth.php         # Authentication handling
│   ├── Models/              # Database models
│   │   ├── NewsModel.php    # News article management
│   │   ├── UserModel.php    # User management
│   │   ├── CategoryModel.php # Category management
│   │   └── TagModel.php     # Tag management
│   ├── Views/               # View templates
│   │   ├── admin/           # Admin panel views
│   │   └── public/          # Public site views
│   └── Config/              # Configuration files
├── public/                  # Public assets and uploads
├── docker/                  # Docker configuration
├── writable/                # Writable directories
└── vendor/                  # Composer dependencies
```

## Database Schema

The application uses the following main tables:
- `users` - User accounts and authentication
- `roles` - User role definitions
- `news` - News articles with content and metadata
- `categories` - Content categories
- `tags` - Content tags
- `news_tags` - Many-to-many relationship between news and tags

## Configuration

### Environment Variables

Key configuration options in `.env`:

```env
# Application
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost'

# Database
database.default.hostname = localhost
database.default.database = barindpost
database.default.username = barinduser
database.default.password = barindpass
database.default.DBDriver = MySQLi

# Security
encryption.key = your-32-character-encryption-key
```

### File Uploads

- Upload directory: `public/uploads/news/`
- Supported formats: JPG, PNG, WebP, GIF
- Maximum file size: Configured in PHP settings

## Development

### Running Tests
```bash
composer test
```

### Database Migrations
```bash
php spark migrate
```

### Database Seeding
```bash
php spark db:seed RoleSeeder
```

## Deployment

For production deployment, see [DEPLOYMENT.md](DEPLOYMENT.md) for detailed cPanel deployment instructions.

### Production Checklist
- [ ] Set `CI_ENVIRONMENT = production`
- [ ] Configure secure database credentials
- [ ] Set up HTTPS
- [ ] Configure proper file permissions
- [ ] Enable caching
- [ ] Set up backup strategy

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions:
- Check the [CodeIgniter 4 documentation](https://codeigniter.com/user_guide/)
- Review the deployment guide in [DEPLOYMENT.md](DEPLOYMENT.md)
- Check error logs in `writable/logs/`

## Server Requirements

- **PHP**: 8.1 or higher
- **Extensions**: intl, mbstring, json, mysqli, curl, gd
- **Database**: MySQL 8.0+ or MariaDB 10.3+
- **Web Server**: Apache (with mod_rewrite) or Nginx

> **Note**: PHP 7.4 and 8.0 are end-of-life. Please upgrade to PHP 8.1+ for security and performance.
