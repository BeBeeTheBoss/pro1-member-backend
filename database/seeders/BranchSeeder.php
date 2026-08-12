<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $branches = [
            [
                "name" => "Mingalardon",
                "address" => "No. (3/Ka1), No. (3) Main Road, Thitseintkone Ward, Thinkan Kyun Gyi, Mingalardon Township.",
                "contact" => "09777000818, 09777005393, 09777000753",
                "opening_time" => "08:30",
                "closing_time" => "18:00",
                "latitude" => "16.9618627",
                "longitude" => "96.1473842",
                "is_active" => true,
                "region" => "Yangon",
                "township" => "Mingalardon",
            ],
            [
                "name" => "Nay Pyi Taw",
                "address" => "No. (12+23), East of Bawgathiri Car Park, Ah Sint Myint Motor Repair Work Shop, Near Max Petrol Station, Old Yangon-Mandalay Road, Nay Pyi Taw.",
                "contact" => "09777000630",
                "opening_time" => "09:00",
                "closing_time" => "17:00",
                "latitude" => "16.8434375",
                "longitude" => "95.9307963",
                "is_active" => true,
                "region" => "Nay Pyi Taw",
                "township" => "Zeyathiri",
            ],
            [
                "name" => "Danyingone-Shwe Pyi Thar",
                "address" => "No(103/104), Bayintnaung Road, Near Danyingone Market, Insein Township, Yangon.",
                "contact" => "09-777047384, 09-777047385",
                "opening_time" => "08:30",
                "closing_time" => "17:30",
                "latitude" => "165537.3",
                "longitude" => "960515.6",
                "is_active" => true,
                "region" => "Yangon",
                "township" => "Shwepyithar",
            ],
            [
                "name" => "Bago Branch",
                "address" => "No-11 to 20 ,Yangon-Mandalay Road, Ward.8, Oakthar Myo Thit, Bago Township",
                "contact" => "09-799695755",
                "opening_time" => "08:30",
                "closing_time" => "17:30",
                "latitude" => "17.2873568",
                "longitude" => "96.4668391",
                "is_active" => true,
                "region" => "Bago",
                "township" => "Bago",
            ],
            [
                "name" => "Southdagon Branch",
                "address" => "No.523, Pin Lone Road , Corner of Mingalar Thiri street & Industrial Zone street, 23 Ward, Near South Dagon Ka.Nya.Na License Yone , Yangon.",
                "contact" => "09765669885 / 09777048759",
                "opening_time" => "08:30",
                "closing_time" => "17:30",
                "latitude" => "16.8424026",
                "longitude" => "96.2222331",
                "is_active" => true,
                "region" => "Yangon",
                "township" => "Dagon Myothit(South)",
            ],
            [
                "name" => "PRO 1 PLUS (Terminal M)",
                "address" => "No.196, 1st Floor, Terminal M Shopping Mall, No.3 Highway, Yangon Industrial Zone, Mingalardon Township, Yangon.",
                "contact" => "09777047310",
                "opening_time" => "10:00",
                "closing_time" => "19:00",
                "latitude" => "16.93740237",
                "longitude" => "96.15401111",
                "is_active" => true,
                "region" => "Yangon",
                "township" => "Mingalardon",
            ],
            [
                "name" => "Aye Tharyar Branch",
                "address" => "No. (6 to 17), Pyi Htaung Su Road, 5-Quarter, Aye Tharyar, Taunggyi Township,Shan State.",
                "contact" => "09-777003701, 09-777003702,09-777003703",
                "opening_time" => "08:30",
                "closing_time" => "17:30",
                "latitude" => "20.7901531",
                "longitude" => "97.0059105",
                "is_active" => true,
                "region" => "Shan",
                "township" => "Taunggyi",
            ],
            [
                "name" => "LanThit Branch",
                "address" => "No.76,Lanthit Street, Near Arleing Ngar Sint Pagoda,Insein Township, Yangon.",
                "contact" => "01-647730,01-644832,09-5027396",
                "opening_time" => "09:00",
                "closing_time" => "17:00",
                "latitude" => "16.88000000",
                "longitude" => "96.12000000",
                "is_active" => true,
                "region" => "Yangon",
                "township" => "Insein",
            ],
            [
                "name" => "Theik Pan Branch",
                "address" => "No.(Ma-8/6), Theik Pan Road, Between 62 st & 63 st, Chanmyatharsi Township, Mandalay.",
                "contact" => "09-777000942, 09-777000943, 09-777000944",
                "opening_time" => "08:30",
                "closing_time" => "17:30",
                "latitude" => "21.9509415",
                "longitude" => "96.1106199",
                "is_active" => true,
                "region" => "Mandalay",
                "township" => "Chanmyathazi",
            ],
            [
                "name" => "SatSan Branch",
                "address" => "No . 05, Upper Pazundaung Road , Near Star High Fuel Station ,9th Street Bus Stop, Mingalar Taung Nyunt Township, Yangon.",
                "contact" => "01-201849, 01-201850, 09-777000849",
                "opening_time" => "08:30",
                "closing_time" => "17:30",
                "latitude" => "16.791752",
                "longitude" => "96.1855129",
                "is_active" => true,
                "region" => "Yangon",
                "township" => "Pazundaung",
            ],
            [
                "name" => "East Dagon Branch",
                "address" => "No.(1/ka), No(2) Main Road,15 Quarter, Near School of Nursing and Midwifery,East Dagon , Yangon",
                "contact" => "09777000872 / 09777049219 / 09777001472",
                "opening_time" => "08:30",
                "closing_time" => "17:30",
                "latitude" => "16.8764453",
                "longitude" => "96.2288347",
                "is_active" => true,
                "region" => "Yangon",
                "township" => "Dagon Myothit(East)",
            ],
            [
                "name" => "Mawlamyine Branch",
                "address" => "No.(70), Corner of Upper Main Road and A Lal Tan Street, ( Kha Pa Ya Compound) , Maung Ngan Quarter, Mawlamyine.",
                "contact" => "09-777000626, 09-777000828, 09-772862715",
                "opening_time" => "08:30",
                "closing_time" => "17:30",
                "latitude" => "16.4588703",
                "longitude" => "97.6285277",
                "is_active" => true,
                "region" => "Mon",
                "township" => "Mawlamyine",
            ],
            [
                "name" => "Tampawaddy Branch",
                "address" => "No. (489/490), Corner of Lanthit Street & Shwe San Kaing Pagoda, ( Kha Pa Ya Compound ) Tampawady Quarter, Chanmyatharsi Township, Mandalay.",
                "contact" => "09-777000925 , 09-777000929 , 09-777000717",
                "opening_time" => "08:30",
                "closing_time" => "17:30",
                "latitude" => "21.937033",
                "longitude" => "96.0751269",
                "is_active" => true,
                "region" => "Mandalay",
                "township" => "Chanmyathazi",
            ],
            [
                "name" => "Hlaing Tharyar Branch",
                "address" => "No ( 4 / 5 ) , Corner of Yangon - Pathein Road & Yangon - Twan Tay Road, Ahtwin Padan , Hlaing Thar Yar Township , Yangon",
                "contact" => "09-777002840 , 09-777002850 , 09-777002844, 09-777002855",
                "opening_time" => "08:30",
                "closing_time" => "17:30",
                "latitude" => "16.8717689",
                "longitude" => "96.0364098",
                "is_active" => true,
                "region" => "Yangon",
                "township" => "Hlaingtharyar",
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }

    }
}
