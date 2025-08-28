<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LegacyDrugsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Legacy drugs data from the drugs table
        $legacyDrugs = [
            // Tablets and Capsules (dtype = 1)
            [
                'did' => 1,
                'ddescription' => 'Acyclovir 200 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 300,
                'ducost' => 73.33,
                'dosage' => '200 mg'
            ],
            [
                'did' => 2,
                'ddescription' => 'Albendazole 400 mg/pack',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1450,
                'ducost' => 33,
                'dosage' => '400 mg'
            ],
            [
                'did' => 3,
                'ddescription' => 'Amlodipine 10 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '10 mg'
            ],
            [
                'did' => 4,
                'ddescription' => 'Amlodipine 5 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 5970,
                'ducost' => 25,
                'dosage' => '5 mg'
            ],
            [
                'did' => 5,
                'ddescription' => 'Amoxicilline + Clavulanic Acid 625 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 120,
                'ducost' => 133.33,
                'dosage' => '625 mg'
            ],
            [
                'did' => 6,
                'ddescription' => 'Amoxicilline 500 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 4000,
                'ducost' => 55,
                'dosage' => '500 mg'
            ],
            [
                'did' => 7,
                'ddescription' => 'Aspirin 75 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 3100,
                'ducost' => 16,
                'dosage' => '75 mg'
            ],
            [
                'did' => 8,
                'ddescription' => 'Atenolol 100 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '100 mg'
            ],
            [
                'did' => 9,
                'ddescription' => 'Atenolol 50 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1970,
                'ducost' => 40,
                'dosage' => '50 mg'
            ],
            [
                'did' => 10,
                'ddescription' => 'Atorvastatin 20 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 90,
                'ducost' => 300,
                'dosage' => '20 mg'
            ],
            [
                'did' => 11,
                'ddescription' => 'Benzyl Penicillin 1 MU vial',
                'dtype' => 1,
                'dunit' => 26,
                'dqty' => 100,
                'ducost' => 200,
                'dosage' => '1 MU'
            ],
            [
                'did' => 12,
                'ddescription' => 'Captopril 25 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 4700,
                'ducost' => 20,
                'dosage' => '25 mg'
            ],
            [
                'did' => 13,
                'ddescription' => 'Carbamazepine 200 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1900,
                'ducost' => 50,
                'dosage' => '200 mg'
            ],
            [
                'did' => 14,
                'ddescription' => 'Cephalexine 500 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 100,
                'ducost' => 110,
                'dosage' => '500 mg'
            ],
            [
                'did' => 15,
                'ddescription' => 'Chloroquine 150 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '150 mg'
            ],
            [
                'did' => 16,
                'ddescription' => 'Chloroquine + Proguanil (Savarine)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 200,
                'ducost' => 233.33,
                'dosage' => null
            ],
            [
                'did' => 17,
                'ddescription' => 'Chlorpheniramine Maleate (CPM) 4 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 6700,
                'ducost' => 13,
                'dosage' => '4 mg'
            ],
            [
                'did' => 18,
                'ddescription' => 'Chlorpromazine 100 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '100 mg'
            ],
            [
                'did' => 19,
                'ddescription' => 'Chlorpromazine 25 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 2500,
                'ducost' => 18,
                'dosage' => '25 mg'
            ],
            [
                'did' => 20,
                'ddescription' => 'Ciprofloxacine 500 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1850,
                'ducost' => 100,
                'dosage' => '500 mg'
            ],
            [
                'did' => 21,
                'ddescription' => 'Clotrimazole 100 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 200,
                'ducost' => 100,
                'dosage' => '100 mg'
            ],
            [
                'did' => 22,
                'ddescription' => 'Co-Trimoxazole (480 mg)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 7000,
                'ducost' => 13.5,
                'dosage' => '480 mg'
            ],
            [
                'did' => 23,
                'ddescription' => 'Co-Trimoxazole Forte (960 mg)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 3100,
                'ducost' => 25,
                'dosage' => '960 mg'
            ],
            [
                'did' => 24,
                'ddescription' => 'Diazepam 10mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '10 mg'
            ],
            [
                'did' => 47,
                'ddescription' => 'Diazepam 5mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 570,
                'ducost' => 100,
                'dosage' => '5 mg'
            ],
            [
                'did' => 48,
                'ddescription' => 'Diclofenac 50 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 500,
                'ducost' => 16.5,
                'dosage' => '50 mg'
            ],
            [
                'did' => 49,
                'ddescription' => 'Diclofenac SR 100mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '100 mg'
            ],
            [
                'did' => 50,
                'ddescription' => 'Diclofenac + Paracetamol (Diclopar/Doulfenac) 50/500 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1800,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 51,
                'ddescription' => 'Diclofenac + PCM + Chlorzoxazone 250mg/50mg/325mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 54,
                'ddescription' => 'Digoxin 0,25 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1510,
                'ducost' => 128,
                'dosage' => '0.25 mg'
            ],
            [
                'did' => 55,
                'ddescription' => 'Dihydroartesemisin + Piperaquine 40/320 mg (Duo Cotecxin)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 110,
                'ducost' => 855.56,
                'dosage' => null
            ],
            [
                'did' => 56,
                'ddescription' => 'Diltiazem 60 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '60 mg'
            ],
            [
                'did' => 57,
                'ddescription' => 'Ephedrine 30 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 1,
                'dosage' => '30 mg'
            ],
            [
                'did' => 58,
                'ddescription' => 'Ergotamine 2 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '2 mg'
            ],
            [
                'did' => 59,
                'ddescription' => 'Erythromycine 250 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 3300,
                'ducost' => 77,
                'dosage' => '250 mg'
            ],
            [
                'did' => 60,
                'ddescription' => 'Febuxostat ((Uloric, Goturic, Feburic, Adenuric, Atenuric) 20 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 61,
                'ddescription' => 'Febuxostat ((Uloric, Goturic, Feburic, Adenuric, Atenuric) 40 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 62,
                'ddescription' => 'Ferrous and Folic Acid 200/5 mg (Fefol)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 3700,
                'ducost' => 50,
                'dosage' => '200 mg'
            ],
            [
                'did' => 63,
                'ddescription' => 'Fluconazole 150 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1120,
                'ducost' => 250,
                'dosage' => '150 mg'
            ],
            [
                'did' => 64,
                'ddescription' => 'Folic Acid 5 mg ',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 3200,
                'ducost' => 25,
                'dosage' => '5 mg'
            ],
            [
                'did' => 66,
                'ddescription' => 'Furosemide (Frusemide) 40 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 6400,
                'ducost' => 22,
                'dosage' => '40 mg'
            ],
            [
                'did' => 67,
                'ddescription' => 'Gabapentin 300 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 90,
                'ducost' => 0,
                'dosage' => '300 mg'
            ],
            [
                'did' => 68,
                'ddescription' => 'Ginseng + Multivitamine + Minerals (Ginsomin)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 400,
                'dosage' => null
            ],
            [
                'did' => 69,
                'ddescription' => 'Glibenclamide 5 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 5170,
                'ducost' => 26,
                'dosage' => '5 mg'
            ],
            [
                'did' => 70,
                'ddescription' => 'Gliclazide 80 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '80 mg'
            ],
            [
                'did' => 71,
                'ddescription' => 'Glimepiride 1 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '1 mg'
            ],
            [
                'did' => 72,
                'ddescription' => 'Glipizide 2,5 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '2.5 mg'
            ],
            [
                'did' => 73,
                'ddescription' => 'Griseofulvine 500 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 600,
                'ducost' => 220,
                'dosage' => '500 mg'
            ],
            [
                'did' => 74,
                'ddescription' => 'GTN 0,5 mg (Glyceryltrinitrate)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '0.5 mg'
            ],
            [
                'did' => 75,
                'ddescription' => 'Haloperidol 1,5 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 3800,
                'ducost' => 60,
                'dosage' => '1.5 mg'
            ],
            [
                'did' => 76,
                'ddescription' => 'Hydrochlorothiazide 25 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '25 mg'
            ],
            [
                'did' => 77,
                'ddescription' => 'Hyoscine (Buscopan) 10 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1500,
                'ducost' => 103,
                'dosage' => '10 mg'
            ],
            [
                'did' => 78,
                'ddescription' => 'Ibuprofen 200 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1900,
                'ducost' => 24,
                'dosage' => '200 mg'
            ],
            [
                'did' => 79,
                'ddescription' => 'Ibuprofen and Paracetamol 200/500 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 80,
                'ddescription' => 'Imipramine 25 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '25 mg'
            ],
            [
                'did' => 81,
                'ddescription' => 'Indomethacin 25 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => '25 mg'
            ],
            [
                'did' => 82,
                'ddescription' => 'Isosorbide dinitrate 10 mg (ISDN)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1100,
                'ducost' => 132,
                'dosage' => '10 mg'
            ],
            [
                'did' => 83,
                'ddescription' => 'Isosorbide Mononitrate 10 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 84,
                'ddescription' => 'Itraconazole 100 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 85,
                'ddescription' => 'Ketoprofen 50 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 86,
                'ddescription' => 'Ketotifen 1 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 87,
                'ddescription' => 'Lansoprazole 30 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 494,
                'ducost' => 166.67,
                'dosage' => null
            ],
            [
                'did' => 88,
                'ddescription' => 'Levodopa + Carbidopa 250/25 mg (Syndopa)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 100,
                'ducost' => 300,
                'dosage' => null
            ],
            [
                'did' => 91,
                'ddescription' => 'Lisinopril 10 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 222,
                'ducost' => 110.71,
                'dosage' => null
            ],
            [
                'did' => 92,
                'ddescription' => 'Lisinopril 5 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 93,
                'ddescription' => 'Loperamide 4 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 94,
                'ddescription' => 'Losartan + Hydrochlorothiazide 50/12,5 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1732,
                'ducost' => 126.67,
                'dosage' => null
            ],
            [
                'did' => 95,
                'ddescription' => 'Losartan 50 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1966,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 97,
                'ddescription' => 'Mefenamic Acid 250 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 3200,
                'ducost' => 45,
                'dosage' => null
            ],
            [
                'did' => 98,
                'ddescription' => 'Meloxicam 15 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 700,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 99,
                'ddescription' => 'Mesalazine',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 100,
                'ddescription' => 'Mesterolone 25 mg (Proviron)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 538,
                'ducost' => 730,
                'dosage' => null
            ],
            [
                'did' => 101,
                'ddescription' => 'Metformin + Glibenclamide 400/2,5 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 102,
                'ddescription' => 'Metformin + Glibenclamide 500/5 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 103,
                'ddescription' => 'Metformin + Glimepiride 500/1mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 298.33,
                'dosage' => null
            ],
            [
                'did' => 104,
                'ddescription' => 'Metformin + Glimepiride 500/2 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 140,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 105,
                'ddescription' => 'Metformin 500 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 33200,
                'ducost' => 25.5,
                'dosage' => null
            ],
            [
                'did' => 106,
                'ddescription' => 'Methyldopa 250 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 303,
                'ducost' => 125,
                'dosage' => null
            ],
            [
                'did' => 107,
                'ddescription' => 'Metoclopramide 10 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 200,
                'ducost' => 80,
                'dosage' => null
            ],
            [
                'did' => 108,
                'ddescription' => 'Metoprolol 50 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 109,
                'ddescription' => 'Metronidazole 200 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 6900,
                'ducost' => 19.5,
                'dosage' => null
            ],
            [
                'did' => 110,
                'ddescription' => 'Magnesium Trisilicate (Mg tris) 20 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 4500,
                'ducost' => 14.5,
                'dosage' => null
            ],
            [
                'did' => 111,
                'ddescription' => 'Multivitamine',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 400,
                'ducost' => 40,
                'dosage' => null
            ],
            [
                'did' => 112,
                'ddescription' => 'Naproxen 500 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 113,
                'ddescription' => 'Nifedipine 20 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 5400,
                'ducost' => 30,
                'dosage' => null
            ],
            [
                'did' => 114,
                'ddescription' => 'Nitrofurantoin 100 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 700,
                'ducost' => 48,
                'dosage' => null
            ],
            [
                'did' => 115,
                'ddescription' => 'Norethisterone 5 mg (Primolut N)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 120,
                'ducost' => 533.33,
                'dosage' => null
            ],
            [
                'did' => 116,
                'ddescription' => 'Norfloxacine + Tinidazole (NORT) 400/600 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 600,
                'ducost' => 155,
                'dosage' => null
            ],
            [
                'did' => 117,
                'ddescription' => 'Norfloxacine 400 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 118,
                'ddescription' => 'Nystatin 500,000 IU',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 119,
                'ddescription' => 'Ofloxacine 200 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 83,
                'dosage' => null
            ],
            [
                'did' => 120,
                'ddescription' => 'Omeprazole 20 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 39400,
                'ducost' => 28,
                'dosage' => null
            ],
            [
                'did' => 121,
                'ddescription' => 'Glucosamine 500 mg + Chondroitin 400 mg (Osteomin/Ostegen)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 90,
                'ducost' => 400,
                'dosage' => null
            ],
            [
                'did' => 122,
                'ddescription' => 'Paracetamol (PCM/PCA) 500 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 19300,
                'ducost' => 17.5,
                'dosage' => null
            ],
            [
                'did' => 125,
                'ddescription' => 'Penicillin 250 mg (Pen V)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 3000,
                'ducost' => 88,
                'dosage' => null
            ],
            [
                'did' => 126,
                'ddescription' => 'Phenobarbital 30 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 6380,
                'ducost' => 30,
                'dosage' => null
            ],
            [
                'did' => 127,
                'ddescription' => 'Piroxicam 20 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1500,
                'ducost' => 28,
                'dosage' => null
            ],
            [
                'did' => 128,
                'ddescription' => 'Praziquantel 600 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 200,
                'ducost' => 330,
                'dosage' => null
            ],
            [
                'did' => 129,
                'ddescription' => 'Prednisolone 5 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 14780,
                'ducost' => 19,
                'dosage' => null
            ],
            [
                'did' => 130,
                'ddescription' => 'Pregabalin 75 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 460,
                'ducost' => 250,
                'dosage' => null
            ],
            [
                'did' => 131,
                'ddescription' => 'Promethazine 25 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 50,
                'ducost' => 29,
                'dosage' => null
            ],
            [
                'did' => 133,
                'ddescription' => 'Quinine 300 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 134,
                'ddescription' => 'Ramipril 10 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 135,
                'ddescription' => 'Ranitidine 150 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 136,
                'ddescription' => 'Salbutamol 4 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1200,
                'ducost' => 14.5,
                'dosage' => null
            ],
            [
                'did' => 137,
                'ddescription' => 'Secnidazole 1000 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 60,
                'ducost' => 475,
                'dosage' => null
            ],
            [
                'did' => 138,
                'ddescription' => 'Secnidazole 2000 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 1,
                'dosage' => null
            ],
            [
                'did' => 139,
                'ddescription' => 'Simvastatin 20 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 140,
                'ddescription' => 'SP Fansidar (Sulphadoxine & Pyrimethamine)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 1,
                'dosage' => null
            ],
            [
                'did' => 141,
                'ddescription' => 'SP Metakelvine (Sulphamethoxyyrazine & Pyrimethamine 500/25 mg)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 500,
                'dosage' => null
            ],
            [
                'did' => 142,
                'ddescription' => 'Spironolactone 25 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 3550,
                'ducost' => 120,
                'dosage' => null
            ],
            [
                'did' => 143,
                'ddescription' => 'Tamsulosine 0,4 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 2735,
                'ducost' => 350,
                'dosage' => null
            ],
            [
                'did' => 144,
                'ddescription' => 'Telmisartan 40 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 60,
                'ducost' => 300,
                'dosage' => null
            ],
            [
                'did' => 145,
                'ddescription' => 'Telmisartan + Hydrochlorothiazide ',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 500,
                'dosage' => null
            ],
            [
                'did' => 146,
                'ddescription' => 'Terbinafine 250 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 466.67,
                'dosage' => null
            ],
            [
                'did' => 147,
                'ddescription' => 'Testosterone 40 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 148,
                'ddescription' => 'Thyroxine 50 mcg (Levothyroxine)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 149,
                'ddescription' => 'Tinidazole 500 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 12400,
                'ducost' => 73.75,
                'dosage' => null
            ],
            [
                'did' => 150,
                'ddescription' => 'Torsemide 10 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 151,
                'ddescription' => 'Tramadol 50 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1700,
                'ducost' => 40,
                'dosage' => null
            ],
            [
                'did' => 153,
                'ddescription' => 'Verapamil 40 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 500,
                'ducost' => 130,
                'dosage' => null
            ],
            [
                'did' => 154,
                'ddescription' => 'Vitamin A',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 155,
                'ddescription' => 'Vitamin B complex',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 1400,
                'ducost' => 5,
                'dosage' => null
            ],
            [
                'did' => 157,
                'ddescription' => 'Vitamin C (Ascorbic Acid)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 90,
                'dosage' => null
            ],
            [
                'did' => 158,
                'ddescription' => 'Vitamin D',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 159,
                'ddescription' => 'Vitamin E',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ]
        ];

        // Insert medications based on legacy drugs data
        foreach ($legacyDrugs as $drug) {
            // Extract generic name and strength from description
            $genericName = $this->extractGenericName($drug['ddescription']);
            $strength = $this->extractStrength($drug['ddescription']);
            
            // Get formulation ID (most tablets/capsules will be tablets)
            $formulation_id = $this->getFormulationId($drug['dtype']);
            
            // Get dispensing unit ID (most will be tablets)
            $dispensing_unit_id = $this->getDispensingUnitId($drug['dunit']);
            
            // Determine category (all medications go to general medicines category)
            $category_id = 1; // Assuming category 1 is for general medicines
            
            $medicationData = [
                'generic_name' => $genericName,
                'brand_name' => null, // Not specified in legacy data
                'strength' => $strength,
                'formulation_id' => $formulation_id,
                'dispensing_unit_id' => $dispensing_unit_id,
                'description' => $drug['ddescription'],
                'category_id' => $category_id,
                'stock_quantity' => $drug['dqty'],
                'reorder_level' => max(10, $drug['dqty'] * 0.1), // 10% of current stock as reorder level
                'maximum_stock_level' => $drug['dqty'] * 3, // Triple current stock as maximum for tablets
                'requires_prescription' => $this->requiresPrescription($drug['ddescription']),
                'is_controlled' => $this->isControlledSubstance($drug['ddescription']),
                'storage_conditions' => $this->getStorageConditions($drug['ddescription']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ];

            DB::table('medications')->insert($medicationData);
        }

        $this->command->info('Successfully migrated ' . count($legacyDrugs) . ' medications from legacy drugs table.');
    }

    /**
     * Extract generic name from drug description
     */
    private function extractGenericName($description)
    {
        // Remove dosage information and brand names in parentheses
        $name = preg_replace('/\s*\d+([.,]\d+)?\s*(mg|mcg|g|ml|iu|mu)\b.*$/i', '', $description);
        $name = preg_replace('/\s*\([^)]*\)\s*/', ' ', $name);
        $name = trim($name);
        
        // Clean up common combinations
        $name = str_replace([' + ', '+'], ' / ', $name);
        
        return $name ?: 'Unknown';
    }

    /**
     * Extract strength from drug description
     */
    private function extractStrength($description)
    {
        // Look for dosage patterns
        if (preg_match('/(\d+([.,]\d+)?)\s*(mg|mcg|g|ml|iu|mu)\b/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]);
        }
        
        // Look for combination strengths like 50/500 mg
        if (preg_match('/(\d+\/\d+([.,]\d+)?)\s*(mg|mcg|g|ml)\b/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]);
        }
        
        return null;
    }

    /**
     * Get formulation ID based on drug type
     */
    private function getFormulationId($dtype)
    {
        switch ($dtype) {
            case 1: return 1; // Tablet
            case 2: return 2; // Capsule  
            case 25: return 3; // Syrup/Liquid
            case 26: return 5; // Injection
            case 24: return 7; // Cream/Ointment
            case 22: return 10; // Other/Inhaler
            default: return 1; // Default to Tablet
        }
    }

    /**
     * Get dispensing unit ID based on drug unit
     */
    private function getDispensingUnitId($dunit)
    {
        switch ($dunit) {
            case 1: return 8; // Tablet
            case 2: return 9; // Capsule
            case 25: return 5; // Milliliter (for syrups)
            case 26: return 10; // Ampoule (for injections)
            case 24: return 14; // Tube (for creams)
            case 22: return 16; // Inhaler
            case 30: return 15; // Patch/Suppository
            default: return 8; // Default to Tablet
        }
    }

    /**
     * Determine if medication requires prescription
     */
    private function requiresPrescription($description)
    {
        $prescriptionRequired = [
            'antibiotics', 'antibiotic', 'amoxicillin', 'ciprofloxacin', 'erythromycin',
            'metronidazole', 'doxycycline', 'cephalexin', 'penicillin', 'norfloxacin',
            'controlled', 'diazepam', 'tramadol', 'morphine', 'codeine',
            'antipsychotic', 'haloperidol', 'chlorpromazine',
            'cardiovascular', 'digoxin', 'atenolol', 'amlodipine', 'lisinopril',
            'diabetes', 'metformin', 'glibenclamide', 'insulin'
        ];

        $description = strtolower($description);
        foreach ($prescriptionRequired as $keyword) {
            if (strpos($description, $keyword) !== false) {
                return true;
            }
        }

        return false; // Default to non-prescription
    }

    /**
     * Determine if medication is a controlled substance
     */
    private function isControlledSubstance($description)
    {
        $controlledSubstances = [
            'tramadol', 'morphine', 'codeine', 'diazepam', 'lorazepam',
            'midazolam', 'ketamine', 'fentanyl', 'pethidine', 'alprazolam'
        ];

        $description = strtolower($description);
        foreach ($controlledSubstances as $substance) {
            if (strpos($description, $substance) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine storage conditions for medication
     */
    private function getStorageConditions($description)
    {
        $description = strtolower($description);
        
        if (strpos($description, 'capsule') !== false) {
            return 'Store at room temperature, protect from moisture';
        } elseif (strpos($description, 'tablet') !== false) {
            return 'Store in dry place at room temperature';
        } else {
            return 'Store at room temperature';
        }
    }
}
