<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install HBM - Requirements Check</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900">HBM Billing Manager</h1>
                <p class="mt-2 text-xl text-gray-600">System Requirements Check</p>
                <p class="mt-1 text-sm text-gray-500">Step 1 of 4</p>
            </div>

            <div class="bg-white shadow-md rounded-lg p-8">
                <!-- PHP Version -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4">PHP Version</h2>
                <div class="mb-6">
                    <div class="flex items-center justify-between p-3 {{ $requirements['php_version']['status'] ? 'bg-green-50' : 'bg-red-50' }} rounded">
                        <span class="text-gray-700">{{ $requirements['php_version']['name'] }}</span>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-600 mr-3">{{ $requirements['php_version']['current'] }}</span>
                            @if($requirements['php_version']['status'])
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- PHP Extensions -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4 mt-8">PHP Extensions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                    @foreach($requirements['extensions'] as $ext)
                    <div class="flex items-center justify-between p-3 {{ $ext['status'] ? 'bg-green-50' : ($ext['required'] ? 'bg-red-50' : 'bg-yellow-50') }} rounded">
                        <span class="text-gray-700">
                            {{ $ext['name'] }}
                            @if(!$ext['required'])
                                <span class="text-xs text-gray-500">(Optional)</span>
                            @endif
                        </span>
                        @if($ext['status'])
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg class="h-5 w-5 {{ $ext['required'] ? 'text-red-600' : 'text-yellow-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @endif
                    </div>
                    @endforeach
                </div>

                <!-- Directory Permissions -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4 mt-8">Directory Permissions</h2>
                <div class="space-y-3">
                    @foreach($requirements['permissions'] as $perm)
                    <div class="flex items-center justify-between p-3 {{ $perm['status'] ? 'bg-green-50' : 'bg-red-50' }} rounded">
                        <div>
                            <span class="text-gray-700 font-medium">{{ $perm['name'] }}</span>
                            <p class="text-xs text-gray-500 break-all">{{ $perm['path'] }}</p>
                        </div>
                        @if($perm['status'])
                            <svg class="h-5 w-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @endif
                    </div>
                    @endforeach
                </div>

                @if(!$requirements['all_passed'])
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-yellow-800">
                        <strong>Warning:</strong> Some required components are not met. Please fix them before continuing.
                    </p>
                    <p class="text-sm text-yellow-700 mt-2">
                        For permission issues, run: <code class="bg-yellow-100 px-2 py-1 rounded">chmod -R 775 storage bootstrap/cache</code>
                    </p>
                </div>
                @endif

                @if($requirements['all_passed'])
                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded">
                    <p class="text-green-800">
                        <strong>✓ All requirements met!</strong> You can proceed with the installation.
                    </p>
                </div>
                @endif

                <div class="flex justify-end mt-8">
                    @if($requirements['all_passed'])
                    <a href="{{ route('install.database.mode') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                        Continue to Database Setup →
                    </a>
                    @else
                    <button onclick="location.reload()"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                        Check Again
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
