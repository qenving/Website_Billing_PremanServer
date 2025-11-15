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
        <div class="max-w-3xl w-full space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">System Requirements Check</h1>
                <p class="mt-2 text-gray-600">Step 1 of 4</p>
            </div>

            <div class="bg-white shadow-md rounded-lg p-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">PHP Requirements</h2>
                <div class="space-y-3 mb-6">
                    @foreach($requirements as $req)
                    <div class="flex items-center justify-between p-3 {{ $req['check'] ? 'bg-green-50' : 'bg-red-50' }} rounded">
                        <span class="text-gray-700">{{ $req['name'] }}</span>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-600 mr-3">{{ $req['value'] }}</span>
                            @if($req['check'])
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
                    @endforeach
                </div>

                <h2 class="text-xl font-semibold text-gray-800 mb-4 mt-8">Directory Permissions</h2>
                <div class="space-y-3">
                    @foreach($permissions as $perm)
                    <div class="flex items-center justify-between p-3 {{ $perm['check'] ? 'bg-green-50' : 'bg-red-50' }} rounded">
                        <div>
                            <span class="text-gray-700 font-medium">{{ $perm['name'] }}</span>
                            <p class="text-xs text-gray-500">{{ $perm['path'] }}</p>
                        </div>
                        @if($perm['check'])
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @endif
                    </div>
                    @endforeach
                </div>

                @php
                    $allChecked = collect($requirements)->every('check') && collect($permissions)->every('check');
                @endphp

                @if(!$allChecked)
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-yellow-800">
                        <strong>Warning:</strong> Some requirements are not met. Please fix them before continuing.
                    </p>
                </div>
                @endif

                <div class="flex justify-between mt-8">
                    <a href="{{ route('install.index') }}"
                       class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-200">
                        ← Back
                    </a>
                    @if($allChecked)
                    <a href="{{ route('install.database') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                        Continue →
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
