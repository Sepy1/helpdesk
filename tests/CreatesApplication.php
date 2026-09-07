<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");
        $usesMemorySqlite = $connection === 'sqlite' && $database === ':memory:';
        $usesNamedTestDatabase = preg_match('/(?:^|[_-])(test|testing)(?:$|[_-])/i', $database) === 1;

        if (! $usesMemorySqlite && ! $usesNamedTestDatabase) {
            throw new \RuntimeException(
                "Pengujian dibatalkan: database '{$database}' bukan database test yang terisolasi."
            );
        }

        return $app;
    }
}
