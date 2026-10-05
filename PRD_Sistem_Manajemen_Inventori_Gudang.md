# Product Requirements Document (PRD)
# Sistem Manajemen Inventori Gudang

**Versi:** 1.0  
**Tanggal:** 3 Oktober 2026  
**Status:** Draft / Development Blueprint  
**Frontend:** Next.js + TypeScript  
**Backend:** Laravel + REST API  
**Database:** MySQL  

---

## 1. Ringkasan Produk

Sistem Manajemen Inventori Gudang adalah aplikasi web untuk membantu perusahaan mengelola data barang, stok, gudang, lokasi penyimpanan, supplier, transaksi barang masuk dan keluar, stock opname, laporan, serta aktivitas pengguna.

Aplikasi dirancang dengan pendekatan **enterprise inventory management** dengan antarmuka modern, elegan, responsif, dan mudah digunakan.

Arsitektur aplikasi menggunakan:

- **Next.js** sebagai frontend.
- **Laravel** sebagai backend/API.
- **MySQL** sebagai database.
- **REST API** sebagai komunikasi frontend dan backend.
- **Laravel Sanctum** untuk autentikasi berbasis token/session API.
- **Role-Based Access Control (RBAC)** untuk membatasi akses berdasarkan peran pengguna.

---

# 2. Latar Belakang

Pengelolaan inventori yang dilakukan secara manual menggunakan spreadsheet atau pencatatan terpisah dapat menyebabkan:

- Kesalahan pencatatan stok.
- Kesulitan mengetahui stok secara real-time.
- Riwayat barang masuk dan keluar sulit ditelusuri.
- Kesulitan mengetahui barang yang hampir habis.
- Proses stock opname kurang efisien.
- Laporan membutuhkan waktu untuk dibuat.
- Sulit mengetahui siapa yang melakukan perubahan data.

Sistem ini dibuat untuk menyediakan satu pusat pengelolaan inventori yang terintegrasi sehingga data stok, transaksi, pengguna, dan laporan dapat dikelola secara terstruktur.

---

# 3. Tujuan Produk

## 3.1 Tujuan Utama

1. Memusatkan pengelolaan data inventori perusahaan.
2. Menyediakan informasi stok yang akurat dan mudah dipantau.
3. Mencatat seluruh transaksi barang masuk dan barang keluar.
4. Mempermudah proses stock opname.
5. Menyediakan laporan inventori secara cepat.
6. Menyediakan kontrol akses berdasarkan role.
7. Menyediakan audit trail terhadap aktivitas penting.
8. Menyediakan UI yang modern, profesional, dan responsif.

## 3.2 Tujuan Teknis

1. Memisahkan frontend dan backend.
2. Menyediakan API yang terstruktur.
3. Menjaga konsistensi data stok melalui backend.
4. Menggunakan validasi server-side.
5. Mengimplementasikan authentication dan authorization.
6. Menyediakan struktur yang mudah dikembangkan.

---

# 4. Target Pengguna

## 4.1 Super Admin

Memiliki akses penuh terhadap sistem.

Hak akses:

- Dashboard.
- Master barang.
- Kategori.
- Satuan.
- Supplier.
- Gudang.
- Lokasi.
- Barang masuk.
- Barang keluar.
- Stock opname.
- Laporan.
- User.
- Role dan permission.
- Activity log.
- Pengaturan sistem.

## 4.2 Admin Gudang

Bertanggung jawab terhadap aktivitas operasional gudang.

Hak akses:

- Dashboard.
- Barang.
- Kategori.
- Supplier.
- Gudang.
- Lokasi.
- Barang masuk.
- Barang keluar.
- Stock opname.
- Riwayat stok.
- Laporan yang berkaitan dengan gudang.

## 4.3 Viewer

Pengguna yang hanya membutuhkan akses informasi.

Hak akses:

- Dashboard.
- Melihat barang.
- Melihat stok.
- Melihat laporan yang diizinkan.

