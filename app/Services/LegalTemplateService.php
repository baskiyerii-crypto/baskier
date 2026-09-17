<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Setting;

class LegalTemplateService
{
    /**
     * @return array<string, string>
     */
    public function placeholders(): array
    {
        return [
            '{{platform_name}}' => (string) (Setting::get('platform_name') ?: 'BaskıYeri'),
            '{{company_title}}' => (string) (Setting::get('legal_company_title') ?: (Setting::get('platform_name') ?: 'BaskıYeri')),
            '{{address}}' => (string) (Setting::get('legal_address') ?: Setting::get('platform_address') ?: 'Türkiye'),
            '{{tax_office}}' => (string) (Setting::get('legal_tax_office') ?: '—'),
            '{{tax_number}}' => (string) (Setting::get('legal_tax_number') ?: '—'),
            '{{mersis}}' => (string) (Setting::get('legal_mersis') ?: '—'),
            '{{email}}' => (string) (Setting::get('legal_email') ?: Setting::get('platform_email') ?: 'destek@baskiyeri.com'),
            '{{phone}}' => (string) (Setting::get('legal_phone') ?: Setting::get('platform_phone') ?: '—'),
            '{{kep}}' => (string) (Setting::get('legal_kep') ?: '—'),
        ];
    }

    public function fill(string $html): string
    {
        return strtr($html, $this->placeholders());
    }

    /**
     * @return array<string, array{title:string,audience:string,html:string}>
     */
    public function templates(): array
    {
        $name = '{{platform_name}}';

        return [
            'terms' => [
                'title' => 'Kullanım Koşulları',
                'audience' => 'all',
                'html' => $this->termsHtml($name),
            ],
            'privacy' => [
                'title' => 'Gizlilik Politikası',
                'audience' => 'all',
                'html' => $this->privacyHtml($name),
            ],
            'kvkk' => [
                'title' => 'KVKK Aydınlatma Metni',
                'audience' => 'all',
                'html' => $this->kvkkHtml($name),
            ],
            'distance_sales' => [
                'title' => 'Mesafeli Satış Sözleşmesi',
                'audience' => 'customer',
                'html' => $this->distanceSalesHtml($name),
            ],
            'order_contract' => [
                'title' => 'Sipariş Sözleşmesi',
                'audience' => 'customer',
                'html' => $this->orderContractHtml($name),
            ],
            'open_consent' => [
                'title' => 'Açık Rıza Onay Metni',
                'audience' => 'all',
                'html' => $this->openConsentHtml($name),
            ],
            'vendor_agreement' => [
                'title' => 'Satıcı Sözleşmesi',
                'audience' => 'vendor',
                'html' => $this->vendorAgreementHtml($name),
            ],
        ];
    }

    public function seedOrRefresh(bool $overwriteEmpty = true): int
    {
        $count = 0;
        foreach ($this->templates() as $key => $tpl) {
            $html = $this->fill($tpl['html']);
            $contract = Contract::query()->firstOrNew(['key' => $key]);
            $contract->fill([
                'title' => $tpl['title'],
                'audience' => $tpl['audience'],
                'version' => max(1, (int) ($contract->version ?: 1)),
                'is_active' => true,
                'content_html' => $html,
            ])->save();
            $count++;
        }

        return $count;
    }

    private function termsHtml(string $name): string
    {
        return "<h2>Kullanım Koşulları</h2>
<p>Bu metin, {$name} ({{company_title}}) tarafından işletilen pazaryeri platformunun kullanım şartlarını düzenler. Adres: {{address}}. Vergi Dairesi: {{tax_office}} Vergi No: {{tax_number}} MERSİS: {{mersis}}.</p>
<p>Platform; hazır ürün satışı ve teklif (RFQ) usulü özel üretim işlerini bir araya getirir. Müşteri ve satıcı, üyelik sırasında bu koşulları kabul eder.</p>
<ol>
<li>Hesap bilgilerinin doğruluğundan kullanıcı sorumludur.</li>
<li>Satıcılar ve müşteriler, platform dışı iletişim (telefon, e-posta, sosyal medya, web sitesi) paylaşamaz.</li>
<li>Hazır ürün siparişlerinde 6502 sayılı Kanun kapsamında mesafeli satış hükümleri uygulanır.</li>
<li>Teklif usulü işlerde satıcı ile müşteri arasında iletişim bilgisi paylaşımı ancak açık rıza ile yapılır.</li>
<li>{$name} aracılık hizmeti sunar; üretim kalitesinden ilgili satıcı sorumludur.</li>
<li>Uyuşmazlıklarda Türkiye Cumhuriyeti hukuku ve platformun yetkili mahkemeleri geçerlidir.</li>
</ol>
<p>İletişim: {{email}} / {{phone}} / KEP: {{kep}}</p>";
    }

