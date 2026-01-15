<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('servidores:reativar')->daily()->at('00:05');

Schedule::command('escalas:verificar-prazo-envio')->daily()->at('08:00');
Schedule::command('escalas:verificar-prazo-correcao')->hourly();
