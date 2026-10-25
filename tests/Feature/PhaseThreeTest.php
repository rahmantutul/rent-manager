<?php

namespace Tests\Feature;

use App\Models\ChargeType;
use App\Models\Owner;
use App\Models\User;
use App\Services\NumberingService;
use App\Services\OwnerBootstrap;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PhaseThreeTest extends TestCase
{
    use RefreshDatabase;

    public static function moneyCases(): array
    {
        return [['1200.50', 2, 120050], ['100000000', 0, 100000000]];
    }

    #[DataProvider('moneyCases')]
    public function test_money_parsing(string $v, int $u, int $expected): void
    {
        $this->assertSame($expected, Money::parse($v, 'USD', $u)->minor);
    }

    public function test_money_math(): void
    {
        $this->assertSame(333, Money::fromMinor(1000, 'USD', 2)->multiplyRatio(10, 30)->minor);
        $this->assertSame(150, Money::fromMinor(1999, 'USD', 2)->multiplyPercentage('7.5')->minor);
        $this->assertSame(3, Money::fromMinor(5, 'USD', 0)->multiplyPercentage('50')->minor);
    }

    private function owner(string $name, string $email): Owner
    {
        $o = new Owner(['name' => $name, 'email' => $email, 'phone' => '', 'currency_code' => 'USD', 'minor_units' => 2, 'timezone' => 'UTC', 'locale' => 'en', 'date_format' => 'Y-m-d']);
        $o->status = 'active';
        $o->save();

        return $o;
    }

    public function test_bootstrap_and_numbering_are_idempotent(): void
    {
        $o = $this->owner('A', 'a@a.test');
        app(OwnerBootstrap::class)->run($o);
        app(OwnerBootstrap::class)->run($o);
        $this->assertSame(3, ChargeType::withoutGlobalScopes()->where('owner_id', $o->id)->count());
        $this->assertSame('INV-'.now('UTC')->format('Y').'-00001', app(NumberingService::class)->next($o->id, 'invoice'));
    }

    public function test_owner_isolation(): void
    {
        $a = $this->owner('A', 'a@a.test');
        $b = $this->owner('B', 'b@b.test');
        app(OwnerBootstrap::class)->run($a);
        app(OwnerBootstrap::class)->run($b);
        $this->actingAs(User::factory()->create(['owner_id' => $a->id, 'role' => 'owner']));
        $this->assertCount(3,ChargeType::all());
    }
}
