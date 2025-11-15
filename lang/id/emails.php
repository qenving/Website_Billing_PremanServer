<?php

return [
    // Common
    'greeting' => 'Halo',
    'regards' => 'Salam Hormat',
    'signature' => 'Tim :company',
    'footer' => 'Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi kami.',
    'contact_support' => 'Hubungi Dukungan',
    'view_account' => 'Lihat Akun Saya',

    // Welcome Email
    'welcome' => [
        'subject' => 'Selamat Datang di :company',
        'title' => 'Selamat Datang di :company!',
        'intro' => 'Terima kasih telah mendaftar dengan kami. Kami senang Anda bergabung!',
        'account_created' => 'Akun Anda telah berhasil dibuat.',
        'next_steps' => 'Berikut yang dapat Anda lakukan selanjutnya:',
        'step_1' => 'Lengkapi informasi profil Anda',
        'step_2' => 'Jelajahi produk dan layanan kami',
        'step_3' => 'Hubungi dukungan jika Anda memerlukan bantuan',
        'login_details' => 'Detail Login',
        'email' => 'Email: :email',
        'login_url' => 'URL Login: :url',
    ],

    // Invoice Email
    'invoice' => [
        'subject' => 'Faktur #:number dari :company',
        'title' => 'Faktur Baru',
        'intro' => 'Faktur baru telah dibuat untuk akun Anda.',
        'invoice_number' => 'Nomor Faktur: #:number',
        'invoice_date' => 'Tanggal Faktur: :date',
        'due_date' => 'Jatuh Tempo: :date',
        'amount_due' => 'Jumlah Tagihan: :amount',
        'pay_now' => 'Bayar Sekarang',
        'view_invoice' => 'Lihat Faktur',
        'items' => 'Item Faktur',
        'subtotal' => 'Subtotal',
        'tax' => 'Pajak',
        'total' => 'Total',
        'payment_instructions' => 'Instruksi Pembayaran',
        'auto_reminder' => 'Ini adalah pengingat otomatis untuk pembayaran Anda yang akan datang.',
    ],

    // Payment Received
    'payment_received' => [
        'subject' => 'Konfirmasi Pembayaran - Faktur #:number',
        'title' => 'Pembayaran Diterima',
        'intro' => 'Terima kasih atas pembayaran Anda!',
        'payment_confirmed' => 'Kami telah berhasil menerima pembayaran Anda.',
        'payment_details' => 'Detail Pembayaran',
        'invoice_number' => 'Nomor Faktur: #:number',
        'amount_paid' => 'Jumlah Dibayar: :amount',
        'payment_date' => 'Tanggal Pembayaran: :date',
        'payment_method' => 'Metode Pembayaran: :method',
        'transaction_id' => 'ID Transaksi: :id',
        'receipt' => 'Tanda Terima',
        'download_receipt' => 'Unduh Tanda Terima',
        'balance' => 'Saldo akun Anda sekarang: :balance',
    ],

    // Service Provisioned
    'service_provisioned' => [
        'subject' => 'Layanan Diaktifkan - :service',
        'title' => 'Layanan Diaktifkan!',
        'intro' => 'Layanan Anda telah berhasil diprovisikan dan sekarang aktif.',
        'service_details' => 'Detail Layanan',
        'service_name' => 'Layanan: :name',
        'service_id' => 'ID Layanan: #:id',
        'domain' => 'Domain: :domain',
        'activation_date' => 'Tanggal Aktivasi: :date',
        'next_due_date' => 'Jatuh Tempo Berikutnya: :date',
        'login_details' => 'Detail Login',
        'username' => 'Username: :username',
        'password' => 'Password: :password',
        'login_url' => 'URL Login: :url',
        'getting_started' => 'Memulai',
        'view_service' => 'Lihat Detail Layanan',
        'knowledge_base' => 'Jelajahi Basis Pengetahuan',
    ],

    // Service Suspended
    'service_suspended' => [
        'subject' => 'Layanan Ditangguhkan - :service',
        'title' => 'Layanan Ditangguhkan',
        'intro' => 'Layanan Anda telah ditangguhkan karena pembayaran belum diterima.',
        'reason' => 'Alasan: :reason',
        'service_name' => 'Layanan: :name',
        'outstanding_balance' => 'Saldo Tertunggak: :amount',
        'action_required' => 'Silakan bayar faktur tertunggak Anda untuk mengaktifkan kembali layanan.',
        'pay_now' => 'Bayar Faktur Tertunggak',
        'contact_support' => 'Jika Anda yakin ini adalah kesalahan, silakan hubungi tim dukungan kami.',
    ],

    // Service Cancelled
    'service_cancelled' => [
        'subject' => 'Layanan Dibatalkan - :service',
        'title' => 'Konfirmasi Pembatalan Layanan',
        'intro' => 'Layanan Anda telah dibatalkan sesuai permintaan.',
        'service_name' => 'Layanan: :name',
        'cancellation_date' => 'Tanggal Pembatalan: :date',
        'end_of_service' => 'Akhir Layanan: :date',
        'feedback' => 'Kami ingin mendengar masukan Anda tentang pengalaman Anda dengan kami.',
        'thank_you' => 'Terima kasih telah menjadi pelanggan kami.',
        'reactivate' => 'Berubah pikiran? Anda dapat mengaktifkan kembali layanan Anda kapan saja.',
    ],

    // Ticket Reply
    'ticket_reply' => [
        'subject' => 'Tiket #:number - Balasan Baru',
        'title' => 'Update Tiket Dukungan',
        'intro' => 'Balasan baru telah ditambahkan ke tiket dukungan Anda.',
        'ticket_number' => 'Tiket #:number',
        'subject_line' => 'Subjek: :subject',
        'department' => 'Departemen: :department',
        'status' => 'Status: :status',
        'reply_from' => 'Balasan dari :name',
        'view_ticket' => 'Lihat Tiket',
        'reply_to_ticket' => 'Balas Tiket',
    ],

    // Password Reset
    'password_reset' => [
        'subject' => 'Permintaan Reset Password',
        'title' => 'Reset Password Anda',
        'intro' => 'Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.',
        'reset_password' => 'Reset Password',
        'expire_notice' => 'Link reset password ini akan kadaluarsa dalam :count menit.',
        'no_action' => 'Jika Anda tidak meminta reset password, tidak ada tindakan lebih lanjut yang diperlukan.',
        'security_notice' => 'Untuk alasan keamanan, jangan bagikan link ini kepada siapa pun.',
    ],

    // Payment Reminder
    'payment_reminder' => [
        'subject' => 'Pengingat Pembayaran - Faktur #:number',
        'title' => 'Pengingat Pembayaran',
        'intro' => 'Ini adalah pengingat ramah tentang pembayaran Anda yang akan datang.',
        'invoice_number' => 'Nomor Faktur: #:number',
        'amount_due' => 'Jumlah Tagihan: :amount',
        'due_date' => 'Jatuh Tempo: :date',
        'days_until_due' => 'Jatuh tempo dalam :days hari',
        'pay_now' => 'Bayar Sekarang',
        'overdue' => 'Faktur ini sekarang telah terlambat :days hari.',
        'late_fee_warning' => 'Biaya keterlambatan mungkin berlaku jika pembayaran tidak diterima segera.',
        'avoid_suspension' => 'Silakan bayar segera untuk menghindari penangguhan layanan.',
    ],

    // Account Update
    'account_update' => [
        'subject' => 'Informasi Akun Diperbarui',
        'title' => 'Konfirmasi Pembaruan Akun',
        'intro' => 'Informasi akun Anda telah berhasil diperbarui.',
        'changes_made' => 'Perubahan yang Dilakukan',
        'updated_at' => 'Diperbarui pada: :time',
        'not_you' => 'Jika Anda tidak melakukan perubahan ini, segera hubungi dukungan.',
        'security_alert' => 'Peringatan Keamanan',
    ],

    // System Maintenance
    'maintenance' => [
        'subject' => 'Notifikasi Maintenance Terjadwal',
        'title' => 'Maintenance Terjadwal',
        'intro' => 'Kami akan melakukan maintenance terjadwal pada sistem kami.',
        'start_time' => 'Waktu Mulai: :time',
        'end_time' => 'Perkiraan Waktu Selesai: :time',
        'duration' => 'Perkiraan Durasi: :duration',
        'affected_services' => 'Layanan yang Terpengaruh',
        'what_to_expect' => 'Yang Dapat Diharapkan',
        'apology' => 'Kami mohon maaf atas ketidaknyamanan yang mungkin ditimbulkan.',
        'updates' => 'Update akan diposting di halaman status kami.',
    ],
];
