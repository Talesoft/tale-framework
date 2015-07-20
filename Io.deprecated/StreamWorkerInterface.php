<?php

namespace Tale\Io;

interface StreamWorkerInterface {

    public function getStream();
    public function __call( $streamMethod, array $args );
}