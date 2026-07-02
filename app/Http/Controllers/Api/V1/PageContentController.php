<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

/**
 * Web’deki Gizlilik / Kullanım / Hakkımızda ile aynı metinler.
 */
class PageContentController extends Controller
{
    public function show(string $slug)
    {
        $pages = [
            'privacy' => [
                'title' => 'Gizlilik politikası',
                'paragraphs' => [
                    'BaskıYeri olarak kişisel verilerinizi 6698 sayılı KVKK kapsamında işleriz. Hesap bilgileriniz sipariş ve teklif süreçlerinin yürütülmesi için kullanılır; üçüncü taraflarla yalnızca yasal zorunluluk veya açık rızanız dahilinde paylaşılır.',
                    'Çerezler ve oturum verileri, güvenli giriş ve alışveriş deneyimi için saklanır. Haklarınız: bilgi talebi, düzeltme, silme ve itiraz — info@baskiyeri.com üzerinden bize ulaşabilirsiniz.',
                    'Bu metin özet bilgilendirme amaçlıdır; yasal danışmanlık yerine geçmez.',
                ],
            ],
            'terms' => [
                'title' => 'Kullanım koşulları',
                'paragraphs' => [
                    'BaskıYeri bir pazaryeri platformudur. Satıcılar ile müşteriler arasındaki ürün, hizmet ve teklif ilişkilerinde taraflar kendi sorumluluklarındadır. Platform, komisyon ve hizmet bedellerini ilan ettiği ölçüde hizmet verir.',
                    'Siparişler ve teklif seçimleri sistem kayıtlarına göre yürütülür. İptal ve iade koşulları ilgili satıcı ile müşteri arasında veya platform kurallarında belirtilen şekilde uygulanır.',
                    'Hesabınızın güvenliğinden siz sorumlusunuz. Kötüye kullanım tespitinde hesap askıya alınabilir.',
                    'Koşullar güncellenebilir; güncel metin bu sayfada yayımlanır.',
                ],
            ],
            'about' => [
                'title' => 'Hakkımızda',
                'paragraphs' => [
                    'BaskıYeri; matbaa, reklam, tabela, kırtasiye, promosyon ve freelancer hizmetlerinin tek çatı altında buluştuğu çok satıcılı bir pazaryeridir. Müşteriler ürün satın alabilir veya teklif talebi ile projelerini paylaşabilir; satıcılar ve uzmanlar işlerini büyütür.',
                    'Sorularınız için info@baskiyeri.com',
                ],
            ],
        ];

        if (! isset($pages[$slug])) {
            abort(404);
        }

        return response()->json(array_merge(['slug' => $slug], $pages[$slug]));
    }
}
