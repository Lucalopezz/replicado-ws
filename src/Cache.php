<?php

namespace Uspdev\Replicado_ws;

/**
 * Compatibilidade do uspdev/cache com PHP 8.3.
 * As versões 0.7.3 e 1.0.0 usam estas propriedades sem declará-las.
 */
class Cache extends \Uspdev\Cache\Cache
{
    public $cachedClass;
    public $cache;
    public $cacheStatus;
    public $cacheKey;
}
