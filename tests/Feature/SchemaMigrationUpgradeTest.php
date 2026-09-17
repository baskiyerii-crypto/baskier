<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Order;
use App\Models\OrderContractAcceptance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaMigrationUpgradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_platform_tables_exist_after_migrations(): void
    {
        $expectedTables = [
            'users',
            'orders',
            'order_items',
            'categories',
            'vendors',
            'contracts',
            'order_contract_acceptances',
            'contract_vendor_acceptances',
            'otp_verifications',
            'vendor_category_requests',
            'vendor_profile_change_requests',
            'contact_shares',
            'direct_quote_requests',
            'ooh_inventories',
            'ooh_plans',
            'vendor_members',
            'countries',
            'world_places',
            'posts',
            'integration_credentials',
            'push_subscriptions',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} should exist in schema.");
        }
    }

    public function test_orders_table_has_all_required_columns(): void
    {
        $expectedColumns = [
            'id',
            'order_number',
            'user_id',
            'vendor_id',
            'status',
            'payment_status',
            'shipped_at',
            'termin_due_at',
            'tracking_number',
            'shipping_label_path',
            'carrier_code',
        ];

        foreach ($expectedColumns as $col) {
            $this->assertTrue(Schema::hasColumn('orders', $col), "Orders table should have column {$col}.");
        }
    }

    public function test_order_contract_acceptance_record_can_be_created(): void
    {
        $user = User::factory()->create();
        $contract = Contract::firstOrCreate(
            ['key' => 'distance_sales'],
            ['title' => 'Mesafeli Satış Sözleşmesi', 'content' => 'Test content', 'version' => 1, 'is_active' => true]
        );

        $acceptance = OrderContractAcceptance::create([
            'user_id' => $user->id,
            'order_id' => null,
            'contract_id' => $contract->id,
            'ip' => '127.0.0.1',
            'scrolled_at' => now(),
            'accepted_at' => now(),
        ]);

        $this->assertDatabaseHas('order_contract_acceptances', [
            'id' => $acceptance->id,
            'user_id' => $user->id,
            'contract_id' => $contract->id,
        ]);
    }

    public function test_users_table_has_public_id_and_security_fields(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'public_id'));
        $this->assertTrue(Schema::hasColumn('users', 'phone'));
        $this->assertTrue(Schema::hasColumn('users', 'phone_verified_at'));
        $this->assertTrue(Schema::hasColumn('users', 'is_active'));
        $this->assertTrue(Schema::hasColumn('vendors', 'country_code'));
        $this->assertTrue(Schema::hasColumn('ooh_inventories', 'country_code'));
    }
}