Tidak dapat:

- Mengubah data.
- Menghapus data.
- Membuat transaksi.
- Melakukan stock adjustment.

---

# 5. Ruang Lingkup

## 5.1 Termasuk

- Authentication.
- Dashboard.
- Master barang.
- Kategori barang.
- Satuan barang.
- Supplier.
- Gudang.
- Lokasi penyimpanan.
- Barang masuk.
- Barang keluar.
- Stock opname.
- Penyesuaian stok.
- Riwayat transaksi.
- Laporan.
- Export data.
- User management.
- Role dan permission.
- Activity log.
- Search dan filter.
- Responsive UI.

## 5.2 Tidak Termasuk Pada MVP

Fitur berikut dapat dikembangkan pada fase berikutnya:

- Purchase Order kompleks.
- Sales Order.
- Integrasi ERP.
- Integrasi barcode scanner secara khusus.
- Integrasi RFID.
- Integrasi accounting.
- Integrasi marketplace.
- Prediksi stok berbasis AI.
- Multi-company accounting.

---

# 6. Konsep UI/UX

## 6.1 Gaya Visual

Desain menggunakan konsep:

> Modern Enterprise Dashboard

Karakteristik:

- Minimalis.
- Elegan.
- Profesional.
- Banyak whitespace.
- Layout konsisten.
- Tidak terlalu banyak warna.
- Fokus pada informasi.
- Responsive.
- Accessible.

## 6.2 Warna

Palet utama yang direkomendasikan:

- Primary: Navy / Blue.
- Background: White / Slate.
- Success: Green.
- Warning: Amber.
- Danger: Red.
- Information: Blue.

Warna status harus digunakan secara konsisten.

Contoh:

- Stok aman → Success.
- Stok menipis → Warning.
- Stok habis → Danger.

## 6.3 Layout

Desktop:

```text
┌─────────────────────────────────────────────────────────┐
│ Topbar                              Notification / User │
├──────────────┬──────────────────────────────────────────┤
│              │                                          │
│ Sidebar      │ Main Content                             │
│              │                                          │
│ Dashboard    │                                          │
│ Inventory    │                                          │
│ Transaction  │                                          │
│ Reports      │                                          │
│ System       │                                          │
│              │                                          │
└──────────────┴──────────────────────────────────────────┘
```

Mobile:

- Sidebar berubah menjadi drawer.
- Tabel dapat di-scroll horizontal.
- Form disusun satu kolom.
- Dashboard card menggunakan grid responsif.

---

# 7. Information Architecture

Struktur menu:

```text
Dashboard

MASTER DATA
├── Barang
├── Kategori
├── Satuan
├── Supplier
├── Gudang
└── Lokasi

INVENTORY
├── Barang Masuk
├── Barang Keluar
├── Stock Opname
├── Penyesuaian Stok
└── Riwayat Stok

REPORT
├── Laporan Stok
├── Laporan Barang Masuk
├── Laporan Barang Keluar
└── Laporan Aktivitas

SYSTEM
├── Users
├── Roles & Permissions
├── Activity Log
└── Settings
```

---

# 8. Functional Requirements

## FR-001 Authentication

Sistem harus menyediakan:

- Login.
- Logout.
- Session/token management.
- Password hashing.
- Validasi credential.
- Proteksi halaman berdasarkan authentication.
- Proteksi endpoint API.

### Login

Input:

- Email/username.
- Password.

Output:

- Token/session.
- Informasi user.
- Role.
- Permission.

Jika gagal:

> Email atau password tidak valid.

---

# 9. Dashboard

Dashboard menampilkan ringkasan kondisi inventori.

## 9.1 Statistik

Minimal:

- Total barang.
- Total stok.
- Barang stok menipis.
- Barang habis.
- Barang masuk hari ini.
- Barang keluar hari ini.
- Jumlah supplier.
- Jumlah gudang.

## 9.2 Grafik

