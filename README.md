# 🧺 NYUCI.ID - Laundry Management System

Aplikasi manajemen toko laundry **built-in Indonesia** untuk pemilik toko dan staff. Designed khusus untuk kebutuhan operasional toko laundry modern dengan fitur lengkap dari input order hingga tracking pembayaran.

**Live Demo:** [nyuci.id](https://nyuci.id)

---

## 📋 Daftar Isi

- [Fitur](#-fitur-utama)
- [Prasyarat](#-prasyarat)
- [Instalasi](#-instalasi)
- [Struktur Database](#-struktur-database)
- [API Endpoints](#-api-endpoints)
- [Development Guide](#-development-guide)
- [Troubleshooting](#-troubleshooting)
- [Roadmap](#-roadmap)

---

## ✨ Fitur Utama

### ✅ Versi 1.0.0 (Current)

#### 👥 **User & Authentication**
- Register akun pemilik toko
- Login dengan email & password
- Two-factor authentication (2FA)
- Profile management (edit data, change password)
- Session management & logout

#### 🏪 **Toko Management**
- Register/setup toko baru per pemilik
- Edit informasi toko (nama, alamat, nomor HP)
- Satu pemilik bisa manage satu toko

#### 📦 **Order Laundry (Laundries)**
- Input order laundry pelanggan (nama, nomor HP, berat, tanggal)
- Pilih jenis layanan: Cuci | Setrika | Keduanya
- Set estimasi tanggal selesai
- Track status order (pending, diambil, belum diambil)
- Toggle status "sudah diambil" / "belum diambil"
- View daftar order terbaru di dashboard

#### 💰 **Managemen Pembayaran**
- Input pembayaran per order
- Track status pembayaran: "belum bayar" | "sudah bayar"
- Mark pembayaran sebagai paid
- Lihat riwayat pembayaran

#### 📊 **Dashboard**
- Stats: Total order, pending pickup, pembayaran terselesaikan
- Daftar order terbaru (5 order terakhir)
- Quick overview omset hari ini

---

## 🛠️ Prasyarat

- **PHP** ≥ 8.3
- **Composer** (dependency manager PHP)
- **Node.js** ≥ 18 (untuk frontend assets)
- **MySQL/MariaDB** ≥ 5.7
- **Git** (untuk version control)

**Untuk Windows:**
- XAMPP (sudah include Apache, PHP, MySQL)
- Atau PHP & MySQL standalone

---

## 📥 Instalasi

### 1️⃣ **Clone Repository**

```bash
git clone https://github.com/EvanderSG29/nyuci.id.git
cd nyuci.id
```

### 2️⃣ **Install Dependencies**

```bash
# Install PHP dependencies menggunakan Composer
composer install

# Install JavaScript dependencies
npm install
```

### 3️⃣ **Setup Environment**

```bash
# Copy .env.example ke .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4️⃣ **Konfigurasi Database**

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nyuci_id
DB_USERNAME=root
DB_PASSWORD=
```

Buat database:

```bash
php artisan migrate
```

### 5️⃣ **Build Frontend Assets**

```bash
# Development
npm run dev

# Production
npm run build
```

### 6️⃣ **Start Server**

```bash
# Buka terminal baru
php artisan serve

# Akses: http://localhost:8000
```

---

## 🗄️ Struktur Database

### Users Table
```
id, email, password, email_verified_at, two_factor_*, timestamps
```

### Tokos Table
```
id, user_id (FK), nama_toko, alamat, no_hp, timestamps
```

### Laundries Table
```
id, toko_id (FK), nama, no_hp, berat, tanggal, layanan, 
estimasi_selesai, is_taken, timestamps
```

### Pembayarans Table
```
id, laundry_id (FK), total, status, timestamps
```

**Relasi:**
- User 1 → 1 Toko
- Toko 1 → Many Laundries
- Laundry 1 → 1 Pembayaran

---

## 🔗 API Endpoints

### Authentication
```
GET  /                          → Home/Dashboard redirect
GET  /forgot-password           → Password reset page
```

### Toko Management
```
GET  /register/toko             → Form register toko
POST /register/toko             → Submit register toko
```

### Laundry Management
```
GET    /laundry                 → List semua laundry
GET    /laundry/create          → Form input laundry baru
POST   /laundry                 → Submit input laundry
GET    /laundry/{id}            → Detail laundry
GET    /laundry/{id}/edit       → Form edit laundry
PUT    /laundry/{id}            → Submit edit laundry
DELETE /laundry/{id}            → Hapus laundry
GET    /laundry/{id}/toggle     → Toggle status is_taken
```

### Pembayaran Management
```
GET    /pembayaran              → List semua pembayaran
GET    /pembayaran/create       → Form input pembayaran
POST   /pembayaran              → Submit input pembayaran
GET    /pembayaran/{id}         → Detail pembayaran
GET    /pembayaran/{id}/paid    → Mark as paid
DELETE /pembayaran/{id}         → Hapus pembayaran
```

### Profile
```
GET   /profile                  → Edit profile page
PATCH /profile                  → Update profile
DELETE /profile                 → Delete account
```

---

## 💻 Development Guide

### Struktur Folder

```
app/
├── Actions/              → Business logic (Fortify actions)
├── Http/
│   ├── Controllers/      → Request handlers
│   ├── Requests/         → Form validation rules
│   └── Responses/        → JSON responses
├── Models/               → Database models
│   ├── User.php
│   ├── Toko.php
│   ├── Laundry.php
│   └── Pembayaran.php
├── Policies/             → Authorization policies
└── Providers/            → Service providers

database/
├── migrations/           → Database schema changes
└── factories/            → Test data generators

routes/
├── web.php               → Web routes
└── console.php           → Artisan commands

resources/
├── css/                  → Stylesheets
├── js/                   → JavaScript
└── views/                → Blade templates

tests/
├── Feature/              → Feature tests
└── Unit/                 → Unit tests
```

### Workflow Development

**Lihat:** [DEVELOPMENT.md](DEVELOPMENT.md) untuk guide lengkap

### Testing

```bash
# Run semua test
php artisan test

# Run test spesifik
php artisan test tests/Feature/LaundryTest.php

# Dengan coverage
php artisan test --coverage
```

### Database

```bash
# Run migration
php artisan migrate

# Rollback migration
php artisan migrate:rollback

# Fresh migration (reset + migrate)
php artisan migrate:fresh

# Seed data (jika ada seeder)
php artisan db:seed
```

---

## 🐛 Troubleshooting

### Problem: `php artisan serve` error

**Solution:**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Generate key
php artisan key:generate
```

### Problem: Migration error "SQLSTATE[HY000]"

**Solution:**
```bash
# Pastikan database sudah dibuat dan credentials di .env benar
php artisan migrate:fresh
```

### Problem: 500 error di browser

**Solution:**
```bash
# Check log
tail storage/logs/laravel.log

# Clear all cache
php artisan optimize:clear
```

### Problem: npm run dev tidak jalan

**Solution:**
```bash
# Clear cache npm
npm cache clean --force

# Reinstall
rm -rf node_modules package-lock.json
npm install
npm run dev
```

---

## 🗺️ Roadmap

### 🔄 Versi 1.1.0 (Upcoming)
- [ ] Staff Management + Authorization
- [ ] Pricing Management per layanan
- [ ] Role-based access control (Pemilik, Staff, Kasir)

### 🔄 Versi 1.2.0 (Planned)
- [ ] Advanced Dashboard dengan Analytics
- [ ] Report generation (PDF/Excel)
- [ ] WhatsApp integration untuk notifikasi pelanggan

### 🔄 Versi 2.0.0 (Future)
- [ ] Multi-tenant support
- [ ] Mobile app (React Native/Flutter)
- [ ] Payment gateway integration (Midtrans, Stripe)
- [ ] Inventory management

---

## 🤝 Contributing

Kontribusi sangat diterima! Silakan baca [CONTRIBUTING.md](CONTRIBUTING.md) untuk guidelines.

### Branch Convention

```
main             → Production (release branch)
develop          → Development (working branch)
feature/*        → Fitur baru (feature/staff-management)
bugfix/*         → Bug fix (bugfix/login-issue)
hotfix/*         → Hot fix production (hotfix/critical-bug)
```

### Commit Message Format

```
feat: deskripsi fitur baru
fix: deskripsi perbaikan bug
docs: perubahan dokumentasi
refactor: perubahan struktur code
style: formatting/linting
test: tambah/update test
```

---

## 📄 Lisensi

Proyek ini menggunakan Lisensi MIT. Lihat [LICENSE](LICENSE) untuk detail.

---

## 📞 Support & Contact

**Issues & Questions?**
- Buka [GitHub Issues](https://github.com/EvanderSG29/nyuci.id/issues)
- Email: support@nyuci.id

---

## 👨‍💻 Author

**Evander SG**  
- GitHub: [@EvanderSG29](https://github.com/EvanderSG29)
- Email: evander@nyuci.id

---

<div align="center">

**Made with ❤️ for Indonesian laundry business owners**

[⬆ Kembali ke atas](#-nyuciid---laundry-management-system)

</div>
