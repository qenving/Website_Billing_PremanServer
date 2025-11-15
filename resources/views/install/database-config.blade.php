<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install HBM - Database Configuration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900">HBM Billing Manager</h1>
                <p class="mt-2 text-xl text-gray-600">Database Configuration</p>
                <p class="mt-1 text-sm text-gray-500">Step 3 of 4</p>
                <div class="mt-3 inline-block px-4 py-2 bg-blue-100 text-blue-800 rounded-lg text-sm font-medium">
                    Mode: {{ ucfirst($mode) }} MySQL
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-8">
                @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded">
                    <p class="text-red-800">{{ session('error') }}</p>
                </div>
                @endif

                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded">
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
                @endif

                <form action="{{ route('install.database.install') }}" method="POST" id="dbForm">
                    @csrf

                    @if($mode === 'local')
                    <!-- Local Mode: Root Credentials Section -->
                    <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">MySQL Root Credentials</h3>
                        <p class="text-sm text-blue-700 mb-4">
                            These credentials are used to create the database and user. Usually "root" with the password you set during MySQL installation.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Root Username</label>
                                <input type="text" name="root_username" value="{{ old('root_username', 'root') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                                @error('root_username')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Root Password</label>
                                <input type="password" name="root_password" value="{{ old('root_password') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Leave empty if no password">
                                @error('root_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Database Connection Details -->
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Connection Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Database Host</label>
                            <input type="text" name="host" value="{{ old('host', 'localhost') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">Usually "localhost" or "127.0.0.1"</p>
                            @error('host')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Database Port</label>
                            <input type="number" name="port" value="{{ old('port', '3306') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">Default MySQL port is 3306</p>
                            @error('port')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Database Name</label>
                        <input type="text" name="database" value="{{ old('database', 'hbm_billing') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        <p class="mt-1 text-xs text-gray-500">
                            @if($mode === 'local')
                            Will be created automatically if it doesn't exist
                            @else
                            Must already exist - create it in cPanel/phpMyAdmin first
                            @endif
                        </p>
                        @error('database')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Database Username</label>
                            <input type="text" name="username" value="{{ old('username', 'hbm_user') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">
                                @if($mode === 'local')
                                Will be created automatically
                                @else
                                Must already exist with permissions
                                @endif
                            </p>
                            @error('username')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Database Password</label>
                            <input type="password" name="password" value="{{ old('password') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Leave empty for no password (not recommended)</p>
                            @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Test Connection Button -->
                    <div class="mb-6">
                        <button type="button" id="testBtn"
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                            Test Connection
                        </button>
                        <div id="testResult" class="mt-3 hidden"></div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="{{ route('install.database.mode') }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-200">
                            ← Back
                        </a>
                        <button type="submit" id="installBtn"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                            Install Database & Run Migrations →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('testBtn').addEventListener('click', function() {
            const btn = this;
            const resultDiv = document.getElementById('testResult');

            btn.disabled = true;
            btn.textContent = 'Testing...';
            resultDiv.className = 'mt-3 p-4 bg-blue-50 border border-blue-200 rounded';
            resultDiv.textContent = 'Testing connection...';
            resultDiv.classList.remove('hidden');

            const formData = new FormData(document.getElementById('dbForm'));

            fetch('{{ route('install.database.test') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = 'mt-3 p-4 bg-green-50 border border-green-200 rounded';
                    resultDiv.innerHTML = '<p class="text-green-800">✓ ' + data.message + '</p>';
                } else {
                    resultDiv.className = 'mt-3 p-4 bg-red-50 border border-red-200 rounded';
                    resultDiv.innerHTML = '<p class="text-red-800">✗ ' + data.message + '</p>';
                }
            })
            .catch(error => {
                resultDiv.className = 'mt-3 p-4 bg-red-50 border border-red-200 rounded';
                resultDiv.innerHTML = '<p class="text-red-800">✗ Connection test failed: ' + error.message + '</p>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Test Connection';
            });
        });

        // Disable install button until test is successful
        let testPassed = false;
        document.getElementById('testBtn').addEventListener('click', function() {
            testPassed = false;
        });

        document.getElementById('dbForm').addEventListener('submit', function(e) {
            const installBtn = document.getElementById('installBtn');
            installBtn.disabled = true;
            installBtn.textContent = 'Installing...';
        });
    </script>
</body>
</html>
