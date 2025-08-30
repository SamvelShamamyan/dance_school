<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
// use Illuminate\Foundation\Console\Kernel as ConsoleKernel;



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



// На время теста — запускать каждую минуту
Schedule::command('balances:rollover')->everyMinute();

// Боевой вариант (оставишь потом)
// Schedule::command('balances:rollover')->monthlyOn(1, '01:00');