Minimal:

1. Barang masuk vs barang keluar.
2. Tren transaksi berdasarkan periode.
3. Distribusi barang berdasarkan kategori.

## 9.3 Aktivitas Terbaru

Menampilkan:

- User.
- Aktivitas.
- Objek yang diubah.
- Waktu.

Contoh:

```text
Admin menambahkan barang "Keyboard Logitech"
5 menit yang lalu
```

## 9.4 Low Stock Alert

Menampilkan barang yang memenuhi kondisi:

```text
current_stock <= minimum_stock
```

---

# 10. Master Barang

## 10.1 Data Barang

Field:

| Field | Tipe | Required |
|---|---|---|
| id | bigint | Yes |
| sku | varchar | Yes |
| name | varchar | Yes |
| category_id | bigint | Yes |
| unit_id | bigint | Yes |
| brand | varchar | No |
| minimum_stock | decimal | Yes |
| purchase_price | decimal | No |
| estimated_price | decimal | No |
| supplier_id | bigint | No |
| warehouse_location_id | bigint | No |
| image | varchar | No |
| description | text | No |
| status | enum | Yes |

Status:

- active
- inactive

## 10.2 Fitur

- List.
- Detail.
- Tambah.
- Edit.
- Nonaktifkan.
- Search.
- Filter.
- Sort.
- Pagination.
- Import.
- Export.

## 10.3 SKU

SKU harus unik.

Contoh:

```text
BRG-000001
BRG-000002
BRG-000003
```

SKU tidak boleh berubah sembarangan setelah barang memiliki histori transaksi.

---

# 11. Kategori

Field:

- ID.
- Nama kategori.
- Kode.
- Deskripsi.
- Status.

Fitur:

- Tambah.
- Edit.
- Nonaktifkan.
- Search.

Kategori yang sudah digunakan oleh transaksi tidak boleh dihapus secara permanen.

---

# 12. Satuan

Contoh:

- pcs.
- unit.
- box.
- kg.
- liter.
- meter.

Field:

- ID.
- Nama.
- Simbol.
- Status.

---

# 13. Supplier

Field:

- ID.
- Kode supplier.
- Nama perusahaan.
- PIC.
- Nomor telepon.
- Email.
- Alamat.
- Status.
- Catatan.

Fitur:

- Tambah.
- Edit.
- Detail.
- Nonaktifkan.
- Search.
- Filter.

---

# 14. Gudang

Sistem mendukung lebih dari satu gudang.

Field:

- ID.
- Kode gudang.
- Nama gudang.
- Alamat.
- Penanggung jawab.
- Nomor telepon.
- Status.

Contoh:

```text
WH-001
Gudang Utama

WH-002
Gudang Cabang
```

---

# 15. Lokasi Penyimpanan

Lokasi harus dapat dikaitkan dengan gudang.

Contoh:

```text
Gudang Utama
├── Rak A
│   ├── A-01
│   ├── A-02
│   └── A-03
├── Rak B
│   ├── B-01
│   └── B-02
└── Area Loading
```

Field:

- ID.
- Warehouse ID.
- Kode lokasi.
- Nama lokasi.
- Deskripsi.
- Status.

---

# 16. Barang Masuk

Barang masuk meningkatkan stok.

## 16.1 Header Transaksi

Field:

- Nomor transaksi.
- Tanggal.
- Supplier.
- Gudang.
- Referensi.
- Catatan.
- Created by.

Contoh nomor:

```text
IN-20261003-0001
```

## 16.2 Detail Transaksi

Field:

- Product ID.
- Quantity.
- Unit.
- Purchase price.
- Subtotal.
- Batch/lot jika diperlukan.
- Catatan.

## 16.3 Proses

```text
Create Transaction
        ↓
Validate Data
        ↓
Validate Product
        ↓
Validate Quantity
        ↓
Save Transaction
        ↓
Increase Stock
        ↓
Create Activity Log
```

