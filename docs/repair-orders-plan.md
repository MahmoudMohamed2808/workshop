# خطة تعديل Repair Orders — الدفع + صفحة العرض + واتساب

**الهدف:**
1. تسجيل دفعات فعلي (نقدي / فودافون كاش / تحويل بنكي) لكل طلب.
2. تبسيط صفحة العرض من 9 أزرار لـ 4، وشيل "تسجيل التشخيص" منها.
3. زرار فاتورة واحد شغال في أي حالة (مش مربوط بستاتس معيّن).
4. إرسال صورة/PDF الفاتورة فعليًا على واتساب مع رسالة.
5. صفحة `create` وملف `RepairOrderForm` و`RepairOrderStatus` **متتلمسش خالص**.

**الملفات المتأثرة (متوقعة، لازم يتأكد منها الأول قبل التنفيذ):**
- `database/migrations/xxxx_add_method_to_payments_table.php` (جديد)
- `app/Models/Payment.php`
- `app/Filament/Resources/RepairOrders/RelationManagers/PaymentsRelationManager.php`
- `app/Filament/Resources/RepairOrders/Pages/ViewRepairOrder.php`
- `app/Filament/Resources/RepairOrders/Actions/UpdateStatusAction.php` (جديد)
- `app/Filament/Resources/RepairOrders/Actions/InvoiceAction.php` (جديد)
- `app/Filament/Resources/RepairOrders/Actions/SendWhatsappUpdateAction.php`
- `app/Services/WhatsappService.php`
- `app/Services/InvoiceService.php`
- resources/views الخاصة بـ `repair-orders.sale-print`
- `database/seeders/PaymentSeeder.php` (جديد) + `DatabaseSeeder.php`
- ملفات الترجمة `lang/ar/resources.php` (أو المكافئ) لإضافة labels جديدة

**حالات الدفع (method):** `cash`, `vodafone_cash`, `bank_transfer`

---

## منطق الدفع

- عمودين جدد على جدول `payments`: `method` (string, default `cash`) و`reference_number`
  (string, nullable — لرقم عملية فودافون كاش أو التحويل البنكي).
- `PaymentsRelationManager` (دلوقتي form() فاضي وبيعرض بس) يتفعّل بحقول: المبلغ، طريقة الدفع
  (Select بالتسميات: نقدي/فودافون كاش/تحويل بنكي)، رقم المرجع (يظهر ويبقى إلزامي لو الطريقة
  مش نقدي)، تاريخ الدفع، ملاحظات.
- بعد كل دفعة جديدة يتحدّث `paid_total`/`remaining_total` بتاعة الفاتورة المرتبطة.
- الفاتورة تعرض Badge واضح: **مدفوعة بالكامل / مدفوعة جزئيًا / غير مدفوعة**.

---

## منطق صفحة العرض (تبسيط الأزرار)

**قبل (9 أزرار):** RecordDiagnosis, ChangeStatus, Approve, Reject, MarkBeyondRepair,
GenerateInvoice, PrintSaleInvoice, ConfirmDelivery, Edit.

**بعد (4 أزرار):** UpdateStatusAction, InvoiceAction, SendWhatsappUpdateAction, EditAction.

### `UpdateStatusAction` (بديل عن Approve/Reject/ChangeStatus/MarkBeyondRepair/ConfirmDelivery)
مودال بيتغيّر شكله حسب `$record->status` الحالية:
- `waiting_approval` → زرارين واضحين "موافقة" / "رفض" مباشرة.
- `inspection` → Select للخطوة الطبيعية (`waiting_approval`) + خيار "الجهاز خارج الإصلاح".
- `ready_for_delivery` → فورم "تأكيد التسليم".
- غير كده (`received`, `approved`, `in_repair`) → Select فيه بس الخطوة المنطقية الجاية + "إلغاء الطلب".

نفس `RepairOrderWorkflowService` الحالي بالضبط — التبسيط في الواجهة فقط، مش في منطق الحالات.

### `InvoiceAction` (بديل عن GenerateInvoiceAction + PrintSaleInvoiceAction)
- مفيش فاتورة → زرار "إصدار فاتورة" (بيصدرها فورًا).
- فيه فاتورة → زرار "عرض/طباعة الفاتورة" (يفتح رابط الطباعة في تاب جديد).
- شغال في **أي** status — مفيش شروط visible مرتبطة بالحالة.

