<?php

declare(strict_types=1);

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('models extend Eloquent Model')
    ->expect('AichaDigital\LaraContent\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('contracts are interfaces')
    ->expect('AichaDigital\LaraContent\Contracts')
    ->toBeInterfaces();

arch('block contracts are interfaces')
    ->expect('AichaDigital\LaraContent\Blocks\Contracts')
    ->toBeInterfaces();

arch('layout contracts are interfaces')
    ->expect('AichaDigital\LaraContent\Layouts\Contracts')
    ->toBeInterfaces();

arch('exceptions extend Exception')
    ->expect('AichaDigital\LaraContent\Exceptions')
    ->toExtend('Exception');

arch('enums are backed enums')
    ->expect('AichaDigital\LaraContent\Enums')
    ->toBeEnums();
