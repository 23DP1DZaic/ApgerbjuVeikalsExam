# Sunny Marketplace 

Mūsdienīga tīmekļa platforma apģērbu pirkšanai, pārdošanai un sludinājumu publicēšanai. Projekts ir izstrādāts izmantojot **Vue 3** frontend pusē un **Laravel** backend pusē.

Projektu var palaist lokāli uz datora, bet tas ir arī pieejams tiešsaistē: **https://sunny-marketplace.vercel.app/**


## Projekta apraksts

Sunny Marketplace ir full-stack tīmekļa lietotne, kur lietotāji var reģistrēties, ievietot savus apģērbu sludinājumus, apskatīt citu lietotāju preces, rakstīt ziņas, izteikt cenu piedāvājumus un veikt pirkumus.
Lai pilnvērtīgi izmantotu vietni, lietotājam vispirms ir jāreģistrējas vai jāpieslēdzas savam kontam. Pēc tam lietotājs var gan pirkt, gan pārdot apģērbus.


## Galvenās iespējas

* Lietotāja reģistrācija un pieslēgšanās
* Profila rediģēšana
* Sludinājumu pievienošana ar attēliem
* Sludinājumu apskate un detalizēta preces informācija
* Preču filtrēšana pēc kategorijas, izmēra, krāsas, cenas un citiem kritērijiem
* Preču kārtošana pēc cenas vai jaunākajiem sludinājumiem
* “Like” funkcija
* Favorītu saraksts
* Ziņojumu sistēma starp pircēju un pārdevēju
* Cenu piedāvājumu jeb offer sistēma
* Pirkuma noformēšana
* Atsauksmju pievienošana pēc pirkuma
* Pārdevēja publiskais profils
* Admin panelis kategoriju pārvaldībai
* Latviešu un angļu valodas atbalsts
* Publiski pieejams hostings


## Kā darbojas vietne

Lietotājs var apskatīt sludinājumus arī kā viesis, taču, lai pārdotu, pirktu, rakstītu ziņas, pievienotu favorītus vai izteiktu piedāvājumus, ir nepieciešams konts.
Pēc reģistrācijas lietotājs var pievienot savu sludinājumu, norādot preces nosaukumu, aprakstu, cenu, kategoriju, izmēru, krāsu, stāvokli un attēlus. Citi lietotāji šo sludinājumu var apskatīt, saglabāt favorītos, atzīmēt ar “like”, nosūtīt pārdevējam ziņu vai piedāvāt savu cenu.
Ja pārdevējs pieņem cenu piedāvājumu, pircējam ir ierobežots laiks, lai veiktu pirkumu. Pēc pirkuma lietotājs var atstāt atsauksmi par pārdevēju.


## Izmantotās tehnoloģijas

### Frontend

* Vue 3
* Vite
* TypeScript
* Vue Router
* CSS

### Backend

* Laravel
* PHP
* MySQL
* Laravel Sanctum
* REST API

### Hosting

* Frontend: Vercel
* Backend: Railway
* Database: Railway MySQL


## Lokāla projekta palaišana

Nepieciešams instalēt:

* PHP 8.2 vai jaunāku versiju
* Composer
* Node.js
* npm
* MySQL

## Repozitorija klonēšana

```bash
git clone https://github.com/23DP1DZaic/ApgerbjuVeikalsExam.git
cd ApgerbjuVeikalsExam
```

## Backend palaišana

```bash
cd Backend/ApgerbjuVeikalsExam
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Backend pēc noklusējuma būs pieejams:

```txt
http://127.0.0.1:8000
```

## Frontend palaišana

```bash
cd Frontend/ApgerbjuVeikalsExam
npm install
npm run dev
```

Frontend parasti būs pieejams:

```txt
http://localhost:5173
```
