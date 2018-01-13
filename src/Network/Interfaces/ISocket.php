<?php

namespace uSIreF\Network\Interfaces;

interface ISocket {

    public function getObjectId();

    public function getTimeout(): float;

    public function getSocket();

    public function getRequest(): IRequest;

    public function getResponse(): IResponse;
}
