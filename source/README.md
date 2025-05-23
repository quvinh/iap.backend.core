<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Project

- tax_free_vouchers table (Chứng từ không thuế / Chi phí phân bổ trong kỳ / Chi phí phân kỳ)
- Cuối kỳ = Mua vào + Tồn đầu kỳ - Giá vốn
- Giá vốn = Bán ra * %(Công thức trung bình)
- VAT Dauky + VAT Banra - VAT Muavao > 0 ? VAT Phainop : VAT Khautru

## Connect Google Drive
- https://github.com/ivanvermeyen/laravel-google-drive-demo/tree/master/README

- Input your own scopes:
```
https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/spreadsheets https://www.googleapis.com/auth/script.projects
```

```
https://www.googleapis.com/auth/spreadsheets.readonly https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/drive.scripts https://www.googleapis.com/auth/script.scriptapp https://www.googleapis.com/auth/script.projects https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/spreadsheets https://www.googleapis.com/auth/drive.file
```


## Create queue Jobs
- .env
```
QUEUE_CONNECTION=database
```

- Run queue
```
php artisan queue:work --queue=excel --stop-when-empty
```
- Show job failed
```
php artisan queue:failed
```
- Retry all jobs
```
php artisan queue:retry --queue=excel
```
- Clear all jobs failed
```
php artisan queue:flush
```

```
Artisan::call('queue:work', ['--stop-when-empty' => true]);
```

## WebServer
```
/usr/local/php80/bin/php80 artisan cache:clear
/usr/local/php80/bin/php80 artisan queue:work --queue=excel --stop-when-empty
/usr/local/php80/bin/php80 artisan sync:documents 2025
```

- Check log latest
```
tail -f laravel.log
```

- Cronjob (chạy 7h đến 19h)
```
* 7-19 * * * /usr/local/php80/bin/php80 /home/admin/domains/api.iap.vn/public_html/artisan queue:work --queue=excel --stop-when-empty
```

```
crontab -u admin -l
```

```
tail -f /var/log/cron
```

- Cronjob as root
```
/etc/crontab
```

- Kiểm tra số lượng kết nối
```
netstat -anp | grep :80 | wc -l
```

- Các IP kết nối
```
netstat -anp | grep :80 | awk '{print $5}' | cut -d: -f1 | sort | uniq -c | sort -nr | head
```

## Deploy
- Vào https://github.com/settings/tokens để set tokens
- Composer install
```
/usr/local/php80/bin/php80 /usr/local/bin/composer install
```

```
rm -rf public_html
ln -s /home/admin/domains/api.iap.vn/iap.backend.core/source/public public_html
ls -la
chown -R admin:admin public_html

chmod -R 755 /home/admin/domains/api.iap.vn/iap.backend.core/source/public
chown -R $USER:$USER /home/admin/domains/api.iap.vn/iap.backend.core/source
chmod -R ugo+rw /home/admin/domains/api.iap.vn/iap.backend.core/source/storage
```

```
unlink public_html
```

## Imunify Antivirus
- Docs https://docs.imunify360.com/imunifyav/cli/#malware
```
imunify-antivirus malware on-demand start --path /home/admin/domains/
imunify-antivirus malware on-demand list
imunify-antivirus malware malicious list
```
