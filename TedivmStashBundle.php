<?php

namespace Tedivm\StashBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tedivm\StashBundle\DependencyInjection\TedivmStashExtension;

/**
 * Bundle.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class TedivmStashBundle extends Bundle
{

    public function getContainerExtension()
    {
        return new TedivmStashExtension();
    }
}
