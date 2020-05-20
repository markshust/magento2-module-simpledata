<?php
declare(strict_types = 1);

namespace MarkShust\SimpleData\Api\Config;

use Magento\Framework\App\Config\Storage\WriterInterface;

// Create an alias for WriterInterface so it's easier to remember
class_alias(WriterInterface::class, SimpleConfig::class);
