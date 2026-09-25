-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eLibrary
-- ------------------------------------------------------
-- Server version	8.4.7

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,2,'borrow_issued','បានកត់ត្រាឱ្យសមាជិក Vannak Keo ខ្ចីសៀវភៅ \"Clean Code\" (កាលបរិច្ឆេទសង: 30/09/2026)','App\\Models\\Borrow',1,NULL,'2026-09-16 07:51:53','2026-09-16 09:51:53'),(2,2,'fine_waived','បានសម្រេចលើកលែងប្រាក់ពិន័យយឺតចំនួន $2.00 (៨,០០០ ៛) ជូនសមាជិក Sreymom Pich (មូលហេតុ៖ សិស្សមានធុរៈជំងឺ មានលិខិតបញ្ជាក់ត្រឹមត្រូវ)','App\\Models\\Borrow',2,NULL,'2026-09-16 04:51:53','2026-09-16 09:51:53'),(3,2,'reservation_approved','បានអនុម័តការកក់សៀវភៅ \"Zero to One\" សម្រាប់ Dr. Rithy Seng (កំណត់យកមុន ៤៨ ម៉ោង)','App\\Models\\Reservation',1,NULL,'2026-09-16 02:51:53','2026-09-16 09:51:53'),(4,1,'book_added','បានបន្ថែមសៀវភៅថ្មី \"Clean Code\" ចូលក្នុងប្រព័ន្ធបណ្ណាល័យ','App\\Models\\Book',1,NULL,'2026-09-15 06:51:53','2026-09-16 09:51:53'),(5,2,'book_returned','បានទទួលសៀវភៅ \"The Great Gatsby\" សងវិញពីសមាជិក Vannak Keo (ស្ថានភាពល្អ Good)','App\\Models\\Borrow',5,NULL,'2026-09-15 03:51:53','2026-09-16 09:51:53'),(6,2,'policy_updated','បានកែសម្រួលគោលការណ៍បណ្ណាល័យ៖ កំណត់អត្រាប្រាក់ពិន័យ ៥០០ ៛/ថ្ងៃ និងកូតាខ្ចី ៣ ក្បាលក្នុងពេលតែមួយ','App\\Models\\Setting',NULL,NULL,'2026-09-14 09:51:53','2026-09-16 09:51:53'),(7,1,'member_moderated','Sokha Chan (Super Admin) បានប្តូរស្ថានភាពសមាជិក Kosal Heng (Expired Sub) ទៅជា ផ្អាកបណ្តោះអាសន្ន','App\\Models\\User',8,'127.0.0.1','2026-09-16 10:06:50','2026-09-16 10:06:50'),(8,1,'member_moderated','Sokha Chan (Super Admin) បានប្តូរស្ថានភាពសមាជិក Kosal Heng (Expired Sub) ទៅជា បានផ្អាក','App\\Models\\User',8,'127.0.0.1','2026-09-16 10:06:50','2026-09-16 10:06:50'),(9,1,'member_moderated','Sokha Chan (Super Admin) បានប្តូរស្ថានភាពសមាជិក Kosal Heng (Expired Sub) ទៅជា សកម្ម','App\\Models\\User',8,'127.0.0.1','2026-09-16 10:06:52','2026-09-16 10:06:52'),(10,1,'fine_waived','Sokha Chan (Super Admin) បានសម្រេចលើកលែងប្រាក់ពិន័យ $5.50 ជូនសមាជិក Bopha Chea (មូលហេតុ៖ Academic thesis research deadline extension approved by Dean)','App\\Models\\Borrow',4,'127.0.0.1','2026-09-16 10:25:43','2026-09-16 10:25:43'),(11,1,'fine_waived','Sokha Chan (Super Admin) បានសម្រេចលើកលែងប្រាក់ពិន័យ $0.50 ជូនសមាជិក Dara Samnang (មូលហេតុ៖ Medical emergency with official doctor certificate)','App\\Models\\Borrow',5,'127.0.0.1','2026-09-16 10:26:02','2026-09-16 10:26:02'),(12,1,'reservation_force_cancelled','Sokha Chan (Super Admin) បានលុបចោលការកក់ពិសេសកូដ RES-9228C8 របស់សមាជិក Sreymom Pich (មូលហេតុ៖ Member failed to pick up within pickup deadline)','App\\Models\\Reservation',2,'127.0.0.1','2026-09-16 10:26:39','2026-09-16 10:26:39'),(13,1,'book_returned','Sokha Chan (Super Admin) បានទទួលសៀវភៅ \"A History of Cambodia\" សងវិញពីសមាជិក Bopha Chea (ស្ថានភាព: Good, ប្រាក់ពិន័យ: $-5.88)','App\\Models\\Borrow',4,'127.0.0.1','2026-09-16 11:13:13','2026-09-16 11:13:13'),(14,1,'fine_waived','Sokha Chan (Super Admin) បានសម្រេចលើកលែងប្រាក់ពិន័យ $2.00 ជូនសមាជិក Sreymom Pich (មូលហេតុ៖ Academic thesis research deadline extension approved by Dean)','App\\Models\\Borrow',2,'127.0.0.1','2026-09-16 11:13:31','2026-09-16 11:13:31'),(15,1,'member_moderated','Sokha Chan (Super Admin) បានប្តូរស្ថានភាពសមាជិក Sam lanh ទៅជា ផ្អាកបណ្តោះអាសន្ន','App\\Models\\User',10,'127.0.0.1','2026-09-20 09:53:22','2026-09-20 09:53:22'),(16,1,'member_moderated','Sokha Chan (Super Admin) បានប្តូរស្ថានភាពសមាជិក Sam lanh ទៅជា បានផ្អាក','App\\Models\\User',10,'127.0.0.1','2026-09-20 09:53:22','2026-09-20 09:53:22'),(17,1,'member_moderated','Sokha Chan (Super Admin) បានប្តូរស្ថានភាពសមាជិក Sam lanh ទៅជា សកម្ម','App\\Models\\User',10,'127.0.0.1','2026-09-20 09:53:23','2026-09-20 09:53:23'),(18,1,'book_deleted','បានលុបសៀវភៅ \"The Lean Startup\" ចេញពីប្រព័ន្ធបណ្ណាល័យ',NULL,NULL,'27.109.113.231','2026-09-21 03:00:04','2026-09-21 03:00:04'),(19,1,'book_returned','Sokha Chan (Super Admin) បានទទួលសៀវភៅ \"IT\" សងវិញពីសមាជិក Sam lanh (ស្ថានភាព: Good, ប្រាក់ពិន័យ: $0.00)','App\\Models\\Borrow',7,'27.109.113.231','2026-09-21 03:00:48','2026-09-21 03:00:48'),(20,1,'book_returned','Sokha Chan (Super Admin) បានទទួលសៀវភៅ \"Designing Data-Intensive Applications\" សងវិញពីសមាជិក Dr. Rithy Seng (ស្ថានភាព: Good, ប្រាក់ពិន័យ: $0.00)','App\\Models\\Borrow',3,'27.109.113.231','2026-09-21 03:00:52','2026-09-21 03:00:52'),(21,1,'book_returned','Sokha Chan (Super Admin) បានទទួលសៀវភៅ \"Atomic Habits\" សងវិញពីសមាជិក Sreymom Pich (ស្ថានភាព: Good, ប្រាក់ពិន័យ: $4.50)','App\\Models\\Borrow',2,'27.109.113.231','2026-09-21 03:00:55','2026-09-21 03:00:55'),(22,1,'book_returned','Sokha Chan (Super Admin) បានទទួលសៀវភៅ \"Clean Code: A Handbook of Agile Software Craftsmanship\" សងវិញពីសមាជិក Vannak Keo (ស្ថានភាព: Good, ប្រាក់ពិន័យ: $0.00)','App\\Models\\Borrow',1,'27.109.113.231','2026-09-21 03:01:00','2026-09-21 03:01:00'),(23,1,'member_deleted','Sokha Chan (Super Admin) បានលុបគណនីសមាជិក Vannak Keo ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.231','2026-09-21 03:01:25','2026-09-21 03:01:25'),(24,1,'member_deleted','Sokha Chan (Super Admin) បានលុបគណនីសមាជិក Sokha Chan (Super Admin) ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.231','2026-09-21 03:01:31','2026-09-21 03:01:31'),(25,2,'user_deleted','Dara Vichea (ប្រធានបណ្ណាល័យ) បានលុបគណនីអ្នកប្រើប្រាស់ Ley Dola ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.231','2026-09-21 03:05:24','2026-09-21 03:05:24'),(26,2,'user_deleted','Dara Vichea (ប្រធានបណ្ណាល័យ) បានលុបគណនីអ្នកប្រើប្រាស់ Sonk San ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.231','2026-09-21 03:05:30','2026-09-21 03:05:30'),(27,2,'user_deleted','Dara Vichea (ប្រធានបណ្ណាល័យ) បានលុបគណនីអ្នកប្រើប្រាស់ Sreymom Pich ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.231','2026-09-21 03:06:22','2026-09-21 03:06:22'),(28,2,'user_deleted','Dara Vichea (ប្រធានបណ្ណាល័យ) បានលុបគណនីអ្នកប្រើប្រាស់ Dr. Rithy Seng ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.231','2026-09-21 03:06:31','2026-09-21 03:06:31'),(29,2,'user_deleted','Dara Vichea (ប្រធានបណ្ណាល័យ) បានលុបគណនីអ្នកប្រើប្រាស់ Bopha Chea ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.231','2026-09-21 03:06:47','2026-09-21 03:06:47'),(30,2,'user_deleted','Dara Vichea (ប្រធានបណ្ណាល័យ) បានលុបគណនីអ្នកប្រើប្រាស់ Dara Samnang ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.231','2026-09-21 03:06:54','2026-09-21 03:06:54'),(31,2,'user_deleted','Dara Vichea (ប្រធានបណ្ណាល័យ) បានលុបគណនីអ្នកប្រើប្រាស់ Kosal Heng (Expired Sub) ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.231','2026-09-21 03:06:59','2026-09-21 03:06:59'),(32,2,'user_deleted','Dara Vichea (ប្រធានបណ្ណាល័យ) បានលុបគណនីអ្នកប្រើប្រាស់ Sam lanh ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.231','2026-09-21 03:07:03','2026-09-21 03:07:03'),(33,2,'user_deleted','Dara Vichea (ប្រធានបណ្ណាល័យ) បានលុបគណនីអ្នកប្រើប្រាស់ Sam lanh ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.231','2026-09-21 03:07:12','2026-09-21 03:07:12'),(34,14,'member_deleted','Sokha Chan (Super Admin) បានលុបគណនីសមាជិក Khouch Poun ចេញពីប្រព័ន្ធ',NULL,NULL,'103.216.51.112','2026-09-21 03:14:46','2026-09-21 03:14:46'),(35,14,'settings_updated','Sokha Chan (Super Admin) បានកែប្រែព័ត៌មានទូទៅរបស់បណ្ណាល័យ',NULL,NULL,'27.109.113.231','2026-09-21 03:16:25','2026-09-21 03:16:25'),(36,14,'user_updated','Ley LIn (Super Admin) បានកែប្រែតួនាទីគណនី Chiiphea Phat ទៅជា Manager','App\\Models\\User',15,'27.109.113.231','2026-09-21 03:21:44','2026-09-21 03:21:44'),(37,14,'book_returned','Ley LIn (Super Admin) បានទទួលសៀវភៅ \"ស្តង់ដាសាលាបឋមសិក្សា​គំរូ\" សងវិញពីសមាជិក Sonk San (ស្ថានភាព: Good, ប្រាក់ពិន័យ: $0.00)','App\\Models\\Borrow',8,'103.216.51.252','2026-09-21 04:20:50','2026-09-21 04:20:50'),(38,14,'book_created','បានបន្ថែមសៀវភៅថ្មី \"Verification Test Book 1790011023\" ទៅក្នុងប្រព័ន្ធបណ្ណាល័យ','App\\Models\\Book',47,'127.0.0.1','2026-09-21 10:17:03','2026-09-21 10:17:03'),(39,14,'book_deleted','បានលុបសៀវភៅ \"Verification Test Book 1790010970\" ចេញពីប្រព័ន្ធបណ្ណាល័យ',NULL,NULL,'175.100.83.82','2026-09-21 10:21:43','2026-09-21 10:21:43'),(40,14,'book_created','បានបន្ថែមសៀវភៅថ្មី \"HSK3\" ទៅក្នុងប្រព័ន្ធបណ្ណាល័យ','App\\Models\\Book',48,'127.0.0.1','2026-09-21 10:46:05','2026-09-21 10:46:05'),(41,14,'book_deleted','បានលុបសៀវភៅ \"សៀវភៅ កុំព្យូទ័ររដ្ឋបាល\" ចេញពីប្រព័ន្ធបណ្ណាល័យ',NULL,NULL,'127.0.0.1','2026-09-21 11:21:25','2026-09-21 11:21:25'),(42,14,'book_created','បានបន្ថែមសៀវភៅថ្មី \"កុំព្យូទ័ររដ្ជបាល\" ទៅក្នុងប្រព័ន្ធបណ្ណាល័យ','App\\Models\\Book',49,'127.0.0.1','2026-09-21 11:22:30','2026-09-21 11:22:30'),(43,14,'member_moderated','Ley LIn (Super Admin) បានប្តូរស្ថានភាពសមាជិក Ley Lin ទៅជា ផ្អាកបណ្តោះអាសន្ន','App\\Models\\User',52,'175.100.79.42','2026-09-22 09:53:15','2026-09-22 09:53:15'),(44,14,'member_moderated','Ley LIn (Super Admin) បានប្តូរស្ថានភាពសមាជិក Ley Lin ទៅជា បានផ្អាក','App\\Models\\User',52,'175.100.79.42','2026-09-22 09:53:16','2026-09-22 09:53:16'),(45,14,'backup_created','បានបង្កើត Backup ទិន្នន័យ E-Library ជោគជ័យចូលទៅក្នុង Drive D (elibrary_backup_2026-09-23_095755_manual.zip, ទំហំ: 1.95 MB)',NULL,NULL,NULL,'2026-09-23 02:57:55','2026-09-23 02:57:55'),(46,14,'backup_deleted','បានលុបឯកសារ Backup: elibrary_backup_2026-09-23_094444_manual.zip',NULL,NULL,NULL,'2026-09-23 03:01:48','2026-09-23 03:01:48'),(47,14,'backup_deleted','បានលុបឯកសារ Backup: elibrary_backup_2026-09-23_095021_manual.zip',NULL,NULL,NULL,'2026-09-23 03:01:52','2026-09-23 03:01:52'),(48,14,'backup_deleted','បានលុបឯកសារ Backup: elibrary_backup_2026-09-23_095105_manual.zip',NULL,NULL,NULL,'2026-09-23 03:01:55','2026-09-23 03:01:55'),(49,14,'backup_created','ទិន្នន័យត្រូវបានបម្រុងទុកចូលទៅក្នុង Server Drive D ដោយជោគជ័យ! (ឯកសារ: elibrary_backup_2026-09-23_102004_manual.zip, ទំហំ: 1.95 MB)',NULL,NULL,NULL,'2026-09-23 03:20:04','2026-09-23 03:20:04'),(50,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_102004_manual.zip)',NULL,NULL,NULL,'2026-09-23 03:20:31','2026-09-23 03:20:31'),(51,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_101914_manual.zip)',NULL,NULL,NULL,'2026-09-23 03:20:34','2026-09-23 03:20:34'),(52,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_101913_manual.zip)',NULL,NULL,NULL,'2026-09-23 03:20:36','2026-09-23 03:20:36'),(53,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_101859_manual.zip)',NULL,NULL,NULL,'2026-09-23 03:20:40','2026-09-23 03:20:40'),(54,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_101858_manual.zip)',NULL,NULL,NULL,'2026-09-23 03:20:42','2026-09-23 03:20:42'),(55,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_101829_manual.zip)',NULL,NULL,NULL,'2026-09-23 03:20:45','2026-09-23 03:20:45'),(56,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_102753_manual.zip)',NULL,NULL,NULL,'2026-09-23 09:02:14','2026-09-23 09:02:14'),(57,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_101828_manual.zip)',NULL,NULL,NULL,'2026-09-23 09:02:15','2026-09-23 09:02:15'),(58,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_095755_manual.zip)',NULL,NULL,NULL,'2026-09-23 09:02:18','2026-09-23 09:02:18'),(59,14,'backup_created','ទិន្នន័យត្រូវបានបម្រុងទុកចូលទៅក្នុង Server Drive D ដោយជោគជ័យ! (ឯកសារ: elibrary_backup_2026-09-23_160415_manual.zip, ទំហំ: 1.95 MB)',NULL,NULL,NULL,'2026-09-23 09:04:15','2026-09-23 09:04:15'),(60,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_160415_manual.zip)',NULL,NULL,NULL,'2026-09-23 09:05:07','2026-09-23 09:05:07'),(61,14,'backup_created','ទិន្នន័យត្រូវបានបម្រុងទុកចូលទៅក្នុង Server Drive D ដោយជោគជ័យ! (ឯកសារ: elibrary_backup_2026-09-23_170244_manual.zip, ទំហំ: 1.95 MB)',NULL,NULL,NULL,'2026-09-23 10:02:45','2026-09-23 10:02:45'),(62,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_170244_manual.zip)',NULL,NULL,NULL,'2026-09-23 10:03:03','2026-09-23 10:03:03'),(63,14,'backup_created','ទិន្នន័យត្រូវបានបម្រុងទុកចូលទៅក្នុង Server Drive D ដោយជោគជ័យ! (ឯកសារ: elibrary_backup_2026-09-24_000810_manual.zip, ទំហំ: 1.95 MB)',NULL,NULL,NULL,'2026-09-23 17:08:10','2026-09-23 17:08:10'),(64,14,'book_returned','Ley LIn (Super Admin) បានទទួលសៀវភៅ \"HSK3\" សងវិញពីសមាជិក បណ្ឌិត អ៊ឹម សុភា (ស្ថានភាព: Good, ប្រាក់ពិន័យ: $0.00)','App\\Models\\Borrow',10,'127.0.0.1','2026-09-23 17:29:26','2026-09-23 17:29:26'),(65,14,'book_returned','Ley LIn (Super Admin) បានទទួលសៀវភៅ \"Designing Data-Intensive Applications: The Big Ideas Behind Reliable Systems\" សងវិញពីសមាជិក ចាន់ សុខា (ស្ថានភាព: Good, ប្រាក់ពិន័យ: $0.00)','App\\Models\\Borrow',9,'127.0.0.1','2026-09-23 17:29:33','2026-09-23 17:29:33'),(66,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-23_170526_auto.zip)',NULL,NULL,NULL,'2026-09-23 17:30:01','2026-09-23 17:30:01'),(67,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-24_000810_manual.zip)',NULL,NULL,NULL,'2026-09-23 17:30:03','2026-09-23 17:30:03'),(68,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-24_000813_auto.zip)',NULL,NULL,NULL,'2026-09-23 17:30:06','2026-09-23 17:30:06'),(69,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-24_004933_auto.zip)',NULL,NULL,NULL,'2026-09-24 11:55:15','2026-09-24 11:55:15'),(70,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-24_004934_auto.zip)',NULL,NULL,NULL,'2026-09-24 11:55:17','2026-09-24 11:55:17'),(71,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-24_004935_manual.zip)',NULL,NULL,NULL,'2026-09-24 11:55:19','2026-09-24 11:55:19'),(72,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-24_004936_manual.zip)',NULL,NULL,NULL,'2026-09-24 11:55:21','2026-09-24 11:55:21'),(73,14,'member_deleted','Ley LIn (Super Admin) បានលុបគណនីសមាជិក ម៉ែន ចរិយា ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 11:58:24','2026-09-24 11:58:24'),(74,14,'member_deleted','Ley LIn (Super Admin) បានលុបគណនីសមាជិក បណ្ឌិត ប៉ែន សម្បត្តិ ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 11:58:31','2026-09-24 11:58:31'),(75,14,'user_updated','Ley LIn (Super Admin) បានកែប្រែតួនាទីគណនី Chhaly Chhoeng ទៅជា Manager','App\\Models\\User',53,'127.0.0.1','2026-09-24 12:02:08','2026-09-24 12:02:08'),(76,14,'user_deleted','Ley LIn (Super Admin) បានលុបគណនីអ្នកប្រើប្រាស់ Dara Vichea (ប្រធានបណ្ណាល័យ) ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:02:22','2026-09-24 12:02:22'),(77,14,'user_deleted','Ley LIn (Super Admin) បានលុបគណនីអ្នកប្រើប្រាស់ ចាន់ សុខា ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:02:28','2026-09-24 12:02:28'),(78,14,'user_deleted','Ley LIn (Super Admin) បានលុបគណនីអ្នកប្រើប្រាស់ កែវ ធីតា ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:02:32','2026-09-24 12:02:32'),(79,14,'user_deleted','Ley LIn (Super Admin) បានលុបគណនីអ្នកប្រើប្រាស់ បណ្ឌិត អ៊ឹម សុភា ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:02:35','2026-09-24 12:02:35'),(80,14,'member_deleted','Ley LIn (Super Admin) បានលុបគណនីសមាជិក អនុបណ្ឌិត រ៉ាំ សុម៉ាលី ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:03:00','2026-09-24 12:03:00'),(81,14,'user_deleted','Ley LIn (Super Admin) បានលុបគណនីអ្នកប្រើប្រាស់ Sonk San ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:03:10','2026-09-24 12:03:10'),(82,14,'user_deleted','Ley LIn (Super Admin) បានលុបគណនីអ្នកប្រើប្រាស់ សាស្ត្រាចារ្យ កែវ វិបុល ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:03:13','2026-09-24 12:03:13'),(83,53,'member_deleted','Chhaly Chhoeng បានលុបគណនីសមាជិក អនុបណ្ឌិត ទៀង វណ្ណារី ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.248','2026-09-24 12:03:14','2026-09-24 12:03:14'),(84,14,'user_deleted','Ley LIn (Super Admin) បានលុបគណនីអ្នកប្រើប្រាស់ បណ្ឌិត ហួត ចាន់រិទ្ធ ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:03:17','2026-09-24 12:03:17'),(85,14,'user_deleted','Ley LIn (Super Admin) បានលុបគណនីអ្នកប្រើប្រាស់ អនុបណ្ឌិត លឹម គីមសួរ ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:03:20','2026-09-24 12:03:20'),(86,14,'user_deleted','Ley LIn (Super Admin) បានលុបគណនីអ្នកប្រើប្រាស់ Ley Lin ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:03:25','2026-09-24 12:03:25'),(87,14,'member_deleted','Ley LIn (Super Admin) បានលុបគណនីសមាជិក អនុបណ្ឌិត ចេង ស៊ីវឡេង ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:03:34','2026-09-24 12:03:34'),(88,14,'member_deleted','Ley LIn (Super Admin) បានលុបគណនីសមាជិក អនុបណ្ឌិត ម៉ម សុភ័ក្ត្រ ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:03:38','2026-09-24 12:03:38'),(89,53,'member_deleted','Chhaly Chhoeng បានលុបគណនីសមាជិក អនុបណ្ឌិត សិន ស្រីនិច ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.248','2026-09-24 12:03:40','2026-09-24 12:03:40'),(90,14,'member_deleted','Ley LIn (Super Admin) បានលុបគណនីសមាជិក បណ្ឌិត John Miller ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:03:49','2026-09-24 12:03:49'),(91,53,'member_deleted','Chhaly Chhoeng បានលុបគណនីសមាជិក អ៊ឹង ម៉ាលីស ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.248','2026-09-24 12:04:48','2026-09-24 12:04:48'),(92,14,'member_deleted','Ley LIn (Super Admin) បានលុបគណនីសមាជិក អនុបណ្ឌិត អ៊ុច ទ្រី ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:04:59','2026-09-24 12:04:59'),(93,53,'member_deleted','Chhaly Chhoeng បានលុបគណនីសមាជិក តាំង គីមហុង ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.248','2026-09-24 12:05:20','2026-09-24 12:05:20'),(94,53,'member_deleted','Chhaly Chhoeng បានលុបគណនីសមាជិក វ៉ាន់ វណ្ណា ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.248','2026-09-24 12:05:34','2026-09-24 12:05:34'),(95,53,'member_deleted','Chhaly Chhoeng បានលុបគណនីសមាជិក បណ្ឌិត ឈាង រតនា ចេញពីប្រព័ន្ធ',NULL,NULL,'27.109.113.248','2026-09-24 12:05:50','2026-09-24 12:05:50'),(96,14,'member_deleted','Ley LIn (Super Admin) បានលុបគណនីសមាជិក អនុបណ្ឌិត គិន សុជាលីស ចេញពីប្រព័ន្ធ',NULL,NULL,'127.0.0.1','2026-09-24 12:48:29','2026-09-24 12:48:29'),(97,14,'member_moderated','Ley LIn (Super Admin) បានប្តូរស្ថានភាពសមាជិក បណ្ឌិត សុខ សុវណ្ណារ៉ា ទៅជា ផ្អាកបណ្តោះអាសន្ន','App\\Models\\User',43,'127.0.0.1','2026-09-24 13:00:12','2026-09-24 13:00:12'),(98,14,'member_moderated','Ley LIn (Super Admin) បានប្តូរស្ថានភាពសមាជិក បណ្ឌិត សុខ សុវណ្ណារ៉ា ទៅជា បានផ្អាក','App\\Models\\User',43,'127.0.0.1','2026-09-24 13:00:13','2026-09-24 13:00:13'),(99,14,'member_moderated','Ley LIn (Super Admin) បានប្តូរស្ថានភាពសមាជិក បណ្ឌិត សុខ សុវណ្ណារ៉ា ទៅជា កំពុងដំណើរការ','App\\Models\\User',43,'127.0.0.1','2026-09-24 13:00:15','2026-09-24 13:00:15'),(100,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-24_194443_manual.zip)',NULL,NULL,NULL,'2026-09-24 13:06:12','2026-09-24 13:06:12'),(101,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-24_194442_auto.zip)',NULL,NULL,NULL,'2026-09-24 13:06:15','2026-09-24 13:06:15'),(102,14,'member_moderated','Ley LIn (Super Admin) បានប្តូរស្ថានភាពសមាជិក Phat Sophea ទៅជា ផ្អាកបណ្តោះអាសន្ន','App\\Models\\User',54,'175.100.79.42','2026-09-24 13:49:38','2026-09-24 13:49:38'),(103,14,'member_moderated','Ley LIn (Super Admin) បានប្តូរស្ថានភាពសមាជិក Chhaly Chhoeng ទៅជា ផ្អាកបណ្តោះអាសន្ន','App\\Models\\User',53,'175.100.79.42','2026-09-24 13:49:39','2026-09-24 13:49:39'),(104,14,'member_moderated','Ley LIn (Super Admin) បានប្តូរស្ថានភាពសមាជិក Chhaly Chhoeng ទៅជា បានផ្អាក','App\\Models\\User',53,'175.100.79.42','2026-09-24 13:49:40','2026-09-24 13:49:40'),(105,14,'member_moderated','Ley LIn (Super Admin) បានប្តូរស្ថានភាពសមាជិក Chhaly Chhoeng ទៅជា កំពុងដំណើរការ','App\\Models\\User',53,'175.100.79.42','2026-09-24 13:50:04','2026-09-24 13:50:04'),(106,14,'member_moderated','Ley LIn (Super Admin) បានប្តូរស្ថានភាពសមាជិក Phat Sophea ទៅជា បានផ្អាក','App\\Models\\User',54,'175.100.79.42','2026-09-24 13:50:05','2026-09-24 13:50:05'),(107,14,'member_moderated','Ley LIn (Super Admin) បានប្តូរស្ថានភាពសមាជិក Phat Sophea ទៅជា កំពុងដំណើរការ','App\\Models\\User',54,'175.100.79.42','2026-09-24 13:50:07','2026-09-24 13:50:07'),(108,14,'book_returned','Ley LIn (Super Admin) បានទទួលសៀវភៅ \"HSK3\" សងវិញពីសមាជិក Phat Sophea (ស្ថានភាព: Good, ប្រាក់ពិន័យ: $0.00)','App\\Models\\Borrow',13,'175.100.79.42','2026-09-24 13:51:37','2026-09-24 13:51:37'),(109,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-24_194441_auto.zip)',NULL,NULL,NULL,'2026-09-24 13:55:05','2026-09-24 13:55:05'),(110,14,'backup_created','ទិន្នន័យត្រូវបានបម្រុងទុកចូលទៅក្នុង Server Drive D ដោយជោគជ័យ! (ឯកសារ: elibrary_backup_2026-09-24_205507_manual.zip, ទំហំ: 3.13 MB)',NULL,NULL,NULL,'2026-09-24 13:55:08','2026-09-24 13:55:08'),(111,14,'backup_deleted','បានលុបឯកសារបម្រុងទុកចេញពី Server Drive D ដោយជោគជ័យ។ (elibrary_backup_2026-09-24_205508_auto.zip)',NULL,NULL,NULL,'2026-09-24 13:55:14','2026-09-24 13:55:14');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_notifications`
--

