<?php

namespace Middleland;

use Interop\Http\Middleware\{MiddlewareInterface, DelegateInterface};

class Dispatcher implements MiddlewareInterface, DelegateInterface
{
    use DispatcherTrait;
}
