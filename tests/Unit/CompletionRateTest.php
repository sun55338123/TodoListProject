<?php

namespace Tests\Unit;

use App\Support\CompletionRate;
use PHPUnit\Framework\TestCase;

class CompletionRateTest extends TestCase
{
    public function test_empty_items_is_zero_percent(): void
    {
        $rate = CompletionRate::fromItems([]);

        $this->assertSame(0, $rate->total);
        $this->assertSame(0, $rate->percentage());
    }

    public function test_percentage_is_rounded(): void
    {
        $rate = CompletionRate::fromItems([
            (object) ['is_completed' => true],
            (object) ['is_completed' => true],
            (object) ['is_completed' => false],
        ]);

        $this->assertSame(2, $rate->completed);
        $this->assertSame(3, $rate->total);
        $this->assertSame(67, $rate->percentage());
    }

    public function test_all_completed_is_hundred_percent(): void
    {
        $rate = CompletionRate::fromItems([(object) ['is_completed' => true]]);

        $this->assertSame(100, $rate->percentage());
    }
}
