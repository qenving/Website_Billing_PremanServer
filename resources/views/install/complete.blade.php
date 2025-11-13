<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install HBM - Complete</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Installation Complete!</h1>
                <p class="mt-2 text-gray-600">Step 4 of 4</p>
            </div>

            <div class="bg-white shadow-md rounded-lg p-8">
                <div class="text-center mb-8">
                    <p class="text-lg text-gray-700 mb-4">
                        Congratulations! HBM has been successfully installed on your server.
                    </p>
                    <p class="text-gray-600">
                        You can now log in to your admin panel and start configuring your billing system.
                    </p>
                </div>

                <div class="border-t border-b py-6 my-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Next Steps:</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-600">
                        <li>Log in to the admin panel</li>
                        <li>Configure payment gateways</li>
                        <li>Set up provisioning providers</li>
                        <li>Create product packages</li>
                        <li>Customize your theme</li>
                        <li>Configure email settings</li>
                    </ol>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-6">
                    <p class="text-sm text-yellow-800">
                        <strong>Important:</strong> For security reasons, please delete or restrict access to the installation files after completing the setup.
                    </p>
                </div>

                <div class="flex justify-center">
                    <a href="{{ url('/login') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-200">
                        Go to Login Page
                    </a>
                </div>
            </div>

            <div class="text-center text-sm text-gray-500">
                <p>Thank you for choosing HBM!</p>
                <p class="mt-1">Need help? Check our documentation or contact support.</p>
            </div>
        </div>
    </div>
</body>
</html>
