<?php

namespace SmallStaticHttp\Kernel\DependencyInjection;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{

}