<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class EnvWriter
{
    /**
     * Update or add environment variable in .env file
     */
    public static function updateEnv(array $data): bool
    {
        $envFile = base_path('.env');

        // If .env doesn't exist, copy from .env.example
        if (!File::exists($envFile)) {
            if (File::exists(base_path('.env.example'))) {
                File::copy(base_path('.env.example'), $envFile);
            } else {
                File::put($envFile, '');
            }
        }

        $envContent = File::get($envFile);

        foreach ($data as $key => $value) {
            // Escape special characters in value
            $value = self::escapeEnvValue($value);

            // Check if key exists
            if (preg_match("/^{$key}=/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Add new key at the end
                $envContent .= "\n{$key}={$value}";
            }
        }

        return File::put($envFile, $envContent) !== false;
    }

    /**
     * Escape value for .env file
     */
    private static function escapeEnvValue($value): string
    {
        // If value is null or empty, return empty string
        if ($value === null || $value === '') {
            return '""';
        }

        // Convert to string
        $value = (string) $value;

        // If value contains spaces, quotes, or special characters, wrap in quotes
        if (preg_match('/\s|"|\'|\$/', $value)) {
            // Escape existing quotes
            $value = str_replace('"', '\\"', $value);
            return "\"{$value}\"";
        }

        return $value;
    }

    /**
     * Get environment variable from .env file
     */
    public static function getEnv(string $key): ?string
    {
        $envFile = base_path('.env');

        if (!File::exists($envFile)) {
            return null;
        }

        $envContent = File::get($envFile);

        if (preg_match("/^{$key}=(.*)$/m", $envContent, $matches)) {
            $value = trim($matches[1]);

            // Remove quotes if present
            if (preg_match('/^"(.*)"$/', $value, $quoted)) {
                return $quoted[1];
            }

            return $value;
        }

        return null;
    }

    /**
     * Generate random application key
     */
    public static function generateAppKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }
}
