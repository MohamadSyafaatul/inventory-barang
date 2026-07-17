-- =====================================================
-- DATABASE INVENTARIS
-- =====================================================

CREATE DATABASE IF NOT EXISTS db_pelindo;
USE db_pelindo;

-- =====================================================
-- TABEL ADMIN
-- =====================================================

CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nama_admin VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

INSERT INTO admin (nama_admin, username, password)
VALUES
('Administrator','admin','admin123');

-- =====================================================
-- TABEL INVENTARIS
-- =====================================================

CREATE TABLE inventaris (
    ip_address VARCHAR(20) NOT NULL PRIMARY KEY,
    user VARCHAR(100) NOT NULL,
    jenis_barang VARCHAR(50) NOT NULL,
    sub_jenis_barang VARCHAR(50) NOT NULL,
    harga_per_unit BIGINT NOT NULL,
    serial_number VARCHAR(100) NOT NULL
);

INSERT INTO inventaris
(ip_address,user,jenis_barang,sub_jenis_barang,harga_per_unit,serial_number)
VALUES
('192.168.1.10','Andi','PC','Desktop',8500000,'SN001'),
('192.168.1.11','Budi','Laptop','ThinkPad',12000000,'SN002'),
('192.168.1.12','Doni','Printer','Epson L3210',3500000,'SN003'),
('192.168.1.13','Rina','PC','Desktop',9000000,'SN004');


-- =====================================================
-- TABEL MAINTENANCE
-- =====================================================
-- Kolom start_time: diisi otomatis oleh server saat maintenance dimulai (INSERT)
-- Kolom finish_time: diisi otomatis oleh server saat maintenance diselesaikan (UPDATE)
-- Kolom status_maintenance: 'Berlangsung' saat dibuat, berubah 'Selesai' saat finish
-- =====================================================

CREATE TABLE maintenance (

    id_maintenance      INT AUTO_INCREMENT PRIMARY KEY,

    ip_address          VARCHAR(20) NOT NULL,

    pelaksana_perawatan VARCHAR(100) NOT NULL,

    teknisi             VARCHAR(100) NOT NULL,

    tipe_perawatan      VARCHAR(100) NOT NULL,

    periode_perawatan   VARCHAR(100) NOT NULL,

    permasalahan        TEXT,

    aksi                TEXT,

    keterangan          TEXT,

    start_time          DATETIME NOT NULL,

    finish_time         DATETIME NULL DEFAULT NULL,

    status_maintenance  ENUM('Berlangsung','Selesai') NOT NULL DEFAULT 'Berlangsung',

    FOREIGN KEY(ip_address)
    REFERENCES inventaris(ip_address)
    ON UPDATE CASCADE
    ON DELETE RESTRICT

);

-- =====================================================
-- SCRIPT MIGRASI (jalankan di phpMyAdmin jika tabel sudah ada)
-- Menambahkan kolom baru dan menghapus kolom tanggal lama
-- =====================================================
-- ALTER TABLE maintenance
--     ADD COLUMN start_time DATETIME NOT NULL DEFAULT NOW() AFTER keterangan,
--     ADD COLUMN finish_time DATETIME NULL DEFAULT NULL AFTER start_time,
--     ADD COLUMN status_maintenance ENUM('Berlangsung','Selesai') NOT NULL DEFAULT 'Berlangsung' AFTER finish_time,
--     DROP COLUMN tanggal;
-- =====================================================


-- =====================================================
-- TABEL MAINTENANCE CHECKLIST
-- =====================================================

CREATE TABLE maintenance_checklist (
    id_checklist INT AUTO_INCREMENT PRIMARY KEY,
    id_maintenance INT NOT NULL,
    item VARCHAR(100) NOT NULL,
    status ENUM('OK','Perlu Perbaikan','Diganti') NOT NULL,
    FOREIGN KEY(id_maintenance)
    REFERENCES maintenance(id_maintenance)
    ON DELETE CASCADE
);

-- =====================================================
-- CONTOH DATA MAINTENANCE
-- =====================================================

INSERT INTO maintenance
(ip_address,pelaksana_perawatan,teknisi,tipe_perawatan,periode_perawatan,permasalahan,aksi,keterangan,start_time,finish_time,status_maintenance)
VALUES
('192.168.1.10','Admin IT','Budi','Preventive','Bulanan','Pembersihan perangkat dan pengecekan hardware','Cleaning & pengecekan fungsi perangkat','Dipastikan dalam kondisi baik',NOW(),NULL,'Berlangsung');


INSERT INTO maintenance_checklist
(id_maintenance,item,status)
VALUES
(1,'Motherboard','OK'),
(1,'Processor','OK'),
(1,'Hard Disk','OK'),
(1,'Power Supply','OK'),
(1,'RAM','OK'),
(1,'Keyboard','OK'),
(1,'Mouse','OK'),
(1,'Monitor','OK'),
(1,'Wifi LAN Card','OK'),
(1,'CD ROM','OK'),
(1,'Cleaning','OK');

