# 🤝 Contributing to NYUCI.ID

Terima kasih sudah tertarik untuk berkontribusi! Panduan ini akan membantu Anda memulai.

---

## 📋 Code of Conduct

Kami menjunjung tinggi komunitas yang respectful dan inclusive. Harap:

- ✅ Bersikap profesional dan ramah
- ✅ Menghormati perspektif yang berbeda
- ✅ Fokus pada konstruktif feedback
- ❌ Hindari diskriminasi, pelecehan, atau ancaman

---

## 🐛 Melaporkan Bug

### Sebelum Report

1. **Search dulu** di [GitHub Issues](https://github.com/EvanderSG29/nyuci.id/issues) - mungkin sudah ada
2. **Cek CHANGELOG** - mungkin sudah fixed di versi terbaru
3. **Cek Documentation** - mungkin behavior yang expected

### Format Bug Report

```markdown
**Deskripsi:**
Penjelasan singkat bug yang terjadi

**Steps to Reproduce:**
1. Buka halaman...
2. Inputkan...
3. Klik tombol...
4. Bug muncul

**Expected Behavior:**
Apa yang seharusnya terjadi

**Actual Behavior:**
Apa yang benar-benar terjadi

**Environment:**
- OS: [Windows 10, Linux, macOS]
- Browser: [Chrome 120, Firefox 121]
- PHP Version: 8.3
- Laravel Version: 13.0

**Screenshots/Logs:**
[Paste error log dari storage/logs/laravel.log]
```

### Contoh Bug Report yang Baik

```markdown
**Deskripsi:**
Saat mark pembayaran sebagai paid, muncul error 500

**Steps to Reproduce:**
1. Login sebagai pemilik toko
2. Buka menu Pembayaran
3. Pilih pembayaran dengan status "belum bayar"
4. Klik tombol "Bayar Sekarang"
5. Error muncul

**Expected Behavior:**
Pembayaran berubah status menjadi "sudah bayar"

**Actual Behavior:**
Muncul error: SQLSTATE[HY000]: General error

**Environment:**
- OS: Windows 10
- Browser: Chrome 120
- PHP Version: 8.3.4
- Laravel Version: 13.0

**Error Log:**
[Error exception in LaundryController.php line 89]
```

---

## ✨ Proposing Features

### Sebelum Mengajukan

1. **Pastikan belum ada** di [GitHub Issues](https://github.com/EvanderSG29/nyuci.id/issues)
2. **Cek Roadmap** di README.md
3. **Pastikan valuable** - fitur ini membantu pengguna?

### Feature Request Format

```markdown
**Deskripsi Feature:**
Penjelasan apa feature yang Anda inginkan

**Use Case:**
Bagaimana feature ini akan digunakan?

**Benefit:**
Keuntungan buat pemilik toko

**Alternative Solutions:**
Solusi alternatif yang ada sekarang

**Additional Context:**
Info tambahan yang relevan

**Design/Mockup:**
[Screenshot mockup jika ada]
```

### Contoh Feature Request yang Baik

```markdown
**Deskripsi Feature:**
Tambahkan notifikasi WhatsApp otomatis ketika order siap diambil

**Use Case:**
Pemilik toko ingin mengingatkan pelanggan via WhatsApp saat order selesai

**Benefit:**
- Meningkatkan customer satisfaction
- Mengurangi missed pickups
- Efisiensi komunikasi

**Alternative Solutions:**
- Manual SMS (memakan waktu)
- Email (tidak efektif)

**Additional Context:**
Bisa integrasi dengan Twilio atau Fonnte API yang populer di Indonesia

**Design/Mockup:**
[Gambar flow notifikasi WhatsApp]
```

---

## 🔧 Membuat Pull Request

### Development Workflow

1. **Fork repository** (jika Anda outside contributor)

2. **Clone & setup**
```bash
git clone https://github.com/YOUR_USERNAME/nyuci.id.git
cd nyuci.id
git checkout develop
```

3. **Buat feature branch**
```bash
git checkout -b feature/nama-fitur
# Contoh: feature/whatsapp-notification
```

4. **Develop & commit**
```bash
# Coding...
git add .
git commit -m "feat: deskripsi fitur

Penjelasan lebih detail tentang perubahan.
- Poin 1
- Poin 2
"
```

5. **Push ke remote**
```bash
git push origin feature/nama-fitur
```

6. **Buat Pull Request**
   - Buka GitHub.com
   - Klik "Compare & pull request"
   - Base: `develop` ← Compare: `feature/nama-fitur`

### PR Description Template

```markdown
## 📝 Deskripsi

Penjelasan singkat perubahan apa yang dibuat

## 🎯 Linked Issue

Closes #123 (ganti dengan nomor issue)

## 🔍 Type of Change

- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## ✅ Checklist

- [ ] Code sudah di-review sendiri
- [ ] Documentation updated (jika perlu)
- [ ] Tests added/updated
- [ ] No new warnings
- [ ] Commit message jelas & descriptive

## 📸 Screenshots (jika UI change)

[Paste screenshot before & after]

## 🚧 Notes

Catatan tambahan untuk reviewer
```

### PR Terbaik - Contoh

```markdown
## 📝 Deskripsi

Implement staff management system dengan role-based access control.

Fitur yang ditambahkan:
- Create/Edit/Delete staff
- Assign roles (Admin, Staff, Kasir)
- Permission checking di routes

## 🎯 Linked Issue

Closes #45

## 🔍 Type of Change

- [x] New feature
- [ ] Bug fix
- [ ] Breaking change

## ✅ Checklist

- [x] Code sudah di-review sendiri
- [x] Database migration sudah created
- [x] Unit & feature tests sudah written
- [x] Documentation updated (DEVELOPMENT.md)
- [x] No breaking changes

## 📸 Screenshots

[Staff Management Form Image]
[Staff List Table Image]

## 🚧 Notes

- Menggunakan existing User model, tidak create baru
- Authorization via Policies (lihat StaffPolicy)
- Tested dengan 5 test cases
```

---

## 📖 Commit Message Guidelines

Format: `<type>(<scope>): <subject>`

### Types

```
feat:     Fitur baru
fix:      Bug fix
docs:     Dokumentasi
refactor: Code restructure (tanpa feature baru)
style:    Formatting/linting (tanpa behavior change)
test:     Test addition/update
chore:    Dependency update, config changes
perf:     Performance improvement
```

### Examples

```
feat(laundry): tambah estimasi biaya ke order
fix(auth): fix infinite redirect di login page
docs(readme): update installation steps
refactor(pembayaran): extract validation ke FormRequest
test(laundry): add test untuk mark as paid
style(code): format code dengan PSR-12
chore(deps): update laravel/framework to 13.1
```

---

## 🧪 Testing Requirements

Sebelum submit PR:

### 1. Add/Update Tests

```bash
# Pastikan test baru ada
php artisan make:test FeatureNameTest

# Test harus cover:
# - Happy path (berhasil)
# - Error cases (validasi, permission)
# - Edge cases
```

### 2. Run Tests Locally

```bash
# Semua test harus pass
php artisan test

# Dengan coverage (target >80%)
php artisan test --coverage
```

### 3. Test Checklist

- [ ] Unit tests ada
- [ ] Feature tests ada
- [ ] Authorization tests ada (jika ada permission change)
- [ ] Semua test passing
- [ ] Coverage >80%

---

## 📋 Code Review Checklist

PR akan di-review dengan criteria:

### Code Quality
- [ ] Mengikuti PSR-12 standard
- [ ] Naming convention consistent
- [ ] DRY principle (Don't Repeat Yourself)
- [ ] No hardcoded values
- [ ] Proper error handling
- [ ] Documented complex logic

### Best Practices
- [ ] Menggunakan Laravel conventions
- [ ] Proper dependency injection
- [ ] Security checks (SQL injection, XSS, etc)
- [ ] Performance considerations
- [ ] Database queries optimized (N+1 queries?)

### Documentation
- [ ] Code comments jelas
- [ ] README updated (jika perlu)
- [ ] CHANGELOG updated
- [ ] Docblocks lengkap

### Testing
- [ ] Tests comprehensive
- [ ] All tests passing
- [ ] No flaky tests
- [ ] Coverage adequate

---

## 🚀 Release Process

Jika PR di-merge ke develop & ready for release:

1. **Testing & QA** - Full testing di staging
2. **Release Branch** - Buat release branch dari develop
3. **Version Tag** - Tag dengan semantic versioning (v1.1.0)
4. **Merge to Main** - Merge release ke main branch
5. **CHANGELOG Update** - Document changes di CHANGELOG.md
6. **Publish Release** - Announce di GitHub Releases

---

## 💬 Communication

### Channels

- **GitHub Issues** - Bug reports & feature requests
- **GitHub Discussions** - General questions & ideas
- **Pull Requests** - Code review & discussion

### Response Time

- Critical bugs: ASAP (same day)
- Feature requests: Within 1 week
- PR reviews: Within 3 days

---

## ✨ Recognition

Contributors akan di-recognize di:
- CHANGELOG.md (dalam release notes)
- GitHub contributors page (automatic)
- README.md (untuk major contributors)

---

## ❓ FAQ Kontribusi

**Q: Bagaimana kalau saya junior developer?**  
A: Welcome! Mulai dari dokumentasi, bug fix kecil, atau feature request.

**Q: Harus approve oleh maintainer sebelum mulai coding?**  
A: Untuk feature besar, sebaiknya discuss di issue dulu. Bug fix bisa langsung.

**Q: Berapa lama review biasanya?**  
A: 3-7 hari untuk PR normal. Prioritas untuk critical bug.

**Q: Kalau PR di-reject, gimana?**  
A: Kita akan explain kenapa. Revise & resubmit dengan changes yang diminta.

**Q: Bisa kontribusi dari non-developers?**  
A: Yes! Dokumentasi, translations, bug reports juga valuable.

---

## 🎓 Resources untuk Contributors

- [Laravel Documentation](https://laravel.com/docs)
- [GitHub Guides](https://guides.github.com/)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
- [Conventional Commits](https://www.conventionalcommits.org/)

---

<div align="center">

## 🙏 Terima Kasih!

Kontribusi Anda membuat NYUCI.ID lebih baik untuk semua!

**Mari berkontribusi dengan baik! 🚀**

[GitHub Repository](https://github.com/EvanderSG29/nyuci.id)

</div>
