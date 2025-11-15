<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install HBM - Existing Installation Detected</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .loading {
            pointer-events: none;
            opacity: 0.6;
        }
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-left: 8px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Existing Installation Detected</h1>
                <p class="mt-2 text-gray-600">Step 2 of 4</p>
            </div>

            <div class="bg-white shadow-md rounded-lg p-8">
                @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded">
                    <p class="text-red-800">{{ session('error') }}</p>
                </div>
                @endif

                <div class="mb-6">
                    <div class="p-6 bg-yellow-50 border-2 border-yellow-300 rounded-lg">
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <h3 class="text-lg font-semibold text-yellow-900 mb-2">Warning: Database Not Empty</h3>
                                <p class="text-yellow-800 mb-2">
                                    We detected <strong>{{ $tableCount }} existing table(s)</strong> in your database. This might be from a previous installation attempt.
                                </p>
                                <p class="text-yellow-800">
                                    To proceed with a fresh installation, all existing tables will be dropped and recreated. This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-red-50 border border-red-200 rounded p-4 mb-6">
                    <p class="text-sm text-red-800">
                        <strong>IMPORTANT:</strong> All data in the database will be permanently deleted. If you have any important data, please backup your database before continuing.
                    </p>
                </div>

                <div class="border-t border-b py-6 my-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">What will happen?</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-600">
                        <li>All existing tables will be dropped</li>
                        <li>Fresh database schema will be created</li>
                        <li>Default data will be seeded</li>
                        <li>You'll create a new administrator account</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('install.database.reset') }}" id="resetForm">
                    @csrf

                    <div class="flex flex-col sm:flex-row gap-4 mt-8">
                        <button type="submit" name="action" value="cancel" id="cancelBtn"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-200 text-center">
                            ← Cancel & Go Back
                        </button>
                        <button type="submit" name="action" value="proceed" id="proceedBtn"
                                onclick="return handleProceed()"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 text-center">
                            <span id="btnText">Drop Tables & Reinstall →</span>
                        </button>
                    </div>
                </form>

                <script>
                    function handleProceed() {
                        if (!confirm('Are you absolutely sure? This will delete ALL data in the database!')) {
                            return false;
                        }

                        // Disable both buttons and show loading
                        const form = document.getElementById('resetForm');
                        const proceedBtn = document.getElementById('proceedBtn');
                        const cancelBtn = document.getElementById('cancelBtn');
                        const btnText = document.getElementById('btnText');

                        proceedBtn.disabled = true;
                        cancelBtn.disabled = true;
                        proceedBtn.classList.add('loading');
                        cancelBtn.classList.add('loading');

                        btnText.innerHTML = 'Processing... <span class="spinner"></span>';

                        return true;
                    }
                </script>
            </div>

            <div class="text-center text-sm text-gray-500">
                <p>If you don't want to lose existing data, click "Cancel & Go Back" and use a different database.</p>
            </div>
        </div>
    </div>
</body>
</html>
