# Panduan Deploy Laravel ke Render & Aiven MySQL

Berikut adalah langkah-langkah lengkap untuk melakukan *deployment* aplikasi Laravel menggunakan layanan **Render** (sebagai *Web Service* berbasis Docker) dan **Aiven** (sebagai *Database Hosting* MySQL). Anda bisa memberikan panduan ini kepada teman Anda.

---

## Tahap 1: Persiapan Repository (GitHub)
Render akan menarik (pull) kode aplikasi Anda langsung dari GitHub.
1. Buat repository baru di [GitHub](https://github.com/).
2. Di lokal komputer Anda (dalam folder project Laravel), jalankan perintah berikut untuk mengirim kode ke GitHub:
   ```bash
   git init
   git add .
   git commit -m "Initial commit untuk deploy"
   git branch -M main
   git remote add origin https://github.com/username-anda/nama-repo.git
   git push -u origin main
   ```
> [!IMPORTANT]
> Pastikan file `.env` tidak ikut masuk ke GitHub demi keamanan kredensial. Biasanya Laravel sudah secara otomatis memasukkan `.env` ke dalam file `.gitignore`.

---

## Tahap 2: Pembuatan Database di Aiven
Aiven akan digunakan untuk menjalankan database MySQL Anda secara *cloud*.
1. Daftar atau Login ke akun [Aiven](https://console.aiven.io/).
2. Buat layanan baru dengan menekan tombol **Create Service**.
3. Pilih **MySQL** sebagai tipe layanan.
4. Pilih **Cloud Provider** dan **Region** (sangat disarankan memilih region yang berdekatan dengan lokasi web server Anda, misalnya *Singapore*).
5. Pilih **Service Plan** (bisa memilih *Hobbyist/Free* jika tersedia, atau plan paling dasar).
6. Beri nama layanan Anda dan klik **Create**.
7. Setelah layanan berstatus *Running*, buka halaman **Overview** dari layanan database tersebut. Di bagian *Connection Information* (biasanya ada URI atau tab Parameters), catat kredensial berikut:
   - **Host**
   - **Port** (biasanya 25060)
   - **User** (biasanya avnadmin)
   - **Password**
   - **Database Name** (biasanya defaultdb)

---

## Tahap 3: Persiapan Docker (Dockerfile)
Render merekomendasikan penggunaan Docker untuk men-deploy aplikasi PHP/Laravel. Kita perlu menambahkan file konfigurasi bernama `Dockerfile`.

1. Buat satu file bernama `Dockerfile` (tanpa ekstensi apa pun) di folder utama (root) project Laravel Anda.
2. Isi `Dockerfile` tersebut dengan kode berikut:

```dockerfile
FROM php:8.2-apache

# Instal dependensi OS yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

# Bersihkan cache instalasi
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instal ekstensi PHP (termasuk pdo_mysql untuk database)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Konfigurasi Apache DocumentRoot (Diarahkan ke folder /public Laravel)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Aktifkan mod_rewrite Apache (Penting untuk routing Laravel)
RUN a2enmod rewrite

# Ambil Composer versi terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory ke folder aplikasi
WORKDIR /var/www/html

# Copy semua file project ke dalam container
COPY . /var/www/html

# Jalankan instalasi dependensi vendor
RUN composer install --no-dev --optimize-autoloader

# Berikan hak akses yang benar untuk folder storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
```

3. Simpan `Dockerfile` tersebut, lalu lakukan `commit` dan `push` ke GitHub:
   ```bash
   git add Dockerfile
   git commit -m "Tambahkan Dockerfile untuk Render"
   git push
   ```

---

## Tahap 4: Deployment ke Render
Sekarang kita hubungkan repository dengan Render agar otomatis online.
1. Login ke [Render](https://dashboard.render.com/).
2. Klik tombol **New** -> **Web Service**.
3. Pilih opsi **Build and deploy from a Git repository**. Hubungkan akun GitHub Anda dan pilih repository aplikasi yang sudah dibuat tadi.
4. Isi formulir konfigurasi Render:
   - **Name**: (misalnya `b2cmusic-app`)
   - **Region**: (samakan/dekatkan dengan region database Aiven, misal *Singapore*)
   - **Branch**: `main`
   - **Environment**: Render biasanya akan otomatis mendeteksi **Docker** karena melihat adanya `Dockerfile`. Jika tidak, pilih *Docker*.
5. Gulir ke bawah dan buka pengaturan **Advanced** -> **Environment Variables**. Masukkan kredensial dari `.env` lokal Anda, dengan penyesuaian khusus untuk Database Aiven. Anda harus klik tombol *Add Environment Variable* satu per satu:

| KEY | VALUE |
|---|---|
| `APP_ENV` | `production` |
| `APP_KEY` | *(Salin isi APP_KEY dari .env lokal Anda, misal: base64:...)* |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://nama-app-anda.onrender.com` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | *(Host yang didapat dari Aiven)* |
| `DB_PORT` | *(Port dari Aiven, misal: 25060)* |
| `DB_DATABASE` | *(Nama database Aiven, misal: defaultdb)* |
| `DB_USERNAME` | *(User Aiven, misal: avnadmin)* |
| `DB_PASSWORD` | *(Password Aiven Anda)* |

> [!TIP]
> Jika ada *environment variable* lainnya dari lokal Anda yang dibutuhkan aplikasi (misalnya konfigurasi Midtrans, email SMTP, dll), masukkan semuanya ke sini.

6. Klik **Create Web Service**. Tunggu beberapa menit, Render akan membuat *image* Docker dan menjalankan aplikasi Anda. Tunggu hingga statusnya berubah menjadi **Live**.

---

## Tahap 5: Eksekusi Migrasi Database (Migrate DB)
Meskipun aplikasi sudah *Live*, tabel di dalam database MySQL (Aiven) masih kosong. Kita perlu menjalankan perintah migrasi dari dalam server Render.
1. Di halaman *Dashboard Web Service* Anda di Render, klik tab **Shell** di menu navigasi atas.
2. Sebuah jendela terminal/konsol akan terbuka (terhubung langsung ke dalam *container* aplikasi Anda).
3. Ketikkan dan jalankan perintah berikut untuk membuat tabel:
   ```bash
   php artisan migrate --force
   ```
4. Jika Anda juga mempunyai *seeder* (data bawaan awal seperti user admin dsb), ketikkan perintah berikut:
   ```bash
   php artisan db:seed --force
   ```
5. *(Opsional)* Jika ada *storage link* yang harus dibuat untuk menampilkan gambar yang di-upload, jalankan juga:
   ```bash
   php artisan storage:link
   ```

**Selesai!** Aplikasi Anda sekarang sudah sepenuhnya berjalan secara online dan terhubung ke database. Teman Anda sudah bisa mengakses aplikasinya menggunakan URL yang diberikan oleh Render (`https://nama-app-anda.onrender.com`).
