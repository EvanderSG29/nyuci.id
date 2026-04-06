# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-04-06

### 🎉 Initial Release

Rilis pertama NYUCI.ID - Sistem manajemen toko laundry yang lengkap untuk pemilik toko dan staff.

#### ✨ Added (Fitur Baru)

**User & Authentication**
- Register akun baru untuk pemilik toko
- Login dengan email & password
- Two-factor authentication (2FA) dengan Laravel Fortify
- Profile management (edit informasi pengguna)
- Password reset via email
- Session management & logout

**Toko Management**
- Register/setup toko baru setelah user mendaftar
- Edit informasi toko (nama toko, alamat, nomor HP)
- Relasi one-to-one antara User dan Toko
- Pemilik + beberapa staff akan manage satu toko

**Order Laundry (Laundries)**
- Buat order laundry baru untuk pelanggan
- Input data: nama pelanggan, nomor HP, berat cucian, tanggal mulai
- Pilih jenis layanan:
  - Cuci saja
  - Setrika saja
  - Keduanya (cuci + setrika)
- Set tanggal estimasi selesai
- Track status order:
  - Pending (baru diterima)
  - Diproses
  - Siap diambil
  - Sudah diambil
- Toggle status "sudah diambil" / "belum diambil"
- View list order terbaru
- Edit order yang belum selesai
- Hapus order dari sistem

**Pembayaran (Payment)**
- Buat invoice pembayaran per order
- Track status pembayaran:
  - Belum bayar
  - Sudah bayar (lunas)
- Mark pembayaran sebagai paid
- Hapus record pembayaran
- Lihat detail pembayaran

**Dashboard**
- Overview statistik harian:
  - Total order hari ini
  - Jumlah order yang belum diambil
  - Jumlah pembayaran yang sudah selesai
- Daftar 5 order terbaru
- Quick links ke fitur utama

**Frontend**
- Responsive design dengan Tailwind CSS
- Alpine.js untuk interaktivitas
- Blade templates dengan layout reusable
- Authentication scaffolding via Laravel Breeze
- Mobile-friendly UI

**Database**
- Users table dengan 2FA support
- Tokos table untuk manajemen toko
- Laundries table untuk order tracking
- Pembayarans table untuk pembayaran
- Proper migrations & seeding

**Development**
- Laravel 13 framework
- PHP 8.3+ support
- MySQL/MariaDB database
- Vite untuk asset bundling
- Pest untuk testing framework
- Git & GitHub integration

#### 🔧 Technical Details

**Tech Stack:**
- Backend: Laravel 13, PHP 8.3
- Frontend: Blade, Tailwind CSS, Alpine.js
- Database: MySQL
- Build Tools: Vite, npm
- Auth: Laravel Fortify

**Package Dependencies:**
- laravel/framework: ^13.0
- laravel/fortify: * (2FA)
- laravel/tinker: ^3.0
- pestphp/pest: * (testing)

**Browser Support:**
- Chrome/Edge ≥ 90
- Firefox ≥ 88
- Safari ≥ 14
- Mobile browsers

#### 📋 Database Schema

```
users (Pemilik Toko)
├── id, email, password
├── email_verified_at
├── two_factor_secret, two_factor_recovery_codes
└── timestamps

tokos (Informasi Toko)
├── id, user_id (FK) → users
├── nama_toko, alamat, no_hp
└── timestamps

laundries (Order Cucian)
├── id, toko_id (FK) → tokos
├── nama, no_hp, berat
├── tanggal (mulai), estimasi_selesai
├── layanan (enum: cuci, setrika, keduanya)
├── is_taken (boolean)
└── timestamps

pembayarans (Pembayaran Order)
├── id, laundry_id (FK) → laundries
├── total (harga)
├── status (enum: belum_bayar, sudah_bayar)
└── timestamps
```

#### 🔗 Routes

**Web Routes:**
- GET / → redirect to dashboard atau welcome
- GET /dashboard → dashboard overview
- POST /register/toko → register toko baru
- CRUD /laundry → manage orders
- CRUD /pembayaran → manage payments
- CRUD /profile → manage user account

#### 📊 File Structure

```
.
├── app/
│   ├── Models/ → User, Toko, Laundry, Pembayaran
│   ├── Http/Controllers/ → LaundryController, PembayaranController, etc
│   ├── Policies/ → LaundryPolicy, PembayaranPolicy
│   └── Providers/ → AppServiceProvider, FortifyServiceProvider
├── database/
│   ├── migrations/ → Database schema
│   ├── factories/ → Fake data generators
│   └── seeders/ → Initial data
├── resources/
│   ├── views/ → Blade templates
│   ├── css/ → Tailwind CSS
│   └── js/ → Alpine.js scripts
├── routes/ → API endpoints
├── tests/ → Unit & Feature tests
└── config/ → Configuration files
```

#### 🧪 Testing

- Pest framework untuk testing
- Feature tests untuk routes
- Unit tests untuk business logic
- PHPUnit integration

#### 🚀 Known Limitations (v1.0.0)

- ⚠️ Single user per toko (belum multi-staff)
- ⚠️ No pricing management (harga manual input)
- ⚠️ No customer login/self-service
- ⚠️ No SMS/WhatsApp integration
- ⚠️ No advanced analytics/reports
- ⚠️ No payment gateway integration
- ⚠️ No inventory tracking

#### 📝 Notes

- Ini adalah versi MVP (Minimum Viable Product)
- Fokus pada core functionality: manage orders & payments
- UI/UX masih basic, bisa di-enhance di versi berikutnya
- Performance testing belum comprehensive
- Belum ada load testing untuk concurrent users

---

## [Unreleased]

### 🔄 Planned untuk v1.1.0

- [ ] Staff Management dengan role-based access
- [ ] Pricing Management per layanan
- [ ] Advanced Dashboard dengan charts
- [ ] Order history & analytics
- [ ] Bug fixes dari community feedback

### 🔄 Planned untuk v1.2.0

- [ ] Report generation (PDF/Excel)
- [ ] WhatsApp integration
- [ ] Direct approval workflow
- [ ] Bulk order import
- [ ] Performance improvements

### 🔄 Planned untuk v2.0.0

- [ ] Mobile app (React Native)
- [ ] Multi-tenant support
- [ ] Payment gateway integration
- [ ] Inventory management
- [ ] Advanced analytics
- [ ] API untuk 3rd party integration

---

## Upgrade Guide

### Dari v1.0.0 ke v1.1.0 (Coming Soon)

```bash
# Pull latest changes
git checkout develop
git pull origin develop

# Install new dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Clear cache
php artisan optimize:clear
```

---

## Version Numbering

Kami menggunakan **Semantic Versioning**:

- **MAJOR** (1.x.x): Breaking changes, redesign
- **MINOR** (x.1.x): Fitur baru yang backward compatible
- **PATCH** (x.x.1): Bug fixes

---

## Feedback & Issues

Temukan bug? Punya saran? Buka [GitHub Issues](https://github.com/EvanderSG29/nyuci.id/issues)

---

<div align="center">

**Last Updated:** 2026-04-06  
**Current Version:** 1.0.0  
**Status:** 🟢 Stable Release

</div>
