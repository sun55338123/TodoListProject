<?php

namespace App\Support;

/**
 * 項目完成率：已完成數 / 總數，以整數百分比表示。
 */
final class CompletionRate
{
    private function __construct(
        public readonly int $completed,
        public readonly int $total,
    ) {}

    /**
     * @param  iterable<object{is_completed: bool}>  $items
     */
    public static function fromItems(iterable $items): self
    {
        $flags = [];

        foreach ($items as $item) {
            $flags[] = (bool) $item->is_completed;
        }

        return self::fromFlags($flags);
    }

    /**
     * @param  iterable<bool>  $flags  每個項目是否完成
     */
    public static function fromFlags(iterable $flags): self
    {
        $completed = 0;
        $total = 0;

        foreach ($flags as $flag) {
            $total++;

            if ($flag) {
                $completed++;
            }
        }

        return new self($completed, $total);
    }

    /**
     * 沒有任何項目時回傳 0。
     */
    public function percentage(): int
    {
        if ($this->total === 0) {
            return 0;
        }

        return (int) round($this->completed / $this->total * 100);
    }
}
