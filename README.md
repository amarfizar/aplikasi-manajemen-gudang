# 📦 Aplikasi Manajemen Gudang

Sistem manajemen inventori gudang modern berbasis **Laravel 11** (API) + **Next.js 16** (Frontend) dengan fitur lengkap untuk mengelola stok, transaksi, dan laporan multi-gudang.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel)
![Next.js](https://img.shields.io/badge/Next.js-16-000000?style=for-the-badge&logo=next.js)
![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=for-the-badge&logo=typescript)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?style=for-the-badge&logo=tailwindcss)
![SQLite](https://img.shields.io/badge/SQLite-3-003B57?style=for-the-badge&logo=sqlite)

---

## ✨ Fitur Utama

### 📊 **Dashboard**

- Statistik real-time: Total barang, stok, supplier, gudang
- Chart Barang Masuk vs Keluar (7 hari)
- Pie chart Barang per Kategori
- Low Stock Alert dengan badge warna
- Aktivitas terbaru

### 📦 **Master Data**

- **Barang**: CRUD lengkap, import Excel, template download
- **Kategori**: Manajemen kategori barang
- **Supplier**: Data supplier dengan PIC & kontak
- **Gudang & Lokasi**: Multi-gudang dengan lokasi rak
- **Satuan**: Satuan barang (pcs, box, kg, dll)

### 🔄 **Transaksi**

- **Barang Masuk**: Penerimaan dari supplier dengan detail item
- **Barang Keluar**: Pengeluaran ke tujuan/departemen
- Validasi stok real-time
- Nomor transaksi auto-generate

### 📋 **Stock Opname & Adjustment**

- Stock Opname dengan review & approve workflow
- Stock Adjustment untuk koreksi stok
- History stok per barang

### 👥 **User Management & RBAC**

- Role: Super Admin, Admin Gudang, Viewer
- Permission berbasis Spatie Laravel Permission
- Manajemen user lengkap

### ⚙️ **Pengaturan**

- Logo & Nama Perusahaan (upload logo custom)
- Info perusahaan (email, telepon, alamat)
- Ambang batas Low Stock
- Dark/Light mode

### 🎨 **UI/UX**

- Tema hijau profesional + Dark mode
- Responsive (mobile-first)
- Component library shadcn/ui + Tailwind CSS v4
- Antislop rules untuk clean code

---

## 🛠️ **Tech Stack**

| Layer                | Technology                       |
| -------------------- | -------------------------------- |
| **Backend**    | Laravel 11, PHP 8.3, SQLite      |
| **Frontend**   | Next.js 16, React 19, TypeScript |
| **Styling**    | Tailwind CSS v4, shadcn/ui       |
| **Charts**     | Recharts                         |
| **Auth**       | Laravel Sanctum (Token-based)    |
| **Permission** | Spatie Laravel Permission        |
| **Export**     | Laravel Excel, DomPDF            |

---

## 🚀 **Instalasi & Menjalankan**

### Prasyarat

- PHP 8.3+
- Composer
- Node.js 18+
- NPM/Yarn

### 1. Clone & Setup Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve --port=8000
```

### 2. Setup Frontend

```bash
cd frontend
npm install
npm run dev
# atau production: npm run build && npm start
```

### 3. Akses Aplikasi

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000

### Default Login

```
Email: admin@inventory.test
Password: password
Role: Super Admin
```

---

## 📁 **Struktur Project**

```
inventory-gudang/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/api.php
│
├── frontend/                # Next.js App
│   ├── src/
│   │   ├── app/(dashboard)/
│   │   ├── components/
│   │   │   ├── layout/
│   │   │   ├── ui/
│   │   │   ├── master/
│   │   │   └── transactions/
│   │   ├── lib/
│   │   └── hooks/
│   └── public/
│
└── README.md
```

---

## 🔐 **Role & Permission**

| Fitur              | Super Admin | Admin Gudang | Viewer |
| ------------------ | ----------- | ------------ | ------ |
| Dashboard          | ✅          | ✅           | ✅     |
| Master Data (CRUD) | ✅          | ✅           | 👁️   |
| Transaksi In/Out   | ✅          | ✅           | ❌     |
| Stock Opname       | ✅          | ✅           | ❌     |
| Reports            | ✅          | ✅           | 👁️   |
| User Management    | ✅          | ❌           | ❌     |
| Settings           | ✅          | ❌           | ❌     |

---

## 📸 **Screenshot**

> *Tambahkan screenshot aplikasi di sini*

---

## 🤝 **Kontribusi**

1. Fork repository
2. Buat branch fitur (`git checkout -b fitur-baru`)
3. Commit perubahan (`git commit -m 'Tambah fitur X'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

---

## 📄 **Lisensi**

MIT License - lihat file [LICENSE](LICENSE) untuk detail.

---

## 👨‍💻 **Developer**

**Amar Fizar** - [GitHub](https://github.com/amarfizar)

---

## 🙏 **Acknowledgments**

- [Laravel](https://laravel.com)
- [Next.js](https://nextjs.org)
- [shadcn/ui](https://ui.shadcn.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Recharts](https://recharts.org)
