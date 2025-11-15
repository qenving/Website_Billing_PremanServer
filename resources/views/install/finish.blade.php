<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install HBM - Installation Complete</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <div class="text-center">
                <!-- Success Icon -->
                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100 mb-6">
                    <svg class="h-16 w-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h1 class="text-5xl font-bold text-gray-900">Installation Complete!</h1>
                <p class="mt-4 text-xl text-gray-600">
                    HBM Billing Manager has been successfully installed
                </p>
            </div>

            <div class="bg-white shadow-xl rounded-lg p-8 border-t-4 border-green-500">
                <!-- Installation Summary -->
                <div class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Installation Summary</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="font-medium text-gray-700">Server Requirements</span>
                            </div>
                            <p class="ml-7 text-sm text-gray-600">All checks passed</p>
                        </div>

                        <div class="p-4 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="font-medium text-gray-700">Database Setup</span>
                            </div>
                            <p class="ml-7 text-sm text-gray-600">Configured and migrated</p>
                        </div>

                        <div class="p-4 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="font-medium text-gray-700">Owner Account</span>
                            </div>
                            <p class="ml-7 text-sm text-gray-600 break-all">{{ $ownerEmail }}</p>
                        </div>

                        <div class="p-4 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="font-medium text-gray-700">System Status</span>
                            </div>
                            <p class="ml-7 text-sm text-gray-600">Ready for production</p>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="mb-8 p-6 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">Next Steps</h3>
                    <ol class="space-y-3 text-sm text-blue-800">
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3">1</span>
                            <div>
                                <strong>Login to Admin Panel</strong>
                                <p class="text-blue-700">Use the owner credentials you just created to access the admin panel</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3">2</span>
                            <div>
                                <strong>Configure Payment Gateways</strong>
                                <p class="text-blue-700">Go to Extensions → Payment Gateways and configure your payment methods</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3">3</span>
                            <div>
                                <strong>Setup Provisioning Modules</strong>
                                <p class="text-blue-700">Configure server provisioning modules (Pterodactyl, Proxmox, cPanel, etc.)</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3">4</span>
                            <div>
                                <strong>Create Products & Services</strong>
                                <p class="text-blue-700">Add your hosting packages, VPS plans, or other services</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3">5</span>
                            <div>
                                <strong>Configure System Settings</strong>
                                <p class="text-blue-700">Set up company info, SMTP, notifications, and other global settings</p>
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Security Reminder -->
                <div class="mb-8 p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="h-6 w-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-yellow-900 mb-2">Security Reminder</h3>
                            <ul class="space-y-2 text-sm text-yellow-800">
                                <li class="flex items-start">
                                    <span class="mr-2">•</span>
                                    <span>The system is now in <strong>production mode</strong> with debug disabled</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-2">•</span>
                                    <span>Keep your <code class="bg-yellow-100 px-1 rounded">.env</code> file secure and never commit it to version control</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-2">•</span>
                                    <span>Regularly backup your database and uploaded files</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-2">•</span>
                                    <span>Keep the system updated by running <code class="bg-yellow-100 px-1 rounded">git pull && composer install</code></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="/admin"
                       class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 shadow-lg">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Go to Admin Panel
                    </a>

                    <a href="/"
                       class="flex items-center justify-center bg-gray-600 hover:bg-gray-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 shadow-lg">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        View Client Area
                    </a>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                    <p class="text-gray-600">
                        Thank you for choosing <strong class="text-gray-900">HBM Billing Manager</strong>!
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        Version {{ config('app.version', '1.0.0') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
