# Benchmark

A lightweight, easy-to-use benchmark tool for measuring execution times of parts of your code using customizable timers. The output of results in different formats is supported.

Used for initial debugging and code optimization.

## Installation

> $ composer require tavgear/benchmark

## Usage

    <?php

    use Tvg\Bench,
        Tvg\Interval;

Default timer.

    Bench::start(); // start default timer

    usleep(100000);

    echo Bench::get(); // get time elapsed from start of default timer to this point in seconds and milliseconds
    // 00:100

    usleep(200000);

    echo Bench::get()->format(); // same, but with an explicit call to convert to a formatted string
    // 00:301

    usleep(800000);

    echo Bench::stop()->format(Interval::FORMAT_MICRO); // stop default timer and get time with microseconds
    // 01s 101ms 234us

    $timeInfo = Bench::get()->detail(); // get detailed time from stopped default timer
    //Array
    //(
    //    [hours] => 0
    //    [minutes] => 0
    //    [seconds] => 1
    //    [ms] => 101
    //    [mk] => 234
    //)

Working with named timers to measure the speed of different parts of the code.

    // Measuring the execution time of two blocks of code using named timers
    Bench::start('block1');
    usleep(200000);
    Bench::stop('block1');

    Bench::start('block2');
    usleep(500000);
    Bench::stop('block2');

    // Get sorted results for all timers
    foreach (Bench::getAll(Bench::SORT_ASC) as $timerName => $interval) {
        echo $timerName . ': ' . $interval->format() . '<br>';
    }
    // block1: 00:200
    // block2: 00:500
    // _default: 01:101

Quickly measure the performance of functions and methods.

    // Some function
    function wait($seconds){
        sleep($seconds);
    }

    // Measuring the execution time of some function (any callable type)
    echo Bench::measure('wait', $r, 1)->format() . '<br>';
    // 01:000

## Additional features

    // Get time from start script (request) to current point
    echo Bench::getFromRequest();
    // 02:809

    // Get the size of memory used by the system process. Including memory used by all resource types.
    echo Bench::getProcessMemoryUsage();

## License

Benchmark is licensed under the MIT License
