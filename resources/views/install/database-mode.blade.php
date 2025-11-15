<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install HBM - Database Mode</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900">HBM Billing Manager</h1>
                <p class="mt-2 text-xl text-gray-600">Select Database Mode</p>
                <p class="mt-1 text-sm text-gray-500">Step 2 of 4</p>
            </div>

            <div class="bg-white shadow-md rounded-lg p-8">
                <p class="text-gray-600 mb-6">Choose how you want to set up your database:</p>

                <form action="{{ route('install.database.mode.store') }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <!-- Local MySQL Option -->
                        <div class="relative">
                            <input type="radio" name="mode" id="mode_local" value="local" class="peer hidden" required>
                            <label for="mode_local" class="block p-6 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition duration-200">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <div class="w-4 h-4 rounded-full border-2 border-gray-400 peer-checked:border-blue-600 peer-checked:bg-blue-600 flex items-center justify-center">
                                            <div class="w-2 h-2 bg-white rounded-full hidden peer-checked:block"></div>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            🖥️ Local MySQL Server
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-2">
                                            MySQL is installed on the same server. Perfect for XAMPP, Laragon, local development, or single-server deployments.
                                        </p>
                                        <div class="mt-3 p-3 bg-gray-50 rounded text-sm">
                                            <p class="font-medium text-gray-700">What will happen:</p>
                                            <ul class="list-disc list-inside mt-1 space-y-1 text-gray-600">
                                                <li>Installer will auto-create database</li>
                                                <li>Create database user with privileges</li>
                                                <li>Grant necessary permissions automatically</li>
                                                <li>Test connection and proceed</li>
                                            </ul>
                                            <p class="mt-2 text-xs text-gray-500">
                                                <strong>Note:</strong> You'll need MySQL root credentials for this mode.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Remote MySQL Option -->
                        <div class="relative">
                            <input type="radio" name="mode" id="mode_remote" value="remote" class="peer hidden" required>
                            <label for="mode_remote" class="block p-6 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition duration-200">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <div class="w-4 h-4 rounded-full border-2 border-gray-400 peer-checked:border-blue-600 peer-checked:bg-blue-600 flex items-center justify-center">
                                            <div class="w-2 h-2 bg-white rounded-full hidden peer-checked:block"></div>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            🌐 Remote MySQL Server
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-2">
                                            MySQL is hosted on a different server (cPanel, external database server, cloud database, etc.)
                                        </p>
                                        <div class="mt-3 p-3 bg-gray-50 rounded text-sm">
                                            <p class="font-medium text-gray-700">What will happen:</p>
                                            <ul class="list-disc list-inside mt-1 space-y-1 text-gray-600">
                                                <li>Validate connection to remote server</li>
                                                <li>Check if database exists</li>
                                                <li>Test database credentials</li>
                                                <li>Proceed if all checks pass</li>
                                            </ul>
                                            <p class="mt-2 text-xs text-gray-500">
                                                <strong>Note:</strong> You must create the database manually in cPanel/phpMyAdmin first.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    @error('mode')
                    <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded">
                        <p class="text-red-800 text-sm">{{ $message }}</p>
                    </div>
                    @enderror

                    <div class="flex justify-between mt-8">
                        <a href="{{ route('install.requirements') }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-200">
                            ← Back
                        </a>
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                            Continue to Database Config →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Visual feedback for radio selection
        document.querySelectorAll('input[name="mode"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('label[for^="mode_"]').forEach(label => {
                    const radio = label.querySelector('input[type="radio"]') ||
                                document.getElementById(label.getAttribute('for'));
                    const indicator = label.querySelector('.w-4.h-4 div');

                    if (radio && radio.checked) {
                        label.classList.add('border-blue-600', 'bg-blue-50');
                        label.classList.remove('border-gray-200');
                        if (indicator) indicator.classList.remove('hidden');
                    } else {
                        label.classList.remove('border-blue-600', 'bg-blue-50');
                        label.classList.add('border-gray-200');
                        if (indicator) indicator.classList.add('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>
