<?php

namespace Tedivm\StashBundle\Adapters;

use Stash\Session;

if(version_compare(phpversion(), '5.4.0', '>=')){
    class SessionHandlerAdapter extends Session {}
} else {
    class SessionHandlerAdapter extends Session implements \SessionHandlerInterface {}
}