Semua proses perubahan stok harus dilakukan secara transactional di backend.

---

# 17. Barang Keluar

Barang keluar mengurangi stok.

## 17.1 Header

- Nomor transaksi.
- Tanggal.
- Gudang.
- Tujuan.
- Departemen.
- Penanggung jawab.
- Catatan.
- Created by.

Contoh:

```text
OUT-20261003-0001
```

## 17.2 Detail

- Product ID.
- Quantity.
- Unit.
- Catatan.

## 17.3 Validasi

Sistem wajib menolak transaksi apabila:

```text
requested_quantity > available_stock
```

Pesan:

> Stok tidak mencukupi untuk barang yang dipilih.

---

# 18. Stock Opname

Stock opname digunakan untuk membandingkan stok sistem dengan stok fisik.

## 18.1 Data

- Nomor opname.
- Tanggal.
- Gudang.
- Penanggung jawab.
- Status.
- Catatan.

## 18.2 Detail

| Data | Contoh |
|---|---:|
| Stok Sistem | 100 |
| Stok Fisik | 97 |
| Selisih | -3 |

Formula:

```text
selisih = stok_fisik - stok_sistem
```

## 18.3 Status

- Draft.
- Counting.
- Review.
- Approved.
- Cancelled.

Adjustment stok hanya boleh dilakukan setelah stock opname disetujui.

---

# 19. Penyesuaian Stok

Adjustment digunakan untuk koreksi stok yang sah.

Alasan:

- Kerusakan.
- Kehilangan.
- Kesalahan pencatatan.
- Selisih stock opname.
- Alasan lainnya.

Setiap adjustment wajib memiliki:

- User.
- Waktu.
- Barang.
- Stok sebelum.
- Perubahan.
- Stok sesudah.
- Alasan.

---

# 20. Riwayat Stok

Sistem harus menyediakan histori perubahan stok.

Contoh:

```text
Tanggal       Transaksi       Perubahan   Saldo
03-10-2026    IN-001          +50         150
03-10-2026    OUT-002         -10         140
04-10-2026    ADJ-001         -3          137
```

Filter:

- Barang.
- Gudang.
- Jenis transaksi.
- User.
- Periode.

---

# 21. Laporan

## 21.1 Laporan Stok

Menampilkan:

- SKU.
- Barang.
- Kategori.
- Gudang.
- Lokasi.
- Stok.
- Minimum stok.
- Status.

## 21.2 Laporan Barang Masuk

Filter:

- Periode.
- Supplier.
- Gudang.
- Barang.

## 21.3 Laporan Barang Keluar

Filter:

- Periode.
- Gudang.
- Barang.
- Tujuan.

## 21.4 Export

Format:

- Excel.
- CSV.
- PDF.

---

# 22. User Management

Field:

- Nama.
- Email.
- Password.
- Role.
- Status.
- Avatar.
- Last login.

Fitur:

- Tambah user.
- Edit user.
- Reset password.
- Aktif/nonaktif.
- Detail user.

---

# 23. Role & Permission

Permission harus menggunakan prinsip least privilege.

Contoh permission:

```text
products.view
products.create
products.update
products.delete

transactions.in.view
transactions.in.create

transactions.out.view
transactions.out.create

stock_opname.view
stock_opname.create
stock_opname.approve

reports.view
reports.export

users.view
users.create
users.update
users.delete
```

---

# 24. Activity Log / Audit Trail

Sistem harus mencatat aktivitas penting.

Minimal:

- Login.
- Logout.
- Create.
- Update.
- Delete/nonaktifkan.
- Barang masuk.
- Barang keluar.
- Adjustment.
- Stock opname approval.
- Perubahan user/role.

Data:

- User.
- Action.
- Module.
- Record ID.
- Description.
- IP address.
- User agent.
- Timestamp.

---

# 25. Search, Filter, Sort & Pagination

List data wajib mendukung:

