<?php
declare(strict_types = 1);

namespace MarkShust\SimpleData\Setup\Patch;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use MarkShust\SimpleData\Api\Cms\SimpleBlock;
use MarkShust\SimpleData\Api\Cms\SimplePage;
use Magento\Framework\App\Config\Storage\WriterInterface as SimpleConfig;

abstract class SimpleDataPatch implements DataPatchInterface
{
    /** @var SimpleBlock */
    protected $block;

    /** @var SimpleConfig */
    protected $config;

    /** @var SimplePage */
    protected $page;

    /**
     * SimpleDataPatch constructor.
     * @param SimpleBlock $simpleBlock
     * @param SimpleConfig $simpleConfig
     * @param SimplePage $simplePage
     */
    public function __construct(
        SimpleBlock $simpleBlock,
        SimpleConfig $simpleConfig,
        SimplePage $simplePage
    ) {
        $this->block = $simpleBlock;
        $this->config = $simpleConfig;
        $this->page = $simplePage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Call your patch updates within this function.
     */
    abstract public function apply();
}
