# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

Belum ada perubahan yang didokumentasikan setelah rilis `1.2.0`.

## [1.2.0] - 2026-04-16

### ✨ Added

- Registrasi akun dengan verifikasi OTP berbasis email dan alur resend yang dibatasi throttle
- Dashboard analytics baru dengan hero metrics, insights, chart breakdowns, dan performance cards
- Global search untuk akses cepat ke modul operasional
- Notification center untuk aktivitas aplikasi
- Modul `pelanggan` dan `biaya-jasa` dengan preview panel dan data table server-side
- Pengaturan dashboard untuk preset chart default dan override per user
- Demo seeder `nyuci:seed-demo` untuk environment development dan QA
- Pengaturan toko untuk informasi merchant QRIS statis

### 🔧 Changed

- Listing operasional dipindahkan ke arsitektur DataTables server-side dari implementasi tabel sebelumnya
- Halaman auth, dashboard, settings, dan layout utama di-refresh agar lebih konsisten untuk desktop dan mobile
- Struktur dokumentasi rilis dipisahkan tegas antara `1.1.0` dan `1.2.0`
- Metadata lisensi repository diganti dari MIT menjadi proprietary source-available

### 🗄️ Database

- Tambah tabel `dashboard_chart_presets` dan `dashboard_chart_user_overrides`
- Tambah tabel `notifications`
- Tambah kolom konfigurasi dashboard dan QRIS di `tokos`
- Tambah kolom email klien dan metadata pembayaran gateway

### 🧪 Quality

- Tambah feature tests untuk dashboard analytics, global search, chart settings, gateway QRIS, register OTP, store settings, dan demo seeder

## [1.1.0] - 2026-04-10

### ✨ Added

- Checkout QRIS publik untuk pembayaran laundry menggunakan QRIS statis lokal
- Sinkron status pembayaran dari sesi checkout QRIS
- Link checkout yang bisa dibagikan ke pelanggan

### 🔧 Changed

- Konfigurasi payment gateway dipindah ke `config/payment_gateway.php`
- Provider QRIS diganti dari simulator eksternal ke generator statis lokal
- Detail pembayaran diperluas agar menampilkan sesi checkout publik

### 🗄️ Database

- Tambah kolom metadata gateway, token checkout, dan waktu pelunasan gateway di tabel `pembayarans`

### ⚠️ Upgrade Notes

- Isi `PAYMENT_GATEWAY_QRIS_STATIC_PAYLOAD` dan `PAYMENT_GATEWAY_QRIS_STATIC_MERCHANT_NAME` di `.env` sebelum mengaktifkan checkout publik

## [1.0.0] - 2026-04-06

### 🎉 Initial Release

Rilis pertama NYUCI.ID sebagai sistem manajemen toko laundry untuk pemilik toko.

### ✨ Added

- Register akun pemilik toko, login, reset password, dan profile management
- Register toko dan pengelolaan informasi toko dasar
- CRUD order laundry dan status pengambilan cucian
- CRUD pembayaran dengan status bayar atau belum bayar
- Dashboard ringkas untuk statistik dan order terbaru
- UI Blade + Tailwind untuk kebutuhan operasional dasar
- Migrations dan testing baseline untuk modul inti

### 🚀 Known Limitations

- Belum ada role-based access control untuk staff
- Belum ada dashboard analytics lanjutan
- Belum ada payment gateway publik
- Belum ada inventory dan integrasi pihak ketiga

---

## Upgrade Guide

### Dari v1.1.0 ke v1.2.0

```bash
git checkout develop
git pull origin develop

composer install
npm install

php artisan migrate
php artisan optimize:clear
npm run build
```

Catatan:
- Pastikan konfigurasi mail aktif untuk alur OTP registrasi
- Pastikan konfigurasi QRIS per toko diisi bila checkout publik akan digunakan
- Jalankan demo seeder jika membutuhkan dataset QA: `php artisan nyuci:seed-demo`

---

## Version Numbering

Kami menggunakan **Semantic Versioning**:

- **MAJOR** (`1.x.x`): Breaking changes atau redesign arsitektur
- **MINOR** (`x.1.x`): Fitur baru yang backward compatible
- **PATCH** (`x.x.1`): Bug fixes dan perbaikan kecil

---

## Feedback & Issues

Temukan bug? Punya saran? Buka [GitHub Issues](https://github.com/EvanderSG29/nyuci.id/issues)

---

<div align="center">

**Last Updated:** 2026-04-16  
**Current Version:** 1.2.0  
**Status:** 🟢 Stable Release

</div>