- Search.
- Filter.
- Sort.
- Pagination.

Contoh query API:

```text
GET /api/products?page=1&search=keyboard&category_id=2
```

Pagination default:

```text
20 records/page
```

Pilihan:

- 10.
- 20.
- 50.
- 100.

---

# 26. API Requirements

API menggunakan RESTful convention.

Base URL:

```text
/api
```

Contoh endpoint:

```text
POST   /api/login
POST   /api/logout
GET    /api/me

GET    /api/products
POST   /api/products
GET    /api/products/{id}
PUT    /api/products/{id}
DELETE /api/products/{id}

GET    /api/categories
POST   /api/categories
PUT    /api/categories/{id}

GET    /api/suppliers
POST   /api/suppliers

GET    /api/warehouses
POST   /api/warehouses

GET    /api/transactions/in
POST   /api/transactions/in

GET    /api/transactions/out
POST   /api/transactions/out

GET    /api/stock-opnames
POST   /api/stock-opnames

GET    /api/reports/stock
GET    /api/reports/transactions

GET    /api/activity-logs
```

---

# 27. API Response Standard

Response berhasil:

```json
{
  "success": true,
  "message": "Data berhasil disimpan",
  "data": {}
}
```

Response error:

```json
{
  "success": false,
  "message": "Data tidak valid",
  "errors": {
    "name": [
      "Nama barang wajib diisi."
    ]
  }
}
```

HTTP status harus digunakan secara tepat:

- 200 OK.
- 201 Created.
- 204 No Content.
- 400 Bad Request.
- 401 Unauthorized.
- 403 Forbidden.
- 404 Not Found.
- 422 Unprocessable Entity.
- 500 Internal Server Error.

---

# 28. Database Design

Struktur awal yang direkomendasikan:

```text
users
roles
permissions
role_permissions

products
categories
units

suppliers

warehouses
warehouse_locations

stock_transactions
stock_transaction_items

stock_opnames
stock_opname_items

stock_adjustments
activity_logs
```

## 28.1 Relasi Utama

```text
Category
   │
   └──< Product

Unit
   │
   └──< Product

Supplier
   │
   └──< Stock Transaction

Warehouse
   │
   ├──< Location
   └──< Stock Transaction

Stock Transaction
   │
   └──< Transaction Item

Product
   │
   ├──< Transaction Item
   ├──< Stock Opname Item
   └──< Stock Adjustment

User
   │
   ├──< Transactions
   ├──< Stock Opnames
   └──< Activity Logs
```

---

# 29. Stock Architecture

Backend Laravel harus menjadi sumber kebenaran stok.

Frontend tidak boleh menentukan saldo stok secara mandiri.

Setiap perubahan stok harus dilakukan melalui service khusus, misalnya:

```text
InventoryService
```

Contoh operasi:

```text
increaseStock()
decreaseStock()
adjustStock()
getCurrentStock()
```

Operasi stok wajib menggunakan database transaction:

```text
DB::transaction(...)
```

Tujuannya untuk mencegah kondisi stok berubah tetapi transaksi gagal tersimpan, atau sebaliknya.

---

# 30. Next.js Architecture

Struktur frontend yang direkomendasikan:

```text
src/
├── app/
│   ├── login/
│   ├── dashboard/
│   ├── products/
│   ├── categories/
│   ├── suppliers/
│   ├── warehouses/
│   ├── transactions/
│   ├── stock-opname/
│   ├── reports/
│   └── settings/
│
├── components/
│   ├── ui/
│   ├── layout/
│   ├── forms/
│   ├── tables/
│   └── charts/
│
├── services/
│   └── api/
│
├── hooks/
├── lib/
├── types/
└── utils/
```

---

# 31. Laravel Architecture

Struktur backend:

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
│
├── Models/
├── Services/
├── Policies/
├── Repositories/
└── Exceptions/

routes/
└── api.php

