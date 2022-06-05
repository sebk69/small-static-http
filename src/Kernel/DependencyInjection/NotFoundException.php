<?php

namespace SmallStaticHttp\DependencyInjection;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{

}