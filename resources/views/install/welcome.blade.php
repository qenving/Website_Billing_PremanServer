<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install HBM - Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Welcome to HBM</h1>
                <p class="text-xl text-gray-600">Hosting & Billing Manager</p>
            </div>

            <div class="bg-white shadow-md rounded-lg p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Installation Wizard</h2>
                    <p class="text-gray-600">
                        This wizard will guide you through the installation process. Before we begin, make sure you have:
                    </p>
                </div>

                <div class="space-y-3 mb-8">
                    <div class="flex items-start">
                        <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">PHP 8.2 or higher installed</span>
                    </div>
                    <div class="flex items-start">
                        <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">MySQL/MariaDB database ready</span>
                    </div>
                    <div class="flex items-start">
                        <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Required PHP extensions enabled</span>
                    </div>
                    <div class="flex items-start">
                        <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Write permissions for storage and cache folders</span>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Installation Steps:</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-600 mb-6">
                        <li>Check system requirements</li>
                        <li>Configure database connection</li>
                        <li>Create administrator account</li>
                        <li>Complete installation</li>
                    </ol>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('install.requirements') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                        Start Installation →
                    </a>
                </div>
            </div>

            <div class="text-center text-sm text-gray-500">
                <p>HBM v1.0.0 - Hosting & Billing Manager</p>
            </div>
        </div>
    </div>
</body>
</html>
