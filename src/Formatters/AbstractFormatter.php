<?php

namespace Astravelata\Nanomachine\Formatters;

abstract class AbstractFormatter {
    abstract public function format(string $filePath): array;
}
