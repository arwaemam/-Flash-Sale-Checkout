# User Management API - Laravel

## نبذة عن المشروع (Project Overview)

تطبيق Laravel متقدم لإدارة بيانات المستخدمين مع نظام فلترة ذكي حسب النوع (ذكر/أنثى). يوفر المشروع API RESTful بسيط وفعال مع استخدام Middleware للتحقق والفلترة.

**A Laravel application for managing user data with intelligent gender-based filtering through middleware.**

---

## المميزات الرئيسية (Key Features)

### 1️⃣ جدول المستخدمين (Users Table)
- معرف فريد (ID)
- الاسم (Name)
- البريد الإلكتروني (Email - فريد)
- رقم الهاتف (Phone)
- النوع/الجنس (Gender - male/female)
- كلمة المرور (Password - مشفرة)
- حقول البيانات الوقتية (Timestamps)

### 2️⃣ Middleware للفلترة حسب النوع (CheckGenderMiddleware)
- يقرأ معامل `gender` من الطلب
- يتحقق من أن القيمة إما `male` أو `female`
- يرجع فقط المستخدمين من النوع المطلوب
- يعيد استجابة JSON تحتوي على اسم الـ middleware والبيانات المفلترة

### 3️⃣ Factory و Seeder
- **UserFactory**: ينشئ بيانات وهمية واقعية (أسماء، بريد إلكتروني، هاتف، نوع عشوائي)
- **DatabaseSeeder**: يملأ قاعدة البيانات ب:
  - 5 مستخدمات من الإناث (Female)
  - 5 مستخدمين من الذكور (Male)
  - مستخدم اختبار محدد

### 4️⃣ API Endpoint
- `GET /api/users?gender=female` - الحصول على المستخدمات فقط
- `GET /api/users?gender=male` - الحصول على المستخدمين فقط

---

## ما تم إنجازه (Implementation Details)

### ✅ قاعدة البيانات (Database)
```sql
-- جدول المستخدمين
CREATE TABLE users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  phone VARCHAR(255),
  gender VARCHAR(255),
  password VARCHAR(255),
  remember_token VARCHAR(255) NULLABLE,
  email_verified_at TIMESTAMP NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### ✅ النموذج (Model)
- `app/Models/User.php` - يحتوي على:
  - `$fillable` مع جميع الحقول المسموح بها
  - العلاقات اللازمة (إن وجدت)

### ✅ Middleware
- `app/Http/Middleware/CheckGenderMiddleware.php`:
  - يقبل طلبات GET على `/api/users`
  - يقرأ معامل `gender` من الـ query string
  - يتحقق من صحة القيمة
  - يرجع JSON مفلتر حسب النوع

### ✅ Controller
- `app/Http/Controllers/UserController.php`:
  - دالة `index()` لاسترجاع المستخدمين
  - معالجة الطلبات وإرسال الاستجابات

### ✅ Routes
- `routes/api.php`:
  ```php
  Route::get('/users', [UserController::class, 'index'])
      ->middleware(CheckGenderMiddleware::class);
  ```

### ✅ Factory
- `database/factories/UserFactory.php`:
  - ينشئ بيانات عشوائية واقعية
  - يولد أسماء وبريد إلكتروني وهاتف عشوائي
  - يختار النوع عشوائياً من (male/female)

### ✅ Seeder
- `database/seeders/DatabaseSeeder.php`:
  - ينشئ 11 مستخدم (5 + 5 + 1)
  - يوزع بين الذكور والإناث

---

## الحصول على البيانات (API Usage)

### طلب الإناث (Female Users)
```http
GET http://127.0.0.1:8000/api/users?gender=female
```

**الاستجابة:**
```json
{
  "middleware": "CheckGenderMiddleware",
  "users": [
    {
      "id": 1,
      "name": "Sara Ahmed",
      "email": "sara@example.com",
      "gender": "female",
      "phone": "+20123456789"
    },
    {
      "id": 2,
      "name": "Mona Hassan",
      "email": "mona@example.com",
      "gender": "female",
      "phone": "+20987654321"
    }
  ]
}
```

### طلب الذكور (Male Users)
```http
GET http://127.0.0.1:8000/api/users?gender=male
```

---

## خطوات التثبيت والتشغيل (Installation & Setup)

### المتطلبات (Requirements)
- PHP 8.2 أو أعلى
- Composer
- MySQL أو SQLite

### التثبيت (Installation)

1. **استنساخ المستودع:**
```bash
git clone https://github.com/arwaemam/users.git
cd users
```

2. **تثبيت المكتبات:**
```bash
composer install
```

3. **إعداد البيئة:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **تكوين قاعدة البيانات:**
- عدّل ملف `.env` وأدخل بيانات اتصال قاعدة البيانات

5. **تشغيل الترحيلات والبيانات الأولية:**
```bash
php artisan migrate:fresh --seed
```

6. **تشغيل السيرفر:**
```bash
php artisan serve --port=8000
```

---

## اختبار الـ API (API Testing)

### استخدام Postman
1. افتح Postman
2. أنشئ طلب GET جديد
3. أدخل الـ URL: `http://127.0.0.1:8000/api/users?gender=female`
4. اضغط Send

### استخدام cURL
```bash
curl "http://127.0.0.1:8000/api/users?gender=female"
```

### استخدام PowerShell
```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/users?gender=female" -Method Get
```

---

## هيكل المشروع (Project Structure)

```
UsersTask/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── UserController.php
│   │   └── Middleware/
│   │       └── CheckGenderMiddleware.php
│   ├── Models/
│   │   └── User.php
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   └── 0001_01_01_000000_create_users_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
├── bootstrap/
│   └── app.php (تم تعديله لتحميل routes/api.php)
├── .env
├── composer.json
└── README.md
```

---

## النقاط التقنية المهمة (Technical Notes)

### 1. تحديث `bootstrap/app.php`
تم إضافة المسار `routes/api.php` إلى إعداد التطبيق:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

### 2. Middleware Logic
يتحقق من:
- وجود معامل `gender`
- صحة القيمة (male أو female بدون حالات حروف)
- عدم تمرير معامل مختلف

### 3. Database Seeding
يتم إنشاء 11 مستخدم:
```php
User::factory()->count(5)->create(['gender' => 'female']);
User::factory()->count(5)->create(['gender' => 'male']);
User::factory()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'gender' => 'female',
]);
```

---

## الأوامر المهمة (Important Commands)

```bash
# تشغيل الترحيلات
php artisan migrate

# تشغيل الترحيلات + البيانات الأولية
php artisan migrate:fresh --seed

# عرض جميع الطرق (Routes)
php artisan route:list

# تشغيل السيرفر
php artisan serve

# مسح الـ Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```
