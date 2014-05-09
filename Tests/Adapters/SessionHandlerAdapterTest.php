<?php

namespace Tedivm\StashBundle\Tests\Adapters;

class SessionHandlerAdapterTest extends \Stash\Test\SessionTest
{
    protected $testClass = '\Tedivm\StashBundle\Adapters\SessionHandlerAdapter';
    protected $poolClass = '\Tedivm\StashBundle\Service\CacheService';

    protected function getPool()
    {
        return new $this->poolClass('name');
    }

    public function testClearAll()
    {
        $pool = $this->getPool();

        $sessionA = $this->getSession($pool);
        $sessionA->open('save_path', 'sessionA');
        $sessionA->setOptions(array('ttl' => -30));
        $sessionA->write('session_id', "session_a_data");

        $sessionB = $this->getSession($pool);
        $sessionB->open('save_path', 'sessionB');
        $sessionB->setOptions(array('ttl' => -30));
        $sessionB->write('session_id', "session_b_data");

        $sessionC = $this->getSession($pool);
        $sessionC->open('save_path', 'sessionC');
        $sessionC->setOptions(array('ttl' => -30));
        $sessionC->write('session_id', "session_c_data");

        $sessionD = $this->getSession($pool);
        $sessionD->clearAll();

        $this->assertEquals('', $sessionA->read('session_id'), 'SessionA cleared after ClearAll');
        $this->assertEquals('', $sessionB->read('session_id'), 'SessionB cleared after ClearAll');
        $this->assertEquals('', $sessionC->read('session_id'), 'SessionC cleared after ClearAll');
    }

}
