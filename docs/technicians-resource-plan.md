# خطة إضافة Resource خاص بالفنيين

الهدف: شاشة منفصلة لإدارة "الفنيين" جوه لوحة Filament، مبنية على موديل `User` نفسه، ومفلترة بحيث ما يظهرش فيها غير اليوزرز اللي عندهم صلاحية `technician` (عن طريق Spatie Ro2les/Permissions).

الملفات جاهزة ومرفقة في `Technicians.zip` (أو منفصلة حسب ما استلمتها)، ودي خطوات التركيب والتحقق بالترتيب.

---

## قبل أي حاجة — تحقق من الافتراضات

الكود اتكتب بناءً على افتراضات لازم تتأكد منها الأول في مشروعك الحقيقي، لأني معنديش زيارة على موديل `User` ولا migration جدول `users`:

| الافتراض | فين المفروض تتأكد منه | لو مختلف |
|---|---|---|
| اسم الصلاحية هو `technician` بالظبط | جدول `roles` بتاع Spatie | غيّر قيمة `TechnicianResource::TECHNICIAN_ROLE` |
| موديل `User` بيستخدم `use HasRoles;` | `app/Models/User.php` | ضيف الـ trait لو ناقص |
| عمود `phone` موجود في جدول `users` | migration بتاعة `users` | احذف حقل/عمود phone من الفورم والجدول لو مش موجود |
| عمود `is_active` موجود وبنفس الاسم | migration بتاعة `users` | عدّل الاسم في `TechnicianForm.php` و`TechniciansTable.php` أو احذفه |
| فيه علاقة `repairOrders()` (hasMany) على `User` مربوطة بـ `technician_id` | `app/Models/User.php` | ضيف العلاقة، أو احذف عمود "عدد الأوردرات" من `TechniciansTable.php` |
| اسم عمود المفتاح الأجنبي على `repair_orders` هو `technician_id` | migration بتاعة `repair_orders` | عدّل اسم العلاقة لو مختلف |

---

## خطوات التركيب

### 1. نسخ الملفات
انسخ الملفات دي بنفس البنية بالظبط جوه `app/Filament/Resources/Technicians/`:
```
Technicians/
├── TechnicianResource.php
├── Schemas/TechnicianForm.php
├── Tables/TechniciansTable.php
└── Pages/
    ├── ListTechnicians.php
    ├── CreateTechnician.php
    └── EditTechnician.php
```

### 2. تسجيل الـ Resource
لو الـ Panel Provider عندك بيسجل الـ Resources يدوي (`->resources([...])`)، ضيف `TechnicianResource::class` للقايمة. لو بيستخدم auto-discovery (الافتراضي في Filament v4 غالبًا) مفيش حاجة تتعمل هنا.

### 3. ضيف مفاتيح الترجمة
افتح `lang/ar/resources.php` (وكمان `lang/en/resources.php` لو المشروع متعدد اللغات) وضيف مفتاح `technicians` من `translations_snippet.php` جوه الـ array الرئيسي.

### 4. عدّل الافتراضات المكتوبة فوق
راجع الجدول في أول الملف ده وعدّل أي حاجة مختلفة عن مشروعك الفعلي.

### 5. اختبار يدوي
- افتح `/admin/technicians` (أو المسار المطابق لاسم البانل عندك).
- تأكد إن اليوزرز اللي مش فنيين (أدمن، استقبال...) **مش ظاهرين** في القايمة.
- اعمل فني جديد من زرار "إنشاء"، وتأكد إنه ظهر في القايمة فورًا (يعني `assignRole` في `CreateTechnician.php` اشتغل صح).
- افتح صفحة أوردرات الصيانة، وتأكد إن الـ Select بتاع "الفني" لسه شغال وبيجيب نفس اليوزرز دول.

---

## معيار القبول النهائي
- شاشة "الفنيين" ظاهرة في الـ navigation.
- بتعرض بس اليوزرز أصحاب صلاحية `technician`.
- إنشاء فني جديد من الشاشة دي بيدّيه الصلاحية تلقائي من غير خطوة يدوية إضافية.
- حذف/تعديل فني من هنا ما بيأثرش على باقي أنواع اليوزرز التانيين في النظام.
