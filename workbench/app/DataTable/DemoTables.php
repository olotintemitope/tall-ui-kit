<?php

namespace Workbench\App\DataTable;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Invented payments and customers for /admin/ui/tables (local and testing only).
 * They live on a private SQLite connection, never the app database: an
 * in-memory database under testing, and a git-ignored file in storage/framework/cache locally so
 * the demo's bulk delete and row actions survive between requests. Every value
 * is made up. reset() rebuilds the data.
 */
final class DemoTables
{
    public const CONNECTION = 'data_table_demo';

    public const PAYMENTS = 320;

    public const CUSTOMERS = 48;

    private static ?\PDO $seededOn = null;

    public static function ensureSeeded(): void
    {
        if (config('database.connections.'.self::CONNECTION) === null) {
            $path = app()->environment('testing') ? ':memory:' : storage_path('framework/cache/data-table-demo.sqlite');
            if ($path !== ':memory:' && ! File::exists($path)) {
                File::put($path, '');
            }
            config(['database.connections.'.self::CONNECTION => ['driver' => 'sqlite', 'database' => $path, 'prefix' => '', 'foreign_key_constraints' => false]]);
        }

        $pdo = DB::connection(self::CONNECTION)->getPdo();
        if (self::$seededOn === $pdo) {
            return;
        }

        if (! Schema::connection(self::CONNECTION)->hasTable('demo_payments')) {
            self::build();
        }

        self::$seededOn = $pdo;
    }

    public static function reset(): void
    {
        self::ensureSeeded();
        $schema = Schema::connection(self::CONNECTION);
        $schema->dropIfExists('demo_payments');
        $schema->dropIfExists('demo_customers');
        self::build();
    }

    private static function build(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        $schema->create('demo_customers', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->index();
            $table->string('country');
        });

        $schema->create('demo_payments', function ($table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('customer_id')->index();
            $table->string('campaign');
            $table->string('platform');
            $table->string('tier');
            $table->unsignedInteger('slots');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);
            $table->unsignedBigInteger('fee');
            $table->string('status')->index();
            $table->string('method');
            $table->boolean('flagged')->default(false);
            $table->string('notes')->nullable();
            $table->timestamp('created_at')->index();
        });

        $first = ['Ada', 'Tobi', 'Kemi', 'Chidi', 'Zainab', 'Femi', 'Amara', 'Seun', 'Ngozi', 'Ife', 'Musa', 'Bisi', 'Uche', 'Lola', 'Dayo', 'Halima'];
        $last = ['Okafor', 'Bakare', 'Adeyemi', 'Nwosu', 'Bello', 'Ojo', 'Eze', 'Alabi', 'Obi', 'Balogun', 'Danjuma', 'Coker'];
        $countries = ['Nigeria', 'Nigeria', 'Nigeria', 'Ghana', 'Kenya', 'United Kingdom', 'United States'];

        $customers = [];
        for ($i = 1; $i <= self::CUSTOMERS; $i++) {
            $name = $first[($i * 5) % count($first)].' '.$last[($i * 7) % count($last)];
            $customers[] = [
                'id' => $i,
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)).$i.'@example.test',
                'country' => $countries[$i % count($countries)],
            ];
        }
        DB::connection(self::CONNECTION)->table('demo_customers')->insert($customers);

        $campaigns = ['Lagos Nights', 'Harmattan EP', 'Island Groove', 'Owambe Season', 'Ember Tapes', 'Third Mainland', 'Sunday Jollof', 'Danfo Diaries', 'Afro Dawn', '100%_Pure Vibes'];
        $platforms = ['playlist', 'social', 'blog'];
        $tiers = ['starter', 'growth', 'pro'];
        $statuses = ['completed', 'completed', 'completed', 'pending', 'failed', 'refunded'];
        $methods = ['Card', 'Bank transfer', 'Wallet'];
        $notes = [null, null, 'Rush review', null, null, 'Label paid', null, 'Repeat customer', null, null];
        $start = Carbon::now(config('app.timezone'))->startOfDay()->subDays(120)->setTime(8, 0);

        $rows = [];
        for ($i = 0; $i < self::PAYMENTS; $i++) {
            $tier = ($i * 7) % 3;
            $currency = $i % 7 === 6 ? 'USD' : 'NGN';
            $amount = $currency === 'USD' ? [40, 90, 210][$tier] + ($i % 30) : [25000, 60000, 150000][$tier] + ($i % 40) * 1500;
            $rows[] = [
                'id' => $i + 1,
                'reference' => sprintf('NB-%05d', 48210 + $i * 37),
                'customer_id' => ($i * 11) % self::CUSTOMERS + 1,
                'campaign' => $campaigns[($i * 3) % count($campaigns)],
                'platform' => $platforms[($i * 2) % 3],
                'tier' => $tiers[$tier],
                'slots' => [5, 10, 25][$tier] + ($i % 4),
                'amount' => $amount,
                'currency' => $currency,
                'fee' => (int) round($amount * 0.2),
                'status' => $statuses[$i % count($statuses)],
                'method' => $methods[$i % 3],
                'flagged' => $i % 13 === 0,
                'notes' => $notes[$i % count($notes)],
                'created_at' => $start->copy()->addMinutes($i * 539)->format('Y-m-d H:i:s'),
            ];
        }
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::connection(self::CONNECTION)->table('demo_payments')->insert($chunk);
        }
    }
}
