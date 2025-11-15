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
        <div class="max-w-2xl w-full space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Database Configuration</h1>
                <p class="mt-2 text-gray-600">Step 2 of 4</p>
            </div>

            <div class="bg-white shadow-md rounded-lg p-8">
                <?php if(session('error')): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded">
                    <p class="text-red-800"><?php echo e(session('error')); ?></p>
                </div>
                <?php endif; ?>

                <?php if($errors->has('error')): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded">
                    <p class="text-red-800"><?php echo e($errors->first('error')); ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('install.database.store')); ?>">
                    <?php echo csrf_field(); ?>

                    <div class="space-y-4">
                        <div>
                            <label for="db_host" class="block text-sm font-medium text-gray-700 mb-1">
                                Database Host
                            </label>
                            <input type="text" name="db_host" id="db_host"
                                   value="<?php echo e(old('db_host', '127.0.0.1')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            <?php $__errorArgs = ['db_host'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label for="db_port" class="block text-sm font-medium text-gray-700 mb-1">
                                Database Port
                            </label>
                            <input type="number" name="db_port" id="db_port"
                                   value="<?php echo e(old('db_port', '3306')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            <?php $__errorArgs = ['db_port'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label for="db_database" class="block text-sm font-medium text-gray-700 mb-1">
                                Database Name
                            </label>
                            <input type="text" name="db_database" id="db_database"
                                   value="<?php echo e(old('db_database', 'hbm_billing')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            <?php $__errorArgs = ['db_database'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <p class="mt-1 text-xs text-gray-500">Make sure this database already exists in MySQL</p>
                        </div>

                        <div>
                            <label for="db_username" class="block text-sm font-medium text-gray-700 mb-1">
                                Database Username
                            </label>
                            <input type="text" name="db_username" id="db_username"
                                   value="<?php echo e(old('db_username', 'root')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            <?php $__errorArgs = ['db_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label for="db_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Database Password
                            </label>
                            <input type="password" name="db_password" id="db_password"
                                   value="<?php echo e(old('db_password')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <?php $__errorArgs = ['db_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <p class="mt-1 text-xs text-gray-500">Leave blank if no password</p>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">
                        <p class="text-sm text-blue-800">
                            <strong>Note:</strong> This will run database migrations and seed initial data. Make sure the database is empty or backup your existing data first.
                        </p>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="<?php echo e(route('install.requirements')); ?>"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-200">
                            ← Back
                        </a>
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                            Install Database →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\gopit\OneDrive\Desktop\Project\Website_Billing_PremanServer-claude-hbm-billing-system-setup-011CV5xW95aYQAWdxCS2fYUK\resources\views/install/database.blade.php ENDPATH**/ ?>