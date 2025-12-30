# Database Development Skill

## Overview

This skill covers database design, migrations, and data management for the Laravel document scanner application.

## Prerequisites

- Laravel migration system knowledge
- MySQL database understanding
- Database design principles
- Eloquent ORM knowledge
- Index optimisation understanding

## Database Design Principles

### Naming Conventions

- Tables: snake_case (e.g., `document_scans`)
- Columns: snake_case (e.g., `created_at`)
- Models: PascalCase (e.g., `DocumentScan`)
- Foreign keys: `{table}_id` (e.g., `user_id`)

### Schema Design

- Use proper data types for columns
- Add appropriate constraints
- Index frequently queried columns
- Use proper foreign key relationships
- Consider future scalability

## Migration Development

### Migration Structure

```php
// database/migrations/2024_01_01_000000_create_document_scans_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->fullText('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_scans');
    }
};
```

### Migration Best Practices

- All migrations must be reversible
- Use proper foreign key constraints with cascade actions
- Add indexes for performance
- Use appropriate column types
- Include proper default values where needed

## Model Development

### Model Structure

```php
// app/Models/DocumentScan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes for common queries
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
```

## Database Relationships

### Relationship Types

- One-to-One: `hasOne()` / `belongsTo()`
- One-to-Many: `hasMany()` / `belongsTo()`
- Many-to-Many: `belongsToMany()`
- Has Many Through: `hasManyThrough()`
- Polymorphic: `morphMany()` / `morphTo()`

### Relationship Best Practices

- Define relationships in both models
- Use proper foreign key naming
- Consider eager loading for performance
- Use cascade deletes where appropriate
- Add proper constraints

## Query Optimisation

### Indexing Strategy

- Primary keys automatically indexed
- Add indexes to foreign key columns
- Add indexes to frequently queried columns
- Use composite indexes for multi-column queries
- Consider full-text indexes for text search

### Query Best Practices

- Use eager loading to prevent N+1 problems
- Select only needed columns
- Use proper query scopes
- Consider database query caching
- Monitor slow query logs

## Database Seeding

### Seeder Structure

```php
// database/seeders/DocumentScanSeeder.php
namespace Database\Seeders;

use App\Models\DocumentScan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentScanSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        DocumentScan::factory()
            ->count(50)
            ->recycle($users)
            ->create();
    }
}
```

### Factory Development

```php
// database/factories/DocumentScanFactory.php
namespace Database\Factories;

use App\Models\DocumentScan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentScanFactory extends Factory
{
    protected $model = DocumentScan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'file_path' => $this->faker->filePath(),
            'metadata' => [
                'size' => $this->faker->numberBetween(1000, 1000000),
                'type' => $this->faker->mimeType(),
            ],
        ];
    }
}
```

## Quality Checklist

Before completing database tasks:

- [ ] Migration is reversible
- [ ] Proper foreign key constraints are set
- [ ] Appropriate indexes are added
- [ ] Naming conventions are followed
- [ ] Model relationships are correctly defined
- [ ] Factory produces realistic data
- [ ] Seeder runs without errors

## Database Commands

### Common Commands

```bash
# Create migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback migration
php artisan migrate:rollback

# Fresh migration (drop all tables)
php artisan migrate:fresh

# Create factory
php artisan make:model ModelName -f

# Create seeder
php artisan make:seeder SeederName

# Run seeders
php artisan db:seed
```

## Performance Considerations

- Monitor query performance
- Use proper indexing strategies
- Consider database partitioning for large tables
- Implement proper caching strategies
- Regular database maintenance

## Backup and Recovery

- Regular database backups
- Test recovery procedures
- Document backup schedules
- Monitor backup success rates
- Store backups securely

## Security Considerations

- Use parameterised queries
- Validate input data
- Implement proper access controls
- Encrypt sensitive data
- Regular security audits