DROP TABLE IF EXISTS `admin_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `book_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reservation_request',
  `recipient_role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_notifications_user_id_foreign` (`user_id`),
  KEY `admin_notifications_book_id_foreign` (`book_id`),
  KEY `admin_notifications_reservation_id_foreign` (`reservation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_notifications`
--

LOCK TABLES `admin_notifications` WRITE;
/*!40000 ALTER TABLE `admin_notifications` DISABLE KEYS */;
INSERT INTO `admin_notifications` VALUES (5,9,9,3,'book_issued','user','📖 បានទទួល (Book Received)','លោកអ្នកបានទទួលសៀវភៅ \"IT\" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ 30 Sep 2026។','http://127.0.0.1:8000/borrows',0,'2026-09-16 11:14:11','2026-09-16 11:14:11'),(2,9,9,3,'reservation_approved','user','🎉 បានកក់ជោគជ័យ (Reservation Approved)','Admin បានទទួលការកក់សៀវភៅ \"IT\" របស់អ្នករួចរាល់ហើយ! សូមមកទទួលយកនៅបណ្ណាល័យមុនម៉ោង 18/09/2026 17:07 (កូដកក់៖ RES-FB5659)។','http://127.0.0.1:8000/reservations',1,'2026-09-16 10:07:33','2026-09-16 10:08:18'),(3,7,6,1,'reservation_approved','user','🎉 បានកក់ជោគជ័យ (Reservation Approved)','Admin បានទទួលការកក់សៀវភៅ \"Atomic Habits\" របស់អ្នករួចរាល់ហើយ! សូមមកទទួលយកនៅបណ្ណាល័យមុនម៉ោង 18/09/2026 17:25 (កូដកក់៖ RES-921D76)។','http://127.0.0.1:8000/reservations',0,'2026-09-16 10:25:30','2026-09-16 10:25:30'),(4,4,4,2,'reservation_force_cancelled','user','⚠️ ការកក់ត្រូវបានលុបចោលពិសេស (Reservation Force Cancelled)','ប្រធានគ្រប់គ្រងបានសម្រេចលុបចោលការកក់សៀវភៅ \"Zero to One: Notes on Startups\" របស់អ្នក ដោយសារមូលហេតុ៖ Member failed to pick up within pickup deadline។ សូមអភ័យទោសចំពោះការរអាក់រអួល!','http://127.0.0.1:8000/reservations',0,'2026-09-16 10:26:39','2026-09-16 10:26:39'),(14,16,2,6,'reservation_approved','user','🎉 បានកក់ជោគជ័យ (Reservation Approved)','Admin បានទទួលការកក់សៀវភៅ \"Designing Data-Intensive Applications: The Big Ideas Behind Reliable Systems\" របស់អ្នករួចរាល់ហើយ! សូមមកទទួលយកនៅបណ្ណាល័យមុនម៉ោង 26/09/2026 00:12 (កូដកក់៖ RES-6C84D9)។','http://127.0.0.1:8000/reservations',0,'2026-09-23 17:12:33','2026-09-23 17:12:33'),(15,16,2,6,'book_issued','user','📖 បានទទួល (Book Received)','លោកអ្នកបានទទួលសៀវភៅ \"Designing Data-Intensive Applications: The Big Ideas Behind Reliable Systems\" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ 08 Oct 2026។','http://127.0.0.1:8000/borrows',0,'2026-09-23 17:12:51','2026-09-23 17:12:51'),(8,3,8,4,'reservation_approved','user','🎉 បានកក់ជោគជ័យ (Reservation Approved)','Admin បានទទួលការកក់សៀវភៅ \"The Great Gatsby\" របស់អ្នករួចរាល់ហើយ! សូមមកទទួលយកនៅបណ្ណាល័យមុនម៉ោង 18/09/2026 18:29 (កូដកក់៖ RES-425B22)។','http://127.0.0.1:8000/reservations',1,'2026-09-16 11:29:31','2026-09-16 11:29:37'),(24,54,44,10,'reservation_request','admin','សំណើសុំកក់សៀវភៅថ្មី (New Reservation Request)','សមាជិក Phat Sophea (LIB-2026-551) បានស្នើសុំកក់សៀវភៅ \"The Essence of Software Engineering\" (កូដកក់៖ RES-74FA9C)។','https://consultants-assets-creation-yellow.trycloudflare.com/reservations?status=Pending',0,'2026-09-24 13:42:47','2026-09-24 13:42:47'),(22,54,48,9,'reservation_request','admin','សំណើសុំកក់សៀវភៅថ្មី (New Reservation Request)','សមាជិក Phat Sophea (LIB-2026-551) បានស្នើសុំកក់សៀវភៅ \"HSK3\" (កូដកក់៖ RES-00363D)។','https://consultants-assets-creation-yellow.trycloudflare.com/reservations?status=Pending',1,'2026-09-24 13:31:28','2026-09-24 13:33:58'),(23,54,48,9,'reservation_approved','user','🎉 បានកក់ជោគជ័យ (Reservation Approved)','Admin បានទទួលការកក់សៀវភៅ \"HSK3\" របស់អ្នករួចរាល់ហើយ! សូមមកទទួលយកនៅបណ្ណាល័យមុនម៉ោង 26/09/2026 20:41 (កូដកក់៖ RES-00363D)។','https://consultants-assets-creation-yellow.trycloudflare.com/reservations',0,'2026-09-24 13:41:29','2026-09-24 13:41:29'),(18,37,48,7,'reservation_approved','user','🎉 បានកក់ជោគជ័យ (Reservation Approved)','Admin បានទទួលការកក់សៀវភៅ \"HSK3\" របស់អ្នករួចរាល់ហើយ! សូមមកទទួលយកនៅបណ្ណាល័យមុនម៉ោង 26/09/2026 00:26 (កូដកក់៖ RES-78412A)។','http://127.0.0.1:8000/reservations',1,'2026-09-23 17:26:13','2026-09-23 17:26:23'),(19,37,48,7,'book_issued','user','📖 បានទទួល (Book Received)','លោកអ្នកបានទទួលសៀវភៅ \"HSK3\" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ 08 Oct 2026។','http://127.0.0.1:8000/borrows',0,'2026-09-23 17:26:36','2026-09-23 17:26:36'),(21,54,11,8,'reservation_request','admin','សំណើសុំកក់សៀវភៅថ្មី (New Reservation Request)','សមាជិក Phat Sophea (LIB-2026-551) បានស្នើសុំកក់សៀវភៅ \"ភាសាអង់គ្លេស\" (កូដកក់៖ RES-2A303C)។','https://consultants-assets-creation-yellow.trycloudflare.com/reservations?status=Pending',0,'2026-09-24 13:30:58','2026-09-24 13:30:58'),(25,54,44,10,'reservation_approved','user','🎉 បានកក់ជោគជ័យ (Reservation Approved)','Admin បានទទួលការកក់សៀវភៅ \"The Essence of Software Engineering\" របស់អ្នករួចរាល់ហើយ! សូមមកទទួលយកនៅបណ្ណាល័យមុនម៉ោង 26/09/2026 20:42 (កូដកក់៖ RES-74FA9C)។','https://consultants-assets-creation-yellow.trycloudflare.com/reservations',0,'2026-09-24 13:42:58','2026-09-24 13:42:58'),(26,54,44,10,'book_issued','user','📖 បានទទួល (Book Received)','លោកអ្នកបានទទួលសៀវភៅ \"The Essence of Software Engineering\" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ 08 Oct 2026។','https://consultants-assets-creation-yellow.trycloudflare.com/borrows',0,'2026-09-24 13:44:41','2026-09-24 13:44:41'),(27,54,44,10,'book_issued_admin','admin','✅ ទទួលរួចហើយ (Book Handed Over)','សមាជិក Phat Sophea បានមកទទួលសៀវភៅ \"The Essence of Software Engineering\" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ 08 Oct 2026។','https://consultants-assets-creation-yellow.trycloudflare.com/borrows',0,'2026-09-24 13:44:41','2026-09-24 13:44:41'),(28,54,39,11,'reservation_request','admin','សំណើសុំកក់សៀវភៅថ្មី (New Reservation Request)','សមាជិក Phat Sophea (LIB-2026-551) បានស្នើសុំកក់សៀវភៅ \"ក្រមរដ្ឋប្បវេណី និងនីតិវិធីរដ្ឋប្បវេណីកម្ពុជា (ការបកស្រាយ និងការអនុវត្ត)\" (កូដកក់៖ RES-4C6272)។','https://consultants-assets-creation-yellow.trycloudflare.com/reservations?status=Pending',0,'2026-09-24 13:50:44','2026-09-24 13:50:44'),(29,54,41,12,'reservation_request','admin','សំណើសុំកក់សៀវភៅថ្មី (New Reservation Request)','សមាជិក Phat Sophea (LIB-2026-551) បានស្នើសុំកក់សៀវភៅ \"វិធីសាស្ត្រស្រាវជ្រាវបែបវិទ្យាសាស្ត្រ សម្រាប់ការសរសេរសារណាបញ្ចប់ការសិក្សា\" (កូដកក់៖ RES-B16BEA)។','https://consultants-assets-creation-yellow.trycloudflare.com/reservations?status=Pending',0,'2026-09-24 13:50:51','2026-09-24 13:50:51'),(30,54,41,12,'reservation_approved','user','🎉 បានកក់ជោគជ័យ (Reservation Approved)','Admin បានទទួលការកក់សៀវភៅ \"វិធីសាស្ត្រស្រាវជ្រាវបែបវិទ្យាសាស្ត្រ សម្រាប់ការសរសេរសារណាបញ្ចប់ការសិក្សា\" របស់អ្នករួចរាល់ហើយ! សូមមកទទួលយកនៅបណ្ណាល័យមុនម៉ោង 26/09/2026 20:51 (កូដកក់៖ RES-B16BEA)។','https://consultants-assets-creation-yellow.trycloudflare.com/reservations',0,'2026-09-24 13:51:05','2026-09-24 13:51:05'),(31,54,11,8,'reservation_approved','user','🎉 បានកក់ជោគជ័យ (Reservation Approved)','Admin បានទទួលការកក់សៀវភៅ \"ភាសាអង់គ្លេស\" របស់អ្នករួចរាល់ហើយ! សូមមកទទួលយកនៅបណ្ណាល័យមុនម៉ោង 26/09/2026 20:51 (កូដកក់៖ RES-2A303C)។','https://consultants-assets-creation-yellow.trycloudflare.com/reservations',0,'2026-09-24 13:51:15','2026-09-24 13:51:15'),(32,54,11,8,'book_issued','user','📖 បានទទួល (Book Received)','លោកអ្នកបានទទួលសៀវភៅ \"ភាសាអង់គ្លេស\" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ 08 Oct 2026។','https://consultants-assets-creation-yellow.trycloudflare.com/borrows',0,'2026-09-24 13:51:19','2026-09-24 13:51:19'),(33,54,11,8,'book_issued_admin','admin','✅ ទទួលរួចហើយ (Book Handed Over)','សមាជិក Phat Sophea បានមកទទួលសៀវភៅ \"ភាសាអង់គ្លេស\" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ 08 Oct 2026។','https://consultants-assets-creation-yellow.trycloudflare.com/borrows',0,'2026-09-24 13:51:19','2026-09-24 13:51:19'),(34,54,48,9,'book_issued','user','📖 បានទទួល (Book Received)','លោកអ្នកបានទទួលសៀវភៅ \"HSK3\" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ 08 Oct 2026។','https://consultants-assets-creation-yellow.trycloudflare.com/borrows',0,'2026-09-24 13:51:23','2026-09-24 13:51:23'),(35,54,48,9,'book_issued_admin','admin','✅ ទទួលរួចហើយ (Book Handed Over)','សមាជិក Phat Sophea បានមកទទួលសៀវភៅ \"HSK3\" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ 08 Oct 2026។','https://consultants-assets-creation-yellow.trycloudflare.com/borrows',1,'2026-09-24 13:51:23','2026-09-24 15:04:13'),(36,54,49,13,'reservation_request','admin','សំណើសុំកក់សៀវភៅថ្មី (New Reservation Request)','សមាជិក Phat Sophea (LIB-2026-551) បានស្នើសុំកក់សៀវភៅ \"កុំព្យូទ័ររដ្ជបាល\" (កូដកក់៖ RES-3DDA26)។','https://consultants-assets-creation-yellow.trycloudflare.com/reservations?status=Pending',0,'2026-09-24 13:58:11','2026-09-24 13:58:11');
/*!40000 ALTER TABLE `admin_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backups`
--

DROP TABLE IF EXISTS `backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disk_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL DEFAULT '0',
  `backup_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  `tables_count` int NOT NULL DEFAULT '0',
  `files_count` int NOT NULL DEFAULT '0',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backups_created_by_foreign` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backups`
--

LOCK TABLES `backups` WRITE;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
INSERT INTO `backups` VALUES (25,'elibrary_backup_2026-09-24_205507_manual.zip','D:\\E-Library-Backups\\elibrary_backup_2026-09-24_205507_manual.zip',3278291,'manual','success',18,8,NULL,14,'2026-09-24 13:55:08','2026-09-24 13:55:07','2026-09-24 13:55:08');
/*!40000 ALTER TABLE `backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `books` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isbn` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `subcategory` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_level` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_academic_year` tinyint unsigned DEFAULT NULL,
  `recommended_major` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allow_pdf_download` tinyint(1) NOT NULL DEFAULT '0',
  `total_copies` int NOT NULL DEFAULT '1',
  `available_copies` int NOT NULL DEFAULT '1',
  `location_shelf` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Shelf A-1',
  `published_year` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `views_count` bigint unsigned NOT NULL DEFAULT '0',
  `downloads_count` bigint unsigned NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `books_isbn_unique` (`isbn`),
  KEY `books_category_id_foreign` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `books`
--

LOCK TABLES `books` WRITE;
/*!40000 ALTER TABLE `books` DISABLE KEYS */;
INSERT INTO `books` VALUES (1,'Clean Code: A Handbook of Agile Software Craftsmanship','Robert C. Martin','978-0132350884',7,'ឯកសារយោង','បរិញ្ញាបត្រ','Software Engineering','ឆ្នាំទី ៣',3,'Software Engineering (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790015406_6ab177ae27810.jpg','/uploads/pdfs/book_1790015406_6ab177ae27a71.pdf',0,15,11,'Shelf CS-04',2008,'Fundamental principles of professional code architecture, readability, refactoring, unit testing, and maintainable software construction.',1,0,1,'2026-09-16 09:51:53','2026-09-21 11:47:47'),(2,'Designing Data-Intensive Applications: The Big Ideas Behind Reliable Systems','Martin Kleppmann','978-1449373320',7,'ឯកសារយោង','បរិញ្ញាបត្រ','Software Engineering','ឆ្នាំទី ៤',4,'Software Engineering (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790015359_6ab1777fb8cf6.jpg','/uploads/pdfs/book_1790015359_6ab1777fb8f01.pdf',0,8,5,'Shelf CS-02',2017,'The definitive guide for senior software engineers on data storage, distributed systems, replication, partitioning, transactions, and stream processing.',2,0,1,'2026-09-16 09:51:53','2026-09-23 17:29:33'),(11,'ភាសាអង់គ្លេស','ក្រសួងអប់រំ យុវជន និងកីឡា','978-99950-01-01',11,'ភាសាបរទេស','ឧត្តមសិក្សា','ភាសាអង់គ្លេស','ឆ្នាំទី ១',1,NULL,'/uploads/covers/khmer_english.jpg',NULL,0,12,9,'A-101',2024,'សៀវភៅសិក្សាភាសាអង់គ្លេស រៀបចំដោយក្រសួងអប់រំ យុវជន និងកីឡា សម្រាប់ពង្រឹងសមត្ថភាពភាសាអង់គ្លេស និងការប្រាស្រ័យទាក់ទង។',2,0,0,'2026-09-21 04:51:58','2026-09-24 13:51:15'),(4,'Zero to One: Notes on Startups, or How to Build the Future','Peter Thiel, Blake Masters','978-0804139298',2,'ឯកសារយោង','បរិញ្ញាបត្រ','ភាពជាសហគ្រិន','ឆ្នាំទី ៤',4,'General Management (គ្រប់គ្រង)','/uploads/covers/pdf_cover_1790013543_6ab17067513ee.jpg','/uploads/pdfs/book_1790013543_6ab1706751726.pdf',0,8,4,'Shelf BM-02',2014,'Innovation and startup strategy: how university graduates and entrepreneurs move from zero to one by building creative monopolies.',0,0,1,'2026-09-16 09:51:53','2026-09-21 11:34:35'),(5,'A History of Cambodia (4th Edition)','David P. Chandler','978-0813343631',9,'ឯកសារយោង','បរិញ្ញាបត្រ','ប្រវត្តិវិទ្យា','ឆ្នាំទី ១',1,'ទូទៅ (General)','/uploads/covers/pdf_cover_1790015207_6ab176e70f883.jpg','/uploads/pdfs/book_1790015207_6ab176e7122f6.pdf',0,12,9,'Shelf HC-01',2008,'Authoritative university reference on Cambodian civilization, Angkorian era, colonial period, and modern political history.',0,0,1,'2026-09-16 09:51:53','2026-09-21 11:34:35'),(6,'Atomic Habits','James Clear','978-0735211292',2,NULL,NULL,NULL,NULL,NULL,'ទូទៅ (General)','/uploads/covers/pdf_cover_1790015289_6ab17739e41b3.jpg','/uploads/pdfs/book_1790015289_6ab17739e43d1.pdf',0,10,2,'Shelf BM-01',2018,'An easy & proven way to build good habits and break bad ones.',2,0,0,'2026-09-16 09:51:53','2026-09-21 11:47:35'),(7,'Brief Answers to the Big Questions','Stephen Hawking','978-1984819192',5,NULL,NULL,NULL,NULL,1,'វិទ្យាសាស្ត្រ និងគណិត (Science & Mathematics)','/uploads/covers/pdf_cover_1790015251_6ab17713f2a13.jpg','/uploads/pdfs/book_1790015251_6ab17713f2ca8.pdf',0,5,3,'Shelf SM-02',2018,'The world-famous cosmologist and bestselling author leaves us with his final thoughts on the universe\'s biggest questions.',0,0,0,'2026-09-16 09:51:53','2026-09-21 11:27:31'),(8,'The Great Gatsby','F. Scott Fitzgerald','978-0743273565',6,NULL,NULL,NULL,NULL,2,'អក្សរសាស្ត្រ និងភាសា (Literature)','/uploads/covers/pdf_cover_1790015109_6ab176854ded1.jpg','/uploads/pdfs/book_1790015109_6ab176854e117.pdf',0,6,4,'Shelf LN-05',1925,'A portrait of the Jazz Age in all its decadence and excess.',9,0,0,'2026-09-16 09:51:53','2026-09-21 11:43:58'),(49,'កុំព្យូទ័ររដ្ជបាល','ក្រសួង','3',7,NULL,NULL,NULL,NULL,NULL,NULL,'/uploads/covers/pdf_cover_1790014950_6ab175e624faf.jpg','/uploads/pdfs/book_1790014950_6ab175e625251.pdf',0,5,5,'Shelf A-1',2024,NULL,9,0,0,'2026-09-21 11:22:30','2026-09-24 13:58:31'),(44,'The Essence of Software Engineering','Volker Gruhn, Rudiger Striemer','1',7,NULL,NULL,NULL,NULL,3,'Software Engineering','/uploads/covers/pdf_cover_1790009611_6ab1610b777d2.jpg','/uploads/pdfs/book_1790008215_6ab15b97bca50.pdf',0,5,4,'Shelf A-1',2024,NULL,15,0,0,'2026-09-21 09:30:15','2026-09-24 13:42:58'),(12,'ភាសាបារាំង ជីវភាសាទី២','ក្រសួងអប់រំ យុវជន និងកីឡា','978-99950-01-02',11,'ភាសាបរទេស','ឧត្តមសិក្សា','ភាសាបារាំង','ឆ្នាំទី ១',1,NULL,'/uploads/covers/khmer_french_2.jpg',NULL,0,10,8,'A-102',2024,'សៀវភៅសិក្សាគោលភាសាបារាំង ជាភាសាទី២ សម្រាប់បណ្ដុះបណ្ដាលមូលដ្ឋានគ្រឹះ និងវេយ្យាករណ៍បារាំង។',0,0,0,'2026-09-21 04:51:58','2026-09-21 11:34:35'),(13,'ភាសាបារាំង','ក្រសួងអប់រំ យុវជន និងកីឡា','978-99950-01-03',11,'ភាសាបរទេស','ឧត្តមសិក្សា','ភាសាបារាំង','ឆ្នាំទី ២',2,NULL,'/uploads/covers/khmer_french.jpg',NULL,0,8,6,'A-103',2024,'កម្មវិធីសិក្សាភាសាបារាំងស្តង់ដារ សម្រាប់ការសិក្សាស្រាវជ្រាវ និងការប្រាស្រ័យទាក់ទង។',0,0,0,'2026-09-21 04:51:58','2026-09-21 11:34:35'),(14,'សៀវភៅវេយ្យាករណ៍ភាសាខ្មែរ','ទីស្តីការគណៈរដ្ឋមន្ត្រី ក្រុមប្រឹក្សាជាតិភាសាខ្មែរ','978-99950-01-04',11,'ភាសាខ្មែរ','ទូទៅ','ភាសាខ្មែរ','គ្រប់កម្រិត',1,NULL,'/uploads/covers/khmer_grammar.jpg',NULL,0,15,14,'B-201',2024,'សៀវភៅវេយ្យាករណ៍ភាសាខ្មែរ រៀបចំ និងចងក្រងដោយក្រុមប្រឹក្សាជាតិភាសាខ្មែរ នៃទីស្តីការគណៈរដ្ឋមន្ត្រី ជាឯកសារយោងស្តង់ដារ។',0,0,0,'2026-09-21 04:51:58','2026-09-21 11:34:35'),(25,'Database System Concepts (7th Edition)','Abraham Silberschatz, Henry F. Korth, S. Sudarshan','978-0078022159',7,'សៀវភៅគោល','បរិញ្ញាបត្រ','វិទ្យាសាស្ត្រកុំព្យូទ័រ','ឆ្នាំទី ២',2,'Software Engineering (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790014564_6ab17464f2ae4.jpg','/uploads/pdfs/book_1790014565_6ab174650091f.pdf',0,12,8,'Shelf CS-06',2019,'Relational database architecture, SQL, relational calculus, query optimization, concurrency control, indexing, and NoSQL databases.',1,0,0,'2026-09-21 09:12:46','2026-09-23 03:28:59'),(17,'ព្រះពុទ្ធសាសនា និងសង្គមខ្មែរ','ពុទ្ធសាសនបណ្ឌិត្យ','978-99950-02-01',12,'ទស្សនវិជ្ជាសាសនា','ទូទៅ','សាសនា','គ្រប់កម្រិត',1,NULL,'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80',NULL,0,10,9,'R-101',2023,'ការសិក្សាស្រាវជ្រាវអំពីឥទ្ធិពលនៃពុទ្ធសាសនាលើវប្បធម៌ និងការរស់នៅរបស់សង្គមខ្មែរ។',0,0,0,'2026-09-21 05:24:11','2026-09-21 11:34:35'),(24,'Introduction to Algorithms (4th Edition)','Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein (CLRS)','978-0262046305',7,'សៀវភៅគោល','បរិញ្ញាបត្រ','វិទ្យាសាស្ត្រកុំព្យូទ័រ','ឆ្នាំទី ២',2,'Software Engineering (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790014624_6ab174a0b0523.jpg','/uploads/pdfs/book_1790014624_6ab174a0b07f6.pdf',0,14,10,'Shelf CS-05',2022,'The globally standard university textbook on data structures, divide-and-conquer, dynamic programming, greedy algorithms, and graph theory.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(23,'Design Patterns: Elements of Reusable Object-Oriented Software','Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides (GoF)','978-0201633610',7,'សៀវភៅគោល','បរិញ្ញាបត្រ','Software Engineering','ឆ្នាំទី ៤',4,'Software Engineering (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790014698_6ab174eaf4071.jpg','/uploads/pdfs/book_1790014699_6ab174eb000f8.pdf',0,12,9,'Shelf CS-03',2019,'Classic architectural patterns (Creational, Structural, Behavioral) essential for university graduation projects and enterprise software development.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(22,'Software Engineering: A Practitioner\'s Approach (9th Edition)','Roger S. Pressman, Bruce R. Maxim','978-1259872976',7,'សៀវភៅគោល','បរិញ្ញាបត្រ','Software Engineering','ឆ្នាំទី ៤',4,'Software Engineering (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790014776_6ab17538e683e.jpg','/uploads/pdfs/book_1790014776_6ab17538e6b12.pdf',0,10,8,'Shelf CS-01',2020,'The comprehensive university textbook on software engineering lifecycle, agile methods, architectural design, testing, and capstone project execution for graduating seniors.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(26,'The Web Application Hacker\'s Handbook: Finding and Exploiting Security Flaws','Dafydd Stuttard, Marcus Pinto','978-1118026472',7,'ឯកសារយោង','បរិញ្ញាបត្រ','សន្តិសុខសាយប័រ','ឆ្នាំទី ៤',4,'Cybersecurity (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790014480_6ab174106a57b.jpg','/uploads/pdfs/book_1790014480_6ab174106a8b5.pdf',0,7,5,'Shelf SEC-01',2021,'Advanced penetration testing, OWASP Top 10 vulnerabilities, authentication bypass, SQL injection, and defensive coding for university cybersecurity students.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(27,'Computer Networking: A Top-Down Approach (8th Edition)','James Kurose, Keith Ross','978-0136681557',7,'សៀវភៅគោល','បរិញ្ញាបត្រ','បណ្តាញកុំព្យូទ័រ','ឆ្នាំទី ៣',3,'Network Engineering (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790014424_6ab173d8eba9d.jpg','/uploads/pdfs/book_1790014424_6ab173d8ebd04.pdf',0,11,7,'Shelf NET-01',2021,'Top-down approach to Internet protocols, application layer, TCP/UDP transport, IP routing, SDN, and network security architectures.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(28,'Artificial Intelligence: A Modern Approach (4th Edition)','Stuart Russell, Peter Norvig','978-0134610993',7,'សៀវភៅគោល','បរិញ្ញាបត្រ','បញ្ញាសិប្បនិម្មិត (AI)','ឆ្នាំទី ៤',4,'Artificial Intelligence (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790014306_6ab17362a8f4e.jpg','/uploads/pdfs/book_1790014306_6ab17362a91e3.pdf',0,9,6,'Shelf AI-01',2020,'The leading authority on artificial intelligence: intelligent agents, search algorithms, logic, probabilistic reasoning, machine learning, and deep learning.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(29,'Python Data Science Handbook: Essential Tools for Working with Data','Jake VanderPlas','978-1491912058',7,'ឯកសារយោង','បរិញ្ញាបត្រ','វិទ្យាសាស្ត្រទិន្នន័យ','ឆ្នាំទី ៣',3,'Data Science (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790014207_6ab172ff97353.jpg','/uploads/pdfs/book_1790014207_6ab172ff97545.pdf',0,10,8,'Shelf DS-01',2022,'Comprehensive guide to IPython, NumPy, Pandas, Matplotlib, and Scikit-Learn for university data science analysis.',0,0,0,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(30,'Strategic Management: Concepts and Cases (17th Edition)','Fred R. David, Forest R. David','978-0135199978',2,'សៀវភៅគោល','បរិញ្ញាបត្រ','គ្រប់គ្រងពាណិជ្ជកម្ម','ឆ្នាំទី ៤',4,'General Management (គ្រប់គ្រង)','/uploads/covers/pdf_cover_1790014165_6ab172d56db2a.jpg','/uploads/pdfs/book_1790014165_6ab172d56ddae.pdf',0,12,9,'Shelf BM-01',2020,'Senior university capstone text covering vision/mission, external/internal audit, strategy formulation, SWOT, BCG matrix, and competitive strategy implementation.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(31,'Marketing Management (16th Global Edition)','Philip Kotler, Kevin Lane Keller, Alexander Chernev','978-0135887158',2,'សៀវភៅគោល','បរិញ្ញាបត្រ','ទីផ្សារ (Marketing)','ឆ្នាំទី ៤',4,'Marketing (គ្រប់គ្រង)','/uploads/covers/pdf_cover_1790014364_6ab1739cac72a.jpg','/uploads/pdfs/book_1790014364_6ab1739cac95d.pdf',0,14,10,'Shelf MKT-01',2021,'The world\'s foremost marketing text: digital marketing transformation, consumer behavior analysis, brand positioning, omnichannel management, and global metrics.',2,0,1,'2026-09-21 09:12:46','2026-09-22 08:52:15'),(32,'The Lean Startup: How Constant Innovation Creates Radically Successful Businesses','Eric Ries','978-0307887894',2,'ឯកសារយោង','បរិញ្ញាបត្រ','ភាពជាសហគ្រិន','ឆ្នាំទី ៣',3,'International Business (គ្រប់គ្រង)','https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=400&auto=format&fit=crop&q=80',NULL,0,10,6,'Shelf BM-03',2011,'Validated learning, Minimum Viable Product (MVP), Build-Measure-Learn feedback loops, and agility in enterprise launch.',0,0,0,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(33,'Financial Accounting and Reporting (20th Edition)','Barry Elliott, Jamie Elliott','978-1292399928',13,'សៀវភៅគោល','បរិញ្ញាបត្រ','គណនេយ្យ','ឆ្នាំទី ៣',3,'Accounting (សេដ្ឋកិច្ច)','/uploads/covers/pdf_cover_1790014116_6ab172a482de5.jpg','/uploads/pdfs/book_1790014116_6ab172a483051.pdf',0,12,9,'Shelf ACC-01',2022,'Comprehensive textbook on IFRS standards, balance sheet recognition, cash flows, consolidated financial statements, and auditing basics.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(34,'Corporate Finance (5th Global Edition)','Jonathan Berk, Peter DeMarzo','978-1292304151',13,'សៀវភៅគោល','បរិញ្ញាបត្រ','ហិរញ្ញវត្ថុ','ឆ្នាំទី ៤',4,'Banking & Finance (សេដ្ឋកិច្ច)','/uploads/covers/pdf_cover_1790014064_6ab17270c6e26.jpg','/uploads/pdfs/book_1790014064_6ab17270c70c8.pdf',0,11,8,'Shelf FIN-01',2020,'Capital budgeting, cost of capital, valuation models, capital structure, dividend policy, derivatives, and risk management for graduating finance seniors.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(35,'Principles of Economics (9th Edition)','N. Gregory Mankiw','978-0357038314',13,'សៀវភៅគោល','បរិញ្ញាបត្រ','សេដ្ឋកិច្ចវិទ្យា','ឆ្នាំទី ១',1,'Economics (សេដ្ឋកិច្ច)','/uploads/covers/pdf_cover_1790013999_6ab1722f94cb5.jpg','/uploads/pdfs/book_1790013999_6ab1722f94fbc.pdf',0,18,14,'Shelf ECO-01',2020,'The premier introduction to microeconomics and macroeconomics: market supply and demand, elasticity, monetary and fiscal policy, and international trade.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(36,'Structural Analysis (10th Edition in SI Units)','R. C. Hibbeler','978-1292247137',14,'សៀវភៅគោល','បរិញ្ញាបត្រ','វិស្វកម្មសំណង់ស៊ីវិល','ឆ្នាំទី ៤',4,'Civil Engineering (វិស្វកម្ម)','/uploads/covers/pdf_cover_1790013791_6ab1715faffe8.jpg','/uploads/pdfs/book_1790013791_6ab1715fb023c.pdf',0,10,7,'Shelf ENG-01',2020,'Analysis of statically determinate and indeterminate beams, trusses, and rigid frames using classical displacement and force methods for senior civil engineers.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(37,'Design of Reinforced Concrete (10th Edition)','Jack C. McCormac, Russell H. Brown','978-1118879108',14,'សៀវភៅគោល','បរិញ្ញាបត្រ','វិស្វកម្មសំណង់ស៊ីវិល','ឆ្នាំទី ៤',4,'Civil Engineering (វិស្វកម្ម)','/uploads/covers/pdf_cover_1790013689_6ab170f99ef39.jpg','/uploads/pdfs/book_1790013689_6ab170f99f209.pdf',0,9,6,'Shelf ENG-02',2021,'Theory and design of reinforced concrete structural elements: beams, columns, slabs, footings, and retaining walls according to ACI Code.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(38,'Electrical Engineering: Principles and Applications (7th Edition)','Allan R. Hambley','978-0134484143',14,'សៀវភៅគោល','បរិញ្ញាបត្រ','វិស្វកម្មអគ្គិសនី','ឆ្នាំទី ៣',3,'Electrical Engineering (វិស្វកម្ម)','/uploads/covers/pdf_cover_1790013471_6ab1701fa87d2.jpg','/uploads/pdfs/book_1790013471_6ab1701fab32f.pdf',0,8,5,'Shelf ELEC-01',2018,'Core concepts of DC/AC circuits, transient response, diodes, transistors, operational amplifiers, and digital logic circuits for university engineering majors.',0,0,0,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(39,'ក្រមរដ្ឋប្បវេណី និងនីតិវិធីរដ្ឋប្បវេណីកម្ពុជា (ការបកស្រាយ និងការអនុវត្ត)','គណៈមេធាវីនៃព្រះរាជាណាចក្រកម្ពុជា','978-99950-88-01-2',15,'សៀវភៅគោល','បរិញ្ញាបត្រ','នីតិសាស្ត្រ','ឆ្នាំទី ៣',3,'នីតិសាស្ត្រ (Law)','/uploads/covers/pdf_cover_1790013312_6ab16f804b813.jpg','/uploads/pdfs/book_1790013312_6ab16f804ba5f.pdf',0,15,11,'Shelf LAW-01',2022,'ឯកសារយោង និងសៀវភៅសិក្សាគោលស្តីពីក្រមរដ្ឋប្បវេណី បុគ្គល សិទ្ធិលើទ្រព្យសម្បត្តិ កាតព្វកិច្ច កិច្ចសន្យា និងនីតិវិធីតុលាការរដ្ឋប្បវេណីកម្រិតឧត្តមសិក្សា។',2,0,1,'2026-09-21 09:12:46','2026-09-24 13:50:41'),(40,'ក្រមព្រហ្មទណ្ឌ និងនីតិវិធីព្រហ្មទណ្ឌនៃព្រះរាជាណាចក្រកម្ពុជា','ក្រសួងយុត្តិធម៌ និងសាកលវិទ្យាល័យភូមិន្ទនីតិសាស្ត្រ','978-99950-88-02-9',15,'សៀវភៅគោល','បរិញ្ញាបត្រ','នីតិសាស្ត្រ','ឆ្នាំទី ៣',3,'នីតិសាស្ត្រ (Law)','/uploads/covers/pdf_cover_1790013227_6ab16f2b555d1.jpg','/uploads/pdfs/book_1790013227_6ab16f2b55d7b.pdf',0,14,10,'Shelf LAW-02',2023,'គោលការណ៍ច្បាប់ព្រហ្មទណ្ឌ ទោសានុទោស ការស៊ើបអង្កេត ការចោទប្រកាន់ និងនីតិវិធីជំនុំជម្រះក្តីនៅតុលាការកម្ពុជា។',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(41,'វិធីសាស្ត្រស្រាវជ្រាវបែបវិទ្យាសាស្ត្រ សម្រាប់ការសរសេរសារណាបញ្ចប់ការសិក្សា','បណ្ឌិតសភាបណ្ឌិត និងអ្នកស្រាវជ្រាវឧត្តមសិក្សា','978-99950-99-01-8',8,'សារណាស្រាវជ្រាវ','បរិញ្ញាបត្រ','វិធីសាស្ត្រស្រាវជ្រាវ','ឆ្នាំទី ៤',4,'Software Engineering (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790013073_6ab16e917a4e5.jpg','/uploads/pdfs/book_1790013073_6ab16e917ab13.pdf',0,20,15,'Shelf RS-01',2023,'សៀវភៅណែនាំស្តង់ដារសម្រាប់និស្សិតឆ្នាំទី៤ ក្នុងការរៀបចំសំណើសុំស្រាវជ្រាវ ការប្រមូលទិន្នន័យ ការវិភាគបែបបរិមាណ និងគុណភាព និងការសរសេរសារណាបញ្ចប់ការសិក្សា។',2,0,1,'2026-09-21 09:12:46','2026-09-24 13:51:05'),(42,'Academic Writing and Critical Thinking for University Students','Stephen Bailey','978-1138048744',11,'ឯកសារយោង','បរិញ្ញាបត្រ','ភាសាអង់គ្លេស','ឆ្នាំទី ១',1,'English Literature (ភាសាបរទេស)','/uploads/covers/pdf_cover_1790012908_6ab16decbd563.jpg','/uploads/pdfs/book_1790012908_6ab16decbd834.pdf',0,15,12,'Shelf ENG-01',2021,'Essential guide for undergraduate students on constructing academic arguments, literature reviews, citations (APA, Harvard), and scholarly prose.',0,0,1,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(43,'Calculus: Early Transcendentals (9th Edition)','James Stewart, Daniel Clegg, Saleem Watson','978-1337613927',5,'សៀវភៅគោល','បរិញ្ញាបត្រ','គណិតវិទ្យាឧត្តមសិក្សា','ឆ្នាំទី ១',1,'Software Engineering (វិទ្យាសាស្ត្រកុំព្យូទ័រ)','/uploads/covers/pdf_cover_1790012646_6ab16ce684909.jpg','/uploads/pdfs/book_1790012646_6ab16ce684dba.pdf',0,16,12,'Shelf MATH-01',2020,'The world-standard undergraduate calculus textbook: limits, derivatives, integrals, multivariable calculus, and vector analysis for STEM students.',0,0,0,'2026-09-21 09:12:46','2026-09-21 11:34:35'),(48,'HSK3','Jiang liping','2',11,NULL,NULL,NULL,NULL,NULL,NULL,'/uploads/covers/pdf_cover_1790012765_6ab16d5d8ceb3.jpg','/uploads/pdfs/book_1790012765_6ab16d5d8d139.pdf',0,5,5,'Shelf A-1',2024,NULL,6,0,0,'2026-09-21 10:46:05','2026-09-24 13:51:37');
/*!40000 ALTER TABLE `books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `borrows`
--

DROP TABLE IF EXISTS `borrows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `borrows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `book_id` bigint unsigned NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `fine_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `fine_paid` tinyint(1) NOT NULL DEFAULT '1',
  `fine_waived` tinyint(1) NOT NULL DEFAULT '0',
  `fine_waived_reason` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waived_by` bigint unsigned DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Borrowed',
  `book_condition` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Good',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `borrows_user_id_foreign` (`user_id`),
  KEY `borrows_book_id_foreign` (`book_id`),
  KEY `borrows_waived_by_foreign` (`waived_by`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrows`
--

LOCK TABLES `borrows` WRITE;
/*!40000 ALTER TABLE `borrows` DISABLE KEYS */;
INSERT INTO `borrows` VALUES (1,3,1,'2026-09-11','2026-09-25','2026-09-21',0.00,1,0,NULL,NULL,'Returned','Good',NULL,'2026-09-16 09:51:53','2026-09-21 03:01:00'),(2,4,6,'2026-08-29','2026-09-12','2026-09-21',4.50,1,0,NULL,NULL,'Returned','Good',NULL,'2026-09-16 09:51:53','2026-09-21 03:00:55'),(3,5,2,'2026-09-14','2026-09-28','2026-09-21',0.00,1,0,NULL,NULL,'Returned','Good',NULL,'2026-09-16 09:51:53','2026-09-21 03:00:52'),(4,6,5,'2026-08-22','2026-09-05','2026-09-16',-5.88,1,0,NULL,NULL,'Returned','Good',NULL,'2026-09-16 09:51:53','2026-09-16 11:13:13'),(5,7,3,'2026-09-02','2026-09-14','2026-09-15',0.00,1,1,'Medical emergency with official doctor certificate',1,'Returned','Good','Book returned in good condition','2026-09-16 09:51:53','2026-09-16 10:26:02'),(6,3,8,'2026-08-17','2026-08-31','2026-08-30',0.00,1,0,NULL,NULL,'Returned','Good','Returned on time','2026-09-16 09:51:53','2026-09-16 09:51:53'),(8,36,10,'2026-09-21','2026-10-05','2026-09-21',0.00,1,0,NULL,NULL,'Returned','Good',NULL,'2026-09-21 04:20:26','2026-09-21 04:20:50'),(7,9,9,'2026-09-16','2026-09-30','2026-09-21',0.00,1,0,NULL,NULL,'Returned','Good',NULL,'2026-09-16 11:14:11','2026-09-21 03:00:48'),(9,16,2,'2026-09-24','2026-10-08','2026-09-24',0.00,1,0,NULL,NULL,'Returned','Good','Fulfilled from Hold: RES-6C84D9','2026-09-23 17:12:51','2026-09-23 17:29:33'),(10,37,48,'2026-09-24','2026-10-08','2026-09-24',0.00,1,0,NULL,NULL,'Returned','Good',NULL,'2026-09-23 17:26:36','2026-09-23 17:29:26'),(11,54,44,'2026-09-24','2026-10-08',NULL,0.00,1,0,NULL,NULL,'Borrowed','Good','Fulfilled from Hold: RES-74FA9C','2026-09-24 13:44:41','2026-09-24 13:44:41'),(12,54,11,'2026-09-24','2026-10-08',NULL,0.00,1,0,NULL,NULL,'Borrowed','Good','Fulfilled from Hold: RES-2A303C','2026-09-24 13:51:19','2026-09-24 13:51:19'),(13,54,48,'2026-09-24','2026-10-08','2026-09-24',0.00,1,0,NULL,NULL,'Returned','Good',NULL,'2026-09-24 13:51:23','2026-09-24 13:51:37');
/*!40000 ALTER TABLE `borrows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (2,'ពាណិជ្ជកម្ម និងការគ្រប់គ្រង','business-management','briefcase','Business Administration, Marketing, Management, Entrepreneurship & Leadership','2026-09-16 09:51:53','2026-09-21 09:12:46'),(12,'សាសនា','religion','sparkles','សាសនា ព្រះពុទ្ធសាសនា និងទស្សនវិជ្ជាសាសនា','2026-09-21 05:24:11','2026-09-21 05:24:11'),(5,'វិទ្យាសាស្ត្រ និងគណិតវិទ្យាឧត្តមសិក្សា','science-math','atom','Calculus, Linear Algebra, Statistics, Applied Physics & Mathematics','2026-09-16 09:51:53','2026-09-21 09:12:46'),(6,'អក្សរសិល្ប៍','literature','book-open','អក្សរសិល្ប៍ខ្មែរ និងប្រលោមលោកបុរាណ-សម័យ','2026-09-21 04:50:55','2026-09-21 04:50:55'),(7,'បច្ចេកវិទ្យា និងវិទ្យាសាស្ត្រកុំព្យូទ័រ','technology','code','Software Engineering, Computer Science, Cybersecurity, AI, Cloud & Data Science','2026-09-21 04:50:55','2026-09-21 09:12:46'),(8,'ស្នាដៃស្រាវជ្រាវ និងសារណាបញ្ចប់ការសិក្សា','general-works','book-open','Research Methodology, Academic Writing, Capstone Theses & Journal Papers','2026-09-21 04:50:55','2026-09-21 09:12:46'),(9,'ប្រវត្តិសាស្ត្រ វប្បធម៌ និងទស្សនវិជ្ជា','geography-history','globe','Cambodian History, Southeast Asian Civilizations, Philosophy & Ethics','2026-09-21 04:50:55','2026-09-21 09:12:46'),(10,'ទស្សនវិជ្ជា និងចិត្តវិទ្យា','philosophy-psychology','brain','ទស្សនវិជ្ជា ចិត្តវិទ្យា និងការអភិវឌ្ឍផ្នត់គំនិត','2026-09-21 04:50:55','2026-09-21 04:50:55'),(11,'ភាសាបរទេស និងទំនាក់ទំនង','languages','translate','Academic English, Business Communication, Chinese, French & Translation','2026-09-21 04:50:55','2026-09-21 09:12:46'),(13,'សេដ្ឋកិច្ច និងហិរញ្ញវត្ថុ','economics-finance','chart-bar','Economics, Accounting, Banking, Corporate Finance & Investment','2026-09-21 09:12:46','2026-09-21 09:12:46'),(14,'វិស្វកម្ម និងស្ថាបត្យកម្ម','engineering','sparkles','Civil Engineering, Electrical Engineering, Architecture & Construction','2026-09-21 09:12:46','2026-09-21 09:12:46'),(15,'នីតិសាស្ត្រ និងវិទ្យាសាស្ត្រនយោបាយ','law','landmark','Civil Law, Criminal Law, Commercial Law, Constitutional Law & International Relations','2026-09-21 09:12:46','2026-09-21 09:12:46');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `membership_payments`
--

DROP TABLE IF EXISTS `membership_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `membership_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `plan_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Cash',
  `reference_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_date` date NOT NULL,
  `start_date` date NOT NULL,
  `expires_at` datetime NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Paid',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `membership_payments_reference_no_unique` (`reference_no`),
  KEY `membership_payments_user_id_foreign` (`user_id`),
  KEY `membership_payments_recorded_by_foreign` (`recorded_by`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `membership_payments`
--

LOCK TABLES `membership_payments` WRITE;
/*!40000 ALTER TABLE `membership_payments` DISABLE KEYS */;
INSERT INTO `membership_payments` VALUES (1,7,'Quarterly',12.00,'ABA KHQR','MEM-2026-001','2026-08-02','2026-08-02','2026-10-31 16:51:53','Paid','Quarterly subscription paid via ABA KHQR at the library counter.',1,'2026-09-16 09:51:53','2026-09-16 09:51:53'),(2,8,'Monthly',5.00,'Cash','MEM-2026-002','2026-08-12','2026-08-12','2026-09-11 16:51:53','Expired','Monthly subscription paid in cash. Currently expired and needs renewal.',1,'2026-09-16 09:51:53','2026-09-16 09:51:53');
/*!40000 ALTER TABLE `membership_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_01_01_000001_create_categories_table',1),(5,'2025_01_01_000002_create_books_table',1),(6,'2025_01_01_000003_create_borrows_table',1),(7,'2025_01_01_000004_create_reservations_table',1),(8,'2025_01_01_000005_add_notes_to_users_table',1),(9,'2025_01_01_000006_enhance_workflow_and_rbac_tables',1),(10,'2026_09_11_100530_create_admin_notifications_table',1),(11,'2026_09_11_103259_add_recipient_role_to_admin_notifications_table',1),(12,'2026_09_11_103302_add_recipient_role_to_admin_notifications_table',1),(13,'2026_09_11_120000_create_settings_table',1),(14,'2026_09_11_131943_add_personal_settings_to_users_table',1),(15,'2026_09_12_000001_create_activity_logs_table',1),(16,'2026_09_12_000002_add_waive_to_borrows_table',1),(17,'2026_09_12_000003_add_views_and_downloads_to_books_table',1),(18,'2026_09_12_090256_add_google_id_to_users_table',1),(19,'2026_09_12_090259_add_google_id_to_users_table',1),(20,'2026_09_16_000001_add_smart_recommendations_and_membership_fees',1),(21,'2026_09_21_181500_add_login_tracking_to_users_table',2),(22,'2026_09_21_192500_add_filter_columns_to_books_table',3),(23,'2026_09_23_000001_create_backups_table',4);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `book_id` bigint unsigned NOT NULL,
  `reservation_date` date NOT NULL,
  `pickup_deadline` datetime DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservations_reservation_code_unique` (`reservation_code`),
  KEY `reservations_user_id_foreign` (`user_id`),
  KEY `reservations_book_id_foreign` (`book_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,'RES-921D76',7,6,'2026-09-15','2026-09-18 17:25:30','Cancelled','First in line for next return | Auto-cancelled: pickup deadline expired.','2026-09-16 09:51:53','2026-09-20 09:37:50'),(2,'RES-9228C8',4,4,'2026-09-13',NULL,'Cancelled','Ready for pick-up at counter | Force cancelled by Manager: Member failed to pick up within pickup deadline','2026-09-16 09:51:53','2026-09-16 10:26:39'),(3,'RES-FB5659',9,9,'2026-09-16','2026-09-18 17:07:33','Fulfilled','Member reservation placed online','2026-09-16 10:05:03','2026-09-16 11:14:11'),(4,'RES-425B22',3,8,'2026-09-16','2026-09-18 18:29:31','Cancelled','Member reservation placed online | Auto-cancelled: pickup deadline expired.','2026-09-16 11:22:28','2026-09-20 09:37:50'),(5,'RES-52685E',36,10,'2026-09-21','2026-09-23 11:19:49','Fulfilled','Member reservation placed online','2026-09-21 04:19:01','2026-09-21 04:20:26'),(6,'RES-6C84D9',16,2,'2026-09-24','2026-09-26 00:12:33','Fulfilled','Member reservation placed online','2026-09-23 17:12:22','2026-09-23 17:12:51'),(7,'RES-78412A',37,48,'2026-09-24','2026-09-26 00:26:13','Fulfilled','Member reservation placed online','2026-09-23 17:25:59','2026-09-23 17:26:36'),(8,'RES-2A303C',54,11,'2026-09-24','2026-09-26 20:51:15','Fulfilled','Member reservation placed online','2026-09-24 13:30:58','2026-09-24 13:51:19'),(9,'RES-00363D',54,48,'2026-09-24','2026-09-26 20:41:29','Fulfilled','Member reservation placed online','2026-09-24 13:31:28','2026-09-24 13:51:23'),(10,'RES-74FA9C',54,44,'2026-09-24','2026-09-26 20:42:58','Fulfilled','Member reservation placed online','2026-09-24 13:42:47','2026-09-24 13:44:41'),(11,'RES-4C6272',54,39,'2026-09-24',NULL,'Pending','Member reservation placed online','2026-09-24 13:50:44','2026-09-24 13:50:44'),(12,'RES-B16BEA',54,41,'2026-09-24','2026-09-26 20:51:05','Approved','Member reservation placed online','2026-09-24 13:50:51','2026-09-24 13:51:05'),(13,'RES-3DDA26',54,49,'2026-09-24',NULL,'Pending','Member reservation placed online','2026-09-24 13:58:11','2026-09-24 13:58:11');
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('9n8ZSJH34BAF3UhddyFVK4QZuqhqeBZ8WrjT44Df',54,'175.100.79.42','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','YToxNTp7czo2OiJfdG9rZW4iO3M6NDA6IjlBWkZvOGVpMU1WaFFlRHh0U1d4WTBtRnFaRGU2YkVlUGhZMUd6Um8iO3M6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjY5OiJodHRwczovL2NvbnN1bHRhbnRzLWFzc2V0cy1jcmVhdGlvbi15ZWxsb3cudHJ5Y2xvdWRmbGFyZS5jb20vc2V0dGluZ3MiO3M6NToicm91dGUiO3M6MTQ6InNldHRpbmdzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NTQ7czoxNToidmlld2VkX3ZpZGVvXzEyIjtiOjE7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo3MDoiaHR0cHM6Ly9jb25zdWx0YW50cy1hc3NldHMtY3JlYXRpb24teWVsbG93LnRyeWNsb3VkZmxhcmUuY29tL2Rhc2hib2FyZCI7fXM6MTQ6InZpZXdlZF9ib29rXzExIjtpOjE3OTAyNTU0MzI7czoxMjoicmVhZF9ib29rXzExIjtpOjE3OTAyNTY2Nzg7czoxNDoidmlld2VkX2Jvb2tfNDgiO2k6MTc5MDI1NjY4NjtzOjEyOiJyZWFkX2Jvb2tfNDgiO2k6MTc5MDI1NjcwNztzOjE0OiJ2aWV3ZWRfYm9va180NCI7aToxNzkwMjU3MzY1O3M6MTQ6InZpZXdlZF9ib29rXzM5IjtpOjE3OTAyNTc4NDE7czoxNDoidmlld2VkX2Jvb2tfNDEiO2k6MTc5MDI1Nzg0OTtzOjE0OiJ2aWV3ZWRfYm9va180OSI7aToxNzkwMjU4Mjg1O3M6MTI6InJlYWRfYm9va180OSI7aToxNzkwMjU4MzExO30=',1790259021),('rfVAeVPG1EJehXd4dvbzMQEG1qq9Ax2LDY747DKR',14,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6InQ1OVB0d2RDSEpybDRmMEZkYjA1VkhJdDY5UUtwQTFKemU2STF0VzkiO3M6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjU1OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vbm90aWZpY2F0aW9ucy91bnJlYWQtc3RyZWFtIjtzOjU6InJvdXRlIjtzOjI3OiJub3RpZmljYXRpb25zLnVucmVhZC1zdHJlYW0iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxNDtzOjE0OiJ2aWV3ZWRfdmlkZW9fMiI7YjoxO3M6MTU6InZpZXdlZF92aWRlb18xMSI7YjoxO3M6MTU6InZpZXdlZF92aWRlb18xNCI7YjoxO3M6MTU6InZpZXdlZF92aWRlb18xNSI7YjoxO3M6NjoibG9jYWxlIjtzOjI6ImttIjtzOjE0OiJ2aWV3ZWRfYm9va180OSI7aToxNzkwMjU1NDE5O30=',1790265513),('H0qnMGQ2NQjOYTRAyOrfdSjZGunKotlnWCZkO2qy',14,'175.100.79.42','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZlRYVG82QlM2dzdvVm1xS2ZNM1pBeE1LVEtxV3FDRzhaREh3Yzh1aCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTQ6Imh0dHBzOi8vY29uc3VsdGFudHMtYXNzZXRzLWNyZWF0aW9uLXllbGxvdy50cnljbG91ZGZsYXJlLmNvbS9hZG1pbi9ub3RpZmljYXRpb25zL3VucmVhZC1zdHJlYW0iO3M6NToicm91dGUiO3M6Mjc6Im5vdGlmaWNhdGlvbnMudW5yZWFkLXN0cmVhbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE0O30=',1790265513);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `group` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'standard_loan_days','14','system','2026-09-16 09:51:53','2026-09-16 09:51:53'),(2,'daily_fine_rate_khr','500','system','2026-09-16 09:51:53','2026-09-16 09:51:53'),(3,'daily_fine_rate','0.125','system','2026-09-16 09:51:53','2026-09-16 09:51:53'),(4,'max_books_student','3','system','2026-09-16 09:51:53','2026-09-16 09:51:53'),(5,'max_books_teacher','5','system','2026-09-16 09:51:53','2026-09-16 09:51:53'),(6,'max_books_general','3','system','2026-09-16 09:51:53','2026-09-16 09:51:53'),(7,'library_name','E-Library Smart Management System','general','2026-09-21 03:16:25','2026-09-21 03:16:25'),(8,'library_name_km','ប្រព័ន្ធគ្រប់គ្រងបណ្ណាល័យឆ្លាតវៃ','general','2026-09-21 03:16:25','2026-09-21 03:16:25'),(9,'library_tagline','Designed for Academic & Research Excellence','general','2026-09-21 03:16:25','2026-09-21 03:16:25'),(10,'library_tagline_km','ពង្រីកចំណេះដឹង និងឧត្តមភាពនៃការស្រាវជ្រាវ','general','2026-09-21 03:16:25','2026-09-21 03:16:25'),(11,'library_email','contact@elibrary.edu.kh','general','2026-09-21 03:16:25','2026-09-21 03:16:25'),(12,'library_phone','012 889 900 / 023 888 999','general','2026-09-21 03:16:25','2026-09-21 03:16:25'),(13,'library_address','SR','general','2026-09-21 03:16:25','2026-09-21 03:16:25'),(14,'library_address_km','សៀមរាប','general','2026-09-21 03:16:25','2026-09-21 03:16:25'),(15,'library_hours','Mon - Fri: 7:30 AM - 6:00 PM | Sat: 8:00 AM - 12:00 PM','general','2026-09-21 03:16:25','2026-09-21 03:16:25'),(16,'library_hours_km','ចន្ទ - សុក្រ: ៧:៣០ ព្រឹក - ៦:០០ ល្ងាច | សៅរ៍: ៨:០០ ព្រឹក - ១២:០០ ថ្ងៃត្រង់','general','2026-09-21 03:16:25','2026-09-21 03:16:25'),(17,'library_website','https://elibrary.edu.kh','general','2026-09-21 03:16:25','2026-09-21 03:16:25'),(18,'library_logo','/images/logo.png','general','2026-09-21 03:16:25','2026-09-23 09:39:03'),(19,'backup_last_run_at','2026-09-24 20:55:08','backup','2026-09-23 02:44:44','2026-09-24 13:55:08'),(20,'backup_last_status','success','backup','2026-09-23 02:44:44','2026-09-23 02:44:44'),(21,'backup_last_type','auto','backup','2026-09-23 02:44:44','2026-09-24 13:55:08'),(22,'backup_last_filename','elibrary_backup_2026-09-24_205508_auto.zip','backup','2026-09-23 02:44:44','2026-09-24 13:55:08');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `member_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Student',
  `academic_year` tinyint unsigned DEFAULT NULL,
  `major` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membership_expires_at` datetime DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `first_login_at` timestamp NULL DEFAULT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `preferred_locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'km',
  `notify_email` tinyint(1) NOT NULL DEFAULT '1',
  `notify_sound` tinyint(1) NOT NULL DEFAULT '1',
  `notify_borrow_reminders` tinyint(1) NOT NULL DEFAULT '1',
  `notify_hold_ready` tinyint(1) NOT NULL DEFAULT '1',
  `role` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_card_id_unique` (`card_id`),
  KEY `users_google_id_index` (`google_id`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (21,'សេង ដានី','st2024001@student.edu.kh',NULL,'ST-2024-001','Student',3,'Data Science (វិទ្យាសាស្ត្រកុំព្យូទ័រ)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80','015 591 395',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: វិទ្យាសាស្ត្រកុំព្យូទ័រ | ជំនាញ: Data Science | ឆ្នាំចូលរៀន: 2024 | ភេទ: ស្រី | DefaultPwd: Stu@2026#06',NULL,'$2y$12$/qCU4rpYtnfh2WPm5cQjo.X4Jn3IXWfpachmfSXU7eRqkY86nELGe',NULL,'2026-09-21 03:29:06','2026-09-21 03:40:53'),(14,'Ley LIn (Super Admin)','admin@elibrary.com',NULL,'LIB-ADM-001','Staff',NULL,NULL,NULL,'Active','2026-09-24 13:34:53','2026-09-21 04:14:55','/storage/uploads/avatars/user_14_1789985958.JPG','069439681','Siem Reap',NULL,'km',1,1,1,1,'admin',NULL,NULL,'$2y$12$SY/ziPLSR9JRvrrfpPwUhO9fvQhbBXlsxQAU.2drR/hgtYuwrBtVW',NULL,'2026-09-21 03:06:42','2026-09-24 13:34:53'),(20,'ហេង ពិសិដ្ឋ','st2023005@student.edu.kh',NULL,'ST-2023-005','Student',4,'Marketing (គ្រប់គ្រង)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80','048 135 731',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: គ្រប់គ្រង | ជំនាញ: Marketing | ឆ្នាំចូលរៀន: 2023 | ភេទ: ប្រុស | DefaultPwd: Stu@2026#05',NULL,'$2y$12$XNd2GnJA5ugCXq2FcUKpYu.iSLE0w5rQkG/gXd6gHM.lLjfvo2m7G',NULL,'2026-09-21 03:29:05','2026-09-21 03:40:53'),(19,'ម៉ៅ សុផល','st2023004@student.edu.kh',NULL,'ST-2023-004','Student',4,'Banking & Finance (សេដ្ឋកិច្ច)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&auto=format&fit=crop&q=80','092 373 262',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: សេដ្ឋកិច្ច | ជំនាញ: Banking & Finance | ឆ្នាំចូលរៀន: 2023 | ភេទ: ស្រី | DefaultPwd: Stu@2026#04',NULL,'$2y$12$NaOU8wYdZ7XC9LUHorS.COyPaFjOx7Y6oY4EAQNs/ug.31/AhsJoq',NULL,'2026-09-21 03:29:05','2026-09-21 03:40:52'),(18,'ឡុង វិបុល','st2023003@student.edu.kh',NULL,'ST-2023-003','Student',4,'Civil Engineering (វិស្វកម្ម)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=150&auto=format&fit=crop&q=80','069 529 869',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: វិស្វកម្ម | ជំនាញ: Civil Engineering | ឆ្នាំចូលរៀន: 2023 | ភេទ: ប្រុស | DefaultPwd: Stu@2026#03',NULL,'$2y$12$q7aK0K39o/9EfqfUlD5n2.zKMJpJIay.r.T0DBIOJdhDi3Q.10bg2',NULL,'2026-09-21 03:29:05','2026-09-21 03:40:52'),(15,'Chiiphea Phat','phatchiiphea@gmail.com','102846842036214749581','LIB-G-9799','General',NULL,NULL,NULL,'Active','2026-09-21 05:13:12','2026-09-21 04:14:55','https://lh3.googleusercontent.com/a/ACg8ocKhFrbhdh596XLKYtxPqkyHDD6lHG_stupL4Z0LFi0v2DPCsWA=s96-c',NULL,NULL,NULL,'km',1,1,1,1,'manager',NULL,NULL,'$2y$12$ulNs9rj6Q4L1KRanM/0sW.JCiDHCGxjHLrxkIezTgWAU/596Z3lXy','DhjNkaubhMpfzP0G6ucJEB5KlT73zfBbhV8uB6LkA23ykzLu60RqvpfhQnuD','2026-09-21 03:15:49','2026-09-21 05:13:12'),(22,'អ៊ុក សំណាង','st2024002@student.edu.kh',NULL,'ST-2024-002','Student',3,'Electrical Engineering (វិស្វកម្ម)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80','070 462 719',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: វិស្វកម្ម | ជំនាញ: Electrical Engineering | ឆ្នាំចូលរៀន: 2024 | ភេទ: ប្រុស | DefaultPwd: Stu@2026#07',NULL,'$2y$12$qHRzuBzskFuRb/oF0U5Qk.ItgFHHR2ojvEisHz7BCk6tKyWfJldti',NULL,'2026-09-21 03:29:06','2026-09-21 03:40:53'),(23,'ជា ស្រីមុំ','st2024003@student.edu.kh',NULL,'ST-2024-003','Student',3,'Accounting (សេដ្ឋកិច្ច)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80','029 415 202',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: សេដ្ឋកិច្ច | ជំនាញ: Accounting | ឆ្នាំចូលរៀន: 2024 | ភេទ: ស្រី | DefaultPwd: Stu@2026#08',NULL,'$2y$12$FufDrjvF.RduwdSJra3xROKJ1VPV2k76AjgQMj0FGUQUk3o4APptu',NULL,'2026-09-21 03:29:06','2026-09-21 03:40:53'),(24,'ឃឹម ចំរើន','st2024004@student.edu.kh',NULL,'ST-2024-004','Student',3,'English Literature (ភាសាបរទេស)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80','029 758 508',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: ភាសាបរទេស | ជំនាញ: English Literature | ឆ្នាំចូលរៀន: 2024 | ភេទ: ប្រុស | DefaultPwd: Stu@2026#09',NULL,'$2y$12$H85SA3PZXzX3q7KJ1O.HQeygkhKa1f/7Bq9BGr.yTmGXanwPsn//W',NULL,'2026-09-21 03:29:06','2026-09-21 03:40:54'),(26,'ព្រំ សុជាតា','st2025001@student.edu.kh',NULL,'ST-2025-001','Student',2,'Artificial Intelligence (វិទ្យាសាស្ត្រកុំព្យូទ័រ)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=150&auto=format&fit=crop&q=80','068 812 707',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: វិទ្យាសាស្ត្រកុំព្យូទ័រ | ជំនាញ: Artificial Intelligence | ឆ្នាំចូលរៀន: 2025 | ភេទ: ស្រី | DefaultPwd: Stu@2026#11',NULL,'$2y$12$Ap7J0Krakp5yNGC/pno.iu5qMh2ayLRvrdiWD5qWthc0do3B8c8Xa',NULL,'2026-09-21 03:29:07','2026-09-21 03:40:54'),(28,'លីន ម៉ានី','st2025003@student.edu.kh',NULL,'ST-2025-003','Student',2,'International Business (គ្រប់គ្រង)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80','075 512 701',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: គ្រប់គ្រង | ជំនាញ: International Business | ឆ្នាំចូលរៀន: 2025 | ភេទ: ស្រី | DefaultPwd: Stu@2026#13',NULL,'$2y$12$VJpddGAiSrlk9jBs/tyT..jm37Hd9.j8zDFDgjX05WUgjZ1o6A97e',NULL,'2026-09-21 03:29:07','2026-09-21 03:40:54'),(29,'ស៊ឹម វឌ្ឍនា','st2025004@student.edu.kh',NULL,'ST-2025-004','Student',2,'Economics (សេដ្ឋកិច្ច)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80','080 176 938',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: សេដ្ឋកិច្ច | ជំនាញ: Economics | ឆ្នាំចូលរៀន: 2025 | ភេទ: ប្រុស | DefaultPwd: Stu@2026#14',NULL,'$2y$12$7XH9Yf/XN.JJ6Rxu8dueRuEivforGQ0mvTdExO4uHpox3/XUAIp8e',NULL,'2026-09-21 03:29:07','2026-09-21 03:40:55'),(30,'ឈិត មុន្នី','st2025005@student.edu.kh',NULL,'ST-2025-005','Student',2,'Cloud Computing (វិទ្យាសាស្ត្រកុំព្យូទ័រ)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&auto=format&fit=crop&q=80','064 734 878',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: វិទ្យាសាស្ត្រកុំព្យូទ័រ | ជំនាញ: Cloud Computing | ឆ្នាំចូលរៀន: 2025 | ភេទ: ស្រី | DefaultPwd: Stu@2026#15',NULL,'$2y$12$l99rOmt/8KRl1Hct8jCycuQgVWktmswudWKm2KBjmdmO//j9yoD4i',NULL,'2026-09-21 03:29:07','2026-09-21 03:40:55'),(31,'នុត សុភ័ក្ត្រ','st2026001@student.edu.kh',NULL,'ST-2026-001','Student',1,'Software Engineering (វិទ្យាសាស្ត្រកុំព្យូទ័រ)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80','070 907 953',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: វិទ្យាសាស្ត្រកុំព្យូទ័រ | ជំនាញ: Software Engineering | ឆ្នាំចូលរៀន: 2026 | ភេទ: ប្រុស | DefaultPwd: Stu@2026#16',NULL,'$2y$12$fnHFJl2m4pNiCWVaHoPhg.gus95Z4TsrSHuEd9/SVTxKN3NJ261n6',NULL,'2026-09-21 03:29:08','2026-09-21 03:40:55'),(33,'យុន រ៉ាវី','st2026003@student.edu.kh',NULL,'ST-2026-003','Student',1,'General Management (គ្រប់គ្រង)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80','025 464 299',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: គ្រប់គ្រង | ជំនាញ: General Management | ឆ្នាំចូលរៀន: 2026 | ភេទ: ប្រុស | DefaultPwd: Stu@2026#18',NULL,'$2y$12$3F24TnkrYlcmjSB8L2eztOmbDNtTuK75/GhRtlrbD9hMYC3ZkrmnK',NULL,'2026-09-21 03:29:08','2026-09-21 03:40:56'),(34,'ឡៀង សុវណ្ណ','st2026004@student.edu.kh',NULL,'ST-2026-004','Student',1,'Finance & Banking (សេដ្ឋកិច្ច)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80','011 719 565',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: សេដ្ឋកិច្ច | ជំនាញ: Finance & Banking | ឆ្នាំចូលរៀន: 2026 | ភេទ: ប្រុស | DefaultPwd: Stu@2026#19',NULL,'$2y$12$ej/zymE4WCuDzqgaZLWcguvWZrTALVn7GvDA/CJ5pzSwfs711SrSC',NULL,'2026-09-21 03:29:08','2026-09-21 03:40:56'),(43,'បណ្ឌិត សុខ សុវណ្ណារ៉ា','lececo001@lecturer.edu.kh',NULL,'LEC-ECO-001','Teacher',NULL,'Macroeconomics & Monetary Policy (សេដ្ឋកិច្ច)',NULL,'Active',NULL,NULL,'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80','023 692 876',NULL,NULL,'km',1,1,1,1,'member','មហាវិទ្យាល័យ: សេដ្ឋកិច្ច | មុខវិជ្ជាបង្រៀន: Macroeconomics & Monetary Policy | ភេទ: ប្រុស | កម្រិតសិទ្ធិ: VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ) | DefaultPwd: Lec@2026#07',NULL,'$2y$12$c6gQA/Kd/q8d4i5IOrf.le6p7wmYekdfwdrfzQOpT8U4JobUGoRgu',NULL,'2026-09-21 04:29:50','2026-09-24 13:00:15'),(54,'Phat Sophea','banbrak2@gmail.com',NULL,'LIB-2026-551','Student',NULL,NULL,NULL,'Active','2026-09-24 12:50:29','2026-09-24 12:50:29','/storage/uploads/avatars/user_54_1790254305.jpg',NULL,NULL,NULL,'km',1,1,1,1,'member',NULL,NULL,'$2y$12$uWwKYw5OLy8KniNlBOpsLuynptsy0lBnci1E57eD5C0sfgke0BHOO',NULL,'2026-09-24 12:50:29','2026-09-24 13:50:07'),(55,'Khouch Poun','khouchpoun@gmail.com','111785619908747282741','LIB-G-1392','General',NULL,NULL,NULL,'Active','2026-09-24 13:47:25','2026-09-24 13:47:25','https://lh3.googleusercontent.com/a/ACg8ocIQKYTqwKiEC993ybc1rh-CzDozf1FHrZ6lrKbjq0ebsNHxu4g=s96-c',NULL,NULL,NULL,'km',1,1,1,1,'member',NULL,NULL,'$2y$12$xGX7UYAs9oM4X9cxTMs8FeQFOqaRQXA7ZCsWj5SBUrknxC0Obdy96','kJmJMpqSiH6HfTs9zaO2WwoD68hUfmHNdK94M5qkKIsrZuC2X9kFA4D0oxg8','2026-09-24 13:47:25','2026-09-24 13:47:25'),(53,'Chhaly Chhoeng','chhalychhoeng@gmail.com','103650440824135731720','LIB-G-3590','Staff',NULL,NULL,NULL,'Active','2026-09-24 11:56:39','2026-09-24 11:56:39','/storage/uploads/avatars/user_53_1790251105.png',NULL,NULL,NULL,'km',1,1,1,1,'manager',NULL,NULL,'$2y$12$s1Dd55YV3xJU93LKxwB85uXkHGoIvvIa5TlZzMP3fuWmyWPC0TGEO','aEwbnY34fL3sUUXc3Kt0bNdfhxxNdyhM1WH7yfacngJ3H9Gg24vUClEbmnNy','2026-09-24 11:56:39','2026-09-24 13:50:04');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'eLibrary'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-24 22:58:36
