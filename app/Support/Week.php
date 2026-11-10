<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;

/**
 * 一週的日期範圍，固定從星期一開始、星期日結束。
 */
final class Week
{
    private function __construct(
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
    ) {}

    /**
     * 取得包含指定日期的那一週。
     */
    public static function containing(DateTimeInterface|string $date): self
    {
        $start = CarbonImmutable::parse($date)->startOfWeek(CarbonInterface::MONDAY)->startOfDay();

        return new self($start, $start->addDays(6));
    }
}
