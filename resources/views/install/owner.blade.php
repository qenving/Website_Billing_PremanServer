<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install HBM - Create Owner Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900">HBM Billing Manager</h1>
                <p class="mt-2 text-xl text-gray-600">Create Owner Account</p>
                <p class="mt-1 text-sm text-gray-500">Step 4 of 4</p>
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

                <!-- Information Box -->
                <div class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-lg font-semibold text-blue-900">About the OWNER Account</h3>
                            <div class="mt-2 text-sm text-blue-800 space-y-2">
                                <p>The OWNER account has special privileges in the system:</p>
                                <ul class="list-disc list-inside ml-4 space-y-1">
                                    <li><strong>Cannot be deleted</strong> - This account is permanently protected</li>
                                    <li><strong>Cannot be downgraded</strong> - OWNER role cannot be changed</li>
                                    <li><strong>Highest permissions</strong> - Full access to all system features</li>
                                    <li><strong>System management</strong> - Can manage all users, roles, and settings</li>
                                    <li><strong>Extension control</strong> - Install, configure, and manage extensions</li>
                                </ul>
                                <p class="mt-3 font-medium">
                                    This is the master account for your billing system. Keep the credentials secure!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('install.owner.store') }}" method="POST" id="ownerForm">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="John Doe"
                                   required>
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="owner@example.com"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">This will be your login username</p>
                            @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" id="password"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Minimum 8 characters"
                                   required
                                   minlength="8">
                            <div id="passwordStrength" class="mt-2 hidden">
                                <div class="flex items-center">
                                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div id="strengthBar" class="h-full transition-all duration-300"></div>
                                    </div>
                                    <span id="strengthText" class="ml-3 text-sm font-medium"></span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Use a strong password with letters, numbers, and symbols</p>
                            @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Re-enter your password"
                                   required
                                   minlength="8">
                            <div id="matchIndicator" class="mt-2 hidden text-sm"></div>
                            @error('password_confirmation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="{{ route('install.database.config') }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-200">
                            ← Back
                        </a>
                        <button type="submit" id="submitBtn"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                            Create Owner & Complete Installation →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        const password = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const strengthDiv = document.getElementById('passwordStrength');

        password.addEventListener('input', function() {
            const val = this.value;
            if (val.length === 0) {
                strengthDiv.classList.add('hidden');
                return;
            }

            strengthDiv.classList.remove('hidden');

            let strength = 0;
            if (val.length >= 8) strength++;
            if (val.length >= 12) strength++;
            if (/[a-z]/.test(val) && /[A-Z]/.test(val)) strength++;
            if (/\d/.test(val)) strength++;
            if (/[^a-zA-Z0-9]/.test(val)) strength++;

            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-lime-500', 'bg-green-500'];
            const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const textColors = ['text-red-600', 'text-orange-600', 'text-yellow-600', 'text-lime-600', 'text-green-600'];

            strengthBar.className = 'h-full transition-all duration-300 ' + colors[strength];
            strengthBar.style.width = ((strength + 1) * 20) + '%';
            strengthText.className = 'ml-3 text-sm font-medium ' + textColors[strength];
            strengthText.textContent = texts[strength];
        });

        // Password match indicator
        const passwordConfirm = document.getElementById('password_confirmation');
        const matchIndicator = document.getElementById('matchIndicator');

        passwordConfirm.addEventListener('input', function() {
            if (this.value.length === 0) {
                matchIndicator.classList.add('hidden');
                return;
            }

            matchIndicator.classList.remove('hidden');

            if (this.value === password.value) {
                matchIndicator.className = 'mt-2 text-sm text-green-600';
                matchIndicator.innerHTML = '✓ Passwords match';
            } else {
                matchIndicator.className = 'mt-2 text-sm text-red-600';
                matchIndicator.innerHTML = '✗ Passwords do not match';
            }
        });

        // Form submission
        document.getElementById('ownerForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating Owner Account...';
        });
    </script>
</body>
</html>
