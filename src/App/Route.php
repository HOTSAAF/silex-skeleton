<?php

namespace App;

use Silex\Route as SilexRoute;
use Silex\Route\SecurityTrait;

class Route extends SilexRoute
{
    use SecurityTrait;
}
