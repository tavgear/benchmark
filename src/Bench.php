<?php

declare(strict_types=1);

namespace Tvg;

/**
 * Create and manage timers to measure code execution time
 */
class Bench
{

    /**
     * No sorting
     */
    const SORT_NONE = 0;

    /**
     * Sort ascending
     */
    const SORT_ASC = 1;

    /**
     * Sort descending
     */
    const SORT_DESC = 2;

    /**
     * List of timers
     *
     * @var array
     */
    private static $timers = [];

    /**
     * Get the time from the beginning of the script to the current point
     *
     * @return \Tavgear\Interval Result time
     */
    public static function getFromRequest(): Interval
    {
        return new Interval(\microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);
    }

    /**
     * Create and start a named timer.
     * If a timer with this name has already been created, then restart it.
     *
     * @param string $name Timer name. If not specified, the default timer is started with _default name.
     * @return float Timer start time in Unix timestamp with microseconds
     */
    public static function start(string $name = '_default'): float
    {
        $startTime = \microtime(true);

        static::$timers[$name] = [
            'start' => $startTime,
        ];

        unset(static::$timers[$name]['interval']);

        return $startTime;
    }

    /**
     * Stop the named timer and remember the execution time
     *
     * @param string $name Timer name. If not specified, the default timer with _default name is used.
     * @return \Tavgear\Interval Execution time from start to stop
     * @throws \Exception When trying to stop a not exist or not running timer
     */
    public static function stop(string $name = '_default'): Interval
    {
        static::checkTimerExist($name);

        if (isset(static::$timers[$name]['interval'])) {
            throw new \Exception(static::getTimerName($name) . ' is already stopped');
        }

        return static::$timers[$name]['interval'] = new Interval(\microtime(true) - static::$timers[$name]['start']);
    }

    /**
     * Delete named timer
     *
     * @param string $name Timer name. If not specified, the default timer with _default name is used.
     * @return void
     */
    public static function delete(string $name = '_default'): void
    {
        unset(static::$timers[$name]);
    }

    /**
     * Delete all timers
     *
     * @return void
     */
    public static function clear(): void
    {
        static::$timers = [];
    }

    /**
     * Get the execution time of named timer.
     *
     * If the timer is already stopped, then the execution time is unchanged and equal to the time from start to stop.
     * If the timer is still running, then the execution time is equal to the time from the start to the current point.
     *
     * @param string $name Timer name. If not specified, the default timer with empty name is used.
     * @return \Tavgear\Interval Result time
     * @throws \Exception When trying to get time from a not exist timer
     */
    public static function get(string $name = '_default'): Interval
    {
        static::checkTimerExist($name);

        return static::$timers[$name]['interval'] ?? new Interval(\microtime(true) - static::$timers[$name]['start']);
    }

    /**
     * Get the execution time of all stopped timers.
     *
     * The list can be sorted by execution time. By default, the list is not sorted
     * and follows in the order in which the timers are created.
     *
     * Acceptable sort modes: SORT_NONE, SORT_ASC, SORT_DESC
     *
     * @param int $sort Sort mode
     * @return array An array where keys are timer names and values are execution times
     */
    public static function getAll(int $sort = self::SORT_NONE): array
    {
        $timers = [];
        foreach (static::$timers as $name => $timer) {
            if (isset($timer['interval'])) {
                $timers[$name] = $timer['interval'];
            }
        }

        if ($sort === self::SORT_ASC or $sort === self::SORT_DESC) {
            uasort($timers, function(Interval $a, Interval $b) use ($sort) {
                if ($a->seconds() > $b->seconds()) {
                    return $sort === self::SORT_ASC ? 1 : -1;
                } elseif ($a->seconds() < $b->seconds()) {
                    return $sort === self::SORT_ASC ? -1 : 1;
                } else {
                    return 0;
                }
            });
        }

        return $timers;
    }

    /**
     * Measure the execution time of a function or method.
     *
     * @param callable $function Tested function or method
     * @param type $result Variable where the result of executing the tested function will be written
     * @param type $params The arguments of the tested function
     * @return \Tavgear\Interval Result time
     */
    public static function measure(callable $function, &$result, ...$params): Interval
    {
        $name = md5(random_bytes(10));

        static::start($name);

        $result = $function(...$params);

        $interval = static::stop($name);

        static::delete($name);

        return $interval;
    }

    /**
     * Get the amount of memory used by a process.
     *
     * Warning! Shell queries are used, so the function is not very fast.
     *
     * @return int Size in kilobytes. If 0 then it was not possible to get the correct value
     */
    public static function getProcessMemoryUsage(): int
    {
        $memUsage = 0;

        if (PHP_SHLIB_SUFFIX === 'dll') {
            try {
                $tasklist = exec('tasklist /fo csv /fi "pid eq ' . getmypid() . '"');
                $parts    = explode(',', $tasklist);

                if (isset($parts[4])) {
                    $memUsage = (int) preg_replace('/\D/', '', $parts[4]);
                }
            } catch (\Throwable $exc) {

            }
        } else {
            try {
                $procStatus = file_get_contents('/proc/' . getmypid() . '/status');
            } catch (\Throwable $exc) {

            }

            $parseProcStatus = function($name) use ($procStatus) {
                $matches = [];
                if (preg_match('/^' . $name . '\D+(?<val>\d+)\D+$/im', $procStatus, $matches) === 1) {
                    return (int) $matches['val'];
                } else {
                    return 0;
                }
            };
            $memUsage = $parseProcStatus('VmRSS') + $parseProcStatus('VmSwap');
        }

        return $memUsage;
    }

    /**
     * Helper for exception
     *
     * @param type $name
     * @return string
     */
    private static function getTimerName($name): string
    {
        return $name === '' ? 'Default timer' : 'Timer "' . $name . '"';
    }

    /**
     * Check timer exist
     *
     * @param type $name
     * @return void
     * @throws \Exception
     */
    private static function checkTimerExist($name): void
    {
        if (!isset(static::$timers[$name])) {
            throw new \Exception(static::getTimerName($name) . ' not found');
        }
    }

}
