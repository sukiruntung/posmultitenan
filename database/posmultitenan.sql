-- Adminer 4.8.1 MySQL 10.4.32-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DELIMITER ;;

DROP FUNCTION IF EXISTS `fn_terbilang`;;
CREATE FUNCTION `fn_terbilang`(`nominal` decimal) RETURNS text CHARSET utf8mb4 COLLATE utf8mb4_general_ci
BEGIN
 DECLARE sString varchar(30);
 DECLARE Bil1 varchar(255);
 DECLARE Bil2 varchar(255);
 DECLARE STot varchar(255);
 DECLARE X int;
 DECLARE Y int;
 DECLARE Z int;
 DECLARE Urai varchar(5000);

 SET sString = CAST(nominal as char);
 SET Urai = '';
 SET X = 0;
 SET Y = 0;
 WHILE X <> LENGTH(sString) DO
SET X = X + 1;
SET sTot = MID(sString, X, 1);
SET Y = Y + CAST(sTot as UNSIGNED);
SET Z = LENGTH(sString) - X + 1;
CASE CAST(sTot as UNSIGNED)
WHEN 1 THEN
 BEGIN
  IF (Z = 1 OR Z = 7 OR Z = 10 OR Z = 13) THEN
   SET Bil1 = 'Satu ';
  ELSEIF (z = 4) THEN
   IF (x = 1) THEN
    SET Bil1 = 'Se';
   ELSE
    SET Bil1 = 'Satu ';
   END IF;
  ELSEIF (Z = 2 OR Z = 5 OR Z = 8 OR Z = 11 OR Z = 14) THEN
   SET X = X + 1;
   SET sTot = MID(sString, X, 1);
   SET Z = LENGTH(sString) - X + 1;
   SET Bil2 = '';
   CASE CAST(sTot AS UNSIGNED)
    WHEN 0 THEN SET Bil1 = 'Sepuluh ';
    WHEN 1 THEN SET Bil1 = 'Sebelas ';
    WHEN 2 THEN SET Bil1 = 'Dua belas ';
    WHEN 3 THEN SET Bil1 = 'Tiga belas ';
    WHEN 4 THEN SET Bil1 = 'Empat belas ';
    WHEN 5 THEN SET Bil1 = 'Lima belas ';
    WHEN 6 THEN SET Bil1 = 'Enam belas ';
    WHEN 7 THEN SET Bil1 = 'Tujuh belas ';
    WHEN 8 THEN SET Bil1 = 'Delapan belas ';
    WHEN 9 THEN SET Bil1 = 'Sembilan belas  ';
   ELSE BEGIN END;
   END CASE;
  ELSE
   SET Bil1 = 'Se';
  END IF;
 END;
WHEN 2 THEN SET Bil1 = 'Dua ';
WHEN 3 THEN SET Bil1 = 'Tiga ';
WHEN 4 THEN SET Bil1 = 'Empat ';
WHEN 5 THEN SET Bil1 = 'Lima ';
WHEN 6 THEN SET Bil1 = 'Enam ';
WHEN 7 THEN SET Bil1 = 'Tujuh ';
WHEN 8 THEN SET Bil1 = 'Delapan ';
WHEN 9 THEN SET Bil1 = 'Sembilan ';
ELSE SET Bil1 = '';
END CASE;
IF CAST(sTot as UNSIGNED) > 0 THEN
IF (Z = 2 OR Z = 5 OR Z = 8 OR Z = 11 OR Z = 14) THEN
 SET Bil2 = 'Puluh ';
ELSEIF (Z = 3 OR Z = 6 OR Z = 9 OR Z = 12 OR Z = 15) THEN
 SET Bil2 = 'Ratus ';
ELSE
 SET Bil2 = '';
END IF;
ELSE
SET Bil2 = '';
END IF;
IF Y > 0 THEN
CASE Z
 WHEN 4 THEN BEGIN SET Bil2 = CONCAT(Bil2, 'Ribu '); SET Y = 0; END;
 WHEN 7 THEN BEGIN SET Bil2 = CONCAT(Bil2, 'Juta '); SET Y = 0; END;
 WHEN 10 THEN BEGIN SET Bil2 = CONCAT(Bil2, 'Milyar '); SET Y = 0; END;
 WHEN 13 THEN BEGIN SET Bil2 = CONCAT(Bil2, 'Trilyun '); SET Y = 0; END;
 ELSE BEGIN END;
END CASE;
END IF;
SET Urai = CONCAT(Urai, Bil1, Bil2);
END WHILE;
RETURN Urai;
END;;

DROP PROCEDURE IF EXISTS `sp_rpt_omset_periode`;;
CREATE PROCEDURE `sp_rpt_omset_periode`(IN `PStartDate` date, IN `PEndDate` date, IN `POutletID` int, IN `PUsername` varchar(100))
begin

select * from penjualan_barangs a
join customers b on b.id=a.customer_id and b.deleted_at is NULL and b.outlet_id=POutletID
where penjualan_barang_tanggal between concat(PStartDate,' 00:00:00') and  concat(PEndDate,' 23:00:00')
and a.deleted_at is NULL and penjualan_barang_validatedat !='' and a.outlet_id=POutletID
order by penjualan_barang_no asc;

end;;

DROP PROCEDURE IF EXISTS `sp_rpt_penjualanbarang`;;
CREATE PROCEDURE `sp_rpt_penjualanbarang`(IN `PID` bigint, IN `POutletID` int, IN `PUsername` varchar(100))
begin

select a.*,b.*,c.product_name,d.satuan_name,e.merk_name,
f.*,
fn_terbilang(penjualan_barang_grandtotal) as terbilang
from penjualan_barangs a
join penjualan_barang_details b on a.id = b.penjualan_barang_id and b.deleted_at is null
join products c on c.id=b.product_id
join satuans d on d.id=c.satuan_id
left join merks e on e.id = merk_id
join customers f on f.id=a.customer_id 
where a.id=PID and a.outlet_id=POutletID;
end;;

DROP PROCEDURE IF EXISTS `sp_rpt_stockopname`;;
CREATE PROCEDURE `sp_rpt_stockopname`(IN `PStartDate` date, IN `POutletID` int, IN `PUsername` varchar(100))
begin

SELECT 
    psh.tanggal,
    ps.id,
    p.product_name,
    ps.product_stock_sn,
    ps.product_stock_ed,
    psh.stock_akhir
FROM product_stocks ps
JOIN products p ON p.id = ps.product_id and p.outlet_id=POutletID
JOIN product_stock_histories psh 
    ON psh.id = (
        SELECT psh2.id 
        FROM product_stock_histories psh2 
        WHERE psh2.product_stock_id = ps.id 
          AND psh2.tanggal <= concat(PStartDate,' 23:00:00')
        ORDER BY psh2.tanggal DESC, psh2.id DESC
        LIMIT 1
    )
ORDER BY p.product_name;

end;;

DELIMITER ;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_alamat` text DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone1` varchar(255) DEFAULT NULL,
  `customer_phone2` varchar(255) DEFAULT NULL,
  `customer_picname1` varchar(255) DEFAULT NULL,
  `customer_picphone1` varchar(255) DEFAULT NULL,
  `customer_picname2` varchar(255) DEFAULT NULL,
  `customer_picphone2` varchar(255) DEFAULT NULL,
  `customer_harga` enum('hargagrosir','harga1','harga2','harga3') NOT NULL DEFAULT 'harga1',
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_customer_name_unique` (`customer_name`),
  KEY `customers_outlet_id_foreign` (`outlet_id`),
  KEY `customers_user_id_foreign` (`user_id`),
  KEY `customers_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `customers_customer_email_index` (`customer_email`),
  KEY `customers_customer_harga_index` (`customer_harga`),
  CONSTRAINT `customers_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customers` (`id`, `outlet_id`, `customer_name`, `customer_alamat`, `customer_email`, `customer_phone1`, `customer_phone2`, `customer_picname1`, `customer_picphone1`, `customer_picname2`, `customer_picphone2`, `customer_harga`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	'test customer',	'-',	NULL,	NULL,	NULL,	'tes',	'0246709867',	'haoo',	'0246709867',	'harga2',	1,	'2026-01-21 21:30:03',	'2026-01-21 21:51:14',	NULL),
(2,	1,	'tjhhjj',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'harga1',	1,	'2026-01-21 21:30:16',	'2026-01-21 21:30:16',	NULL),
(3,	4,	'super',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'hargagrosir',	10,	'2026-01-28 13:15:43',	'2026-01-28 13:15:43',	NULL);

