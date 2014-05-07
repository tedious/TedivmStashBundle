<?php

namespace Tedivm\StashBundle\Adapters;

use Stash\Session;

if (version_compare(phpversion(), '5.4.0', '>=')) {
    class SessionHandlerAdapterShim extends Session {}
} else {
    class SessionHandlerAdapterShim extends Session implements \SessionHandlerInterface {}
}

class SessionHandlerAdapter extends SessionHandlerAdapterShim
{
    protected function getCache($session_id)
    {
        $path = 'ss_ss/' .
            base64_encode($this->path) . '/' .
            base64_encode($this->name) . '/' .
            base64_encode($session_id);

        return $this->pool->getItem($path);
    }

    public function clearAll()
    {
        $item = $this->pool->getItem('ss_ss');
        $item->clear();
    }
}
