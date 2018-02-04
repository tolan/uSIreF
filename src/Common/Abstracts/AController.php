<?php

namespace uSIreF\Common\Abstracts;

use uSIreF\Common\Provider;

/**
 * This file defines abstract class for controller.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AController {

    /**
     * @var Provider
     */
    private $_provider;

    /**
     * Construct method for inject provider.
     *
     * @param Provider $provider Provider instance
     */
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
