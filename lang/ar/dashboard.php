<?php

return [
    'title' => 'لوحة التحكم',
    'currency' => 'ج.م',

    'board' => [
        'title' => 'لوحة أوامر الشغل',
        'navigation' => 'لوحة الأوامر',
        'description' => 'اسحب بطاقة الأمر وأفلتها في العمود المناسب لتغيير حالته.',
        'empty' => 'لا توجد أوامر في هذه المرحلة.',
        'view_order' => 'عرض الأمر',
        'move_success' => 'تم نقل الأمر :order إلى «:status».',
        'move_not_allowed' => 'هذا الانتقال غير مسموح من الحالة الحالية.',
        'delivered_hint' => 'التسليم يتم من صفحة الأمر عبر «تأكيد التسليم» حتى يُنشأ الضمان.',
    ],

    'overview' => [
        'heading' => 'ملخص اليوم',
        'received' => 'مستلم',
        'received_description' => 'بانتظار الفحص حالياً',
        'in_repair' => 'قيد الإصلاح',
        'in_repair_description' => 'يعمل عليها الفنيون حالياً',
        'ready' => 'جاهز للتسليم',
        'ready_description' => 'بانتظار استلام العميل',
        'delivered_today' => 'سُلِّم اليوم',
        'delivered_today_description' => 'اكتمل تسليمها اليوم',
        'revenue_today' => 'إيراد اليوم (ج.م)',
        'revenue_today_description' => 'إجمالي فواتير اليوم',
        'paid_today' => 'محصَّل اليوم (ج.م)',
        'paid_today_description' => 'مدفوعات استُلمت اليوم',
        'outstanding' => 'مستحق متبقي (ج.م)',
        'outstanding_description' => 'أرصدة غير مسددة حالياً',
    ],

    'low_stock' => [
        'title' => 'قطع تحتاج توريد',
        'empty_heading' => 'المخزون سليم',
        'empty_description' => 'لا توجد قطع وصلت للحد الأدنى للمخزون.',
    ],

    'technicians_workload' => [
        'title' => 'عبء الفنيين',
        'active' => 'قيد التنفيذ',
        'ready' => 'جاهز للتسليم',
        'empty_heading' => 'لا يوجد فنيون بعد',
        'empty_description' => 'أضف أول فني من صفحة الفنيين لبدء إسناد الأوامر.',
    ],

    'settings' => [
        'title' => 'إعدادات الورشة',
        'navigation' => 'الإعدادات',
        'shop_section' => 'بيانات المحل (تظهر على الفواتير)',
        'shop_name' => 'اسم المحل',
        'shop_phones' => 'التليفونات',
        'shop_address' => 'العنوان',
        'tax_number' => 'السجل / البطاقة الضريبية',
        'invoice_section' => 'فاتورة البيع',
        'terms_ar' => 'الشروط (عربي)',
        'terms_en' => 'الشروط (إنجليزي)',
        'show_accessories' => 'إظهار الملحقات',
        'show_technician' => 'إظهار الفني',
        'show_device_details' => 'إظهار بيانات الجهاز',
        'show_signatures' => 'إظهار التوقيعات',
        'paper' => 'مقاس الورق',
        'sheet_section' => 'كشف حالة أمر الشغل',
        'show_costs' => 'إظهار التكاليف',
        'save' => 'حفظ الإعدادات',
        'saved' => 'تم حفظ الإعدادات بنجاح',
    ],
];