database/
├── migrations/
├── seeders/
└── factories/
```

Business logic utama sebaiknya tidak diletakkan seluruhnya di controller.

Contoh:

```text
StockTransactionController
        ↓
InventoryService
        ↓
Product / Transaction Model
        ↓
Database
```

---

# 32. Security Requirements

Minimal:

1. Password harus di-hash.
2. Authentication wajib untuk endpoint privat.
3. Authorization berdasarkan role/permission.
4. Validasi input server-side.
5. Rate limiting untuk endpoint sensitif.
6. CORS dikonfigurasi secara aman.
7. SQL Injection dicegah melalui ORM/query builder.
8. XSS dicegah melalui escaping dan validasi.
9. CSRF protection diterapkan sesuai metode authentication.
10. File upload harus divalidasi.
11. Jangan menyimpan password dalam plain text.
12. Jangan mengirim informasi sensitif dalam response API.
13. Audit log untuk aktivitas penting.
14. Production harus menggunakan HTTPS.
15. Error internal tidak boleh membocorkan stack trace kepada client.

---

# 33. File Upload

Jika barang memiliki foto:

Allowed:

```text
jpg
jpeg
png
webp
```

Maksimal ukuran:

```text
2 MB
```

File harus:

- Divalidasi MIME type.
- Divalidasi ukuran.
- Memiliki nama file aman.
- Tidak menggunakan nama file dari user secara langsung.
- Disimpan melalui storage Laravel.

---

# 34. Validasi Barang

Contoh:

```text
SKU:
- Required
- Unique

Nama:
- Required
- String
- Max 255

Minimum Stock:
- Required
- Numeric
- Min 0

Harga:
- Numeric
- Min 0
```

Quantity transaksi:

```text
Required
Numeric
Greater than 0
```

---

# 35. Error Handling

Frontend harus memberikan feedback yang jelas.

Contoh:

### Success

> Barang berhasil ditambahkan.

### Validation

> Mohon periksa kembali data yang diinput.

### Stock Error

> Stok tidak mencukupi.

### Server Error

> Terjadi kesalahan pada server. Silakan coba lagi.

### Unauthorized

> Sesi Anda telah berakhir. Silakan login kembali.

---

# 36. Loading State

Semua proses asynchronous harus memiliki state:

- Loading.
- Success.
- Error.
- Empty.

Contoh:

```text
Loading:
[ Skeleton Table ]

Empty:
Belum ada data barang.

Error:
Data gagal dimuat.
[ Coba Lagi ]
```

---

# 37. Confirmation Dialog

Operasi berisiko harus menggunakan confirmation.

Contoh:

```text
Nonaktifkan Barang?

Barang "Keyboard Logitech" akan dinonaktifkan.
Data transaksi sebelumnya tetap tersimpan.

[ Batal ] [ Nonaktifkan ]
```

Penghapusan permanen sebaiknya dihindari untuk data yang sudah memiliki histori transaksi.

---

# 38. Responsive Design

Breakpoint minimal:

- Mobile.
- Tablet.
- Desktop.
- Large desktop.

Dashboard harus tetap dapat digunakan pada layar kecil.

Tabel:

- Horizontal scroll.
- Kolom penting tetap terlihat.
- Action menu tidak boleh menyebabkan layout rusak.

---

# 39. Accessibility

UI harus memperhatikan:

- Kontras warna.
- Label form.
- Keyboard navigation.
- Focus state.
- Aria label jika diperlukan.
- Ukuran tombol yang mudah digunakan.
- Pesan error yang jelas.

---

# 40. Performance

Target:

- Initial page load cepat.
- Pagination untuk data besar.
- Debounce pada search.
- Lazy loading jika diperlukan.
- Optimasi gambar.
- Hindari request API yang tidak diperlukan.
- Gunakan caching pada data yang relatif jarang berubah.

---

# 41. Testing

## 41.1 Backend

Testing:

- Authentication.
- Authorization.
- Product CRUD.
- Transaction.
- Stock calculation.
- Stock opname.
- Validation.
- API response.

## 41.2 Frontend

Testing:

- Form validation.
- Table.
- Filter.
- Pagination.
- Authentication state.
- Permission UI.
- Error handling.

## 41.3 Integration Testing

Minimal:

```text
Create Product
      ↓
