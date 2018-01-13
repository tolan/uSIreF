<?php

namespace uSIreF\Common\Abstracts;

use uSIreF\Common\Provider;

abstract class AController {

    /**
     * @var Provider
     */
    private $_provider;

    final public function __construct(Provider $provider) {
        $this->_provider = $provider;
    }

    /**
     * Returns provider.
     *
     * @return Provider
     */
    final protected function getProvider() {
        return $this->_provider;
    }
}
