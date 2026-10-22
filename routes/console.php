<?php
use Illuminate\Support\Facades\Artisan;
Artisan::command('cinemax:hello', function () { $this->info('CINEMAX ready'); });
