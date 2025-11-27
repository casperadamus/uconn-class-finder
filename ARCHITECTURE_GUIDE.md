# UConn Class Finder - Architecture & Code Explanation

## 📚 Table of Contents
1. [What is PHP?](#what-is-php)
2. [What is Laravel?](#what-is-laravel)
3. [How Your Application Works](#how-your-application-works)
4. [Directory Structure Explained](#directory-structure-explained)
5. [Code Flow & Examples](#code-flow--examples)
6. [Database Relationships](#database-relationships)
7. [Key Concepts](#key-concepts)

---

## What is PHP?

**PHP** (Hypertext Preprocessor) is a **server-side scripting language** that runs on a web server.

### How PHP Works:
```
1. User visits: http://localhost:8000/courses
2. Web server receives the request
3. PHP processes the code on the SERVER (not in browser)
4. PHP generates HTML/JSON
5. Server sends HTML back to user's browser
6. Browser displays the page
```

### PHP vs JavaScript:
- **PHP**: Runs on the **server** (your computer or web hosting)
- **JavaScript**: Runs in the **browser** (user's computer)

### Example PHP Code:
```php
<?php
// This is a PHP opening tag - ALL PHP code starts with this

namespace App\Models;  // Organizes code into folders/categories

class Course extends Model  // Define a "blueprint" for Course data
{
    // Properties (variables that belong to this class)
    protected $fillable = ['code', 'title'];
    
    // Methods (functions that belong to this class)
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
```

---

## What is Laravel?

**Laravel** is a **PHP framework** - a pre-built collection of code that handles common web application tasks so you don't have to build everything from scratch.

### What Laravel Provides:

#### 1. **MVC Pattern** (Model-View-Controller)
```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│   BROWSER   │ ────→   │   ROUTES     │ ────→   │ CONTROLLER  │
│  (Request)  │         │ (web.php)    │         │  (Logic)    │
└─────────────┘         └──────────────┘         └─────────────┘
                                                        │
                                                        ↓
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│   BROWSER   │ ←────   │    VIEW      │ ←────   │    MODEL    │
│  (Response) │         │ (.blade.php) │         │  (Database) │
└─────────────┘         └──────────────┘         └─────────────┘
```

#### 2. **Eloquent ORM** (Database Made Easy)
Instead of writing SQL:
```sql
SELECT * FROM courses WHERE subject = 'CSE';
```

You write PHP:
```php
Course::where('subject', 'CSE')->get();
```

#### 3. **Artisan Commands** (Command-Line Tools)
```bash
php artisan serve              # Start web server
php artisan migrate            # Create database tables
php artisan crawl:uconn-courses  # Your custom command
```

#### 4. **Routing** (URL Management)
```php
Route::get('/courses', function() {
    // This code runs when someone visits /courses
});
```

---

## How Your Application Works

### The Complete Journey of a Request:

#### **Example: User visits `/courses`**

```
STEP 1: Browser Request
User types: http://localhost:8000/courses
Browser sends HTTP GET request

    ↓

STEP 2: Laravel Router (routes/web.php)
Route::get('/courses', function () {
    // This anonymous function runs
    
    // Query database for courses
    $courses = Course::with('sections')->paginate(20);
    
    // Return a view with data
    return view('courses.index', compact('courses'));
});

    ↓

STEP 3: Eloquent ORM (app/Models/Course.php)
Course::with('sections')  // "Get courses WITH their sections"

Laravel translates this to SQL:
SELECT * FROM courses
SELECT * FROM sections WHERE course_id IN (1,2,3...)

    ↓

STEP 4: Database Returns Data
MySQL/PostgreSQL returns course records

    ↓

STEP 5: View Rendering (resources/views/courses/index.blade.php)
Blade template engine processes:
@foreach($courses as $course)
    <h2>{{ $course->title }}</h2>
@endforeach

Converts to HTML:
<h2>Introduction to Programming</h2>
<h2>Data Structures</h2>

    ↓

STEP 6: Response to Browser
HTML is sent back to user's browser
Browser displays the page
```

---

## Directory Structure Explained

### **app/** - Your Application Code

```
app/
├── Console/Commands/
│   └── CrawlUConnCourses.php    ← Your custom artisan command
├── Http/Controllers/
│   └── (Controllers would go here)
├── Models/
│   ├── Course.php               ← Database "blueprint" for courses
│   └── Section.php              ← Database "blueprint" for sections
├── Providers/
│   └── UConnApiServiceProvider.php  ← Registers services
└── Services/
    └── UConnApiService.php      ← API communication logic
```

### **routes/** - URL Definitions

```php
// routes/web.php
Route::get('/courses', function() { ... });
// When user visits /courses, this code runs
```

### **database/** - Database Setup

```
database/
├── migrations/
│   ├── create_courses_table.php   ← Blueprint for courses table
│   └── create_sections_table.php  ← Blueprint for sections table
└── seeders/
    └── DatabaseSeeder.php         ← Sample data (optional)
```

### **resources/** - Frontend Files

```
resources/
├── views/
│   └── courses/
│       ├── index.blade.php   ← List of all courses
│       └── show.blade.php    ← Single course detail
├── css/
└── js/
```

### **config/** - Configuration Files

```
config/
├── app.php        ← General app settings
├── database.php   ← Database connection settings
└── services.php   ← Third-party services
```

### **vendor/** - Third-Party Code

This is where Composer (PHP's package manager) installs libraries. **Don't edit files here!**

---

## Code Flow & Examples

### Example 1: How the Crawler Works

```php
// Terminal: php artisan crawl:uconn-courses --subject=CSE

// 1. Laravel finds and runs: app/Console/Commands/CrawlUConnCourses.php
class CrawlUConnCourses extends Command
{
    // Command name and options
    protected $signature = 'crawl:uconn-courses {--subject=}';
    
    public function handle(UConnApiService $apiService)
    {
        // Laravel automatically creates UConnApiService instance
        // This is called "Dependency Injection"
        
        $subject = $this->option('subject');  // Gets --subject=CSE
        
        // 2. Call API to get course data
        $results = $apiService->searchClasses('STORR@STORRS', $subject);
        
        // 3. Results look like:
        // [
        //   ['code' => 'CSE 1010', 'title' => 'Intro Programming', 'crn' => '12345'],
        //   ['code' => 'CSE 2050', 'title' => 'Data Structures', 'crn' => '12346'],
        // ]
        
        // 4. Group by course code
        foreach ($results as $section) {
            // 5. Create/update database records
            $course = Course::updateOrCreate(
                ['code' => $section['code']],  // Search criteria
                ['title' => $section['title']]  // Data to save
            );
            
            // This generates SQL like:
            // INSERT INTO courses (code, title) VALUES ('CSE 1010', 'Intro Programming')
            // ON DUPLICATE KEY UPDATE title = 'Intro Programming'
        }
    }
}
```

### Example 2: How API Service Works

```php
// app/Services/UConnApiService.php
class UConnApiService
{
    private $baseUrl = 'https://classes.uconn.edu/api/';
    
    public function searchClasses($campus, $subject)
    {
        // 1. Build request payload (data to send)
        $payload = [
            'other' => ['srcdb' => '1263'],  // Spring 2026 term
            'criteria' => [
                ['field' => 'camp', 'value' => $campus],
                ['field' => 'subject', 'value' => $subject]
            ]
        ];
        
        // 2. Send HTTP POST request to UConn API
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'User-Agent' => 'Mozilla/5.0...'
        ])->post($this->baseUrl . '?page=fose&route=search', $payload);
        
        // 3. Check if request was successful
        if ($response->successful()) {
            return $response->json();  // Convert JSON to PHP array
        }
        
        return ['error' => 'Request failed'];
    }
}
```

**What's happening:**
1. Your code makes an HTTP request to UConn's server
2. UConn's server processes it and sends back JSON data
3. Laravel converts JSON to a PHP array you can use

### Example 3: How Models Work

```php
// app/Models/Course.php
class Course extends Model
{
    // What fields can be saved to database
    protected $fillable = ['code', 'subject', 'title'];
    
    // Relationship: A course HAS MANY sections
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}

// app/Models/Section.php
class Section extends Model
{
    // Relationship: A section BELONGS TO one course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

// USAGE:
$course = Course::find(1);  // Get course with ID 1
$sections = $course->sections;  // Get all its sections (automatic query!)

// Laravel automatically runs:
// SELECT * FROM sections WHERE course_id = 1
```

### Example 4: How Routes Work

```php
// routes/web.php

// Simple route
Route::get('/courses', function() {
    $courses = Course::all();  // Get all courses
    return view('courses.index', ['courses' => $courses]);
});

// Route with parameter
Route::get('/courses/{code}', function($code) {
    // {code} in URL becomes $code variable
    // Example: /courses/CSE-1010 → $code = 'CSE-1010'
    
    $course = Course::where('code', $code)->firstOrFail();
    return view('courses.show', ['course' => $course]);
});
```

---

## Database Relationships

### One-to-Many: Course → Sections

```
COURSES TABLE                    SECTIONS TABLE
┌────┬───────────┬─────────┐    ┌────┬───────────┬───────┬─────┐
│ id │   code    │  title  │    │ id │ course_id │  crn  │ ... │
├────┼───────────┼─────────┤    ├────┼───────────┼───────┼─────┤
│ 1  │ CSE 1010  │ Intro.. │◄───┤ 1  │     1     │ 12345 │ ... │
│    │           │         │    ├────┼───────────┼───────┼─────┤
│    │           │         │◄───┤ 2  │     1     │ 12346 │ ... │
│    │           │         │    ├────┼───────────┼───────┼─────┤
├────┼───────────┼─────────┤◄───┤ 3  │     1     │ 12347 │ ... │
│ 2  │ CSE 2050  │ Data... │    └────┴───────────┴───────┴─────┘
└────┴───────────┴─────────┘         Foreign Key
        Primary Key                   Links to Course
```

**In Code:**
```php
// Get a course and its sections
$course = Course::find(1);
echo $course->title;  // "Intro to Programming"

foreach ($course->sections as $section) {
    echo $section->crn;  // 12345, 12346, 12347
}

// Go backwards: Get section's course
$section = Section::find(1);
echo $section->course->title;  // "Intro to Programming"
```

---

## Key Concepts

### 1. **Namespaces** (Code Organization)

```php
namespace App\Models;  // This file is in app/Models/ folder

use App\Services\UConnApiService;  // Import from another namespace
```

Think of namespaces like folders on your computer. They prevent naming conflicts.

### 2. **Dependency Injection** (Automatic Object Creation)

```php
public function handle(UConnApiService $apiService)
{
    // Laravel sees you need UConnApiService
    // It automatically creates one and passes it in
    // You don't have to write: $apiService = new UConnApiService();
}
```

### 3. **Eloquent ORM** (Database Abstraction)

```php
// Instead of SQL:
$result = mysqli_query($conn, "SELECT * FROM courses WHERE subject = 'CSE'");

// You write:
$courses = Course::where('subject', 'CSE')->get();
```

### 4. **Migrations** (Database Version Control)

```php
// database/migrations/create_courses_table.php
Schema::create('courses', function (Blueprint $table) {
    $table->id();                    // CREATE COLUMN id (auto-increment)
    $table->string('code');          // CREATE COLUMN code (varchar)
    $table->string('title');         // CREATE COLUMN title (varchar)
    $table->timestamps();            // created_at, updated_at columns
});
```

Run with: `php artisan migrate`

### 5. **Blade Templates** (View Engine)

```blade
{{-- resources/views/courses/index.blade.php --}}
<h1>Courses</h1>

@foreach($courses as $course)
    <div class="course">
        <h2>{{ $course->code }}</h2>
        <p>{{ $course->title }}</p>
        
        @if($course->sections->count() > 0)
            <p>Sections: {{ $course->sections->count() }}</p>
        @else
            <p>No sections available</p>
        @endif
    </div>
@endforeach
```

- `{{ $variable }}` - Echo variable (auto-escaped for security)
- `@if`, `@foreach` - PHP control structures
- `{{-- comment --}}` - Blade comment (not in HTML output)

### 6. **HTTP Client** (Making API Requests)

```php
use Illuminate\Support\Facades\Http;

// Make POST request
$response = Http::post('https://api.example.com', [
    'key' => 'value'
]);

// Check response
if ($response->successful()) {
    $data = $response->json();  // Get JSON as PHP array
}
```

### 7. **Collections** (Enhanced Arrays)

```php
$courses = Course::all();  // Returns a Collection, not plain array

// Collection methods:
$courses->count();
$courses->filter(function($course) {
    return $course->credits > 3;
});
$courses->map(function($course) {
    return $course->code;
});
$courses->first();
$courses->pluck('title');  // Get array of just titles
```

---

## Real-World Example: Complete Request Flow

### User visits: `http://localhost:8000/courses/CSE-1010`

```
1. WEB SERVER (public/index.php)
   ↓ Receives request
   ↓ Loads Laravel framework
   
2. ROUTER (routes/web.php)
   ↓ Matches URL pattern: /courses/{code}
   ↓ Extracts: $code = 'CSE-1010'
   
3. ROUTE CLOSURE/CONTROLLER
   Route::get('/courses/{code}', function($code) {
       // Query database
       $course = Course::where('code', $code)
                       ->with('sections')
                       ->firstOrFail();
       
       // Load view with data
       return view('courses.show', compact('course'));
   });
   
4. ELOQUENT (Models)
   Course::where('code', 'CSE-1010')->with('sections')->firstOrFail()
   ↓ Generates SQL:
   SELECT * FROM courses WHERE code = 'CSE-1010' LIMIT 1
   SELECT * FROM sections WHERE course_id = 1
   
5. DATABASE
   ↓ Returns course data + section data
   
6. BLADE VIEW (resources/views/courses/show.blade.php)
   <h1>{{ $course->title }}</h1>
   <p>{{ $course->description }}</p>
   
   @foreach($course->sections as $section)
       <div>Section {{ $section->section_number }}</div>
   @endforeach
   
   ↓ Compiles to PHP
   ↓ Executes PHP to generate HTML
   
7. HTML RESPONSE
   <h1>Introduction to Programming</h1>
   <p>First course in computer science...</p>
   <div>Section 001</div>
   <div>Section 002</div>
   
8. BROWSER
   ↓ Receives HTML
   ↓ Renders page for user
```

---

## Common Commands Explained

```bash
# Install dependencies (downloads code from composer.json)
composer install

# Generate encryption key for .env file
php artisan key:generate

# Create database tables from migrations
php artisan migrate

# Start development web server at http://localhost:8000
php artisan serve

# Run your custom crawler command
php artisan crawl:uconn-courses --subject=CSE

# Clear application cache
php artisan cache:clear

# View all available artisan commands
php artisan list
```

---

## Tips for Understanding PHP/Laravel

1. **Read from top to bottom** - PHP executes sequentially
2. **Follow the data** - Track how data flows from API → Database → View
3. **Use `dd($variable)`** - "Dump and Die" - shows variable contents and stops execution
4. **Check Laravel docs** - https://laravel.com/docs - excellent documentation
5. **Think in objects** - `$course->sections` means "get sections property of course object"

---

## Summary

Your application:
1. **Crawls** course data from UConn API (using `UConnApiService`)
2. **Stores** it in a database (using `Course` and `Section` models)
3. **Displays** it on web pages (using Blade views and routes)
4. **Uses Laravel** to tie it all together with minimal boilerplate code

The power of Laravel is that it handles:
- Database queries (Eloquent)
- URL routing
- Security (CSRF protection, SQL injection prevention)
- View rendering
- Dependency injection
- And much more...

So you can focus on your application's unique features!
