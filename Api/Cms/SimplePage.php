<?php
declare(strict_types = 1);

namespace MarkShust\SimpleData\Api\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Class SimplePage
 * @package MarkShust\SimpleData\Api\Cms
 */
class SimplePage
{
    /** @var GetPageByIdentifierInterface */
    protected GetPageByIdentifierInterface $getPageByIdentifier;

    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    /** @var PageInterfaceFactory */
    protected PageInterfaceFactory $pageInterfaceFactory;

    /** @var PageRepository */
    protected PageRepository $pageRepository;

    /**
     * UpdateConfig constructor.
     * @param GetPageByIdentifierInterface $getPageByIdentifier
     * @param LoggerInterface $logger
     * @param PageInterfaceFactory $pageInterfaceFactory
     * @param PageRepository $pageRepository
     */
    public function __construct(
        GetPageByIdentifierInterface $getPageByIdentifier,
        LoggerInterface $logger,
        PageInterfaceFactory $pageInterfaceFactory,
        PageRepository $pageRepository
    ) {
        $this->getPageByIdentifier = $getPageByIdentifier;
        $this->logger = $logger;
        $this->pageInterfaceFactory = $pageInterfaceFactory;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Delete a page from a given identifier and optional store id.
     * @param string $identifier
     * @param int $storeId
     */
    public function delete(string $identifier, int $storeId = Store::DEFAULT_STORE_ID): void
    {
        try {
            $page = $this->getPageByIdentifier->execute($identifier, $storeId);
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
            return;
        }

        try {
            $this->pageRepository->delete($page);
        } catch (CouldNotDeleteException $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * If the CMS page identifier is found, attempt to update the record.
     * If it is not found, attempt to create a new record.
     * @param array $data
     */
    public function save(array $data): void
    {
        $identifier = $data['identifier'];
        $storeId = $data['store_id'] ?? Store::DEFAULT_STORE_ID;

        try {
            $page = $this->getPageByIdentifier->execute($identifier, $storeId);
        } catch (NoSuchEntityException $e) {
            // Rather than throwing an exception, create a new page instance

            /** @var PageInterface|AbstractModel $page */
            $page = $this->pageInterfaceFactory->create();
            $page->setIdentifier($identifier);

            // Set initial store data to "all stores"
            $page->setData('store_id', $storeId);
            $page->setData('stores', [$storeId]);

            // Set a default page layout
            $page->setData('page_layout', '1column');
        }

        $elements = [
            'content',
            'content_heading',
            'custom_layout_update_xml',
            'custom_root_template',
            'custom_theme',
            'custom_theme_from',
            'custom_theme_to',
            'is_active',
            'layout_update_selected',
            'layout_update_xml',
            'meta_description',
            'meta_keywords',
            'meta_title',
            'page_layout',
            'sort_order',
            'stores',
            'title',
        ];

        foreach ($elements as $element) {
            if (isset($data[$element])) {
                $page->setData($element, $data[$element]);
            }
        }

        try {
            $this->pageRepository->save($page);
        } catch (CouldNotSaveException $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
