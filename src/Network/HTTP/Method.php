<?php

namespace uSIreF\Network\HTTP;

use uSIreF\Common\Traits\TEnum;

/**
 * This file defines enum class of HTTP method.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Method {
    use TEnum;

    const GET    = 'GET';
    const POST   = 'POST';
    const PUT    = 'PUT';
    const DELETE = 'DELETE';
}
