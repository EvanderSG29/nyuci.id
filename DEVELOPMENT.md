# 🚀 Development Guide - NYUCI.ID

Panduan lengkap untuk development NYUCI.ID. Dokumentasi ini untuk developer yang akan berkontribusi atau melanjutkan project ini.

---

## 📋 Daftar Isi

1. [Setup Environment](#1-setup-environment)
2. [Project Structure](#2-project-structure)
3. [Git Workflow](#3-git-workflow)
4. [Coding Standards](#4-coding-standards)
5. [Database Management](#5-database-management)
6. [Testing](#6-testing)
7. [Debugging Tips](#7-debugging-tips)
8. [Common Tasks](#8-common-tasks)

---

## 1. Setup Environment

### Prerequisites Minimal

```bash
PHP >= 8.3
Composer
Node.js >= 18
MySQL/MariaDB
Git
```

### Instalasi Development Environment

**Clone & Setup:**
```bash
git clone https://github.com/EvanderSG29/nyuci.id.git
cd nyuci.id
git checkout develop  # Pastikan di branch develop

# Install dependencies
composer install
npm install

# Setup .env
cp .env.example .env
php artisan key:generate

# Setup database
# Edit .env dengan database credentials Anda
php artisan migrate
php artisan db:seed  # Optional
```

**Start Development Servers:**
```bash
# Terminal 1: PHP Development Server
php artisan serve

# Terminal 2: Asset compilation
npm run dev

# Akses: http://localhost:8000
```

### IDE Recommendations

- **VS Code** + Laravel Extensions:
  - Laravel Blade Snippets
  - Laravel goto view
  - PHP Intelephense
  - MySQL Client

- **PhpStorm** (berbayar):
  - Built-in Laravel support
  - Database integration
  - Git integration

---

## 2. Project Structure

### Folder Organization

```
nyuci.id/
├── app/
│   ├── Actions/          → Business logic (Fortify)
│   │   └── Fortify/      → Authentication actions
│   ├── Http/
│   │   ├── Controllers/  → Route handlers
│   │   │   ├── LaundryController.php
│   │   │   ├── PembayaranController.php
│   │   │   └── ProfileController.php
│   │   ├── Requests/     → Form validation
│   │   │   └── ...
│   │   └── Responses/    → JSON responses
│   ├── Models/           → Database models
│   │   ├── User.php
│   │   ├── Toko.php
│   │   ├── Laundry.php
│   │   └── Pembayaran.php
│   ├── Policies/         → Authorization logic
│   │   ├── LaundryPolicy.php
│   │   └── PembayaranPolicy.php
│   └── Providers/        → Service providers
│       ├── AppServiceProvider.php
│       └── FortifyServiceProvider.php
│
├── bootstrap/            → App bootstrap files
├── config/               → Configuration files
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   └── fortify.php
│
├── database/
│   ├── migrations/       → Schema changes
│   │   ├── *_create_users_table.php
│   │   ├── *_create_tokos_table.php
│   │   ├── *_create_laundries_table.php
│   │   └── *_create_pembayarans_table.php
│   ├── factories/        → Fake data generators
│   │   └── UserFactory.php
│   └── seeders/          → Initial data seeders
│       └── DatabaseSeeder.php
│
├── resources/
│   ├── css/              → Tailwind CSS
│   │   └── app.css
│   ├── js/               → JavaScript/Alpine.js
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views/            → Blade templates
│       ├── welcome.blade.php
│       ├── dashboard.blade.php
│       ├── layouts/      → Page layouts
│       ├── auth/         → Auth pages
│       ├── laundry/      → Laundry CRUD views
│       ├── pembayaran/   → Payment CRUD views
│       ├── profile/      → Profile pages
│       └── components/   → Reusable components
│
├── routes/
│   ├── web.php           → Web routes
│   └── console.php       → Artisan commands
│
├── storage/              → Logs, cache, uploads
│   ├── app/
│   ├── framework/
│   └── logs/
│
├── tests/
│   ├── Feature/          → Feature tests
│   ├── Unit/             → Unit tests
│   ├── TestCase.php      → Base test class
│   └── Pest.php          → Pest configuration
│
├── vendor/               → Composer packages
├── node_modules/         → NPM packages
├── .env.example          → Environment template
├── artisan               → Artisan CLI
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.js
├── README.md
├── CHANGELOG.md
├── CONTRIBUTING.md
└── DEVELOPMENT.md        → File ini
```

---

## 3. Git Workflow

### Branch Convention

```
main              → Production/Release branch
develop           → Development branch (working)
feature/*         → Feature branch (feature/staff-management)
bugfix/*          → Bug fix branch (bugfix/login-issue)
hotfix/*          → Production hotfix (hotfix/critical-bug)
```

### Workflow: Tambah Fitur Baru

**Step 1: Buat Feature Branch**
```bash
# Update develop ke versi terbaru
git checkout develop
git pull origin develop

# Buat feature branch dari develop
git checkout -b feature/nama-fitur-mu
# Contoh: git checkout -b feature/staff-management
```

**Step 2: Development (push berkala)**
```bash
# Setiap ada progress, commit
git add .
git commit -m "feat: deskripsi singkat fitur"

# Push ke GitHub
git push origin feature/nama-fitur-mu
```

**Step 3: Siap Merge (buat Pull Request)**
```bash
# Pastikan up-to-date dengan develop
git fetch origin
git rebase origin/develop

# Push (mungkin perlu force jika ada rebase)
git push origin feature/nama-fitur-mu
```

Di GitHub:
1. Buka Pull Request dari `feature/nama-fitur` → `develop`
2. Tambah deskripsi apa yang di-implement
3. Tag reviewer jika perlu
4. Tunggu approval
5. Merge PR

**Step 4: Cleanup**
```bash
# Hapus branch lokal
git branch -d feature/nama-fitur-mu

# Hapus branch remote (atau pake tombol di GitHub)
git push origin --delete feature/nama-fitur-mu
```

### Commit Message Format

```
feat: tambah fitur baru
fix: perbaiki bug
docs: update dokumentasi
refactor: reorganisasi code
style: formatting/linting
test: tambah/update test
chore: dependency updates
```

**Contoh commit message yang baik:**
```
feat: implementasi staff management dengan role checking
fix: perbaiki validasi nomor HP pada form input laundry
docs: update README untuk fitur staff management
refactor: extract validation logic ke FormRequest
test: add unit test untuk LaundryController
```

---

## 4. Coding Standards

### PHP Style Guide (PSR-12)

**File Structure:**
```php
<?php

namespace App\Http\Controllers;

use App\Models\Laundry;
use Illuminate\Http\Request;

class LaundryController extends Controller
{
    // 1. Properties
    private $someProperty = 'value';

    // 2. Constructor
    public function __construct()
    {
        //
    }

    // 3. Public methods
    public function index()
    {
        //
    }

    // 4. Protected methods
    protected function someHelper()
    {
        //
    }

    // 5. Private methods
    private function internalLogic()
    {
        //
    }
}
```

**Naming Conventions:**

| Element | Convention | Contoh |
|---------|-----------|--------|
| Classes | PascalCase | `LaundryController` |
| Methods | camelCase | `markAsPaid()` |
| Properties | camelCase | `$totalWeight` |
| Constants | UPPER_SNAKE_CASE | `MAX_WEIGHT` |
| Database tables | snake_case (plural) | `laundries` |
| Database columns | snake_case | `is_taken` |
| Routes | kebab-case | `/laundry/{id}/toggle` |
| Views | kebab-case | `laundry-form.blade.php` |

**Code Style:**

```php
// ✅ Good
public function store(StoreLaundryRequest $request)
{
    $laundry = $this->toko->laundries()->create(
        $request->validated()
    );
    
    return redirect()->route('laundry.show', $laundry);
}

// ❌ Bad
public function store(Request $request) {
    $l = new Laundry;
    $l->nama = $request->input('nama');
    $l->no_hp = $request->input('no_hp');
    $l->save();
    return redirect('/');
}
```

### Laravel Best Practices

**Model Usage:**
```php
// ✅ Use model methods
$laundries = Laundry::where('toko_id', $toko->id)->get();
$laundry->markAsPaid();

// ❌ Avoid raw queries
$laundries = DB::table('laundries')->where('toko_id', $toko_id)->get();
```

**Route Protection:**
```php
// Routes harus auth & authorized
Route::middleware(['auth'])->group(function () {
    Route::resource('laundry', LaundryController::class);
});

// Gunakan Policies untuk detail authorization
// Lihat: LaundryPolicy.php
```

**Validation:**
```php
// Gunakan FormRequest, bukan inline validation
// app/Http/Requests/StoreLaundryRequest.php
class StoreLaundryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->toko_id === request('toko_id');
    }
    
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|numeric',
            'berat' => 'required|numeric|min:0.1',
        ];
    }
}
```

---

## 5. Database Management

### Create New Migration

```bash
# Buat migration
php artisan make:migration create_something_table

# Edit file di database/migrations/
# Jalankan
php artisan migrate
```

### Migration Best Practices

```php
Schema::create('something', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('user_id')->constrained(); // FK dengan cascade
    $table->enum('status', ['pending', 'done'])->default('pending');
    $table->timestamps(); // created_at, updated_at
});
```

### Database Reset (HATI-HATI!)

```bash
# Fresh migration (reset semua data! ⚠️)
php artisan migrate:fresh

# Fresh + seed
php artisan migrate:fresh --seed
```

### Create Model dengan Migration

```bash
# Buat model + migration sekaligus
php artisan make:model ModelName -m

# Dengan controller
php artisan make:model ModelName -m -c
```

---

## 6. Testing

### Run Tests

```bash
# Semua test
php artisan test

# Test spesifik file
php artisan test tests/Feature/LaundryTest.php

# Test dengan coverage
php artisan test --coverage

# Test dengan verbosity
php artisan test -v
```

### Create Test

```bash
# Feature test
php artisan make:test LaundryTest

# Unit test
php artisan make:test LaundryModelTest --unit
```

### Test Example

```php
// tests/Feature/LaundryTest.php
use Tests\TestCase;

class LaundryTest extends TestCase
{
    #[Test]
    public function user_can_create_laundry()
    {
        $user = User::factory()->create();
        $toko = Toko::factory()->create(['user_id' => $user->id]);
        
        $response = $this->actingAs($user)
            ->post('/laundry', [
                'nama' => 'John Doe',
                'no_hp' => '081234567890',
                'berat' => 5.5,
                'layanan' => 'cuci',
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('laundries', ['nama' => 'John Doe']);
    }
}
```

---

## 7. Debugging Tips

### Laravel Debugging Tools

```php
// 1. Gunakan dd() untuk debug
$laundries = Laundry::all();
dd($laundries); // Dump & Die

// 2. Gunakan dump() untuk print tanpa die
dump($laundries);
dump('Berhasil sampai sini');

// 3. Log untuk production
Log::info('Debug info', ['data' => $laundries]);

// 4. Tinker REPL
php artisan tinker
> User::first()
> Toko::factory()->create()
```

### Check Logs

```bash
# Lihat log real-time
tail -f storage/logs/laravel.log

# Atau gunakan Laravel Pail
php artisan pail

# Filter specific channel
php artisan pail --filter=error
```

### Browser DevTools

```javascript
// Chrome DevTools → Console
// Debug JavaScript dengan:
console.log('data:', data);
console.table([obj1, obj2]); // Lihat dalam table format
```

### Database Debugging

```bash
# Check current migrations
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Rollback specific batch
php artisan migrate:rollback --step=1
```

---

## 8. Common Tasks

### Add New Feature (Step-by-step)

**Scenario: Tambah "estimasi biaya" ke form input laundry**

#### 1. Create Migration
```bash
php artisan make:migration add_estimasi_biaya_to_laundries_table

# Edit migration file:
Schema::table('laundries', function (Blueprint $table) {
    $table->decimal('estimasi_biaya', 10, 2)->nullable();
});

php artisan migrate
```

#### 2. Update Model
```php
// app/Models/Laundry.php
protected $fillable = [
    'toko_id', 'nama', 'no_hp', 'berat', 
    'tanggal', 'layanan', 'estimasi_selesai',
    'estimasi_biaya', // ← TAMBAH INI
    'is_taken',
];
```

#### 3. Update Form Request
```php
// app/Http/Requests/StoreLaundryRequest.php
public function rules(): array
{
    return [
        'nama' => 'required|string',
        'no_hp' => 'required|numeric',
        'estimasi_biaya' => 'nullable|numeric|min:0', // ← TAMBAH
        // ... rules lain
    ];
}
```

#### 4. Update View
```blade
{{-- resources/views/laundry/form.blade.php --}}
<div class="mb-4">
    <label for="estimasi_biaya">Estimasi Biaya</label>
    <input type="number" 
           name="estimasi_biaya" 
           step="0.01"
           value="{{ $laundry->estimasi_biaya ?? '' }}"
           class="form-input">
</div>
```

#### 5. Test Feature
```php
// tests/Feature/LaundryTest.php
#[Test]
public function can_create_laundry_with_estimasi_biaya()
{
    $response = $this->actingAs($user)
        ->post('/laundry', [
            'nama' => 'John',
            'estimasi_biaya' => 50000,
        ]);
    
    $this->assertDatabaseHas('laundries', [
        'estimasi_biaya' => 50000,
    ]);
}
```

#### 6. Commit & Push
```bash
git add .
git commit -m "feat: tambah estimasi_biaya ke form input laundry"
git push origin feature/estimasi-biaya
```

---

### Update Package

```bash
# Update single package
composer update laravel/framework

# Update all packages (HATI-HATI!)
composer update

# Update npm packages
npm update

# Check outdated packages
composer outdated
npm outdated
```

---

### Deploy to Production

```bash
# Optimize for production
php artisan optimize

# Build assets
npm run build

# Run migrations (jika ada)
php artisan migrate --force

# Clear cache
php artisan optimize:clear
```

---

## 📚 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Best Practices](https://laravel-best-practices.com/)
- [Pest Testing](https://pestphp.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)

---

## ❓ FAQ

**Q: Bagaimana jika ada conflict saat merge?**  
A: Resolve conflict di text editor, lalu `git add` & `git commit`

**Q: Boleh langsung push ke main?**  
A: TIDAK! Selalu via develop dulu, baru ke main saat release

**Q: Berapa lama feature branch biasanya?**  
A: 1-3 hari untuk fitur kecil, lebih untuk fitur besar

**Q: Database saya corrupt, bagaimana?**  
A: `php artisan migrate:fresh --seed` (hapus semua data!)

---

<div align="center">

**Happy Coding! 🚀**

Jika ada pertanyaan, buka [GitHub Issues](https://github.com/EvanderSG29/nyuci.id/issues)

</div>
