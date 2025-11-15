@extends('layouts.guest')

@section('title', 'SMTP Configuration')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl w-full">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Email Configuration</h1>
            <p class="text-gray-600">Configure SMTP settings for sending emails</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-green-600 font-medium">✓ Requirements</span>
                    <span class="text-green-600 font-medium">✓ Database</span>
                    <span class="text-green-600 font-medium">✓ Admin</span>
                    <span class="text-blue-600 font-semibold">→ Email</span>
                    <span class="text-gray-400">Complete</span>
                </div>
                <div class="mt-2 flex space-x-2">
                    <div class="flex-1 h-2 bg-green-600 rounded"></div>
                    <div class="flex-1 h-2 bg-green-600 rounded"></div>
                    <div class="flex-1 h-2 bg-green-600 rounded"></div>
                    <div class="flex-1 h-2 bg-blue-600 rounded"></div>
                    <div class="flex-1 h-2 bg-gray-300 rounded"></div>
                </div>
            </div>

            @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-900">SMTP Setup</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Configure SMTP to enable email notifications for invoices, tickets, and service updates.</p>
                            <p class="mt-1"><strong>Optional:</strong> You can skip this step and configure it later in the admin panel.</p>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('install.smtp.store') }}">
                @csrf

                <div class="space-y-6">
                    <!-- Mail Driver -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mail Driver
                        </label>
                        <select name="mail_driver" id="mail_driver" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="smtp" {{ old('mail_driver', 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="sendmail" {{ old('mail_driver') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                            <option value="log" {{ old('mail_driver') == 'log' ? 'selected' : '' }}>Log (Testing Only)</option>
                        </select>
                    </div>

                    <!-- SMTP Settings (shown when SMTP is selected) -->
                    <div id="smtp-settings">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Host
                                </label>
                                <input type="text" name="mail_host" value="{{ old('mail_host', 'smtp.gmail.com') }}" placeholder="smtp.gmail.com" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="mt-1 text-xs text-gray-500">Your SMTP server address</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Port
                                </label>
                                <input type="number" name="mail_port" value="{{ old('mail_port', '587') }}" placeholder="587" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="mt-1 text-xs text-gray-500">Common: 587 (TLS), 465 (SSL), 25</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Username
                                </label>
                                <input type="text" name="mail_username" value="{{ old('mail_username') }}" placeholder="your-email@gmail.com" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Password
                                </label>
                                <input type="password" name="mail_password" value="{{ old('mail_password') }}" placeholder="Your SMTP password or app password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Encryption
                                </label>
                                <select name="mail_encryption" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="tls" {{ old('mail_encryption', 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ old('mail_encryption') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="" {{ old('mail_encryption') == '' ? 'selected' : '' }}>None</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    From Email
                                </label>
                                <input type="email" name="mail_from_address" value="{{ old('mail_from_address') }}" placeholder="noreply@yourdomain.com" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    From Name
                                </label>
                                <input type="text" name="mail_from_name" value="{{ old('mail_from_name', config('app.name')) }}" placeholder="Your Company Name" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Common SMTP Providers Help -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-3">Common SMTP Providers:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="font-medium text-gray-800">Gmail:</p>
                                <p class="text-gray-600">Host: smtp.gmail.com | Port: 587 (TLS)</p>
                                <p class="text-xs text-gray-500">Requires App Password if 2FA enabled</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Outlook/Office 365:</p>
                                <p class="text-gray-600">Host: smtp.office365.com | Port: 587 (TLS)</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">SendGrid:</p>
                                <p class="text-gray-600">Host: smtp.sendgrid.net | Port: 587 (TLS)</p>
                                <p class="text-xs text-gray-500">Username: apikey</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Mailgun:</p>
                                <p class="text-gray-600">Host: smtp.mailgun.org | Port: 587 (TLS)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-between">
                    <a href="{{ route('install.admin') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-semibold">
                        ← Back
                    </a>
                    <div class="space-x-3">
                        <a href="{{ route('install.complete') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-semibold">
                            Skip for Now
                        </a>
                        <button type="submit" class="px-8 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition duration-200">
                            Continue →
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-gray-500">
            Installation Step 4 of 5
        </p>
    </div>
</div>

<script>
// Show/hide SMTP settings based on driver selection
document.getElementById('mail_driver').addEventListener('change', function() {
    const smtpSettings = document.getElementById('smtp-settings');
    if (this.value === 'smtp') {
        smtpSettings.style.display = 'block';
    } else {
        smtpSettings.style.display = 'none';
    }
});
</script>
@endsection
