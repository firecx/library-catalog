<?php

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Route {
    public function __construct(
        public string $path,
        public array $methods = ['GET']
    ) {

    }
}