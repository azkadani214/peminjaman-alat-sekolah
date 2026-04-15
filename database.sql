-- Database: peminjaman_alat

CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('siswa', 'admin utama', 'petugas') NOT NULL DEFAULT 'siswa',
    nis VARCHAR(12) DEFAULT NULL,
    kelas VARCHAR(20) DEFAULT NULL,
    no_telp VARCHAR(15) DEFAULT NULL,
    tgl_daftar DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS kategori_alat_olahraga (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    kategori VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS alat_olahraga (
    id_alat_olahraga VARCHAR(50) PRIMARY KEY,
    nama_alat_olahraga VARCHAR(255) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    stok INT DEFAULT 0,
    foto_alat_olahraga VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_petugas INT DEFAULT NULL,
    status ENUM('menunggu', 'disetujui', 'dipinjam', 'dikembalikan', 'ditolak', 'batal') DEFAULT 'menunggu',
    waktu_pinjam DATETIME NOT NULL,
    batas_kembali DATETIME NOT NULL,
    waktu_kembali DATETIME DEFAULT NULL,
    kondisi VARCHAR(100) DEFAULT NULL,
    keterlambatan ENUM('ya', 'tidak') DEFAULT 'tidak',
    denda INT DEFAULT 0,
    pembayaran ENUM('belum bayar', 'lunas') DEFAULT 'belum bayar',
    metode_pembayaran VARCHAR(50) DEFAULT NULL,
    bukti_kartu VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_alat_olahraga VARCHAR(50) NOT NULL,
    jumlah INT NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
    FOREIGN KEY (id_alat_olahraga) REFERENCES alat_olahraga(id_alat_olahraga)
);

CREATE TABLE IF NOT EXISTS keranjang (
    id_keranjang INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_alat_olahraga VARCHAR(50) NOT NULL,
    jumlah INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_alat_olahraga) REFERENCES alat_olahraga(id_alat_olahraga)
);

CREATE TABLE IF NOT EXISTS log_aktivitas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    aktivitas VARCHAR(255) NOT NULL,
    waktu_aktivitas DATETIME NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifikasi (
    id_notif INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    pesan TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    waktu_notif DATETIME NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- Sample Data
-- Password: admin123
INSERT IGNORE INTO users (id_user, nama, username, password, role, tgl_daftar) VALUES 
(1, 'Administrator', 'admin', '$2y$10$DAYY6fwhjZePNzWih5A3x.JGCQLUuI5TAKu4NJkubtcnJJTQf2WV.', 'admin utama', NOW());

INSERT IGNORE INTO kategori_alat_olahraga (id_kategori, kategori) VALUES 
(1, 'Bola'), (2, 'Raket'), (3, 'Atletik'), (4, 'Lainnya');
