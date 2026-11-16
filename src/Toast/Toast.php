<?php

namespace Vbq23\TopLayerBundle\Toast;

final readonly class Toast
{
    public function __construct(
        public ToastType $type,
        public string $title,
        public ?string $message = null,
    ) {
    }
}
