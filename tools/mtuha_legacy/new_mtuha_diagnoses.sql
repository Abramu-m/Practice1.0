-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 29. Aug 2025 um 18:09
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `medcom1_0`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `new_mtuha_diagnoses`
--

CREATE TABLE `new_mtuha_diagnoses` (
  `id` int(11) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `new_mtuha_diagnoses`
--

(-- NOTE: original dump used `catname`; updated to `description` for consistency
INSERT INTO `new_mtuha_diagnoses` (`id`, `description`) VALUES
(0, 'Symptoms'),
(4, 'Hepatitis B'),
(5, 'Hepatitis C'),
(6, 'Septicaemia Unspecified'),
(7, 'Diarrhea with no dehydration'),
(8, 'Diarrhea with some dehydration'),
(9, 'Human Africa Trypanosomiasis'),
(10, 'Urogenital Schistosomiasis'),
(11, 'Intestinal Schistosomiasis'),
(12, 'Lympahtic Filariasis - Hydrocele'),
(13, 'Lympahtic Filariasis - Lymphoedema'),
(15, 'Suspected Onchocerciasis (River Blindness)'),
(16, 'Amoebiasis'),
(17, 'STH (Trichuris, Hookworm, Ascaris)'),
(18, 'Sexually Transmitted Infections, Others'),
(19, 'Tuberculosis'),
(20, 'Other Infections and parasite diseases'),
(21, 'Neoplasim/Cancer unspecified'),
(22, 'Anaemia, Mild/Moderate'),
(23, 'Anaemia, Severe'),
(24, 'Iron deficiency anaemia'),
(25, 'Sickle cell Diseases'),
(26, 'Other diseases of blood and blood forming organs'),
(27, 'Diabetes'),
(28, 'Severe wasting with Nutritional Edema'),
(29, 'Severe wasting without Nutritional Edema'),
(30, 'Goiter'),
(31, 'Other thyroid diseases'),
(32, 'Moderate Wasting'),
(33, 'Obesity'),
(34, 'Vitamin A Deficiency'),
(35, 'Other Endocrine, Nutritional and Metabolic diseases'),
(36, 'Schizophrenia'),
(37, 'Epilepsy'),
(38, 'Neurosis'),
(39, 'Substance abuse'),
(40, 'Other Mental and behavioural disorders'),
(41, 'Cerebral palsy'),
(42, 'Other nervous system disorders'),
(43, 'Eye diseases, Infectious'),
(44, 'Eye diseases, Non infectious'),
(45, 'Eye disease, Injuries'),
(46, 'Other eye diseases'),
(47, 'Ear infections, Acute'),
(48, 'Ear infections, Chronic'),
(49, 'Other diseases of ear and mastoid process'),
(50, 'Hypertension'),
(51, 'Rheumatic Heart diseases'),
(52, 'Other diseases of the circulatory system'),
(53, 'Upper Respiratory Infections (Pharyngitis, Tonsillitis, Rhinitis)'),
(54, 'Pneumonia'),
(55, 'Bronchial Asthma '),
(56, 'Acute Bronchitis'),
(57, 'Other respiratory diseases'),
(58, 'Appendicitis'),
(59, 'Hernia, unspecified'),
(60, 'Hemorrhoids'),
(61, 'Peptic Ulcers'),
(62, 'GIT Diseases, other Non-infectious'),
(63, 'Dental Caries'),
(64, 'Periodontal Diseases'),
(65, 'Dental Condition, Other'),
(66, 'Dental Abscess'),
(67, 'Other digestive diseases'),
(68, 'Skin infection, Non-Fungal'),
(69, 'Skin infection, Fungal'),
(70, 'Skin Diseases, Non-infectious'),
(71, 'Fungal Infection, Non-skin'),
(72, 'Other skin and subcutaneous tissue diseases'),
(73, 'Gout'),
(74, 'Osteomyelitis'),
(75, 'Rheumatoid and Joint Diseases'),
(76, 'Cellulitis'),
(77, 'Soft tissue injury'),
(78, 'Other diseases of the Musculoskeletal system'),
(79, 'Urinary Tact Infections {UTI}'),
(80, 'Acute Kidney Disease (AKI)'),
(81, 'Bartholin Abscess'),
(82, 'Benign prostatic Hyperplasia (BPH)'),
(83, 'Nephrotic syndrome (Unspecified)'),
(84, 'Pelvic Inflammatory disease (PID)'),
(85, 'STI Genital Discharge Syndrome (GDS)'),
(86, 'STI Genital Ulcer Disease (GUD)'),
(87, 'Other diseases of the Genital-Urinary and Pelvic system'),
(88, 'Gynaecological diseases, Others'),
(89, 'Cystitis'),
(90, 'Ectopic Pregnancy'),
(91, 'Abortion'),
(92, 'Pregnancy Complications'),
(93, 'Pueperal sepsis'),
(94, 'Cracked Nipple'),
(95, 'Other Pregnancy, Childbirth and Pueperal diseases'),
(96, 'Neonatal sepsis'),
(97, 'Low birth weight and Prematurity'),
(98, 'Birth asphyxia'),
(99, 'Other certain conditions originating in the perinatal period'),
(100, 'Congenital Hydrocephalus'),
(101, 'Other Congenital Disorders'),
(102, 'Burn'),
(103, 'Fractures'),
(104, 'Poisoning'),
(105, 'Dislocations (Unspecified)'),
(106, 'Sprain/Strain'),
(107, 'Alcohol Intoxication'),
(108, 'Snake Bites'),
(109, 'Insect Bites'),
(110, 'Animal Bite (Non Suspected Rabies)'),
(111, 'Other injury, poisoning and certain others'),
(112, 'Road Traffic Accidents'),
(113, 'Assault (Unspecified)'),
(114, 'Drowning'),
(115, 'Other external causes of morbidity'),
(116, 'Waliopewa rufaa'),
(117, 'Waliofariki OPD'),
(118, 'Waliotibiwa kwa Bima ya Afya - NHIF'),
(119, 'Waliotibiwa kwa Bima ya CHF'),
(120, 'Waliotibiwa kwa kutumia Bima nyingine'),
(121, 'Waliotibiwa Pesa taslimu (Cash)'),
(122, 'Waliotibiwa kwa Msamaha');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `new_mtuha_diagnoses`
--
ALTER TABLE `new_mtuha_diagnoses`
  ADD UNIQUE KEY `mtuha_d_description` (`description`),
  ADD UNIQUE KEY `d_id` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
