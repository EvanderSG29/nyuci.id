# NYUCI.ID - Laundry Management System

Aplikasi manajemen toko laundry untuk operasional harian toko laundry Indonesia. Dokumentasi ini mencerminkan rilis **v1.2.0** dengan fokus pada analytics, workflow operasional, dan checkout QRIS publik.

---

## 📋 Daftar Isi

- [Fitur](#-fitur-utama)
- [Prasyarat](#-prasyarat)
- [Instalasi](#-instalasi)
- [Struktur Database](#-struktur-database)
- [Endpoint Aplikasi](#-endpoint-aplikasi)
- [Development Guide](#-development-guide)
- [Troubleshooting](#-troubleshooting)
- [Roadmap](#-roadmap)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Utama

### ✅ Versi 1.2.0 (Current)

#### 👥 **User & Authentication**
- Register akun pemilik toko dengan verifikasi OTP via email
- Login dengan email dan password
- Resend OTP dengan throttle untuk onboarding yang lebih aman
- Profile management untuk update data akun dan password
- Session management, logout, dan proteksi route berbasis auth

#### 🏪 **Toko & Pengaturan**
- Register toko baru setelah akun aktif
- Update identitas toko, kontak, dan preferensi pengaturan toko
- Simpan konfigurasi QRIS statis per toko untuk checkout publik
- Halaman pengaturan dashboard untuk preset dan override chart

#### 📦 **Operasional Laundry**
- CRUD data pelanggan (`pelanggan`) dan biaya jasa (`biaya-jasa`)
- CRUD order laundry dengan preview detail cepat
- Update status laundry tanpa keluar dari daftar utama
- Tabel operasional server-side untuk laundry, pelanggan, jasa, dan pembayaran

#### 💰 **Pembayaran & Checkout**
- CRUD pembayaran per order laundry
- Daftar pembayaran belum lunas dengan aksi cepat
- Public checkout QRIS dari halaman detail pembayaran
- Sinkron status pembayaran dari sesi checkout QRIS statis
- Salin link checkout untuk dibagikan ke pelanggan

#### 📊 **Dashboard & Insight**
- Dashboard analytics dengan hero metrics, insights, chart breakdowns, dan performance cards
- Global search untuk akses cepat ke modul penting
- Chart cards yang bisa dikustom per toko dan per user
- Notification center untuk aktivitas aplikasi
- Demo seeder untuk menyiapkan data contoh pengujian

---

## 🛠️ Prasyarat

- **PHP** >= 8.3
- **Composer**
- **Node.js** >= 18
- **MySQL/MariaDB** >= 5.7
- **Git**

**Untuk Windows:**
- XAMPP
- Atau instalasi PHP + MySQL standalone

---

## 📥 Instalasi

### 1️⃣ Clone Repository

```bash
git clone https://github.com/EvanderSG29/nyuci.id.git
cd nyuci.id
git checkout develop
```

### 2️⃣ Install Dependencies

```bash
composer install
npm install
```

### 3️⃣ Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4️⃣ Konfigurasi Database

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nyuci_id
DB_USERNAME=root
DB_PASSWORD=
```

Lalu jalankan:

```bash
php artisan migrate
```

### 5️⃣ Konfigurasi Email OTP

Fitur registrasi OTP membutuhkan mail transport yang valid. Sesuaikan SMTP di `.env`, atau gunakan contoh di `.env.mailtrap.io` untuk environment pengujian.

### 6️⃣ Konfigurasi QRIS Statis

Siapkan payload QRIS statis dari hasil scan QR merchant, lalu isi `.env`:

```env
PAYMENT_GATEWAY_DRIVER=qris_static
PAYMENT_GATEWAY_CHECKOUT_TTL_MINUTES=30
PAYMENT_GATEWAY_QRIS_STATIC_PAYLOAD=...
PAYMENT_GATEWAY_QRIS_STATIC_MERCHANT_NAME=...
```

Jika belum dikonfigurasi, halaman checkout akan menampilkan warning konfigurasi.

### 7️⃣ Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 8️⃣ Start Server

```bash
php artisan serve
```

Akses aplikasi di `http://localhost:8000`.

---

## 🗄️ Struktur Database

### Users
```text
id, email, password, email_verified_at, timestamps
```

### Tokos
```text
id, user_id, nama_toko, alamat, no_hp, background_preferences,
dashboard settings, QRIS settings, timestamps
```

### Kliens
```text
id, toko_id, nama, no_hp, email, catatan, timestamps
```

### Jasas
```text
id, toko_id, nama_jasa, harga, satuan, deskripsi, timestamps
```

### Laundries
```text
id, toko_id, klien_id, jasa_id, nama, no_hp, berat, tanggal,
estimasi_selesai, layanan, is_taken, status phase, timestamps
```

### Pembayarans
```text
id, laundry_id, total, status, gateway metadata,
gateway token, gateway paid timestamps, timestamps
```

### Supporting Tables
```text
notifications, dashboard_chart_presets, dashboard_chart_user_overrides
```

**Relasi utama:**
- User 1 -> 1 Toko
- Toko 1 -> many Klien, Jasa, Laundries
- Laundry 1 -> 1 Pembayaran

---

## 🔗 Endpoint Aplikasi

### Public / Guest

```text
GET  /                           -> Home / redirect dashboard
GET  /register                   -> Form register
POST /register                   -> Submit register
GET  /register/otp               -> Form verifikasi OTP
POST /register/otp               -> Verifikasi OTP
POST /register/otp/resend        -> Kirim ulang OTP
GET  /forgot-password            -> Form reset password
GET  /bayar/{pembayaran}/{token} -> Checkout publik QRIS
POST /bayar/{pembayaran}/{token}/sync -> Sinkron status QRIS
```

### Authenticated

```text
GET|POST   /register/toko
GET        /dashboard
GET        /search/global

GET|PATCH|DELETE pengaturan dashboard:
/pengaturan-dashboard
/pengaturan-dashboard/defaults
/pengaturan-dashboard/overrides
/pengaturan-dashboard/overrides/{slot}
/pengaturan-dashboard/reset-default/{slot}

PATCH      /notifikasi/{notification}/read
POST       /notifikasi/read-all

Resource   /biaya-jasa
GET        /biaya-jasa/data
GET        /biaya-jasa/{jasa}/preview

Resource   /pelanggan
GET        /pelanggan/data
GET        /pelanggan/{klien}/preview

Resource   /laundry
GET        /laundry/data
GET        /laundry/{laundry}/preview
PATCH      /laundry/{laundry}/status

Resource   /pembayaran
GET        /pembayaran/belum-bayar
GET        /pembayaran/belum-bayar/data
GET        /pembayaran/data
GET        /pembayaran/{pembayaran}/preview
GET        /pembayaran/{pembayaran}/paid
POST       /pembayaran/{pembayaran}/gateway

GET|PATCH  /pengaturan-toko
GET|PATCH|DELETE /profile
```

---

## 💻 Development Guide

### Struktur Folder

```text
app/
├── DataTables/           -> Server-side table builders
├── Http/Controllers/     -> Route handlers
├── Http/Requests/        -> Validation rules
├── Models/               -> User, Toko, Klien, Jasa, Laundry, Pembayaran
├── Notifications/        -> Mail and in-app notifications
├── Otp/                  -> Register OTP flow
└── Services/             -> Dashboard charts, payment gateway

database/
├── migrations/           -> Schema changes
└── seeders/              -> Demo and initial data

resources/
├── css/                  -> Application styles
├── js/                   -> Frontend behavior and charts
└── views/                -> Blade templates

tests/
├── Feature/              -> Feature coverage
└── Unit/                 -> Unit coverage
```

### Workflow Development

Lihat [DEVELOPMENT.md](DEVELOPMENT.md) untuk panduan development lengkap.

### Testing

```bash
php artisan test
php artisan test tests/Feature/PembayaranGatewayTest.php
php artisan test --coverage
```

### Database

```bash
php artisan migrate
php artisan migrate:rollback
php artisan migrate:fresh
php artisan db:seed
php artisan nyuci:seed-demo
```

---

## 🐛 Troubleshooting

### Problem: `php artisan serve` error

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan key:generate
```

### Problem: migration gagal

```bash
php artisan migrate:fresh
```

### Problem: registrasi OTP tidak terkirim

```bash
# Cek konfigurasi SMTP di .env atau .env.mailtrap.io
php artisan config:clear
tail storage/logs/laravel.log
```

### Problem: asset frontend tidak ter-build

```bash
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
npm run build
```

---

## 🗺️ Roadmap

### 🔄 Versi 1.3.0 (Planned)
- [ ] Staff management dan role-based access control
- [ ] Report generation (PDF/Excel)
- [ ] WhatsApp integration untuk notifikasi pelanggan
- [ ] Approval workflow operasional internal

### 🔄 Versi 2.0.0 (Future)
- [ ] Multi-tenant support
- [ ] Mobile app
- [ ] Inventory management
- [ ] Public API untuk integrasi pihak ketiga

---

## 🤝 Contributing

Kontribusi tetap diterima. Baca [CONTRIBUTING.md](CONTRIBUTING.md) sebelum membuat issue atau pull request.

### Branch Convention

```text
main             -> Production / release branch
develop          -> Active development branch
feature/*        -> Fitur baru
bugfix/*         -> Bug fix
hotfix/*         -> Hot fix production
release/*        -> Kandidat rilis
```

### Commit Message Format

```text
feat: deskripsi fitur baru
fix: deskripsi perbaikan bug
docs: perubahan dokumentasi
refactor: perubahan struktur code
style: formatting atau linting
test: tambah atau update test
chore: dependency atau config updates
```

---

## 📄 Lisensi

Repository ini menggunakan lisensi **proprietary source-available**. Kode sumber dipublikasikan untuk evaluasi, review, dan kontribusi terbatas, tetapi tidak boleh dipakai, diubah, didistribusikan, di-host, atau dikomersialkan tanpa izin tertulis dari pemegang hak cipta.

Dokumen hukum yang berlaku:
- [LICENSE](LICENSE)
- [NOTICE.md](NOTICE.md)
- [TRADEMARKS.md](TRADEMARKS.md)
- [COMMERCIAL-LICENSE.md](COMMERCIAL-LICENSE.md)

---

## 📞 Support & Contact

**Issues & Questions?**
- Buka [GitHub Issues](https://github.com/EvanderSG29/nyuci.id/issues)
- Email: smidgidionevander@gmail.com

---

## 👨‍💻 Author

**Evander SG**
- GitHub: [@EvanderSG29](https://github.com/EvanderSG29)
- Email: smidgidionevander@gmail.com

---

<div align="center">

**Last Updated:** 2026-04-16  
**Current Version:** 1.2.0  
**Status:** 🟢 Stable Release

[⬆ Kembali ke atas](#-nyuciid---laundry-management-system)

</div>
