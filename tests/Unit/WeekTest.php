<?php

namespace Tests\Unit;

use App\Support\Week;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WeekTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function dates(): array
    {
        return [
            '星期一' => ['2026-11-09', '2026-11-09', '2026-11-15'],
            '星期三' => ['2026-11-11', '2026-11-09', '2026-11-15'],
            '星期日' => ['2026-11-15', '2026-11-09', '2026-11-15'],
            '跨年' => ['2027-01-01', '2026-12-28', '2027-01-03'],
        ];
    }

    #[DataProvider('dates')]
    public function test_week_starts_on_monday(string $date, string $start, string $end): void
    {
        $week = Week::containing($date);

        $this->assertSame($start, $week->start->toDateString());
        $this->assertSame($end, $week->end->toDateString());
    }
}
