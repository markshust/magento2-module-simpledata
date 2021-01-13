<?php
declare(strict_types = 1);

namespace MarkShust\SimpleData\Api\Cms;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Class SimpleBlock
 * @package MarkShust\SimpleData\Api\Cms
 */
class SimpleBlock
{
    /** @var BlockInterfaceFactory */
    protected $blockInterfaceFactory;

    /** @var BlockRepository */
    protected $blockRepository;

    /** @var GetBlockByIdentifierInterface */
    protected $getBlockByIdentifier;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * UpdateConfig constructor.
     * @param BlockInterfaceFactory $blockInterfaceFactory
     * @param BlockRepository $blockRepository
     * @param GetBlockByIdentifierInterface $getBlockByIdentifier
     * @param LoggerInterface $logger
     */
    public function __construct(
        BlockInterfaceFactory $blockInterfaceFactory,
        BlockRepository $blockRepository,
        GetBlockByIdentifierInterface $getBlockByIdentifier,
        LoggerInterface $logger
    ) {
        $this->blockInterfaceFactory = $blockInterfaceFactory;
        $this->blockRepository = $blockRepository;
        $this->getBlockByIdentifier = $getBlockByIdentifier;
        $this->logger = $logger;
    }

    /**
     * Delete a block from a given identifier and optional store id.
     * @param string $identifier
     * @param int $storeId
     */
    public function delete(string $identifier, int $storeId = Store::DEFAULT_STORE_ID): void
    {
        try {
            $block = $this->getBlockByIdentifier->execute($identifier, $storeId);
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
            return;
        }

        try {
            $this->blockRepository->delete($block);
        } catch (CouldNotDeleteException $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * If the CMS block identifier is found, attempt to update the record.
     * If it is not found, attempt to create a new record.
     * @param array $data
     */
    public function save(array $data): void
    {
        $identifier = $data['identifier'];
        $storeId = $data['store_id'] ?? Store::DEFAULT_STORE_ID;

        try {
            $block = $this->getBlockByIdentifier->execute($identifier, $storeId);
        } catch (NoSuchEntityException $e) {
            // Rather than throwing an exception, create a new block instance

            /** @var BlockInterface|AbstractModel $block */
            $block = $this->blockInterfaceFactory->create();
            $block->setIdentifier($identifier);

            // Set initial store data to "all stores"
            $block->setData('store_id', $storeId);
            $block->setData('stores', [$storeId]);
        }

        $elements = [
            'content',
            'is_active',
            'stores',
            'title',
        ];

        foreach ($elements as $element) {
            if (isset($data[$element])) {
                $block->setData($element, $data[$element]);
            }
        }

        try {
            $this->blockRepository->save($block);
        } catch (CouldNotSaveException $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
