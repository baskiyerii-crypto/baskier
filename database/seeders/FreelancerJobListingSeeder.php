<?php

namespace Database\Seeders;

use App\Models\FreelancerJobListing;
use App\Models\User;
use Illuminate\Database\Seeder;

class FreelancerJobListingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereNotNull('id')->pluck('id')->toArray();
        if (empty($users)) {
            return;
        }

        $jobs = [
            [
                'category' => 'logo',
                'title' => 'Matbaa firması için logo tasarımı',
                'description' => "Kurumsal bir matbaa firması için modern ve akılda kalıcı bir logo tasarımı arıyoruz. Renk paleti: lacivert ve turuncu tonları. Vektör formatında teslim edilmesi gerekiyor.",
                'budget_min' => 1500,
                'budget_max' => 3500,
            ],
            [
                'category' => 'brochure',
                'title' => 'Katalog ve broşür tasarımı',
                'description' => "Ürün kataloğu ve 3 sayfalık broşür tasarımı. Mevcut kurumsal kimliğe uyumlu olmalı. InDesign veya benzeri programda çalışılacak.",
                'budget_min' => 800,
                'budget_max' => 2000,
            ],
            [
                'category' => 'digital',
                'title' => 'E-ticaret sitesi banner ve sosyal medya görselleri',
                'description' => "Web sitemiz ve sosyal medya hesaplarımız için kampanya görselleri. Aylık 10–15 görsel. Canva veya Photoshop kullanılabilir.",
                'budget_min' => 500,
                'budget_max' => 1200,
            ],
            [
                'category' => 'other',
                'title' => 'Tabela ve dükkan cephesi tasarımı',
                'description' => "Kuaför salonu için tabela ve cephe tasarımı. Işıklı tabela ölçüleri: 2x0,8 m. Çizim üzerinden üretim yapılacak.",
                'budget_min' => 2000,
                'budget_max' => 5000,
            ],
            [
                'category' => 'wordpress',
                'title' => 'Küçük işletme için kurumsal web sitesi',
                'description' => "Matbaa firmamız için 5–6 sayfalık WordPress sitesi. Hakkımızda, Hizmetler, Referanslar, İletişim. Tema önerisi ve kurulum dahil.",
                'budget_min' => 3000,
                'budget_max' => 7000,
            ],
            [
                'category' => 'logo',
                'title' => 'Cafe için logo ve menü tasarımı',
                'description' => "Yeni açılacak bir cafe için sıcak ve davetkar bir logo ile menü kartı tasarımı. Minimal ve okunaklı olmalı.",
                'budget_min' => 1000,
                'budget_max' => 2500,
            ],
            [
                'category' => 'brochure',
                'title' => 'Davetiye ve kartvizit seti',
                'description' => "Düğün davetiyesi, teşekkür kartı ve eşleşen kartvizit tasarımı. Yaklaşık 200 adet baskıya uygun dosya teslimi.",
                'budget_min' => 600,
                'budget_max' => 1500,
            ],
            [
                'category' => 'digital',
                'title' => 'Instagram hikaye şablonları (10 adet)',
                'description' => "Markamız için kullanacağımız hikaye şablonları. Sabit format, sadece metin değişecek. 10 farklı layout.",
                'budget_min' => 400,
                'budget_max' => 900,
            ],
        ];

        foreach ($jobs as $i => $data) {
            FreelancerJobListing::create([
                'user_id' => $users[$i % count($users)],
                'category' => $data['category'],
                'title' => $data['title'],
                'description' => $data['description'],
                'budget_min' => $data['budget_min'],
                'budget_max' => $data['budget_max'],
                'status' => 'open',
            ]);
        }
    }
}
