<?php

return [
    'title' => 'Dashboard',
    'currency' => 'EGP',

    'board' => [
        'title' => 'Repair orders board',
        'navigation' => 'Orders board',
        'description' => 'Drag an order card and drop it into the matching column to change its status.',
        'empty' => 'No orders in this stage.',
        'view_order' => 'View order',
        'move_success' => 'Order :order moved to “:status”.',
        'move_not_allowed' => 'This transition is not allowed from the current status.',
        'delivered_hint' => 'Delivery is confirmed from the order page via “Confirm delivery” so the warranty is created.',
    ],

    'overview' => [
        'heading' => 'Today at a glance',
        'received' => 'Received',
        'received_description' => 'Currently awaiting inspection',
        'in_repair' => 'In repair',
        'in_repair_description' => 'Technicians are working on these',
        'ready' => 'Ready for delivery',
        'ready_description' => 'Waiting for customer pickup',
        'delivered_today' => 'Delivered today',
        'delivered_today_description' => 'Handed over today',
        'revenue_today' => 'Today’s revenue (EGP)',
        'revenue_today_description' => 'Total of today’s invoices',
        'paid_today' => 'Collected today (EGP)',
        'paid_today_description' => 'Payments received today',
        'outstanding' => 'Outstanding balance (EGP)',
        'outstanding_description' => 'Currently unpaid balances',
    ],

    'low_stock' => [
        'title' => 'Parts needing restock',
        'empty_heading' => 'Stock is healthy',
        'empty_description' => 'No parts reached the minimum stock level.',
    ],

    'technicians_workload' => [
        'title' => 'Technician workload',
        'active' => 'In progress',
        'ready' => 'Ready for delivery',
        'empty_heading' => 'No technicians yet',
        'empty_description' => 'Add the first technician from the technicians page to start assigning orders.',
    ],

    'settings' => [
        'title' => 'Workshop settings',
        'navigation' => 'Settings',
        'shop_section' => 'Shop profile (prints on invoices)',
        'shop_name' => 'Shop name',
        'shop_phones' => 'Phones',
        'shop_address' => 'Address',
        'tax_number' => 'Tax / commercial number',
        'invoice_section' => 'Sales invoice',
        'terms_ar' => 'Terms (Arabic)',
        'terms_en' => 'Terms (English)',
        'show_accessories' => 'Show accessories',
        'show_technician' => 'Show technician',
        'show_device_details' => 'Show device details',
        'show_signatures' => 'Show signatures',
        'paper' => 'Paper size',
        'sheet_section' => 'Work-order case sheet',
        'show_costs' => 'Show costs',
        'save' => 'Save settings',
        'saved' => 'Settings saved successfully',
    ],
];
