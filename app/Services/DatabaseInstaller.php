<?php

namespace App\Services;

use PDO;
use PDOException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DatabaseInstaller
{
    /**
     * Test database connection
     */
    public function testConnection(array $config): array
    {
        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']}";

            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Test if database exists
            $stmt = $pdo->query("SHOW DATABASES LIKE '{$config['database']}'");
            $databaseExists = $stmt->rowCount() > 0;

            return [
                'success' => true,
                'database_exists' => $databaseExists,
                'message' => $databaseExists
                    ? "Connection successful! Database '{$config['database']}' exists."
                    : "Connection successful! Database '{$config['database']}' does not exist yet."
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Install database for LOCAL mode
     * Creates database, user, and grants privileges
     */
    public function installLocal(array $config): array
    {
        try {
            // Connect as root to create database and user
            $dsn = "mysql:host={$config['host']};port={$config['port']}";

            $pdo = new PDO(
                $dsn,
                $config['root_username'] ?? 'root',
                $config['root_password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Create database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Create user if not exists (MySQL 8.0+ syntax)
            $createUserSql = "CREATE USER IF NOT EXISTS '{$config['username']}'@'{$config['host']}'
                IDENTIFIED BY '{$config['password']}'";

            try {
                $pdo->exec($createUserSql);
            } catch (PDOException $e) {
                // If user already exists with different password, update it
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    $pdo->exec("ALTER USER '{$config['username']}'@'{$config['host']}'
                        IDENTIFIED BY '{$config['password']}'");
                } else {
                    throw $e;
                }
            }

            // Grant all privileges
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$config['database']}`.*
                TO '{$config['username']}'@'{$config['host']}'");

            $pdo->exec("FLUSH PRIVILEGES");

            // Test connection with new credentials
            $testResult = $this->testConnection($config);

            if (!$testResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Database created but connection test failed: ' . $testResult['message']
                ];
            }

            return [
                'success' => true,
                'message' => "Database '{$config['database']}' created successfully with user '{$config['username']}'"
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Local installation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Install database for REMOTE mode
     * Only validates connection, does not create database
     */
    public function installRemote(array $config): array
    {
        $testResult = $this->testConnection($config);

        if (!$testResult['success']) {
            return $testResult;
        }

        if (!$testResult['database_exists']) {
            return [
                'success' => false,
                'message' => "Database '{$config['database']}' does not exist on remote server. Please create it manually."
            ];
        }

        return [
            'success' => true,
            'message' => "Remote database connection validated successfully!"
        ];
    }

    /**
     * Run database migrations
     */
    public function runMigrations(): array
    {
        try {
            // Clear config cache first
            Artisan::call('config:clear');

            // Run migrations
            Artisan::call('migrate', ['--force' => true]);

            $output = Artisan::output();

            return [
                'success' => true,
                'message' => 'Migrations executed successfully',
                'output' => $output
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Seed database with initial data
     */
    public function seedDatabase(array $seeders = []): array
    {
        try {
            if (empty($seeders)) {
                // Run default seeder
                Artisan::call('db:seed', ['--force' => true]);
            } else {
                // Run specific seeders
                foreach ($seeders as $seeder) {
                    Artisan::call('db:seed', [
                        '--class' => $seeder,
                        '--force' => true
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => 'Database seeded successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Seeding failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if database connection is valid with current .env settings
     */
    public function checkCurrentConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get MySQL version
     */
    public function getMySQLVersion(array $config): ?string
    {
        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']}";

            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $stmt = $pdo->query("SELECT VERSION()");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return null;
        }
    }
}
