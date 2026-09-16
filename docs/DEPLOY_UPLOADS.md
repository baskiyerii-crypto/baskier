# Deploy: kalıcı dosya ve veritabanı

Coolify her deploy’da uygulama konteynerini sıfırdan kurar. Volume yoksa **logo, ürün görseli, belgeler silinir**. Kullanıcı/ürün/sipariş satırları veritabanındadır; DB ayrı servis değilse onlar da silinir.

## Coolify Persistent Storage → Volume Mount

Nixpacks kökü genelde `/app`. Source Path = volume adı (sunucu klasörü değil).

| Source Path (volume adı) | Destination Path | Ne durur |
|---|---|---|
| `baskiyeri-uploads` | `/app/public/uploads` | Platform logosu |
| `baskiyeri-storage` | `/app/storage/app/public` | Ürün/kategori/satıcı görselleri, teklif dosyaları, tasarımlar, belgeler |

Kaydet → **Redeploy**. Volume ilk sefer boştur; logoyu ve görselleri **bir kez** yeniden yükle. Sonraki deploy’larda durur.

## Veritabanı

Environment Variables:

- `DB_CONNECTION=mysql` (veya `pgsql`)
- `DB_HOST` = Coolify **Database** servisinin internal hostname’i
- SQLite (`database/database.sqlite`) kullanma; her deploy kullanıcıyı siler.

Database servisinin kendisinde de persistent volume olsun (Coolify DB eklerken varsayılan gelir).

## Post-deployment command

Coolify → Configuration → **Pre/Post Deployment** (veya Custom command):

```bash
sh scripts/coolify-postdeploy.sh
```

Bu komut `storage:link` ve `migrate --force` çalıştırır.

## Yapma

- S3 Storages (şimdilik gerekmez)
- File Mount / Directory Mount (logo için değil)
- `storage/` veya `public/uploads` içeriğini Git’e commit etme
