# UConn Class Finder

A Laravel-based application that crawls and indexes course data from classes.uconn.edu, providing an easy-to-use interface for searching and viewing UConn course information.

## Features

- **Course Crawling**: Automated data collection from UConn's course catalog
- **Relational Data**: Properly structured Course and Section models with relationships
- **Campus Support**: Filter courses by campus (Storrs, Hartford, Stamford, Waterbury, Avery Point)
- **Subject Filtering**: Crawl specific departments or all subjects
- **Detailed Information**: Section details including instructors, times, and locations

## Prerequisites

- PHP >= 8.1
- Composer
- MySQL/MariaDB or PostgreSQL

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd uconn-class-finder
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install NPM dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   
   Edit `.env` and set your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=uconn_class_finder
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build frontend assets**
   ```bash
   npm run dev
   ```

## Usage

### Starting the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### Crawling Course Data

#### Crawl All Courses (Basic)
```bash
php artisan crawl:uconn-courses
```

#### Crawl All Courses with Details
```bash
php artisan crawl:uconn-courses --details
```

#### Crawl Specific Subject
```bash
php artisan crawl:uconn-courses --subject=ANTH --details
```

#### Crawl by Campus
```bash
php artisan crawl:uconn-courses --subject=ANTH --campus=HART
```

**Available Campus Codes:**
- `STORR` - Storrs Campus
- `HART` - Hartford Campus
- `STAM` - Stamford Campus
- `WATER` - Waterbury Campus
- `AVERY` - Avery Point Campus

## Project Structure

### Models
- **Course** (`app/Models/Course.php`) - Course information with relationships to sections
- **Section** (`app/Models/Section.php`) - Section details including schedules and instructors

### Services
- **UConnApiService** (`app/Services/UConnApiService.php`) - Clean, documented service for interacting with UConn's course API

### Commands
- **CrawlUConnCourses** (`app/Console/Commands/`) - Artisan command for crawling course data

### Views
- `resources/views/courses/index.blade.php` - Course listing page
- `resources/views/courses/show.blade.php` - Individual course details

### Routes
- `routes/web.php` - Web routes for course views
- `routes/api.php` - API endpoints (if applicable)

## Database Schema

### Courses Table
- Course code, title, description
- Credits, prerequisites
- Campus and term information

### Sections Table
- Section number and type
- Instructor information
- Meeting times and locations
- Enrollment capacity and availability

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Clearing Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Troubleshooting

### Database Connection Issues
- Verify your `.env` database credentials
- Ensure your database server is running
- Check that the database exists: `CREATE DATABASE uconn_class_finder;`

### Migration Errors
- Reset migrations: `php artisan migrate:fresh` (⚠️ Warning: This will delete all data)
- Check for conflicting migrations

### API Rate Limiting
- The crawler may be rate-limited by UConn's servers
- Consider adding delays between requests if encountering issues

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Acknowledgments

- Data sourced from [classes.uconn.edu](https://classes.uconn.edu)
- Built with [Laravel](https://laravel.com)

