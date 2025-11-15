<?php

return [
    // Common
    'greeting' => 'Hello',
    'regards' => 'Best Regards',
    'signature' => 'The :company Team',
    'footer' => 'If you have any questions, please don\'t hesitate to contact us.',
    'contact_support' => 'Contact Support',
    'view_account' => 'View My Account',

    // Welcome Email
    'welcome' => [
        'subject' => 'Welcome to :company',
        'title' => 'Welcome to :company!',
        'intro' => 'Thank you for registering with us. We\'re excited to have you on board!',
        'account_created' => 'Your account has been successfully created.',
        'next_steps' => 'Here\'s what you can do next:',
        'step_1' => 'Complete your profile information',
        'step_2' => 'Browse our products and services',
        'step_3' => 'Contact support if you need any assistance',
        'login_details' => 'Login Details',
        'email' => 'Email: :email',
        'login_url' => 'Login URL: :url',
    ],

    // Invoice Email
    'invoice' => [
        'subject' => 'Invoice #:number from :company',
        'title' => 'New Invoice',
        'intro' => 'A new invoice has been generated for your account.',
        'invoice_number' => 'Invoice Number: #:number',
        'invoice_date' => 'Invoice Date: :date',
        'due_date' => 'Due Date: :date',
        'amount_due' => 'Amount Due: :amount',
        'pay_now' => 'Pay Now',
        'view_invoice' => 'View Invoice',
        'items' => 'Invoice Items',
        'subtotal' => 'Subtotal',
        'tax' => 'Tax',
        'total' => 'Total',
        'payment_instructions' => 'Payment Instructions',
        'auto_reminder' => 'This is an automated reminder for your upcoming payment.',
    ],

    // Payment Received
    'payment_received' => [
        'subject' => 'Payment Confirmation - Invoice #:number',
        'title' => 'Payment Received',
        'intro' => 'Thank you for your payment!',
        'payment_confirmed' => 'We have successfully received your payment.',
        'payment_details' => 'Payment Details',
        'invoice_number' => 'Invoice Number: #:number',
        'amount_paid' => 'Amount Paid: :amount',
        'payment_date' => 'Payment Date: :date',
        'payment_method' => 'Payment Method: :method',
        'transaction_id' => 'Transaction ID: :id',
        'receipt' => 'Receipt',
        'download_receipt' => 'Download Receipt',
        'balance' => 'Your account balance is now: :balance',
    ],

    // Service Provisioned
    'service_provisioned' => [
        'subject' => 'Service Activated - :service',
        'title' => 'Service Activated!',
        'intro' => 'Your service has been successfully provisioned and is now active.',
        'service_details' => 'Service Details',
        'service_name' => 'Service: :name',
        'service_id' => 'Service ID: #:id',
        'domain' => 'Domain: :domain',
        'activation_date' => 'Activation Date: :date',
        'next_due_date' => 'Next Due Date: :date',
        'login_details' => 'Login Details',
        'username' => 'Username: :username',
        'password' => 'Password: :password',
        'login_url' => 'Login URL: :url',
        'getting_started' => 'Getting Started',
        'view_service' => 'View Service Details',
        'knowledge_base' => 'Browse Knowledge Base',
    ],

    // Service Suspended
    'service_suspended' => [
        'subject' => 'Service Suspended - :service',
        'title' => 'Service Suspended',
        'intro' => 'Your service has been suspended due to non-payment.',
        'reason' => 'Reason: :reason',
        'service_name' => 'Service: :name',
        'outstanding_balance' => 'Outstanding Balance: :amount',
        'action_required' => 'Please pay your outstanding invoices to reactivate your service.',
        'pay_now' => 'Pay Outstanding Invoices',
        'contact_support' => 'If you believe this is an error, please contact our support team.',
    ],

    // Service Cancelled
    'service_cancelled' => [
        'subject' => 'Service Cancelled - :service',
        'title' => 'Service Cancellation Confirmation',
        'intro' => 'Your service has been cancelled as requested.',
        'service_name' => 'Service: :name',
        'cancellation_date' => 'Cancellation Date: :date',
        'end_of_service' => 'End of Service: :date',
        'feedback' => 'We\'d love to hear your feedback about your experience with us.',
        'thank_you' => 'Thank you for being our customer.',
        'reactivate' => 'Changed your mind? You can reactivate your service anytime.',
    ],

    // Ticket Reply
    'ticket_reply' => [
        'subject' => 'Ticket #:number - New Reply',
        'title' => 'Support Ticket Update',
        'intro' => 'A new reply has been added to your support ticket.',
        'ticket_number' => 'Ticket #:number',
        'subject_line' => 'Subject: :subject',
        'department' => 'Department: :department',
        'status' => 'Status: :status',
        'reply_from' => 'Reply from :name',
        'view_ticket' => 'View Ticket',
        'reply_to_ticket' => 'Reply to Ticket',
    ],

    // Password Reset
    'password_reset' => [
        'subject' => 'Password Reset Request',
        'title' => 'Reset Your Password',
        'intro' => 'You are receiving this email because we received a password reset request for your account.',
        'reset_password' => 'Reset Password',
        'expire_notice' => 'This password reset link will expire in :count minutes.',
        'no_action' => 'If you did not request a password reset, no further action is required.',
        'security_notice' => 'For security reasons, please do not share this link with anyone.',
    ],

    // Payment Reminder
    'payment_reminder' => [
        'subject' => 'Payment Reminder - Invoice #:number',
        'title' => 'Payment Reminder',
        'intro' => 'This is a friendly reminder about your upcoming payment.',
        'invoice_number' => 'Invoice Number: #:number',
        'amount_due' => 'Amount Due: :amount',
        'due_date' => 'Due Date: :date',
        'days_until_due' => 'Due in :days days',
        'pay_now' => 'Pay Now',
        'overdue' => 'This invoice is now :days days overdue.',
        'late_fee_warning' => 'Late fees may apply if payment is not received promptly.',
        'avoid_suspension' => 'Please pay promptly to avoid service suspension.',
    ],

    // Account Update
    'account_update' => [
        'subject' => 'Account Information Updated',
        'title' => 'Account Update Confirmation',
        'intro' => 'Your account information has been successfully updated.',
        'changes_made' => 'Changes Made',
        'updated_at' => 'Updated at: :time',
        'not_you' => 'If you did not make these changes, please contact support immediately.',
        'security_alert' => 'Security Alert',
    ],

    // System Maintenance
    'maintenance' => [
        'subject' => 'Scheduled Maintenance Notification',
        'title' => 'Scheduled Maintenance',
        'intro' => 'We will be performing scheduled maintenance on our systems.',
        'start_time' => 'Start Time: :time',
        'end_time' => 'Estimated End Time: :time',
        'duration' => 'Expected Duration: :duration',
        'affected_services' => 'Affected Services',
        'what_to_expect' => 'What to Expect',
        'apology' => 'We apologize for any inconvenience this may cause.',
        'updates' => 'Updates will be posted on our status page.',
    ],
];
