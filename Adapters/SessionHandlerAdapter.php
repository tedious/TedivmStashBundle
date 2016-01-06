<?php

/*
 * This file is part of the StashBundle package.
 *
 * (c) Josh Hall-Bachner <jhallbachner@gmail.com>
 * (c) Robert Hafner <tedivm@tedivm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tedivm\StashBundle\Adapters;

use Stash\Session;

/**
 * Class SessionHandlerAdapter.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class SessionHandlerAdapter extends Session
{
    protected function getCache($session_id)
    {
        $path = 'ss_ss/'.
            base64_encode($this->path).'/'.
            base64_encode($this->name).'/'.
            base64_encode($session_id);

        return $this->pool->getItem($path);
    }

    /**
     * Clears all sessions.
     */
    public function clearAll()
    {
        $item = $this->pool->getItem('ss_ss');
        $item->clear();
    }
}
