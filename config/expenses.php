<?php

// config/expenses.php


return [
    'max_expenses' => 10,

    'categories' => [
        'advertising-marketing'  => 'Advertising & Marketing',
        'bank-fees'              => 'Bank Charges & Fees',
        'equipment'              => 'Equipment',
        'home-office'            => 'Home Office',
        'insurance'              => 'Insurance',
        'legal-professional'     => 'Legal & Professional Fees',
        'motor-travel'           => 'Motor & Travel',
        'office-supplies'        => 'Office Supplies',
        'payroll'                => 'Payroll & Staff Costs',
        'postage-delivery'       => 'Postage & Delivery',
        'rent'                   => 'Rent',
        'repairs-maintenance'    => 'Repairs & Maintenance',
        'software-subscriptions' => 'Software & Subscriptions',
        'stock-materials'        => 'Stock & Materials',
        'telephone-internet'     => 'Telephone & Internet',
        'training'               => 'Training & Courses',
        'utilities'              => 'Utilities',
        'other'                  => 'Other',
    ],

    'payment_methods' => [
        'cash'          => 'Cash',
        'bank-transfer' => 'Bank Transfer',
        'debit-card'    => 'Debit Card',
        'credit-card'   => 'Credit Card',
        'direct-debit'  => 'Direct Debit',
        'cheque'        => 'Cheque',
        'paypal'        => 'PayPal',
        'other'         => 'Other',
    ],
];