Stock In
      ↓
Check Stock
      ↓
Stock Out
      ↓
Check Stock
      ↓
Stock Opname
      ↓
Adjustment
      ↓
Check Final Stock
```

---

# 42. Acceptance Criteria

## Product

- User dapat membuat barang.
- SKU tidak boleh duplikat.
- Barang dapat diedit.
- Barang dapat dinonaktifkan.
- Barang dapat dicari dan difilter.

## Stock In

- Transaksi dapat dibuat.
- Stok bertambah sesuai quantity.
- Nomor transaksi unik.
- Histori tercatat.
- Activity log tercatat.

## Stock Out

- Transaksi dapat dibuat.
- Stok berkurang.
- Sistem menolak quantity melebihi stok.
- Histori tercatat.

## Stock Opname

- Stok sistem dapat dibandingkan dengan stok fisik.
- Selisih dihitung otomatis.
- Adjustment hanya dapat dilakukan sesuai permission.
- Histori tersimpan.

## Security

- Endpoint privat tidak dapat diakses tanpa authentication.
- User tanpa permission tidak dapat melakukan operasi terlarang.
- Password tidak disimpan dalam bentuk plain text.

---

# 43. Non-Functional Requirements

| Requirement | Target |
|---|---|
| Availability | Sistem dapat digunakan selama jam operasional |
| Security | Authentication + Authorization |
| Scalability | Mendukung pertumbuhan data |
| Performance | Response API normal < 1–2 detik pada kondisi wajar |
| Responsive | Mobile, tablet, desktop |
| Maintainability | Struktur modular |
| Auditability | Aktivitas penting tercatat |
| Data Integrity | Perubahan stok transactional |

---

# 44. Recommended Tech Stack

## Frontend

```text
Next.js
TypeScript
Tailwind CSS
shadcn/ui
Lucide Icons
React Hook Form
Zod
TanStack Query
Recharts
```

## Backend

```text
Laravel
PHP
Laravel Sanctum
Laravel API Resources
Laravel Form Requests
Laravel Policies
Laravel Eloquent
MySQL
```

## Development Tools

```text
Git
GitHub
Postman / Insomnia
ESLint
Prettier
PHPUnit / Pest
Laravel Pint
```

---

# 45. Environment

Frontend:

```env
NEXT_PUBLIC_API_URL=
```

Backend:

```env
APP_ENV=
APP_KEY=
APP_URL=

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Credential production tidak boleh disimpan langsung di repository.

---

# 46. Development Phases

## Phase 1 — Foundation

- Setup Laravel.
- Setup Next.js.
- Setup database.
- Authentication.
- Base layout.
- Sidebar.
- Topbar.
- API structure.

## Phase 2 — Master Data

- Products.
- Categories.
- Units.
- Suppliers.
- Warehouses.
- Locations.

## Phase 3 — Inventory

- Stock calculation.
- Barang masuk.
- Barang keluar.
- Stock history.

## Phase 4 — Stock Opname

- Stock opname.
- Approval.
- Adjustment.

## Phase 5 — Reports

- Stock report.
- Transaction report.
- Export.

## Phase 6 — Administration

- Users.
- Roles.
- Permissions.
- Activity logs.

## Phase 7 — UI/UX Refinement

- Responsive.
- Loading states.
- Empty states.
- Error states.
- Accessibility.
- Animation.
- Dark mode jika diperlukan.

## Phase 8 — Testing & Deployment

- Unit testing.
- Feature testing.
- Integration testing.
- Security testing.
- Performance testing.
- Production deployment.

---

# 47. MVP Prioritas