    private function privacyHtml(string $name): string
    {
        return "<h2>Gizlilik Politikası</h2>
<p>{$name}, 6698 sayılı KVKK kapsamında kişisel verileri işler. Veri sorumlusu: {{company_title}}, {{address}}.</p>
<p>Toplanan veriler: kimlik, iletişim, sipariş, fatura, teklif ve mesajlaşma kayıtları. Amaçlar: üyelik, sipariş teslimi, faturalama, güvenlik, yasal yükümlülükler.</p>
<p>Veriler, yasal saklama süreleri boyunca muhafaza edilir ve yetkisiz üçüncü kişilerle paylaşılmaz. Haklarınız için {{email}} adresine başvurabilirsiniz.</p>";
    }

    private function kvkkHtml(string $name): string
    {
        return "<h2>KVKK Aydınlatma Metni</h2>
<p>6698 sayılı Kanun md. 10 uyarınca: Veri sorumlusu {{company_title}} (MERSİS: {{mersis}}, Vergi No: {{tax_number}}), {$name} platformunu işletmektedir.</p>
<p><strong>İşlenen veriler:</strong> ad, soyad, TCKN/vergi no, e-posta, telefon, adres, sipariş ve teklif içeriği, IP ve log kayıtları.</p>
<p><strong>Hukuki sebepler:</strong> sözleşmenin kurulması (md. 5/2-c), hukuki yükümlülük (md. 5/2-ç), meşru menfaat (md. 5/2-f) ve gerektiğinde açık rıza (md. 5/1).</p>
<p><strong>Haklarınız:</strong> Kanun md. 11 kapsamındaki haklarınızı {{email}} veya KEP {{kep}} üzerinden iletebilirsiniz.</p>";
    }

    private function distanceSalesHtml(string $name): string
    {
        return "<h2>Mesafeli Satış Sözleşmesi</h2>
<p>İşbu sözleşme, 6502 sayılı Kanun ve Mesafeli Sözleşmeler Yönetmeliği uyarınca, {$name} üzerinden verilen hazır ürün siparişleri için satıcı ile alıcı arasında kurulur. Platform aracılık eder.</p>
<p><strong>Satıcı:</strong> siparişte belirtilen mağaza. <strong>Alıcı:</strong> siparişi veren üye. <strong>Aracı:</strong> {{company_title}}, {{address}}.</p>
<p>Ürün, fiyat, teslimat süresi ve kargo bilgileri sipariş özetinde yer alır. Cayma hakkı, yönetmelikteki istisnalar (kişiselleştirilmiş baskı ürünleri vb.) saklı kalmak kaydıyla geçerlidir.</p>
<p>Ödeme, {$name} ödeme altyapısı üzerinden tahsil edilir. Fatura satıcı veya platform politikasına göre düzenlenir.</p>";
    }

    private function orderContractHtml(string $name): string
    {
        return "<h2>Sipariş Sözleşmesi</h2>
<p>{$name} üzerinde oluşan sipariş; ürün/hizmet tanımı, bedel, teslimat ve üretim takvimini bağlar. Satıcı işi belirtilen şartlarda üretir; müşteri bedeli öder.</p>
<p>Tasarım onayı gereken işlerde üretim, onaydan sonra başlar. Anlaşmazlıklarda platform mesajlaşma ve sipariş kayıtları esas alınır.</p>";
    }

    private function openConsentHtml(string $name): string
    {
        return "<h2>Açık Rıza Onay Metni</h2>
<p>Teklif (RFQ) ve özel üretim süreçlerinde, işin yürütülebilmesi için ad, telefon, e-posta ve teslimat adresimin ilgili satıcı ile {$name} üzerinden paylaşılmasına 6698 sayılı Kanun md. 5/1 uyarınca açık rıza veriyorum.</p>
<p>Hazır ürün (katalog) satışlarında bu paylaşım yapılmaz. Rızamı dilediğim zaman {{email}} adresinden geri çekebilirim; geri çekme, rıza tarihinden önceki işlemleri etkilemez.</p>";
    }

    private function vendorAgreementHtml(string $name): string
    {
        return "<h2>Satıcı Sözleşmesi</h2>
<p>{{company_title}} ile satıcı arasında {$name} pazaryeri kullanımını düzenler. Satıcı; doğru ürün, fiyat, stok, teslimat ve yasal belge bilgisi sunmayı kabul eder.</p>
<p>Komisyon, abonelik ve tabela görüşme ücretleri paneldeki tarife ile uygulanır. Teklif başı ücret yalnızca tabela imalatı için geçerlidir. Ozalit, freelancer ve baskı tekliflerinde sipariş komisyonu uygulanmaz; ilgili aylık modül ücreti geçerlidir.</p>
<p>Platform dışı yönlendirme yasaktır. Askıya alma, sözleşme ihlali ve belge eksikliğinde yönetim hesabı kısıtlayabilir.</p>";
    }
}
