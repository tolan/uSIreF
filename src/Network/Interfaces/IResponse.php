<?php

namespace uSIreF\Network\Interfaces;

interface IResponse {

    public function isReadCompleted(): bool;

    public function isWriteCompleted(): bool;

    public function render(): ?string;

    public function cleanup(): bool;

    public function addData(string $message): IResponse;

}
