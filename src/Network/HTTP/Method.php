<?php

namespace uSIreF\Network\HTTP;

use uSIreF\Common\Traits\TEnum;

class Method {
    use TEnum;

    const GET    = 'GET';
    const POST   = 'POST';
    const PUT    = 'PUT';
    const DELETE = 'DELETE';
}
