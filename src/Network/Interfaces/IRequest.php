<?php

namespace uSIreF\Network\Interfaces;

interface IRequest {

    public function isReadCompleted(): bool;

    public function addData(string $data): IRequest;

    public function cleanup(): IRequest;

    public function build(): string;

}