Jika waktu development terbatas, fitur MVP:

### Priority 1

- Login.
- Dashboard.
- Barang.
- Kategori.
- Satuan.
- Gudang.
- Supplier.
- Barang masuk.
- Barang keluar.
- Stok real-time.
- Riwayat stok.

### Priority 2

- Stock opname.
- Adjustment.
- Laporan.
- Export.
- User management.

### Priority 3

- Advanced permission.
- Audit log lengkap.
- Import Excel.
- Dark mode.
- Barcode.
- Advanced analytics.

---

# 48. Prinsip Pengembangan

1. **Backend adalah sumber kebenaran stok.**
2. **Jangan menghapus histori transaksi secara permanen.**
3. **Semua transaksi stok harus atomic.**
4. **Gunakan validation di frontend dan backend.**
5. **Gunakan authorization di backend, bukan hanya menyembunyikan tombol di frontend.**
6. **Gunakan reusable components pada Next.js.**
7. **Business logic ditempatkan pada service Laravel.**
8. **Gunakan database transaction untuk perubahan stok.**
9. **Semua operasi penting harus dapat diaudit.**
10. **UI harus konsisten pada seluruh halaman.**

---

# 49. Gambaran User Flow Utama

## Login

```text
Login
 ↓
Authentication
 ↓
Check Role & Permission
 ↓
Dashboard
```

## Barang Masuk

```text
Dashboard
 ↓
Barang Masuk
 ↓
Tambah Transaksi
 ↓
Pilih Supplier
 ↓
Pilih Gudang
 ↓
Tambah Barang
 ↓
Input Quantity
 ↓
Validasi
 ↓
Simpan
 ↓
Stock + Quantity
 ↓
Activity Log
```

## Barang Keluar

```text
Dashboard
 ↓
Barang Keluar
 ↓
Tambah Transaksi
 ↓
Pilih Gudang
 ↓
Pilih Tujuan
 ↓
Pilih Barang
 ↓
Input Quantity
 ↓
Check Stock
 ↓
Valid
 ↓
Simpan
 ↓
Stock - Quantity
 ↓
Activity Log
```

## Stock Opname

```text
Stock Opname
 ↓
Pilih Gudang
 ↓
Generate Daftar Barang
 ↓
Input Stok Fisik
 ↓
Hitung Selisih
 ↓
Review
 ↓
Approve
 ↓
Adjustment
 ↓
Activity Log
```

---

# 50. Kesimpulan

Sistem ini dirancang sebagai aplikasi inventori gudang berbasis web dengan pendekatan enterprise. Fokus utama sistem adalah:

- Akurasi stok.
- Kemudahan operasional.
- Riwayat transaksi.
- Keamanan.
- Auditability.
- Reporting.
- UI/UX modern.
- Arsitektur frontend dan backend yang terpisah.

Next.js bertanggung jawab terhadap presentation layer dan user interaction, sedangkan Laravel bertanggung jawab terhadap authentication, authorization, business logic, validasi, transaksi stok, dan akses database.

Dengan struktur ini, sistem dapat dimulai sebagai MVP sederhana namun tetap memiliki fondasi yang cukup kuat untuk dikembangkan menjadi sistem warehouse management yang lebih besar.

---

# 51. Definition of Done

Sebuah fitur dianggap selesai apabila:

- UI sudah tersedia.
- Responsive.
- Validasi frontend tersedia.
- Validasi backend tersedia.
- API tersedia dan terdokumentasi.
- Authorization diterapkan.
- Error handling tersedia.
- Loading state tersedia.
- Empty state tersedia.
- Database migration tersedia.
- Test utama tersedia.
- Activity log diterapkan jika diperlukan.
- Tidak terdapat error kritis.
- Fitur telah diuji pada desktop dan mobile.

---

**Dokumen PRD ini merupakan blueprint awal dan dapat diperbarui seiring perkembangan kebutuhan bisnis dan hasil implementasi.**