### `RecordDiagnosisAction`
يتشال من الـ header actions بتاعة صفحة العرض خالص. يفضل موجود كـ class (يُستخدم لو حابب تحطه
في مكان تاني)، لكن التشخيص هيتسجل من صفحة Edit عادي عن طريق `technician_diagnosis` وقسم
`orderProblems` الموجودين أصلًا في `RepairOrderForm`.

---

## صفحة الجدول الرئيسية (`Tables/RepairOrdersTable.php`)

نفس التبسيط اللي حصل في صفحة العرض لازم يتطبّق هنا كمان، لأن `recordActions()` في الجدول
دلوقتي بتستخدم الـ Actions القديمة نفسها:

```php
->recordActions([
    ViewAction::make(),
    ChangeStatusAction::make(),      // ← يتستبدل بـ UpdateStatusAction::make()
    PrintSaleInvoiceAction::make(),  // ← يتستبدل بـ InvoiceAction::make()
    EditAction::make(),
]),
```

يبقى بعد التعديل:
```php
->recordActions([
    ViewAction::make(),
    UpdateStatusAction::make(),
    InvoiceAction::make(),
    EditAction::make(),
]),
```

كمان، عشان "متابعة الحالات" تبقى سهلة من نفس الجدول من غير ما تفتح كل طلب لوحده، نضيف
عمود جديد **حالة الدفع** في الجدول (badge): مدفوعة بالكامل (أخضر) / مدفوعة جزئيًا (أصفر) /
غير مدفوعة (أحمر) / لا توجد فاتورة (رمادي) — مبني على `$record->invoice` لو موجودة.

الفلاتر الموجودة (`status`, `technician_id`, `device_type_id`, `TrashedFilter`) تفضل زي ما هي.

---

## واتساب فعلي (إرفاق صورة/PDF الفاتورة)

`WhatsappService` الحالية بترسل نص بس. المطلوب إضافة:
- method جديدة `sendMedia(Customer $customer, string $fileUrl, string $caption): void` تستخدم
  البوابة الفعلية المستخدمة في المشروع (Meta WhatsApp Cloud API الرسمي، أو بوابة زي
  UltraMsg/Green API، حسب المستخدم فعليًا — **لازم يتأكد منها قبل الكتابة**).
- method جديدة `exportPdf(Invoice $invoice): string` في `InvoiceService` تحوّل نفس فيو
  `sale-print` لملف PDF وتخزّنه على disk عام وترجع رابطه.
- في `SendWhatsappUpdateAction`: Checkbox "إرفاق فاتورة الطلب" (يظهر بس لو الفاتورة موجودة)،
  ولو اتفعل يتبعت PDF/صورة الفاتورة فعليًا بدل الرسالة النصية العادية.
- كل رسالة (نص أو مرفق) تتسجل في `WhatsappMessage` بنفس المنطق الحالي.

> بوابة غير رسمية (UltraMsg/Green API) محتاجة رقم متصل (QR) شغال على سيرفر باستمرار —
> ده جزء بنية تحتية لازم يكون جاهز قبل اختبار الإرسال الفعلي.

---

## صفحة طباعة الفاتورة

إضافة:
- Badge لحالة الدفع (مدفوعة بالكامل/جزئيًا/غير مدفوعة) بناءً على `paid_total`/`remaining_total`.
- جدول صغير يسرد كل دفعة: التاريخ، المبلغ، طريقة الدفع، رقم المرجع.
- لو مفيش دفعات، سطر "لا توجد مدفوعات مسجلة".

---

## Seeder واقعي للدفعات

- على الفواتير المرتبطة بطلبات `delivered`: 70% دفعة واحدة كاملة، 30% دفعتين (عربون + الباقي).
- على الفواتير المرتبطة بطلبات `ready_for_delivery`: 60% دفعة جزئية (عربون)، 40% من غير أي دفعة خالص.
- طرق الدفع موزّعة واقعيًا: نقدي أكتر، فودافون كاش وتحويل بنكي أقل، مع `reference_number`
  عشوائي لغير النقدي.

---

## أسئلة محتاجة رد قبل التنفيذ الكامل

1. **بوابة الواتساب**: Meta Cloud API الرسمية، ولا بوابة زي UltraMsg/Green API، ولا حاجة تانية؟
2. **الفاتورة PDF ولا صورة؟** لو صورة محتاج مكتبة تحويل إضافية (زي spatie/browsershot).
