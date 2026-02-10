# ApģērbuVeikalsExam (vēl neizdomaju nosaukumu)
Mūsdienīga tīmekļa platforma apģērbu tirdzniecībai un sludinājumu publicēšanai, iedvesmota no tādiem risinājumiem kā Depop. Projekts tiek izstrādāts kā mācību darbs, izmantojot Vue 3 Frontend pusē un Laravel Backend pusē.

---
---
---
## Projekta apraksts

ApģērbuVeikalsExam ir Full-stack tīmekļa lietotne, kas paredzēta apģērbu sludinājumu publicēšanai, pārlūkošanai un filtrēšanai. Platforma paredzēta gan pircējiem, gan pārdevējiem, nodrošinot vienkāršu un pārskatāmu lietošanas pieredzi.

**Platforma ļauj:**
👕 Pārlūkot apģērbu sludinājumus

🔍 Filtrēt apģērbus pēc kategorijām un cenas

📦 Apskatīt detalizētu informāciju par preci

🛒 Sagatavot pamatu groza un pirkuma funkcionalitātei

🔗 Saņemt datus no back-end caur API

---

## Projekta struktūra
ApgerbjuVeikalsExam/
1. Backend/ApgerbjuVeikalsExam/ - **Laravel API**
2. Frontend/ApgerbjuVeikalsExam/ - **Vue 3 + Vite**

---

# Sākšana
Priekšnoteikumi:
- PHP 8.2+
- Node.js 18+
- npm
- Composer
- MySQL (vai cita SQL datubāze)

---

# Instalācija
## Repozitorija klonēšana

**`git clone`**
**`cd ApgerbjuVeikalsExam`**

---

## Backend iestatīšana

**`cd Backend/ApgerbjuVeikalsExam`**

**`composer install`**
**`cp .env.example .env`**
**`php artisan key:generate`**

---

## Frontend iestatīšana

**`cd Frontend/ApgerbjuVeikalsExam`**

**`npm install`**
**`npm run dev`**