DROP TABLE IF EXISTS `customer_marketings`;
CREATE TABLE `customer_marketings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `marketing_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_marketings_customer_id_foreign` (`customer_id`),
  KEY `customer_marketings_marketing_id_foreign` (`marketing_id`),
  KEY `customer_marketings_user_id_foreign` (`user_id`),
  KEY `customer_marketings_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  CONSTRAINT `customer_marketings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_marketings_marketing_id_foreign` FOREIGN KEY (`marketing_id`) REFERENCES `marketings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_marketings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customer_marketings` (`id`, `customer_id`, `marketing_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	2,	1,	'2026-01-21 21:33:04',	'2026-01-21 21:33:04',	NULL);

DROP TABLE IF EXISTS `customer_products`;
CREATE TABLE `customer_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `first_product_stock_id` int(11) NOT NULL,
  `harga_jual` decimal(10,2) DEFAULT NULL,
  `frekuensi` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_products_customer_id_foreign` (`customer_id`),
  KEY `customer_products_product_id_foreign` (`product_id`),
  KEY `customer_products_first_product_stock_id_index` (`first_product_stock_id`),
  KEY `customer_products_frekuensi_index` (`frekuensi`),
  CONSTRAINT `customer_products_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customer_products` (`id`, `customer_id`, `product_id`, `first_product_stock_id`, `harga_jual`, `frekuensi`, `created_at`, `updated_at`) VALUES
(1,	1,	2,	2,	40000.00,	2,	'2026-01-22 00:19:12',	'2026-01-22 00:37:35'),
(2,	1,	1,	1,	25000.00,	1,	'2026-01-22 00:37:35',	'2026-01-22 00:37:35'),
(3,	3,	5,	5,	10000.00,	1,	'2026-01-28 13:31:16',	'2026-01-28 13:31:16'),
(4,	3,	3,	3,	40000.00,	9,	'2026-01-28 13:31:16',	'2026-02-04 09:47:15'),
(5,	3,	4,	4,	32500.00,	2,	'2026-01-28 13:31:16',	'2026-01-28 13:39:03');

DROP TABLE IF EXISTS `dashboard_accesses`;
CREATE TABLE `dashboard_accesses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `system_dashboard_id` bigint(20) unsigned NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `user_group_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dashboard_accesses_outlet_id_foreign` (`outlet_id`),
  KEY `dashboard_accesses_system_dashboard_id_foreign` (`system_dashboard_id`),
  KEY `dashboard_accesses_user_group_id_foreign` (`user_group_id`),
  KEY `dashboard_accesses_user_id_foreign` (`user_id`),
  KEY `dashboard_accesses_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `dashboard_accesses_can_view_index` (`can_view`),
  CONSTRAINT `dashboard_accesses_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dashboard_accesses_system_dashboard_id_foreign` FOREIGN KEY (`system_dashboard_id`) REFERENCES `system_dashboards` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dashboard_accesses_user_group_id_foreign` FOREIGN KEY (`user_group_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dashboard_accesses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dashboard_accesses` (`id`, `outlet_id`, `system_dashboard_id`, `can_view`, `user_group_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	1,	1,	2,	1,	'2025-10-07 17:45:42',	'2025-10-07 17:45:42',	NULL),
(2,	1,	2,	1,	2,	1,	'2025-10-07 17:45:53',	'2025-10-07 17:45:53',	NULL),
(3,	1,	3,	1,	2,	1,	'2025-10-07 17:46:03',	'2025-10-07 17:46:03',	NULL),
(4,	1,	4,	1,	2,	1,	'2025-10-07 17:46:13',	'2025-11-25 20:55:12',	NULL),
(5,	1,	5,	1,	2,	1,	'2025-10-07 17:46:23',	'2025-11-25 20:55:17',	NULL),
(6,	4,	1,	1,	2,	10,	'2026-01-28 13:40:49',	'2026-01-28 13:40:49',	NULL),
(7,	4,	2,	1,	2,	1,	'2026-01-28 13:41:05',	'2026-01-29 06:16:11',	NULL),
(8,	4,	3,	1,	2,	1,	'2026-01-28 13:43:53',	'2026-01-29 06:16:35',	NULL),
(9,	4,	4,	1,	2,	1,	'2026-01-29 06:13:45',	'2026-01-29 06:19:10',	NULL),
(10,	4,	5,	0,	2,	1,	'2026-01-29 06:14:48',	'2026-01-29 06:14:48',	NULL),
(11,	12,	1,	1,	2,	1,	'2026-02-09 06:26:39',	'2026-02-09 06:58:20',	'2026-02-09 06:58:20'),
(12,	12,	2,	1,	2,	1,	'2026-02-09 06:26:39',	'2026-02-09 06:58:20',	'2026-02-09 06:58:20'),
(13,	12,	3,	1,	2,	1,	'2026-02-09 06:26:39',	'2026-02-09 06:58:20',	'2026-02-09 06:58:20'),
(14,	13,	1,	1,	2,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(15,	13,	2,	1,	2,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(16,	13,	3,	1,	2,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(17,	4,	6,	1,	2,	1,	'2026-02-10 03:46:21',	'2026-02-10 03:46:21',	NULL),
(18,	12,	6,	1,	2,	19,	'2026-02-10 03:49:04',	'2026-02-10 03:49:04',	NULL),
(19,	12,	1,	1,	2,	19,	'2026-02-10 03:59:37',	'2026-02-10 03:59:37',	NULL),
(20,	12,	2,	1,	2,	19,	'2026-02-10 04:01:18',	'2026-02-10 04:01:18',	NULL),
(21,	13,	6,	1,	2,	21,	'2026-02-10 04:08:05',	'2026-02-10 04:08:05',	NULL);

DROP TABLE IF EXISTS `document_numberings`;
CREATE TABLE `document_numberings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `document_numbering_name` varchar(255) NOT NULL,
  `document_numbering_prefix` varchar(255) DEFAULT NULL,
  `document_numbering_format` varchar(255) DEFAULT NULL,
  `document_numbering_numberlength` int(11) NOT NULL DEFAULT 4,
  `document_numbering_currentnumber` int(11) NOT NULL DEFAULT 0,
  `document_numbering_resettype` enum('daily','yearly','monthly') NOT NULL DEFAULT 'yearly',
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_numberings_outlet_id_foreign` (`outlet_id`),
  KEY `document_numberings_user_id_foreign` (`user_id`),
  KEY `document_numberings_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `document_numberings_document_numbering_name_index` (`document_numbering_name`),
  KEY `document_numberings_document_numbering_prefix_index` (`document_numbering_prefix`),
  KEY `document_numberings_document_numbering_currentnumber_index` (`document_numbering_currentnumber`),
  KEY `document_numberings_document_numbering_resettype_index` (`document_numbering_resettype`),
  CONSTRAINT `document_numberings_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_numberings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `document_numberings` (`id`, `outlet_id`, `document_numbering_name`, `document_numbering_prefix`, `document_numbering_format`, `document_numbering_numberlength`, `document_numbering_currentnumber`, `document_numbering_resettype`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2,	1,	'penjualan_barang',	'SJ',	'[document_numbering_prefix] - [Y][M]',	4,	5,	'yearly',	1,	'2026-01-21 23:28:49',	'2026-01-22 00:06:46',	NULL),
(3,	4,	'penjualan_barang',	'MPM',	'[document_numbering_prefix] - [Y][M]',	4,	1,	'monthly',	1,	'2026-01-21 23:28:49',	'2026-02-04 08:35:38',	NULL);

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `kas_harians`;
CREATE TABLE `kas_harians` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `kasir_id` bigint(20) unsigned NOT NULL,
  `kas_harian_tanggalbuka` datetime NOT NULL,
  `kas_harian_tanggaltutup` datetime DEFAULT NULL,
  `kas_harian_saldoawal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kas_harian_saldoakhir` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kas_harian_saldoseharusnya` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kas_harian_selisih` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kas_harian_status` enum('buka','tutup') NOT NULL DEFAULT 'buka',
  `kas_harian_notes` text DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kas_harians_outlet_id_foreign` (`outlet_id`),
  KEY `kas_harians_kasir_id_foreign` (`kasir_id`),
  KEY `kas_harians_user_id_foreign` (`user_id`),
  KEY `kas_harians_kas_harian_tanggalbuka_index` (`kas_harian_tanggalbuka`),
  KEY `kas_harians_kas_harian_tanggaltutup_index` (`kas_harian_tanggaltutup`),
  KEY `kas_harians_kas_harian_status_index` (`kas_harian_status`),
  CONSTRAINT `kas_harians_kasir_id_foreign` FOREIGN KEY (`kasir_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_harians_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_harians_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kas_harians` (`id`, `outlet_id`, `kasir_id`, `kas_harian_tanggalbuka`, `kas_harian_tanggaltutup`, `kas_harian_saldoawal`, `kas_harian_saldoakhir`, `kas_harian_saldoseharusnya`, `kas_harian_selisih`, `kas_harian_status`, `kas_harian_notes`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	4,	10,	'2026-01-28 20:32:10',	NULL,	100000.00,	0.00,	0.00,	0.00,	'buka',	NULL,	10,	'2026-01-28 13:32:10',	'2026-01-28 13:32:10',	NULL);

DROP TABLE IF EXISTS `kas_pemasukkans`;
CREATE TABLE `kas_pemasukkans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kas_harian_id` bigint(20) unsigned NOT NULL,
  `kasir_id` bigint(20) unsigned NOT NULL,
  `kas_pemasukkan_jenis` enum('masuk','keluar') NOT NULL,
  `kas_pemasukkan_jumlah` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kas_pemasukkan_sumber` varchar(255) DEFAULT NULL,
  `kas_pemasukkan_reference` int(11) DEFAULT NULL,
  `kas_pemasukkan_notransaksi` varchar(50) NOT NULL,
  `kas_pemasukkan_notes` text DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kas_pemasukkans_kas_harian_id_foreign` (`kas_harian_id`),
  KEY `kas_pemasukkans_kasir_id_foreign` (`kasir_id`),
  KEY `kas_pemasukkans_user_id_foreign` (`user_id`),
  KEY `kas_pemasukkans_kas_pemasukkan_jenis_index` (`kas_pemasukkan_jenis`),
  KEY `kas_pemasukkans_kas_pemasukkan_jumlah_index` (`kas_pemasukkan_jumlah`),
  KEY `kas_pemasukkans_kas_pemasukkan_reference_index` (`kas_pemasukkan_reference`),
  KEY `kas_pemasukkans_kas_pemasukkan_notransaksi_index` (`kas_pemasukkan_notransaksi`),
  CONSTRAINT `kas_pemasukkans_kas_harian_id_foreign` FOREIGN KEY (`kas_harian_id`) REFERENCES `kas_harians` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_pemasukkans_kasir_id_foreign` FOREIGN KEY (`kasir_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_pemasukkans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kas_pemasukkans` (`id`, `kas_harian_id`, `kasir_id`, `kas_pemasukkan_jenis`, `kas_pemasukkan_jumlah`, `kas_pemasukkan_sumber`, `kas_pemasukkan_reference`, `kas_pemasukkan_notransaksi`, `kas_pemasukkan_notes`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	10,	'masuk',	280275.00,	'PaymentPenjualan',	1,	'MPM - 2026010006',	'cash',	10,	'2026-01-28 13:32:22',	'2026-01-28 13:32:22',	NULL),
(2,	1,	10,	'keluar',	315000.00,	'PaymentPenerimaan',	1,	'TEST 01',	'cash',	10,	'2026-01-28 13:35:36',	'2026-01-28 13:35:36',	NULL),
(3,	1,	10,	'masuk',	124875.00,	'PaymentPenjualan',	2,	'MPM - 2026010007',	'qris',	10,	'2026-01-28 13:39:41',	'2026-01-28 13:39:41',	NULL);

DROP TABLE IF EXISTS `kas_pengeluarans`;
CREATE TABLE `kas_pengeluarans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kas_pengeluaran_tanggal` date NOT NULL,
  `kas_harian_id` bigint(20) unsigned NOT NULL,
  `kasir_id` bigint(20) unsigned NOT NULL,
  `kategori_pengeluaran_id` bigint(20) unsigned NOT NULL,
  `kas_pengeluaran_notes` text DEFAULT NULL,
  `kas_pengeluaran_jumlah` decimal(10,2) NOT NULL DEFAULT 0.00,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kas_pengeluarans_kas_harian_id_foreign` (`kas_harian_id`),
  KEY `kas_pengeluarans_kasir_id_foreign` (`kasir_id`),
  KEY `kas_pengeluarans_kategori_pengeluaran_id_foreign` (`kategori_pengeluaran_id`),
  KEY `kas_pengeluarans_user_id_foreign` (`user_id`),
  KEY `kas_pengeluarans_kas_pengeluaran_tanggal_index` (`kas_pengeluaran_tanggal`),
  KEY `kas_pengeluarans_kas_pengeluaran_jumlah_index` (`kas_pengeluaran_jumlah`),
  CONSTRAINT `kas_pengeluarans_kas_harian_id_foreign` FOREIGN KEY (`kas_harian_id`) REFERENCES `kas_harians` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_pengeluarans_kasir_id_foreign` FOREIGN KEY (`kasir_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_pengeluarans_kategori_pengeluaran_id_foreign` FOREIGN KEY (`kategori_pengeluaran_id`) REFERENCES `kategori_pengeluarans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_pengeluarans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `kategori_pengeluarans`;
CREATE TABLE `kategori_pengeluarans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `kategori_pengeluaran_kode` varchar(50) DEFAULT NULL,
  `kategori_pengeluaran_name` varchar(100) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kategori_pengeluarans_outlet_id_foreign` (`outlet_id`),
  KEY `kategori_pengeluarans_user_id_foreign` (`user_id`),
  KEY `kategori_pengeluarans_kategori_pengeluaran_kode_index` (`kategori_pengeluaran_kode`),
  KEY `kategori_pengeluarans_kategori_pengeluaran_name_index` (`kategori_pengeluaran_name`),
  CONSTRAINT `kategori_pengeluarans_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kategori_pengeluarans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kategori_pengeluarans` (`id`, `outlet_id`, `kategori_pengeluaran_kode`, `kategori_pengeluaran_name`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	NULL,	'Payment Supplier',	1,	NULL,	NULL,	NULL);

DROP TABLE IF EXISTS `kelompok_products`;
CREATE TABLE `kelompok_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `kelompok_productname` varchar(75) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kelompok_products_outlet_id_foreign` (`outlet_id`),
  KEY `kelompok_products_user_id_foreign` (`user_id`),
  KEY `kelompok_products_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `kelompok_products_kelompok_productname_index` (`kelompok_productname`),
  CONSTRAINT `kelompok_products_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kelompok_products_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kelompok_products` (`id`, `outlet_id`, `kelompok_productname`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	'Kunci Kontak',	10,	'2026-01-21 23:17:51',	'2026-01-28 12:28:32',	NULL),
(2,	4,	'Kunci Kontak',	10,	'2026-01-28 12:40:14',	'2026-01-28 12:40:14',	NULL),
(3,	4,	'Busi',	10,	'2026-01-28 12:40:23',	'2026-01-28 12:40:23',	NULL);

DROP TABLE IF EXISTS `laporans`;
CREATE TABLE `laporans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `laporan_kode` varchar(255) NOT NULL,
  `laporan_name` varchar(255) NOT NULL,
  `params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`params`)),
  `path` varchar(255) DEFAULT NULL,
  `is_excel` tinyint(1) NOT NULL DEFAULT 0,
  `path_excel` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `laporans_outlet_id_foreign` (`outlet_id`),
  KEY `laporans_user_id_foreign` (`user_id`),
  KEY `laporans_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `laporans_laporan_kode_index` (`laporan_kode`),
  KEY `laporans_laporan_name_index` (`laporan_name`),
  CONSTRAINT `laporans_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laporans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `laporans` (`id`, `outlet_id`, `laporan_kode`, `laporan_name`, `params`, `path`, `is_excel`, `path_excel`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	4,	'L001',	'Laporan Omset Periode',	'{\r\n\"PStartDate\": {\r\n    \"label\": \"Tanggal Awal\",\r\n    \"type\": \"date\"\r\n  },\r\n\"PEndDate\": {\r\n    \"label\": \"Tanggal Akhir\",\r\n    \"type\": \"date\"\r\n  }\r\n}',	'postexample/rpt_omsetperiodemulti',	0,	NULL,	2,	'2025-10-29 16:07:31',	'2025-10-29 16:07:31',	NULL),
(2,	4,	'L002',	'Laporan Stock Opname',	'{\r\n  \"PStartDate\": {\r\n    \"label\": \"Tanggal Akhir\",\r\n    \"type\": \"date\"\r\n  }\r\n}',	'postexample/rpt_stockopnamemulti',	0,	NULL,	2,	'2025-10-29 16:07:31',	'2025-10-29 16:07:31',	NULL),
(3,	4,	'L003',	'Laporan Penjualan Periode Excel',	'{\r\n\"PStartDate\": {\r\n    \"label\": \"Tanggal Awal\",\r\n    \"type\": \"date\"\r\n  },\r\n\"PEndDate\": {\r\n    \"label\": \"Tanggal Akhir\",\r\n    \"type\": \"date\"\r\n  }\r\n}',	NULL,	1,	'App\\Exports\\LaporanPenjualanPivotExportTemplate',	2,	'2025-10-29 16:07:31',	'2025-10-29 16:07:31',	NULL);

DROP TABLE IF EXISTS `laporan_accesses`;
CREATE TABLE `laporan_accesses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `laporan_id` bigint(20) unsigned NOT NULL,
  `user_group_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `laporan_accesses_laporan_id_foreign` (`laporan_id`),
  KEY `laporan_accesses_user_group_id_foreign` (`user_group_id`),
  KEY `laporan_accesses_user_id_foreign` (`user_id`),
  KEY `laporan_accesses_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  CONSTRAINT `laporan_accesses_laporan_id_foreign` FOREIGN KEY (`laporan_id`) REFERENCES `laporans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laporan_accesses_user_group_id_foreign` FOREIGN KEY (`user_group_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laporan_accesses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `laporan_accesses` (`id`, `laporan_id`, `user_group_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	2,	2,	'2025-10-29 16:10:33',	'2025-10-29 16:10:33',	NULL),
(2,	2,	2,	2,	'2025-10-29 16:10:33',	'2025-10-29 16:10:33',	NULL),
(3,	3,	2,	2,	'2025-10-29 16:10:33',	'2025-10-29 16:10:33',	NULL);

DROP TABLE IF EXISTS `marketings`;
CREATE TABLE `marketings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `marketing_name` varchar(255) NOT NULL,
  `marketing_email` varchar(255) DEFAULT NULL,
  `marketing_address` varchar(255) DEFAULT NULL,
  `marketing_phone1` varchar(255) DEFAULT NULL,
  `marketing_phone2` varchar(255) DEFAULT NULL,
  `marketing_team_id` int(11) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketings_outlet_id_foreign` (`outlet_id`),
  KEY `marketings_user_id_foreign` (`user_id`),
  KEY `marketings_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `marketings_marketing_name_index` (`marketing_name`),
  KEY `marketings_marketing_email_index` (`marketing_email`),
  CONSTRAINT `marketings_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `marketings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `marketings` (`id`, `outlet_id`, `marketing_name`, `marketing_email`, `marketing_address`, `marketing_phone1`, `marketing_phone2`, `marketing_team_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	'dedi',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	'2026-01-21 21:30:41',	'2026-01-21 21:30:41',	NULL),
(2,	1,	'budi',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	'2026-01-21 21:30:51',	'2026-01-21 21:30:51',	NULL);

DROP TABLE IF EXISTS `marketing_teams`;
CREATE TABLE `marketing_teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `marketing_team_name` varchar(255) NOT NULL,
  `marketing_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketing_teams_marketing_id_foreign` (`marketing_id`),
  KEY `marketing_teams_user_id_foreign` (`user_id`),
  KEY `marketing_teams_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `marketing_teams_marketing_team_name_index` (`marketing_team_name`),
  CONSTRAINT `marketing_teams_marketing_id_foreign` FOREIGN KEY (`marketing_id`) REFERENCES `marketings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `marketing_teams_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `master_datas`;
CREATE TABLE `master_datas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `master_dataname` varchar(255) NOT NULL,
  `master_datalink` varchar(255) DEFAULT NULL,
  `master_data_group_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_datas_master_data_group_id_foreign` (`master_data_group_id`),
  KEY `master_datas_user_id_foreign` (`user_id`),
  KEY `master_datas_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `master_datas_master_dataname_index` (`master_dataname`),
  CONSTRAINT `master_datas_master_data_group_id_foreign` FOREIGN KEY (`master_data_group_id`) REFERENCES `master_data_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_datas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `master_datas` (`id`, `master_dataname`, `master_datalink`, `master_data_group_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	'Kelompok Product',	'products/kelompok-products',	1,	1,	NULL,	NULL,	NULL),
(2,	'Satuan',	'products/satuans',	1,	1,	NULL,	NULL,	NULL),
(3,	'Product',	'products/products',	1,	1,	NULL,	NULL,	NULL),
(5,	'User Group',	'accesses/user-groups',	2,	1,	NULL,	NULL,	NULL),
(6,	'User',	'accesses/users',	2,	1,	NULL,	NULL,	NULL),
(7,	'Master Data Group',	'accesses/master-data-groups',	2,	1,	NULL,	NULL,	NULL),
(8,	'Master Data',	'accesses/master-datas',	2,	1,	NULL,	NULL,	NULL),
(9,	'Master Data Access',	'accesses/master-data-accesses',	2,	1,	NULL,	NULL,	NULL),
(10,	'Merk',	'products/merks',	1,	1,	NULL,	NULL,	NULL),
(11,	'Stock Awal',	'products/product-stocks',	1,	1,	NULL,	NULL,	NULL),
(12,	'Customer',	'mitra/customers',	3,	1,	NULL,	NULL,	NULL),
(13,	'Supplier',	'mitra/suppliers',	3,	1,	NULL,	NULL,	NULL),
(14,	'Marketing',	'mitra/marketings',	3,	1,	NULL,	NULL,	NULL),
(15,	'Team Marketing',	'mitra/marketing-teams',	3,	1,	NULL,	NULL,	NULL),
(16,	'Menu',	'accesses/menus',	2,	1,	NULL,	NULL,	NULL),
(17,	'Menu Access',	'accesses/menu-accesses',	2,	1,	NULL,	NULL,	NULL),
(18,	'Menu Dashboard',	'accesses/system-dashboards',	2,	1,	NULL,	NULL,	NULL),
(19,	'Dashbord Access',	'accesses/dashboard-accesses',	2,	1,	NULL,	NULL,	NULL),
(20,	'Outlet',	'mitra/outlets',	3,	1,	'2026-01-28 12:24:17',	'2026-01-28 12:24:39',	NULL);

DROP TABLE IF EXISTS `master_data_accesses`;
CREATE TABLE `master_data_accesses` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `master_data_id` bigint(20) unsigned NOT NULL,
  `user_group_id` bigint(20) unsigned NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_data_accesses_user_group_id_foreign` (`user_group_id`),
  KEY `master_data_accesses_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `master_data_accesses_user_id_foreign` (`user_id`),
  KEY `master_data_id` (`master_data_id`),
  KEY `outlet_id_master_data_id_user_group_id` (`outlet_id`,`master_data_id`,`user_group_id`),
  CONSTRAINT `master_data_accesses_master_data_id_foreign` FOREIGN KEY (`master_data_id`) REFERENCES `master_datas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_data_accesses_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_data_accesses_user_group_id_foreign` FOREIGN KEY (`user_group_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_data_accesses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `master_data_accesses` (`id`, `outlet_id`, `master_data_id`, `user_group_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`, `deleted_at`, `user_id`) VALUES
(2,	1,	1,	2,	1,	1,	1,	0,	'2025-08-05 18:48:14',	'2025-10-27 12:51:36',	NULL,	1),
(3,	1,	2,	2,	1,	1,	1,	1,	'2025-08-05 18:48:31',	'2025-10-26 19:23:22',	NULL,	1),
(4,	1,	3,	2,	1,	1,	1,	0,	'2025-08-05 18:48:48',	'2025-10-26 17:09:55',	NULL,	1),
(5,	1,	1,	4,	1,	0,	0,	0,	'2025-08-06 11:49:01',	'2025-08-06 11:49:01',	NULL,	1),
(6,	1,	5,	2,	1,	1,	0,	0,	'2025-08-07 10:58:24',	'2025-10-26 17:10:36',	NULL,	1),
(8,	1,	6,	2,	1,	1,	1,	1,	'2025-08-07 10:59:09',	'2025-09-03 06:19:26',	NULL,	1),
(9,	1,	7,	2,	1,	1,	1,	1,	'2025-08-07 10:59:18',	'2025-08-19 05:18:07',	NULL,	1),
(10,	1,	8,	2,	1,	1,	1,	1,	'2025-08-07 10:59:29',	'2025-08-19 05:19:46',	NULL,	1),
(11,	1,	9,	2,	1,	1,	1,	1,	'2025-08-07 10:59:36',	'2025-08-07 10:59:36',	NULL,	1),
(12,	1,	2,	4,	1,	0,	0,	0,	'2025-08-07 11:26:58',	'2025-08-07 11:26:58',	NULL,	1),
(13,	1,	6,	4,	1,	0,	0,	0,	'2025-08-10 05:58:51',	'2025-08-10 05:58:51',	NULL,	5),
(14,	1,	10,	2,	1,	1,	1,	1,	'2025-08-14 06:44:54',	'2025-08-14 06:44:54',	NULL,	1),
(15,	1,	11,	2,	1,	1,	1,	0,	'2025-08-17 05:41:11',	'2025-08-17 05:41:11',	NULL,	1),
(16,	1,	13,	2,	1,	1,	1,	1,	'2025-08-19 09:18:45',	'2025-08-19 09:18:45',	NULL,	1),
(17,	1,	12,	2,	1,	1,	1,	1,	'2025-08-19 09:19:06',	'2025-08-19 09:19:06',	NULL,	1),
(18,	1,	14,	2,	1,	1,	1,	1,	'2025-09-02 07:36:53',	'2025-09-02 07:36:53',	NULL,	1),
(19,	1,	15,	2,	1,	1,	1,	1,	'2025-09-02 07:37:02',	'2025-09-02 07:37:02',	NULL,	1),
(20,	1,	16,	2,	1,	1,	1,	1,	'2025-09-02 12:08:47',	'2025-09-02 12:08:47',	NULL,	1),
(21,	1,	17,	2,	1,	1,	1,	1,	'2025-09-02 12:08:56',	'2025-09-02 12:08:56',	NULL,	1),
(22,	1,	18,	2,	1,	1,	1,	1,	'2025-10-06 18:45:47',	'2025-10-06 18:45:47',	NULL,	1),
(23,	1,	19,	2,	1,	1,	1,	1,	'2025-10-07 17:12:09',	'2025-10-07 17:44:35',	NULL,	1),
(24,	1,	12,	6,	1,	1,	1,	0,	'2025-10-21 16:47:14',	'2025-10-21 16:47:14',	NULL,	1),
(25,	1,	3,	6,	1,	1,	1,	0,	'2025-10-21 16:47:44',	'2025-10-21 16:47:44',	NULL,	1),
(26,	1,	20,	2,	1,	1,	1,	1,	'2026-01-28 12:25:10',	'2026-01-28 12:25:10',	NULL,	1),
(37,	4,	1,	2,	1,	1,	1,	0,	'2025-08-05 18:48:14',	'2025-10-27 12:51:36',	NULL,	1),
(38,	4,	2,	2,	1,	1,	1,	1,	'2025-08-05 18:48:31',	'2025-10-26 19:23:22',	NULL,	1),
(39,	4,	3,	2,	1,	1,	1,	0,	'2025-08-05 18:48:48',	'2025-10-26 17:09:55',	NULL,	1),
(40,	4,	1,	4,	1,	0,	0,	0,	'2025-08-06 11:49:01',	'2025-08-06 11:49:01',	NULL,	1),
(41,	4,	5,	2,	1,	1,	0,	0,	'2025-08-07 10:58:24',	'2025-10-26 17:10:36',	NULL,	1),
(42,	4,	6,	2,	1,	1,	1,	1,	'2025-08-07 10:59:09',	'2025-09-03 06:19:26',	NULL,	1),
(43,	4,	7,	2,	1,	1,	1,	1,	'2025-08-07 10:59:18',	'2025-08-19 05:18:07',	NULL,	1),
(44,	4,	8,	2,	1,	1,	1,	1,	'2025-08-07 10:59:29',	'2025-08-19 05:19:46',	NULL,	1),
(45,	4,	9,	2,	1,	1,	1,	1,	'2025-08-07 10:59:36',	'2025-08-07 10:59:36',	NULL,	1),
(46,	4,	2,	4,	1,	0,	0,	0,	'2025-08-07 11:26:58',	'2025-08-07 11:26:58',	NULL,	1),
(47,	4,	6,	4,	1,	0,	0,	0,	'2025-08-10 05:58:51',	'2025-08-10 05:58:51',	NULL,	5),
(48,	4,	10,	2,	1,	1,	1,	1,	'2025-08-14 06:44:54',	'2025-08-14 06:44:54',	NULL,	1),
(49,	4,	11,	2,	1,	1,	1,	0,	'2025-08-17 05:41:11',	'2025-08-17 05:41:11',	NULL,	1),
(50,	4,	13,	2,	1,	1,	1,	1,	'2025-08-19 09:18:45',	'2025-08-19 09:18:45',	NULL,	1),
(51,	4,	12,	2,	1,	1,	1,	1,	'2025-08-19 09:19:06',	'2025-08-19 09:19:06',	NULL,	1),
(52,	4,	14,	2,	1,	1,	1,	1,	'2025-09-02 07:36:53',	'2025-09-02 07:36:53',	NULL,	1),
(53,	4,	15,	2,	1,	1,	1,	1,	'2025-09-02 07:37:02',	'2025-09-02 07:37:02',	NULL,	1),
(54,	4,	16,	2,	1,	1,	1,	1,	'2025-09-02 12:08:47',	'2025-09-02 12:08:47',	NULL,	1),
(55,	4,	17,	2,	1,	1,	1,	1,	'2025-09-02 12:08:56',	'2025-09-02 12:08:56',	NULL,	1),
(56,	4,	18,	2,	1,	1,	1,	1,	'2025-10-06 18:45:47',	'2025-10-06 18:45:47',	NULL,	1),
(57,	4,	19,	2,	1,	1,	1,	1,	'2025-10-07 17:12:09',	'2025-10-07 17:44:35',	NULL,	1),
(58,	4,	12,	6,	1,	1,	1,	0,	'2025-10-21 16:47:14',	'2025-10-21 16:47:14',	NULL,	1),
(59,	4,	3,	6,	1,	1,	1,	0,	'2025-10-21 16:47:44',	'2025-10-21 16:47:44',	NULL,	1),
(60,	4,	20,	2,	1,	1,	1,	1,	'2026-01-28 12:25:10',	'2026-01-28 12:25:10',	NULL,	1),
(105,	12,	1,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(106,	12,	2,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(107,	12,	3,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(108,	12,	6,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(109,	12,	9,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(110,	12,	10,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(111,	12,	11,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(112,	12,	12,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(113,	12,	13,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(114,	12,	14,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(115,	12,	15,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(116,	12,	17,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(117,	12,	19,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(118,	12,	20,	2,	1,	1,	1,	1,	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL,	1),
(119,	12,	1,	4,	1,	1,	1,	0,	'2026-02-09 06:31:01',	'2026-02-09 08:08:53',	NULL,	19),
(120,	12,	2,	4,	1,	1,	1,	0,	'2026-02-09 06:31:01',	'2026-02-09 08:08:53',	NULL,	19),
(121,	12,	3,	4,	1,	1,	1,	0,	'2026-02-09 06:31:01',	'2026-02-09 08:08:53',	NULL,	19),
(122,	12,	6,	4,	1,	1,	1,	0,	'2026-02-09 06:31:01',	'2026-02-09 08:08:53',	NULL,	19),
(123,	12,	10,	4,	1,	1,	1,	0,	'2026-02-09 06:31:01',	'2026-02-09 08:08:53',	NULL,	19),
(124,	12,	11,	4,	1,	1,	1,	0,	'2026-02-09 06:31:01',	'2026-02-09 08:08:53',	NULL,	19),
(125,	12,	12,	4,	1,	1,	1,	0,	'2026-02-09 06:31:01',	'2026-02-09 08:08:53',	NULL,	19),
(126,	12,	13,	4,	1,	1,	1,	0,	'2026-02-09 06:31:01',	'2026-02-09 08:08:53',	NULL,	19),
(127,	12,	14,	4,	1,	1,	1,	0,	'2026-02-09 06:31:01',	'2026-02-09 08:08:53',	NULL,	19),
(128,	12,	15,	4,	1,	1,	1,	0,	'2026-02-09 06:31:01',	'2026-02-09 08:08:53',	NULL,	19),
(129,	13,	1,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(130,	13,	2,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(131,	13,	3,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(132,	13,	6,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(133,	13,	9,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(134,	13,	10,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(135,	13,	11,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(136,	13,	12,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(137,	13,	13,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(138,	13,	14,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(139,	13,	15,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(140,	13,	17,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(141,	13,	19,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1),
(142,	13,	20,	2,	1,	1,	1,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	1);

DROP TABLE IF EXISTS `master_data_groups`;
CREATE TABLE `master_data_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `master_data_groupname` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_data_groups_user_id_foreign` (`user_id`),
  KEY `master_data_groups_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `master_data_groups_master_data_groupname_index` (`master_data_groupname`),
  CONSTRAINT `master_data_groups_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `master_data_groups` (`id`, `master_data_groupname`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	'ITEM',	1,	NULL,	NULL,	NULL),
(2,	'PENGGUNA',	1,	NULL,	NULL,	NULL),
(3,	'MITRA',	1,	NULL,	NULL,	NULL);

DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(255) NOT NULL,
  `menu_link` varchar(255) DEFAULT NULL,
  `menu_icon` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menus_user_id_foreign` (`user_id`),
  KEY `menus_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `menus_menu_name_index` (`menu_name`),
  CONSTRAINT `menus_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menus` (`id`, `menu_name`, `menu_link`, `menu_icon`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	'Master Data',	'products/kelompok-products',	NULL,	1,	NULL,	NULL,	NULL),
(2,	'Penerimaan Barang',	'pembelian/penerimaan-barangs',	NULL,	1,	NULL,	NULL,	NULL),
(3,	'Surat Jalan / Faktur',	'penjualan/penjualan-barangs',	NULL,	1,	NULL,	NULL,	NULL),
(4,	'Payment Penjualan',	'penjualan/payment-penjualans',	NULL,	1,	NULL,	NULL,	NULL),
(5,	'Transaksi Payment',	'kasir/payment-kasirs',	NULL,	1,	NULL,	NULL,	NULL),
(6,	'Pengeluaran Lain-Lain',	'pembelian/kas-pengeluarans',	NULL,	1,	NULL,	NULL,	NULL),
(7,	'History Product Stock',	'products/history-product-stock',	NULL,	1,	NULL,	NULL,	NULL),
(8,	'Laporan lain-lain',	'laporan/laporan',	NULL,	1,	NULL,	NULL,	NULL),
(9,	'Payment Supplier',	'pembelian/payment-penerimaans',	NULL,	1,	NULL,	NULL,	NULL),
(10,	'Kasir',	'kasir/buka-kasirs',	NULL,	1,	NULL,	NULL,	NULL);

DROP TABLE IF EXISTS `menu_accesses`;
CREATE TABLE `menu_accesses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `menu_id` bigint(20) unsigned NOT NULL,
  `user_group_id` bigint(20) unsigned NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `can_validate` tinyint(1) NOT NULL DEFAULT 0,
  `can_unvalidate` tinyint(1) NOT NULL DEFAULT 0,
  `can_print1` tinyint(1) NOT NULL DEFAULT 0,
  `can_print2` tinyint(1) NOT NULL DEFAULT 0,
  `can_ppn` tinyint(1) NOT NULL DEFAULT 0,
  `ppn_rate` tinyint(4) NOT NULL DEFAULT 0,
  `can_ongkir` tinyint(1) NOT NULL DEFAULT 0,
  `can_hargapembelian` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_menu_user_group_outlet` (`menu_id`,`user_group_id`,`outlet_id`),
  KEY `menu_accesses_outlet_id_foreign` (`outlet_id`),
  KEY `menu_accesses_user_group_id_foreign` (`user_group_id`),
  KEY `menu_accesses_user_id_foreign` (`user_id`),
  KEY `menu_accesses_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `menu_accesses_can_view_index` (`can_view`),
  KEY `menu_accesses_can_create_index` (`can_create`),
  KEY `menu_accesses_can_edit_index` (`can_edit`),
  KEY `menu_accesses_can_delete_index` (`can_delete`),
  KEY `menu_accesses_can_validate_index` (`can_validate`),
  KEY `menu_accesses_can_unvalidate_index` (`can_unvalidate`),
  KEY `menu_accesses_can_print1_index` (`can_print1`),
  KEY `menu_accesses_can_print2_index` (`can_print2`),
  KEY `menu_accesses_can_ppn_index` (`can_ppn`),
  KEY `menu_accesses_can_ongkir_index` (`can_ongkir`),
  CONSTRAINT `menu_accesses_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_accesses_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_accesses_user_group_id_foreign` FOREIGN KEY (`user_group_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_accesses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_accesses` (`id`, `outlet_id`, `menu_id`, `user_group_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_validate`, `can_unvalidate`, `can_print1`, `can_print2`, `can_ppn`, `ppn_rate`, `can_ongkir`, `can_hargapembelian`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	1,	2,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-09-01 13:20:08',	'2025-12-16 00:33:21',	NULL),
(2,	1,	2,	2,	1,	1,	1,	1,	1,	1,	0,	0,	0,	0,	0,	1,	1,	'2025-09-01 13:20:28',	'2025-12-10 19:38:01',	NULL),
(3,	1,	1,	4,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-09-02 12:36:23',	'2025-09-02 12:36:23',	NULL),
(4,	1,	3,	2,	1,	1,	1,	1,	1,	1,	1,	1,	1,	11,	1,	0,	1,	'2025-09-03 06:00:18',	'2025-10-26 17:55:36',	NULL),
(5,	1,	4,	2,	1,	0,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-09-16 11:07:25',	'2025-10-26 17:47:44',	NULL),
(6,	1,	5,	2,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-09-16 11:07:33',	'2025-09-16 11:07:33',	NULL),
(7,	1,	6,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-09-16 11:07:40',	'2025-12-16 02:58:33',	NULL),
(8,	1,	3,	6,	1,	1,	1,	1,	1,	1,	1,	1,	0,	0,	0,	0,	1,	'2025-10-21 16:48:46',	'2025-10-21 16:48:46',	NULL),
(9,	1,	1,	6,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-10-21 16:49:14',	'2025-10-21 16:49:14',	NULL),
(11,	1,	7,	2,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-12-03 17:25:05',	'2025-12-03 17:25:05',	NULL),
(12,	1,	8,	2,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-12-03 18:18:52',	'2025-12-03 18:18:52',	NULL),
(13,	1,	9,	2,	1,	0,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-12-11 12:37:35',	'2025-12-11 12:37:35',	NULL),
(14,	1,	10,	2,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-12-15 16:16:08',	'2025-12-15 16:18:23',	NULL),
(34,	4,	1,	2,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-09-01 13:20:08',	'2025-12-16 00:33:21',	NULL),
(35,	4,	2,	2,	1,	1,	1,	1,	1,	1,	0,	0,	0,	0,	0,	1,	1,	'2025-09-01 13:20:28',	'2025-12-10 19:38:01',	NULL),
(36,	4,	1,	4,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-09-02 12:36:23',	'2025-09-02 12:36:23',	NULL),
(37,	4,	3,	2,	1,	1,	1,	1,	1,	1,	1,	1,	1,	11,	1,	0,	1,	'2025-09-03 06:00:18',	'2025-10-26 17:55:36',	NULL),
(38,	4,	4,	2,	1,	0,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-09-16 11:07:25',	'2025-10-26 17:47:44',	NULL),
(39,	4,	5,	2,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-09-16 11:07:33',	'2025-09-16 11:07:33',	NULL),
(40,	4,	6,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-09-16 11:07:40',	'2025-12-16 02:58:33',	NULL),
(41,	4,	3,	6,	1,	1,	1,	1,	1,	1,	1,	1,	0,	0,	0,	0,	1,	'2025-10-21 16:48:46',	'2025-10-21 16:48:46',	NULL),
(42,	4,	1,	6,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-10-21 16:49:14',	'2025-10-21 16:49:14',	NULL),
(43,	4,	7,	2,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-12-03 17:25:05',	'2025-12-03 17:25:05',	NULL),
(44,	4,	8,	2,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-12-03 18:18:52',	'2025-12-03 18:18:52',	NULL),
(45,	4,	9,	2,	1,	0,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-12-11 12:37:35',	'2025-12-11 12:37:35',	NULL),
(46,	4,	10,	2,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2025-12-15 16:16:08',	'2025-12-15 16:18:23',	NULL),
(49,	12,	1,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-09 06:26:39',	'2026-02-09 07:55:50',	NULL),
(50,	12,	2,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-09 06:26:39',	'2026-02-09 07:55:50',	NULL),
(51,	12,	3,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-09 06:26:39',	'2026-02-09 07:55:50',	NULL),
(52,	12,	4,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-09 06:26:39',	'2026-02-09 07:55:50',	NULL),
(53,	12,	5,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-09 06:26:39',	'2026-02-09 07:55:50',	NULL),
(54,	12,	6,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-09 06:26:39',	'2026-02-09 07:55:50',	NULL),
(55,	12,	7,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-09 06:26:39',	'2026-02-09 07:55:50',	NULL),
(56,	12,	9,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-09 06:26:39',	'2026-02-09 07:55:50',	NULL),
(57,	12,	10,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-09 06:26:39',	'2026-02-09 07:55:50',	NULL),
(58,	12,	1,	4,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	19,	'2026-02-09 06:31:01',	'2026-02-09 07:55:50',	NULL),
(59,	13,	1,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(60,	13,	2,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(61,	13,	3,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(62,	13,	4,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(63,	13,	5,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(64,	13,	6,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(65,	13,	7,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(66,	13,	8,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(67,	13,	9,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL),
(68,	13,	10,	2,	1,	1,	1,	1,	0,	0,	0,	0,	0,	0,	0,	0,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL);

DROP TABLE IF EXISTS `merks`;
CREATE TABLE `merks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `merk_name` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merks_merk_name_unique` (`merk_name`),
  KEY `merks_outlet_id_foreign` (`outlet_id`),
  KEY `merks_user_id_foreign` (`user_id`),
  KEY `merks_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  CONSTRAINT `merks_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `merks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `merks` (`id`, `outlet_id`, `merk_name`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	'-',	1,	'2026-01-21 23:17:04',	'2026-01-21 23:17:04',	NULL);

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1,	'0001_01_01_000000_create_users_table',	1),
(2,	'0001_01_01_000001_create_cache_table',	1),
(3,	'0001_01_01_000002_create_jobs_table',	1),
(4,	'0001_01_01_000003_create_table_outlets',	1),
(5,	'2025_08_02_082632_create_satuans_table',	1),
(6,	'2025_08_04_080055_create_kelompok_products_table',	1),
(7,	'2025_08_04_081559_create_products_table',	1),
(8,	'2025_08_04_093420_create_suppliers_table',	1),
(9,	'2025_08_06_070003_create_user_groups_table',	1),
(10,	'2025_08_06_070841_add_user_group_id_table_users',	1),
(11,	'2025_08_06_071151_create_master_data_groups_table',	1),
(12,	'2025_08_06_071459_create_master_data_table',	1),
(13,	'2025_08_06_071711_create_master_data_accesses',	1),
(14,	'2025_08_07_034047_add_user_id_to_users',	1),
(15,	'2025_08_07_081457_add_user_id_to_master_data_accesses_table',	1),
(16,	'2025_08_11_075306_add_master_datalink_to_master_datas',	1),
(17,	'2025_08_13_040827_create_product_stocks_table',	1),
(18,	'2025_08_13_051232_add_hargagrosir_hargajual1_hargajual2_hargajual3_in_products',	1),
(19,	'2025_08_13_051756_create_product_stock_histories_table',	1),
(20,	'2025_08_13_070253_create_merks_table',	1),
(21,	'2025_08_13_071819_add_product_slug_and_merk_id_to_products',	1),
(22,	'2025_08_18_025830_add_product_stock_sn_and_product_stock_ed_to_product_stocks',	1),
(23,	'2025_08_18_041147_add_stock_awal_to_product_stock_histories',	1),
(24,	'2025_08_18_044326_change_field_qty_awal_and_qty_akhir_to_product_stock_histories',	1),
(25,	'2025_08_18_083659_create_penerimaan_barangs_table',	1),
(26,	'2025_08_18_084732_create_customers_table',	1),
(27,	'2025_08_18_085308_create_penerimaan_barang_details',	1),
(28,	'2025_08_20_030110_add_supplier_phone1_and_supplier_phone2_to_suppliers',	1),
(29,	'2025_08_20_031949_add_customer_picname1_and_customer_picphone1_to_customers',	1),
(30,	'2025_08_20_090543_add_penerimaan_barang_discounttype_to_penerimaan_barangs',	1),
(31,	'2025_08_20_090928_add_penerimaan_barang_detail_discounttype_to_penerimaan_barang_details',	1),
(32,	'2025_08_28_060404_add_validated_by_and_validated_at_and_status_in_penerimaanbarangs',	1),
(33,	'2025_09_02_025642_create_menus_table',	1),
(34,	'2025_09_02_030159_create_menu_accesses_table',	1),
(35,	'2025_09_02_041555_add_can_unvalidate_in_menu_accesses',	1),
(36,	'2025_09_02_082336_create_marketings_tables',	1),
(37,	'2025_09_02_082454_create_marketing_teams_tables',	1),
(38,	'2025_09_02_083913_create_penjualan_barangs_table',	1),
(39,	'2025_09_02_083950_create_penjualan_barang_details',	1),
(40,	'2025_09_02_091740_create_sales_orders_table',	1),
(41,	'2025_09_02_091841_create_sales_order_details_table',	1),
(42,	'2025_09_03_025528_create_customer_marketings_table',	1),
(43,	'2025_09_03_033458_add_sales_order_detail_id_in_penjualan_barang_details',	1),
(44,	'2025_09_09_024502_create_document_numberings_table',	1),
(45,	'2025_09_16_080902_add_print1_and_print2_in_menu_accesses',	1),
(46,	'2025_09_17_083416_create_table_payment_penjualans',	1),
(47,	'2025_09_18_041352_add_penjualan_barang_tanggaljth_in_penjualan_barangs',	1),
(48,	'2025_09_30_033007_add_penjualan_barang_jumlahpayment_in_penjualan_barangs',	1),
(49,	'2025_10_08_115140_create_system_dashboards_table',	1),
(50,	'2025_10_08_131928_create_dashboard_accesses_table',	1),
(51,	'2025_10_23_141548_add_ppn_ongkir_menu_accesses',	1),
(52,	'2025_10_28_095726_add_customer_harga_in_customers',	1),
(53,	'2025_10_30_124108_create_table_laporan',	1),
(54,	'2025_10_30_130042_create_laporan_accesses_table',	1),
(55,	'2025_10_31_082501_add_canhargapembelian_in_menu_accesses_table',	1),
(56,	'2025_11_25_131254_create_table_customer_products',	1),
(57,	'2025_11_25_135135_create_user_outlets_table',	1),
(58,	'2025_12_05_162939_create_supplier_products_table',	1),
(59,	'2025_12_09_102303_add_harga_beli_in_supplier_product',	1),
(60,	'2025_12_09_141548_add_harga_jual_in_customer_product',	1),
(61,	'2025_12_11_210141_create_kas_harians_table',	1),
(62,	'2025_12_11_210225_create_kas_pemasukkans_table',	1),
(63,	'2025_12_11_210320_create_kategori_pengeluarans_table',	1),
(64,	'2025_12_11_210321_create_payment_penerimaans_table',	1),
(65,	'2025_12_11_210328_create_kas_pengeluarans_table',	1),
(66,	'2025_12_11_224019_add_penerimaan_barang_jumlahpayment_in_penerimaan_barangs',	1),
(67,	'2026_01_14_141119_create_text_queues_table',	1),
(68,	'2026_02_04_134506_add_phone_in_users_table',	2),
(69,	'2026_02_06_155925_add_role_in_user_outlets_table',	3);

DROP TABLE IF EXISTS `outlets`;
CREATE TABLE `outlets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_name` varchar(150) NOT NULL,
  `outlet_address` varchar(150) NOT NULL,
  `owner_user_id` bigint(20) unsigned NOT NULL,
  `outlet_logo` varchar(100) DEFAULT NULL,
  `outlet_hp` varchar(20) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `outlets_owner_user_id_foreign` (`owner_user_id`),
  KEY `outlets_user_id_foreign` (`user_id`),
  KEY `outlets_outlet_name_index` (`outlet_name`),
  CONSTRAINT `outlets_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `outlets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `outlets` (`id`, `outlet_name`, `outlet_address`, `owner_user_id`, `outlet_logo`, `outlet_hp`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	'Outlet 1',	'Jl. Raya Oytlet 1',	1,	NULL,	NULL,	2,	'2026-01-21 20:22:36',	'2026-01-21 20:22:36',	NULL),
(3,	'Outlet 2',	'Jl. Raya Oytlet 2',	2,	NULL,	NULL,	2,	'2026-01-21 20:22:36',	'2026-01-21 20:22:36',	NULL),
(4,	'Mas Putra Motor',	'-',	10,	'outlet-logos/01KDSP5AB9CY2JGGN9MKBK5XQV.png',	NULL,	1,	'2026-01-28 12:26:58',	'2026-01-28 12:26:58',	NULL),
(12,	'halloodffd',	'-',	19,	'outlet-logos/01KDSP5AB9CY2JGGN9MKBK5XQV.png',	NULL,	19,	'2026-02-09 06:26:39',	'2026-02-09 08:34:21',	NULL),
(13,	'cabang ke 6',	'-',	19,	NULL,	NULL,	1,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL);

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `payment_penerimaans`;
CREATE TABLE `payment_penerimaans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `penerimaan_barang_id` bigint(20) unsigned NOT NULL,
  `payment_penerimaan_tanggal` date NOT NULL,
  `payment_penerimaan_metode` enum('cash','transfer','debit/EDC','qris','ewallet','giro/cek') NOT NULL DEFAULT 'cash',
  `payment_penerimaan_status` enum('Belum Lunas','Lunas','Tidak Terbayar') NOT NULL DEFAULT 'Lunas',
  `payment_penerimaan_jumlah` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_penerimaan_grandtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_penerimaan_bankname` varchar(100) DEFAULT NULL,
  `payment_penerimaan_accountnumber` varchar(50) DEFAULT NULL,
  `payment_penerimaan_approvalcode` varchar(50) DEFAULT NULL COMMENT 'EDC/Transfer/Debit',
  `payment_penerimaan_referenceid` varchar(50) DEFAULT NULL COMMENT 'QRIS/E-Wallet',
  `payment_penerimaan_checkquenumber` varchar(50) DEFAULT NULL COMMENT 'GIRO/CEK',
  `payment_penerimaan_jatuhtempo` date DEFAULT NULL COMMENT 'GIRO/CEK',
  `payment_penerimaan_notes` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_penerimaans_penerimaan_barang_id_foreign` (`penerimaan_barang_id`),
  KEY `payment_penerimaans_user_id_foreign` (`user_id`),
  KEY `payment_penerimaans_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `payment_penerimaans_payment_penerimaan_tanggal_index` (`payment_penerimaan_tanggal`),
  KEY `payment_penerimaans_payment_penerimaan_metode_index` (`payment_penerimaan_metode`),
  KEY `payment_penerimaans_payment_penerimaan_status_index` (`payment_penerimaan_status`),
  KEY `payment_penerimaans_payment_penerimaan_jumlah_index` (`payment_penerimaan_jumlah`),
  KEY `payment_penerimaans_payment_penerimaan_grandtotal_index` (`payment_penerimaan_grandtotal`),
  KEY `payment_penerimaans_payment_penerimaan_bankname_index` (`payment_penerimaan_bankname`),
  CONSTRAINT `payment_penerimaans_penerimaan_barang_id_foreign` FOREIGN KEY (`penerimaan_barang_id`) REFERENCES `penerimaan_barangs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_penerimaans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_penerimaans` (`id`, `penerimaan_barang_id`, `payment_penerimaan_tanggal`, `payment_penerimaan_metode`, `payment_penerimaan_status`, `payment_penerimaan_jumlah`, `payment_penerimaan_grandtotal`, `payment_penerimaan_bankname`, `payment_penerimaan_accountnumber`, `payment_penerimaan_approvalcode`, `payment_penerimaan_referenceid`, `payment_penerimaan_checkquenumber`, `payment_penerimaan_jatuhtempo`, `payment_penerimaan_notes`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	'2026-01-28',	'cash',	'Lunas',	315000.00,	315000.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	10,	'2026-01-28 13:35:36',	'2026-01-28 13:35:36',	NULL);

DROP TABLE IF EXISTS `payment_penjualans`;
CREATE TABLE `payment_penjualans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `penjualan_barang_id` bigint(20) unsigned NOT NULL,
  `payment_penjualan_tanggal` date NOT NULL,
  `payment_penjualan_metode` enum('cash','transfer','debit/EDC','qris','ewallet','giro/cek') NOT NULL DEFAULT 'cash',
  `payment_penjualan_status` enum('Belum Lunas','Lunas','Tidak Terbayar') NOT NULL DEFAULT 'Lunas',
  `payment_penjualan_jumlah` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_penjualan_grandtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_penjualan_bankname` varchar(100) DEFAULT NULL,
  `payment_penjualan_accountnumber` varchar(50) DEFAULT NULL,
  `payment_penjualan_approvalcode` varchar(50) DEFAULT NULL COMMENT 'EDC/Transfer/Debit',
  `payment_penjualan_referenceid` varchar(50) DEFAULT NULL COMMENT 'QRIS/E-Wallet',
  `payment_penjualan_checkquenumber` varchar(50) DEFAULT NULL COMMENT 'GIRO/CEK',
  `payment_penjualan_jatuhtempo` date DEFAULT NULL COMMENT 'GIRO/CEK',
  `payment_penjualan_notes` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_penjualans_penjualan_barang_id_foreign` (`penjualan_barang_id`),
  KEY `payment_penjualans_user_id_foreign` (`user_id`),
  KEY `payment_penjualans_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `payment_penjualans_payment_penjualan_tanggal_index` (`payment_penjualan_tanggal`),
  KEY `payment_penjualans_payment_penjualan_metode_index` (`payment_penjualan_metode`),
  KEY `payment_penjualans_payment_penjualan_status_index` (`payment_penjualan_status`),
  KEY `payment_penjualans_payment_penjualan_jumlah_index` (`payment_penjualan_jumlah`),
  KEY `payment_penjualans_payment_penjualan_grandtotal_index` (`payment_penjualan_grandtotal`),
  KEY `payment_penjualans_payment_penjualan_bankname_index` (`payment_penjualan_bankname`),
  CONSTRAINT `payment_penjualans_penjualan_barang_id_foreign` FOREIGN KEY (`penjualan_barang_id`) REFERENCES `penjualan_barangs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_penjualans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_penjualans` (`id`, `penjualan_barang_id`, `payment_penjualan_tanggal`, `payment_penjualan_metode`, `payment_penjualan_status`, `payment_penjualan_jumlah`, `payment_penjualan_grandtotal`, `payment_penjualan_bankname`, `payment_penjualan_accountnumber`, `payment_penjualan_approvalcode`, `payment_penjualan_referenceid`, `payment_penjualan_checkquenumber`, `payment_penjualan_jatuhtempo`, `payment_penjualan_notes`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	9,	'2026-01-28',	'cash',	'Lunas',	280275.00,	280275.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	10,	'2026-01-28 13:32:22',	'2026-01-28 13:32:22',	NULL),
(2,	10,	'2026-01-28',	'qris',	'Lunas',	124875.00,	124875.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	10,	'2026-01-28 13:39:41',	'2026-01-28 13:39:41',	NULL);

DROP TABLE IF EXISTS `penerimaan_barangs`;
CREATE TABLE `penerimaan_barangs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `penerimaan_barang_tanggal` datetime NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `penerimaan_barang_invoicenumber` varchar(255) NOT NULL,
  `penerimaan_barang_total` decimal(10,2) NOT NULL,
  `penerimaan_barang_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `penerimaan_barang_ppn` decimal(10,2) NOT NULL DEFAULT 0.00,
  `penerimaan_barang_grandtotal` decimal(10,2) NOT NULL,
  `penerimaan_barang_jumlahpayment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `penerimaan_barang_validatedby` int(11) NOT NULL DEFAULT 0,
  `penerimaan_barang_validatedat` datetime DEFAULT NULL,
  `penerimaan_barang_status` enum('pending','validated','belum lunas','lunas') NOT NULL DEFAULT 'pending',
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `penerimaan_barang_discounttype` enum('percent','rupiah') NOT NULL DEFAULT 'percent',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_invoice_outlet` (`penerimaan_barang_invoicenumber`,`outlet_id`),
  KEY `penerimaan_barangs_outlet_id_foreign` (`outlet_id`),
  KEY `penerimaan_barangs_supplier_id_foreign` (`supplier_id`),
  KEY `penerimaan_barangs_user_id_foreign` (`user_id`),
  KEY `penerimaan_barangs_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `penerimaan_barangs_penerimaan_barang_tanggal_index` (`penerimaan_barang_tanggal`),
  KEY `penerimaan_barangs_penerimaan_barang_total_index` (`penerimaan_barang_total`),
  KEY `penerimaan_barangs_penerimaan_barang_grandtotal_index` (`penerimaan_barang_grandtotal`),
  KEY `penerimaan_barangs_penerimaan_barang_validatedat_index` (`penerimaan_barang_validatedat`),
  KEY `penerimaan_barangs_penerimaan_barang_status_index` (`penerimaan_barang_status`),
  KEY `penerimaan_barangs_penerimaan_barang_jumlahpayment_index` (`penerimaan_barang_jumlahpayment`),
  CONSTRAINT `penerimaan_barangs_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penerimaan_barangs_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penerimaan_barangs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `penerimaan_barangs` (`id`, `outlet_id`, `penerimaan_barang_tanggal`, `supplier_id`, `penerimaan_barang_invoicenumber`, `penerimaan_barang_total`, `penerimaan_barang_discount`, `penerimaan_barang_ppn`, `penerimaan_barang_grandtotal`, `penerimaan_barang_jumlahpayment`, `notes`, `penerimaan_barang_validatedby`, `penerimaan_barang_validatedat`, `penerimaan_barang_status`, `user_id`, `created_at`, `updated_at`, `deleted_at`, `penerimaan_barang_discounttype`) VALUES
(1,	4,	'2026-01-28 00:00:00',	2,	'TEST 01',	315000.00,	0.00,	0.00,	315000.00,	315000.00,	NULL,	10,	'2026-01-28 20:04:22',	'lunas',	10,	'2026-01-28 12:54:06',	'2026-01-28 13:35:36',	NULL,	'percent');

DROP TABLE IF EXISTS `penerimaan_barang_details`;
CREATE TABLE `penerimaan_barang_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `penerimaan_barang_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `penerimaan_barang_detailproduct_name` varchar(255) NOT NULL,
  `penerimaan_barang_detail_sn` varchar(255) DEFAULT NULL,
  `penerimaan_barang_detail_ed` date DEFAULT NULL,
  `penerimaan_barang_detail_qty` int(11) NOT NULL DEFAULT 0,
  `penerimaan_barang_detail_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `penerimaan_barang_detail_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `penerimaan_barang_detail_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `penerimaan_barang_detail_discounttype` enum('percent','rupiah') NOT NULL DEFAULT 'percent',
  PRIMARY KEY (`id`),
  KEY `penerimaan_barang_details_penerimaan_barang_id_foreign` (`penerimaan_barang_id`),
  KEY `penerimaan_barang_details_product_id_foreign` (`product_id`),
  KEY `penerimaan_barang_details_user_id_foreign` (`user_id`),
  KEY `penerimaan_barang_details_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `product_name` (`penerimaan_barang_detailproduct_name`),
  KEY `penerimaan_barang_details_penerimaan_barang_detail_sn_index` (`penerimaan_barang_detail_sn`),
  KEY `penerimaan_barang_details_penerimaan_barang_detail_ed_index` (`penerimaan_barang_detail_ed`),
  KEY `penerimaan_barang_details_penerimaan_barang_detail_qty_index` (`penerimaan_barang_detail_qty`),
  CONSTRAINT `penerimaan_barang_details_penerimaan_barang_id_foreign` FOREIGN KEY (`penerimaan_barang_id`) REFERENCES `penerimaan_barangs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penerimaan_barang_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penerimaan_barang_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `penerimaan_barang_details` (`id`, `penerimaan_barang_id`, `product_id`, `penerimaan_barang_detailproduct_name`, `penerimaan_barang_detail_sn`, `penerimaan_barang_detail_ed`, `penerimaan_barang_detail_qty`, `penerimaan_barang_detail_price`, `penerimaan_barang_detail_discount`, `penerimaan_barang_detail_total`, `user_id`, `created_at`, `updated_at`, `deleted_at`, `penerimaan_barang_detail_discounttype`) VALUES
(1,	1,	3,	'Kunci Kontak Grand',	'',	NULL,	10,	35000.00,	10.00,	315000.00,	10,	'2026-01-28 12:54:06',	'2026-01-28 12:54:06',	NULL,	'percent');

DROP TABLE IF EXISTS `penjualan_barangs`;
CREATE TABLE `penjualan_barangs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `penjualan_barang_tanggal` datetime NOT NULL,
  `penjualan_barang_tanggaljth` date DEFAULT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `penjualan_barang_no` varchar(255) NOT NULL,
  `penjualan_barang_total` decimal(10,2) NOT NULL,
  `penjualan_barang_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `penjualan_barang_ongkir` decimal(10,2) NOT NULL DEFAULT 0.00,
  `penjualan_barang_discounttype` enum('percent','rupiah') NOT NULL DEFAULT 'percent',
  `penjualan_barang_ppn` decimal(10,2) NOT NULL DEFAULT 0.00,
  `penjualan_barang_grandtotal` decimal(10,2) NOT NULL,
  `penjualan_barang_jumlahpayment` decimal(8,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `penjualan_barang_validatedby` int(11) NOT NULL DEFAULT 0,
  `penjualan_barang_validatedat` datetime DEFAULT NULL,
  `penjualan_barang_status` enum('pending','validated','belum lunas','lunas') NOT NULL DEFAULT 'pending',
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `penjualan_barangs_penjualan_barang_no_unique` (`penjualan_barang_no`),
  KEY `penjualan_barangs_outlet_id_foreign` (`outlet_id`),
  KEY `penjualan_barangs_customer_id_foreign` (`customer_id`),
  KEY `penjualan_barangs_user_id_foreign` (`user_id`),
  KEY `penjualan_barangs_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `penjualan_barangs_penjualan_barang_tanggal_index` (`penjualan_barang_tanggal`),
  KEY `penjualan_barangs_penjualan_barang_total_index` (`penjualan_barang_total`),
  KEY `penjualan_barangs_penjualan_barang_grandtotal_index` (`penjualan_barang_grandtotal`),
  KEY `penjualan_barangs_penjualan_barang_validatedat_index` (`penjualan_barang_validatedat`),
  KEY `penjualan_barangs_penjualan_barang_status_index` (`penjualan_barang_status`),
  KEY `penjualan_barangs_penjualan_barang_tanggaljth_index` (`penjualan_barang_tanggaljth`),
  KEY `penjualan_barangs_penjualan_barang_jumlahpayment_index` (`penjualan_barang_jumlahpayment`),
  CONSTRAINT `penjualan_barangs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penjualan_barangs_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penjualan_barangs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `penjualan_barangs` (`id`, `outlet_id`, `penjualan_barang_tanggal`, `penjualan_barang_tanggaljth`, `customer_id`, `penjualan_barang_no`, `penjualan_barang_total`, `penjualan_barang_discount`, `penjualan_barang_ongkir`, `penjualan_barang_discounttype`, `penjualan_barang_ppn`, `penjualan_barang_grandtotal`, `penjualan_barang_jumlahpayment`, `notes`, `penjualan_barang_validatedby`, `penjualan_barang_validatedat`, `penjualan_barang_status`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3,	1,	'2026-01-22 00:00:00',	'2026-02-22',	1,	'SJ - 2026010001',	130000.00,	0.00,	0.00,	'percent',	11.00,	144300.00,	0.00,	NULL,	1,	'2026-01-22 14:37:35',	'validated',	1,	'2026-01-21 23:25:49',	'2026-01-22 00:37:35',	NULL),
(5,	1,	'2026-01-22 00:00:00',	'2026-02-22',	2,	'SJ - 2026010002',	100000.00,	0.00,	0.00,	'percent',	11.00,	111000.00,	0.00,	NULL,	0,	NULL,	'pending',	1,	'2026-01-21 23:29:10',	'2026-01-21 23:29:10',	NULL),
(6,	1,	'2026-01-22 00:00:00',	'2026-02-22',	1,	'SJ - 2026010003',	25000.00,	0.00,	0.00,	'percent',	11.00,	27750.00,	0.00,	NULL,	0,	NULL,	'pending',	1,	'2026-01-21 23:38:48',	'2026-01-21 23:38:48',	NULL),
(7,	1,	'2026-01-22 00:00:00',	'2026-02-22',	2,	'SJ - 2026010004',	60000.00,	0.00,	0.00,	'percent',	11.00,	66600.00,	0.00,	NULL,	0,	NULL,	'pending',	1,	'2026-01-21 23:48:58',	'2026-01-21 23:48:58',	NULL),
(8,	1,	'2026-01-22 00:00:00',	'2026-02-22',	1,	'SJ - 2026010005',	40000.00,	0.00,	0.00,	'percent',	11.00,	44400.00,	0.00,	NULL,	1,	'2026-01-22 14:19:12',	'validated',	1,	'2026-01-22 00:06:46',	'2026-01-22 00:19:12',	NULL),
(9,	4,	'2026-01-28 00:00:00',	'2026-02-28',	3,	'MPM - 2026010006',	252500.00,	0.00,	0.00,	'percent',	11.00,	280275.00,	280275.00,	NULL,	10,	'2026-01-28 20:31:16',	'lunas',	10,	'2026-01-28 13:31:05',	'2026-01-28 13:32:22',	NULL),
(10,	4,	'2026-01-28 00:00:00',	'2026-02-28',	3,	'MPM - 2026010007',	112500.00,	0.00,	0.00,	'percent',	11.00,	124875.00,	124875.00,	NULL,	10,	'2026-01-28 20:39:03',	'lunas',	10,	'2026-01-28 13:38:28',	'2026-01-28 13:39:41',	NULL),
(11,	4,	'2026-02-04 00:00:00',	'2026-03-04',	3,	'MPM - 2026020001',	40000.00,	0.00,	0.00,	'percent',	11.00,	44400.00,	0.00,	NULL,	1,	'2026-02-04 16:47:15',	'validated',	1,	'2026-02-04 08:35:38',	'2026-02-04 09:47:15',	NULL),
(13,	12,	'2026-02-04 00:00:00',	'2026-03-04',	3,	'ds - 2026020001',	40000.00,	0.00,	0.00,	'percent',	11.00,	44400.00,	0.00,	NULL,	1,	'2026-02-04 16:47:15',	'validated',	1,	'2026-02-04 08:35:38',	'2026-02-04 09:47:15',	NULL),
(14,	13,	'2026-02-04 00:00:00',	'2026-03-04',	3,	'fg - 2026020001',	40000.00,	0.00,	0.00,	'percent',	11.00,	44400.00,	0.00,	NULL,	1,	'2026-02-04 16:47:15',	'validated',	1,	'2026-02-04 08:35:38',	'2026-02-04 09:47:15',	NULL),
(15,	12,	'2026-03-04 00:00:00',	'2026-04-04',	3,	'ds - 2026020002',	40000.00,	0.00,	0.00,	'percent',	11.00,	44400.00,	0.00,	NULL,	1,	'2026-02-04 16:47:15',	'validated',	1,	'2026-02-04 08:35:38',	'2026-02-04 09:47:15',	NULL),
(16,	13,	'2026-01-04 00:00:00',	'2026-02-04',	3,	'fg - 2026020002',	40000.00,	0.00,	0.00,	'percent',	11.00,	44400.00,	0.00,	NULL,	1,	'2026-02-04 16:47:15',	'validated',	1,	'2026-02-04 08:35:38',	'2026-02-04 09:47:15',	NULL);

DROP TABLE IF EXISTS `penjualan_barang_details`;
CREATE TABLE `penjualan_barang_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `penjualan_barang_id` bigint(20) unsigned NOT NULL,
  `product_stock_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `penjualan_barang_detailproduct_name` varchar(255) NOT NULL,
  `penjualan_barang_detail_sn` varchar(255) DEFAULT NULL,
  `penjualan_barang_detail_ed` date DEFAULT NULL,
  `penjualan_barang_detail_qty` int(11) NOT NULL DEFAULT 0,
  `penjualan_barang_detail_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `penjualan_barang_detail_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `penjualan_barang_detail_discounttype` enum('percent','rupiah') NOT NULL DEFAULT 'percent',
  `penjualan_barang_detail_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sales_order_detail_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `penjualan_barang_details_penjualan_barang_id_foreign` (`penjualan_barang_id`),
  KEY `penjualan_barang_details_product_stock_id_foreign` (`product_stock_id`),
  KEY `penjualan_barang_details_product_id_foreign` (`product_id`),
  KEY `penjualan_barang_details_user_id_foreign` (`user_id`),
  KEY `penjualan_barang_details_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `penjualan_product_name` (`penjualan_barang_detailproduct_name`),
  KEY `penjualan_barang_details_penjualan_barang_detail_sn_index` (`penjualan_barang_detail_sn`),
  KEY `penjualan_barang_details_penjualan_barang_detail_ed_index` (`penjualan_barang_detail_ed`),
  KEY `penjualan_barang_details_penjualan_barang_detail_qty_index` (`penjualan_barang_detail_qty`),
  KEY `penjualan_barang_details_penjualan_barang_detail_price_index` (`penjualan_barang_detail_price`),
  KEY `penjualan_barang_details_sales_order_detail_id_index` (`sales_order_detail_id`),
  CONSTRAINT `penjualan_barang_details_penjualan_barang_id_foreign` FOREIGN KEY (`penjualan_barang_id`) REFERENCES `penjualan_barangs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penjualan_barang_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penjualan_barang_details_product_stock_id_foreign` FOREIGN KEY (`product_stock_id`) REFERENCES `product_stocks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penjualan_barang_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `penjualan_barang_details` (`id`, `penjualan_barang_id`, `product_stock_id`, `product_id`, `penjualan_barang_detailproduct_name`, `penjualan_barang_detail_sn`, `penjualan_barang_detail_ed`, `penjualan_barang_detail_qty`, `penjualan_barang_detail_price`, `penjualan_barang_detail_discount`, `penjualan_barang_detail_discounttype`, `penjualan_barang_detail_total`, `user_id`, `created_at`, `updated_at`, `deleted_at`, `sales_order_detail_id`) VALUES
(2,	3,	1,	1,	'test otomatis',	NULL,	NULL,	2,	25000.00,	0.00,	'percent',	50000.00,	1,	'2026-01-21 23:25:49',	'2026-01-21 23:25:49',	NULL,	NULL),
(3,	3,	2,	2,	'test product outlet 1',	NULL,	NULL,	2,	40000.00,	0.00,	'percent',	80000.00,	1,	'2026-01-21 23:25:49',	'2026-01-21 23:25:49',	NULL,	NULL),
(4,	5,	1,	1,	'test otomatis',	NULL,	NULL,	2,	20000.00,	0.00,	'percent',	40000.00,	1,	'2026-01-21 23:29:10',	'2026-01-21 23:29:10',	NULL,	NULL),
(5,	5,	2,	2,	'test product outlet 1',	NULL,	NULL,	2,	30000.00,	0.00,	'percent',	60000.00,	1,	'2026-01-21 23:29:10',	'2026-01-21 23:29:10',	NULL,	NULL),
(6,	6,	1,	1,	'test otomatis',	NULL,	NULL,	1,	25000.00,	0.00,	'percent',	25000.00,	1,	'2026-01-21 23:38:48',	'2026-01-21 23:38:48',	NULL,	NULL),
(7,	7,	2,	2,	'test product outlet 1',	NULL,	NULL,	2,	30000.00,	0.00,	'percent',	60000.00,	1,	'2026-01-21 23:48:58',	'2026-01-21 23:48:58',	NULL,	NULL),
(8,	8,	2,	2,	'test product outlet 1',	NULL,	NULL,	1,	40000.00,	0.00,	'percent',	40000.00,	1,	'2026-01-22 00:06:46',	'2026-01-22 00:06:46',	NULL,	NULL),
(9,	9,	5,	5,	'Busi Grand',	NULL,	NULL,	5,	10000.00,	0.00,	'percent',	50000.00,	10,	'2026-01-28 13:31:05',	'2026-01-28 13:31:05',	NULL,	NULL),
(10,	9,	3,	3,	'Kunci Kontak Grand',	NULL,	NULL,	1,	40000.00,	0.00,	'percent',	40000.00,	10,	'2026-01-28 13:31:05',	'2026-01-28 13:31:05',	NULL,	NULL),
(11,	9,	4,	4,	'Kunci Kontak Supra',	NULL,	NULL,	5,	32500.00,	0.00,	'percent',	162500.00,	10,	'2026-01-28 13:31:05',	'2026-01-28 13:31:05',	NULL,	NULL),
(12,	10,	3,	3,	'Kunci Kontak Grand',	NULL,	NULL,	2,	40000.00,	0.00,	'percent',	80000.00,	10,	'2026-01-28 13:38:28',	'2026-01-28 13:38:28',	NULL,	NULL),
(13,	10,	4,	4,	'Kunci Kontak Supra',	NULL,	NULL,	1,	32500.00,	0.00,	'percent',	32500.00,	10,	'2026-01-28 13:38:28',	'2026-01-28 13:38:28',	NULL,	NULL),
(14,	11,	3,	3,	'Kunci Kontak Grand',	NULL,	NULL,	1,	40000.00,	0.00,	'percent',	40000.00,	1,	'2026-02-04 08:35:38',	'2026-02-04 08:35:38',	NULL,	NULL);

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `product_kode` varchar(20) DEFAULT NULL,
  `product_catalog` varchar(100) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_slug` varchar(255) NOT NULL,
  `product_minstock` int(11) NOT NULL DEFAULT 0,
  `satuan_id` bigint(20) unsigned NOT NULL,
  `hargajualgrosir` decimal(15,2) NOT NULL DEFAULT 0.00,
  `hargajual1` decimal(15,2) NOT NULL DEFAULT 0.00,
  `hargajual2` decimal(15,2) NOT NULL DEFAULT 0.00,
  `hargajual3` decimal(15,2) NOT NULL DEFAULT 0.00,
  `kelompok_product_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `merk_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product` (`product_catalog`,`satuan_id`),
  UNIQUE KEY `products_product_slug_unique` (`product_slug`),
  KEY `products_outlet_id_foreign` (`outlet_id`),
  KEY `products_satuan_id_foreign` (`satuan_id`),
  KEY `products_kelompok_product_id_foreign` (`kelompok_product_id`),
  KEY `products_user_id_foreign` (`user_id`),
  KEY `products_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `products_product_kode_index` (`product_kode`),
  KEY `products_product_catalog_index` (`product_catalog`),
  KEY `products_product_name_index` (`product_name`),
  KEY `products_product_minstock_index` (`product_minstock`),
  KEY `products_merk_id_foreign` (`merk_id`),
  CONSTRAINT `products_kelompok_product_id_foreign` FOREIGN KEY (`kelompok_product_id`) REFERENCES `kelompok_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_merk_id_foreign` FOREIGN KEY (`merk_id`) REFERENCES `merks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `satuans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `outlet_id`, `product_kode`, `product_catalog`, `product_name`, `product_slug`, `product_minstock`, `satuan_id`, `hargajualgrosir`, `hargajual1`, `hargajual2`, `hargajual3`, `kelompok_product_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`, `merk_id`) VALUES
(1,	1,	NULL,	'PRD-DPY7_-',	'test otomatis',	'test-otomatis-1',	0,	1,	5000.00,	20000.00,	25000.00,	30000.00,	1,	1,	'2026-01-21 23:18:26',	'2026-01-21 23:18:26',	NULL,	1),
(2,	1,	NULL,	'PRD-51N3_-',	'test product outlet 1',	'test-product-outlet-1-1',	0,	1,	20000.00,	30000.00,	40000.00,	45000.00,	1,	1,	'2026-01-21 23:18:50',	'2026-01-21 23:18:50',	NULL,	1),
(3,	4,	'KKS-GRA',	'PRD-EKMW_-',	'Kunci Kontak Grand',	'kunci-kontak-grand-3',	5,	3,	40000.00,	42500.00,	0.00,	0.00,	2,	10,	'2026-01-28 12:43:36',	'2026-01-28 12:43:36',	NULL,	1),
(4,	4,	'KKS-SUP',	'PRD-4WQV_-',	'Kunci Kontak Supra',	'kunci-kontak-supra-3',	5,	3,	32500.00,	35000.00,	0.00,	0.00,	2,	10,	'2026-01-28 12:44:55',	'2026-01-28 12:45:12',	NULL,	1),
(5,	4,	'BUS-GRA',	'PRD-SSM7_-',	'Busi Grand',	'busi-grand-3',	5,	3,	10000.00,	15000.00,	0.00,	0.00,	3,	10,	'2026-01-28 12:45:50',	'2026-01-28 12:45:50',	NULL,	1);

DROP TABLE IF EXISTS `product_stocks`;
CREATE TABLE `product_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_stock_sn` varchar(255) DEFAULT NULL,
  `product_stock_ed` date DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_stocks_product_id_foreign` (`product_id`),
  KEY `product_stocks_user_id_foreign` (`user_id`),
  KEY `product_stocks_deleted_at_created_at_stock_index` (`deleted_at`,`created_at`,`stock`),
  KEY `product_stocks_product_stock_sn_index` (`product_stock_sn`),
  KEY `product_stocks_product_stock_ed_index` (`product_stock_ed`),
  CONSTRAINT `product_stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_stocks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_stocks` (`id`, `product_id`, `product_stock_sn`, `product_stock_ed`, `stock`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	NULL,	NULL,	18,	1,	'2026-01-21 23:19:06',	'2026-01-22 00:37:35',	NULL),
(2,	2,	NULL,	NULL,	27,	1,	'2026-01-21 23:19:12',	'2026-01-22 00:37:35',	NULL),
(3,	3,	NULL,	NULL,	16,	10,	'2026-01-28 12:46:27',	'2026-02-04 09:47:15',	NULL),
(4,	4,	NULL,	NULL,	4,	10,	'2026-01-28 12:46:37',	'2026-01-28 13:39:03',	NULL),
(5,	5,	NULL,	NULL,	5,	10,	'2026-01-28 12:46:47',	'2026-01-28 13:31:16',	NULL);

DROP TABLE IF EXISTS `product_stock_histories`;
CREATE TABLE `product_stock_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_stock_id` bigint(20) unsigned NOT NULL,
  `qty_masuk` int(11) NOT NULL DEFAULT 0,
  `qty_keluar` int(11) NOT NULL DEFAULT 0,
  `stock_awal` int(11) NOT NULL DEFAULT 0,
  `stock_akhir` int(11) NOT NULL DEFAULT 0,
  `harga_beli` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_biaya_beli` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
  `jenis` enum('barang masuk','barang keluar','retur customer','retur supplier','stock awal','pemulihan stock','batal barang masuk','batal barang keluar') NOT NULL DEFAULT 'barang masuk',
  `keterangan` varchar(255) DEFAULT NULL,
  `no_transaksi` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_stock_histories_product_stock_id_foreign` (`product_stock_id`),
  KEY `product_stock_histories_user_id_foreign` (`user_id`),
  KEY `product_stock_histories_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `product_stock_histories_tanggal_index` (`tanggal`),
  KEY `product_stock_histories_jenis_index` (`jenis`),
  KEY `product_stock_histories_no_transaksi_index` (`no_transaksi`),
  CONSTRAINT `product_stock_histories_product_stock_id_foreign` FOREIGN KEY (`product_stock_id`) REFERENCES `product_stocks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_stock_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_stock_histories` (`id`, `tanggal`, `product_stock_id`, `qty_masuk`, `qty_keluar`, `stock_awal`, `stock_akhir`, `harga_beli`, `total_biaya_beli`, `harga_jual`, `jenis`, `keterangan`, `no_transaksi`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	'2026-01-21 23:19:06',	1,	20,	0,	0,	20,	0.00,	0.00,	0.00,	'stock awal',	NULL,	NULL,	1,	'2026-01-21 23:19:06',	'2026-01-21 23:19:06',	NULL),
(2,	'2026-01-21 23:19:12',	2,	30,	0,	0,	30,	0.00,	0.00,	0.00,	'stock awal',	NULL,	NULL,	1,	'2026-01-21 23:19:12',	'2026-01-21 23:19:12',	NULL),
(3,	'2026-01-22 00:19:12',	2,	0,	1,	30,	29,	0.00,	0.00,	40000.00,	'barang keluar',	NULL,	'SJ - 2026010005',	1,	'2026-01-22 00:19:12',	'2026-01-22 00:19:12',	NULL),
(4,	'2026-01-22 00:37:35',	1,	0,	2,	20,	18,	0.00,	0.00,	25000.00,	'barang keluar',	NULL,	'SJ - 2026010001',	1,	'2026-01-22 00:37:35',	'2026-01-22 00:37:35',	NULL),
(5,	'2026-01-22 00:37:35',	2,	0,	2,	29,	27,	0.00,	0.00,	40000.00,	'barang keluar',	NULL,	'SJ - 2026010001',	1,	'2026-01-22 00:37:35',	'2026-01-22 00:37:35',	NULL),
(6,	'2026-01-28 05:46:27',	3,	10,	0,	0,	10,	0.00,	0.00,	0.00,	'stock awal',	NULL,	NULL,	10,	'2026-01-28 12:46:27',	'2026-01-28 12:46:27',	NULL),
(7,	'2026-01-28 05:46:37',	4,	10,	0,	0,	10,	0.00,	0.00,	0.00,	'stock awal',	NULL,	NULL,	10,	'2026-01-28 12:46:37',	'2026-01-28 12:46:37',	NULL),
(8,	'2026-01-28 05:46:47',	5,	10,	0,	0,	10,	0.00,	0.00,	0.00,	'stock awal',	NULL,	NULL,	10,	'2026-01-28 12:46:47',	'2026-01-28 12:46:47',	NULL),
(10,	'2026-01-28 13:04:22',	3,	10,	0,	10,	20,	35000.00,	700000.00,	0.00,	'barang masuk',	NULL,	'TEST 01',	10,	'2026-01-28 13:04:22',	'2026-01-28 13:04:22',	NULL),
(11,	'2026-01-28 13:31:16',	5,	0,	5,	10,	5,	0.00,	0.00,	10000.00,	'barang keluar',	NULL,	'MPM - 2026010006',	10,	'2026-01-28 13:31:16',	'2026-01-28 13:31:16',	NULL),
(12,	'2026-01-28 13:31:16',	3,	0,	1,	20,	19,	0.00,	0.00,	40000.00,	'barang keluar',	NULL,	'MPM - 2026010006',	10,	'2026-01-28 13:31:16',	'2026-01-28 13:31:16',	NULL),
(13,	'2026-01-28 13:31:16',	4,	0,	5,	10,	5,	0.00,	0.00,	32500.00,	'barang keluar',	NULL,	'MPM - 2026010006',	10,	'2026-01-28 13:31:16',	'2026-01-28 13:31:16',	NULL),
(14,	'2026-01-28 13:39:03',	3,	0,	2,	19,	17,	0.00,	0.00,	40000.00,	'barang keluar',	NULL,	'MPM - 2026010007',	10,	'2026-01-28 13:39:03',	'2026-01-28 13:39:03',	NULL),
(15,	'2026-01-28 13:39:03',	4,	0,	1,	5,	4,	0.00,	0.00,	32500.00,	'barang keluar',	NULL,	'MPM - 2026010007',	10,	'2026-01-28 13:39:03',	'2026-01-28 13:39:03',	NULL),
(16,	'2026-02-04 08:35:45',	3,	0,	1,	17,	16,	0.00,	0.00,	40000.00,	'barang keluar',	NULL,	'MPM - 2026020001',	1,	'2026-02-04 08:35:45',	'2026-02-04 08:49:15',	'2026-02-04 08:49:15'),
(17,	'2026-02-04 08:49:20',	3,	0,	1,	17,	16,	0.00,	0.00,	40000.00,	'barang keluar',	NULL,	'MPM - 2026020001',	1,	'2026-02-04 08:49:20',	'2026-02-04 08:51:46',	'2026-02-04 08:51:46'),
(18,	'2026-02-04 08:51:49',	3,	0,	1,	17,	16,	0.00,	0.00,	40000.00,	'barang keluar',	NULL,	'MPM - 2026020001',	1,	'2026-02-04 08:51:49',	'2026-02-04 09:04:17',	'2026-02-04 09:04:17'),
(19,	'2026-02-04 09:04:21',	3,	0,	1,	17,	16,	0.00,	0.00,	40000.00,	'barang keluar',	NULL,	'MPM - 2026020001',	1,	'2026-02-04 09:04:21',	'2026-02-04 09:11:43',	'2026-02-04 09:11:43'),
(20,	'2026-02-04 09:11:47',	3,	0,	1,	17,	16,	0.00,	0.00,	40000.00,	'barang keluar',	NULL,	'MPM - 2026020001',	1,	'2026-02-04 09:11:47',	'2026-02-04 09:21:33',	'2026-02-04 09:21:33'),
(21,	'2026-02-04 09:21:36',	3,	0,	1,	17,	16,	0.00,	0.00,	40000.00,	'barang keluar',	NULL,	'MPM - 2026020001',	1,	'2026-02-04 09:21:36',	'2026-02-04 09:47:11',	'2026-02-04 09:47:11'),
(22,	'2026-02-04 09:47:15',	3,	0,	1,	17,	16,	0.00,	0.00,	40000.00,	'barang keluar',	NULL,	'MPM - 2026020001',	1,	'2026-02-04 09:47:15',	'2026-02-04 09:47:15',	NULL);

DROP TABLE IF EXISTS `sales_orders`;
CREATE TABLE `sales_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `sales_orders_tanggal` datetime NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `sales_order_no` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_orders_sales_order_no_unique` (`sales_order_no`),
  KEY `sales_orders_outlet_id_foreign` (`outlet_id`),
  KEY `sales_orders_customer_id_foreign` (`customer_id`),
  KEY `sales_orders_user_id_foreign` (`user_id`),
  KEY `sales_orders_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `sales_orders_sales_orders_tanggal_index` (`sales_orders_tanggal`),
  CONSTRAINT `sales_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_orders_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `sales_order_details`;
CREATE TABLE `sales_order_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sales_order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `sales_order_detailproduct_name` varchar(255) NOT NULL,
  `sales_order_detail_qty` int(11) NOT NULL DEFAULT 0,
  `sales_order_detail_qty_terpenuhi` int(11) NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_order_details_sales_order_id_foreign` (`sales_order_id`),
  KEY `sales_order_details_product_id_foreign` (`product_id`),
  KEY `sales_order_details_user_id_foreign` (`user_id`),
  KEY `sales_order_details_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `sales_order_details_sales_order_detailproduct_name_index` (`sales_order_detailproduct_name`),
  KEY `sales_order_details_sales_order_detail_qty_index` (`sales_order_detail_qty`),
  KEY `sales_order_details_sales_order_detail_qty_terpenuhi_index` (`sales_order_detail_qty_terpenuhi`),
  CONSTRAINT `sales_order_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_order_details_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_order_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `satuans`;
CREATE TABLE `satuans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `satuan_name` varchar(100) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `satuan_name_outlet_id` (`satuan_name`,`outlet_id`),
  KEY `satuans_outlet_id_foreign` (`outlet_id`),
  KEY `satuans_user_id_foreign` (`user_id`),
  KEY `satuans_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  CONSTRAINT `satuans_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `satuans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `satuans` (`id`, `outlet_id`, `satuan_name`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	'pcs',	1,	'2026-01-21 23:16:45',	'2026-01-21 23:16:45',	NULL),
(2,	1,	'box',	1,	'2026-01-21 23:16:50',	'2026-01-21 23:16:50',	NULL),
(3,	4,	'pcs',	1,	'2026-01-21 23:16:45',	'2026-01-21 23:16:45',	NULL);

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `supplier_alamat` text DEFAULT NULL,
  `supplier_email` varchar(255) DEFAULT NULL,
  `supplier_phone1` varchar(255) DEFAULT NULL,
  `supplier_phone2` varchar(255) DEFAULT NULL,
  `supplier_picname1` varchar(255) DEFAULT NULL,
  `supplier_picphone1` varchar(255) DEFAULT NULL,
  `supplier_picname2` varchar(255) DEFAULT NULL,
  `supplier_picphone2` varchar(255) DEFAULT NULL,
  `supplier_kodepos` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suppliers_supplier_email_unique` (`supplier_email`),
  KEY `suppliers_outlet_id_foreign` (`outlet_id`),
  KEY `suppliers_user_id_foreign` (`user_id`),
  KEY `suppliers_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `suppliers_supplier_name_index` (`supplier_name`),
  KEY `suppliers_supplier_alamat_index` (`supplier_alamat`(768)),
  KEY `suppliers_supplier_kodepos_index` (`supplier_kodepos`),
  CONSTRAINT `suppliers_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `suppliers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `suppliers` (`id`, `outlet_id`, `supplier_name`, `supplier_alamat`, `supplier_email`, `supplier_phone1`, `supplier_phone2`, `supplier_picname1`, `supplier_picphone1`, `supplier_picname2`, `supplier_picphone2`, `supplier_kodepos`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	'supllier baru',	NULL,	NULL,	NULL,	NULL,	'sdds',	'0246709876',	'sdffdfg',	'0246709876',	NULL,	1,	'2026-01-21 21:59:26',	'2026-01-21 21:59:26',	NULL),
(2,	4,	'SUP 01',	NULL,	NULL,	NULL,	NULL,	'SUP',	NULL,	NULL,	NULL,	NULL,	10,	'2026-01-28 12:47:59',	'2026-01-28 12:47:59',	NULL);

DROP TABLE IF EXISTS `supplier_products`;
CREATE TABLE `supplier_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `first_product_stock_id` int(11) NOT NULL,
  `harga_beli` decimal(10,2) NOT NULL DEFAULT 0.00,
  `frekuensi` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_products_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_products_product_id_foreign` (`product_id`),
  KEY `supplier_products_first_product_stock_id_index` (`first_product_stock_id`),
  KEY `supplier_products_frekuensi_index` (`frekuensi`),
  CONSTRAINT `supplier_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_products_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `supplier_products` (`id`, `supplier_id`, `product_id`, `first_product_stock_id`, `harga_beli`, `frekuensi`, `created_at`, `updated_at`) VALUES
(1,	2,	3,	3,	35000.00,	2,	'2026-01-28 12:54:15',	'2026-01-28 13:04:22');

DROP TABLE IF EXISTS `system_dashboards`;
CREATE TABLE `system_dashboards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `system_dashboardname` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_dashboards_user_id_foreign` (`user_id`),
  KEY `system_dashboards_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `system_dashboards_system_dashboardname_index` (`system_dashboardname`),
  CONSTRAINT `system_dashboards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_dashboards` (`id`, `system_dashboardname`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	'Omset',	1,	NULL,	NULL,	NULL),
(2,	'Laporan Penjualan per Bulan',	1,	NULL,	NULL,	NULL),
(3,	'Laporan Perbandingan Omset Marketing Per Bulan',	1,	NULL,	NULL,	NULL),
(4,	'Laporan Produk dibawah Stok Minimal',	1,	NULL,	NULL,	NULL),
(5,	'Daftar Produk ED / Kurang dari 1 Bulan',	1,	NULL,	NULL,	NULL),
(6,	'Laporan Omset Outlet Cabang',	1,	'2026-02-10 03:45:52',	'2026-02-10 03:45:52',	NULL);

DROP TABLE IF EXISTS `text_queues`;
CREATE TABLE `text_queues` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `text` text NOT NULL,
  `priority` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `text_queues_priority_index` (`priority`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `text_queues` (`id`, `title`, `text`, `priority`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	'Header',	'jika terdapat kendala bisa menghubungi tim kami 081901233316 (Untung)',	0,	'2026-01-28 00:08:28',	'2026-01-28 00:08:28',	NULL);

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_kasir` tinyint(1) NOT NULL DEFAULT 0,
  `is_owner` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_group_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_is_kasir_index` (`is_kasir`),
  KEY `users_user_group_id_foreign` (`user_group_id`),
  CONSTRAINT `users_user_group_id_foreign` FOREIGN KEY (`user_group_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `is_kasir`, `is_owner`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `user_group_id`, `user_id`) VALUES
(1,	'untung',	'sukiruntung@gmail.com',	NULL,	NULL,	'$2y$12$Yr8fongyseFvRFKDOK6VX.S06vIec46y33loYo.LCYZozNDJfOlWe',	1,	0,	'Ebh9BMisI4IOwGvxA6RrfU5SuQSiMdIdSkgIfFGTt7acMm9tsILURHIBhVmo',	'2025-08-03 10:54:27',	'2025-12-16 00:33:43',	NULL,	2,	1),
(2,	'test v',	'test@gmail.com',	NULL,	NULL,	'$2y$12$Yr8fongyseFvRFKDOK6VX.S06vIec46y33loYo.LCYZozNDJfOlWe',	0,	0,	'iasAhWACfpaZt5aG5KLQhS66Sa6IV5kF852RuS4Y4nQxg7w656dEqLwsLbUi',	'2025-08-03 10:54:27',	'2025-09-03 06:19:49',	NULL,	4,	1),
(4,	'test userfggf',	'test2@gmail.com',	NULL,	NULL,	'$2y$12$UVJzEyaqqFzHySL0mTUgbuLFlD0U/F0VLUz/74PBMIEmG3A5kVIES',	0,	0,	'SjRVUTn354euxXnJtkrzXSXlQXOk7NRQVHW6BPkbuTgrdDiohZoLuhdwsBkk',	'2025-08-06 06:20:20',	'2025-08-06 07:32:23',	NULL,	2,	NULL),
(5,	'hallo gfg dcdf',	'hallo@gmail.com',	NULL,	NULL,	'$2y$12$90TCGEY9wDanNbyHnjgvGu67WOA2qv.2fB.paZXjEjO2HbIRBF.Ei',	0,	0,	NULL,	'2025-08-06 06:37:39',	'2025-08-06 09:21:09',	NULL,	4,	4),
(6,	'hihi',	'hihi@gmail.com',	NULL,	NULL,	'$2y$12$8MUiPIVRHcSy3CTXz0A3yOVZG3wu5UCbEjfbPEjVW.afJh/BcsBAe',	0,	0,	NULL,	'2025-08-06 06:58:46',	'2025-08-06 06:58:46',	NULL,	4,	1),
(7,	'lili',	'lili@gmail.com',	NULL,	NULL,	'$2y$12$Bw8F8/rlspM3CyYQf0jZqetsNgaSeS7WtIH0YjiZFANZnikoJai7O',	0,	0,	NULL,	'2025-08-06 08:04:25',	'2025-08-06 08:04:25',	NULL,	4,	1),
(8,	'susan',	'susan@gmail.com',	NULL,	NULL,	'$2y$12$MdROXvgraDa.04hYVZyHhuqoH3Lf3U7qFcYwEOCKU1NaSAao0zeVW',	0,	0,	NULL,	'2025-10-21 16:42:06',	'2025-10-21 16:45:51',	NULL,	6,	1),
(9,	'untung',	'untung@gmail.com',	NULL,	NULL,	'$2y$12$Yr8fongyseFvRFKDOK6VX.S06vIec46y33loYo.LCYZozNDJfOlWe',	0,	0,	'4MWN9owxBpxmrwqB3Pbcc9i3unBSmdQuiVhqw7DpRfG1uBV1n7cGohgcJerK',	'2025-08-03 10:54:27',	'2025-08-03 10:54:27',	NULL,	2,	NULL),
(10,	'ryan pw',	'ryanputrawijayaaa@gmail.com',	'082125447168',	NULL,	'$2y$12$XZ7HqCyKInJiK1lK4dwpCubTbt3jWVgtPPBhGMqKgm8HoqOjdRVEe',	1,	0,	NULL,	'2026-01-28 12:26:32',	'2026-01-28 12:26:32',	NULL,	2,	1),
(19,	'test user outlet 5',	'outlet5@gmail.com',	NULL,	NULL,	'$2y$12$IdS6lwJF9oWRROOvYh.6d.jqNCPr68Rg2Jt.05qFcZUUWUFstxpUK',	1,	0,	NULL,	'2026-02-09 06:26:39',	'2026-02-09 08:34:21',	NULL,	2,	19),
(20,	'staffoutlet5',	'staffoutlet5@gmail.com',	NULL,	NULL,	'$2y$12$.830Ajdr1N00qCjNORldi.4F8VwGzgJdt3bf7Hw9R/hYObqtnP1cG',	1,	0,	NULL,	'2026-02-09 06:31:01',	'2026-02-09 08:32:43',	NULL,	4,	20),
(21,	'outlet6',	'outlet6@gmail.com',	NULL,	NULL,	'$2y$12$S31FtDSqkTNrkSko8ENlguJ6IWg0MZuBu2uqtFRFcex0mli9z0qCu',	1,	0,	NULL,	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL,	2,	1);

DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE `user_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_groupname` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_groups_user_id_foreign` (`user_id`),
  KEY `user_groups_deleted_at_created_at_index` (`deleted_at`,`created_at`),
  KEY `user_groups_user_groupname_index` (`user_groupname`),
  CONSTRAINT `user_groups_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_groups` (`id`, `user_groupname`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2,	'adminer',	1,	'2025-08-05 18:39:42',	'2025-08-06 06:03:28',	NULL),
(4,	'user',	1,	'2025-08-05 18:42:53',	'2025-08-05 18:42:53',	NULL),
(5,	'fdffd',	1,	'2025-08-06 06:07:39',	'2025-08-06 06:07:45',	'2025-08-06 06:07:45'),
(6,	'user 2',	1,	'2025-10-21 16:44:30',	'2025-10-21 16:44:30',	NULL);

DROP TABLE IF EXISTS `user_outlets`;
CREATE TABLE `user_outlets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `outlet_id` bigint(20) unsigned NOT NULL,
  `role` enum('admin','owner','staff') NOT NULL DEFAULT 'staff',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_outlets_user_id_foreign` (`user_id`),
  KEY `user_outlets_outlet_id_foreign` (`outlet_id`),
  CONSTRAINT `user_outlets_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_outlets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_outlets` (`id`, `user_id`, `outlet_id`, `role`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	1,	4,	'admin',	NULL,	NULL,	NULL),
(2,	4,	1,	'staff',	NULL,	NULL,	NULL),
(3,	10,	4,	'staff',	'2026-01-28 12:26:32',	'2026-01-28 12:26:32',	NULL),
(9,	19,	12,	'owner',	'2026-02-09 06:26:39',	'2026-02-09 08:08:53',	NULL),
(10,	20,	12,	'staff',	'2026-02-09 06:31:01',	'2026-02-09 08:08:53',	NULL),
(11,	21,	13,	'owner',	'2026-02-10 03:20:31',	'2026-02-10 03:20:31',	NULL);

-- 2026-02-10 07:31:16
