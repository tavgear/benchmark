<?php

declare(strict_types=1);

namespace Tvg;

/**
 * Represents interval in various formats
 */
class Interval
{

    /**
     * seconds:milliseconds
     */
    const FORMAT_SIMPLE = '%3$02d:%4$03d';

    /**
     * seconds milliseconds microseconds
     */
    const FORMAT_MICRO = '%3$02ds %4$03dms %5$03dus';

    /**
     * hours minutes seconds
     */
    const FORMAT_HOURS = '%1$03dh %2$02dm %3$02ds';

    /**
     *
     * @var float
     */
    private $seconds = null;

    /**
     *
     * @var array
     */
    private $detail = null;

    /**
     *
     * @param float $seconds Interval in seconds accurate to the nearest microsecond
     */
    public function __construct(float $seconds)
    {
        $this->seconds = $seconds;
    }

    /**
     * Get interval in seconds
     *
     * @return float Seconds accurate to the nearest microsecond
     */
    public function seconds(): float
    {
        return $this->seconds;
    }

    /**
     * Get interval in time components
     *
     * @return array List of values: [hours,minutes,seconds,ms,mk]
     */
    public function detail(): array
    {
        if ($this->detail === null) {
            $this->detail = $this->explodeDetail($this->seconds);
        }

        return $this->detail;
    }

    /**
     * Get interval as a human-readable string. Some formats in constants FORMAT_*
     *
     * Format develop note:
     *
     * - %1$ - hours
     * - %2$ - minutes
     * - %3$ - seconds
     * - %4$ - milliseconds
     * - %5$ - microseconds
     *
     * @param type $format Format used by the printf function
     * @return string String in specified format
     */
    public function format($format = self::FORMAT_SIMPLE): string
    {
        return vsprintf($format, $this->detail());
    }

    /**
     * Decompose the interval in seconds into components
     *
     * @param float $inputSeconds
     * @return array List of components
     */
    private function explodeDetail(float $inputSeconds): array
    {
        $seconds = (int) $inputSeconds;

        $mk = round(($inputSeconds - $seconds) * 1000000);
        $ms = intdiv((int) $mk, 1000);
        $mk -= $ms * 1000;

        $hours   = intdiv($seconds, 3600);
        $seconds -= $hours * 3600;

        $minutes = intdiv($seconds, 60);
        $seconds -= $minutes * 60;

        return compact('hours', 'minutes', 'seconds', 'ms', 'mk');
    }

    public function __toString()
    {
        return $this->format();
    }

}
