<?php

    namespace MarkShust\SimpleData\Console;

    use Amasty\Shopby\Api\CmsPageRepositoryInterface;
    use Magento\Cms\Api\BlockRepositoryInterface;
    use Magento\Cms\Api\GetBlockByIdentifierInterface;
    use Magento\Cms\Api\GetPageByIdentifierInterface;
    use Magento\Framework\Console\Cli;
    use Magento\Framework\Exception\NoSuchEntityException;
    use Magento\Store\Model\Store;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class ExportItem extends Command
    {
        const COMMAND_NAME = 'markshust:simpledata:exportcms';
        const TYPE_ARGUMENT = 'type';
        const IDENTIFIER_ARGUMENT = 'identifier';
        const STORE_ARGUMENT = 'store';

        const TYPE_BLOCK = 'block';
        const TYPE_PAGE = 'page';
        /**
         * @var GetPageByIdentifierInterface
         */
        private $getPageByIdentifier;
        /**
         * @var GetBlockByIdentifierInterface
         */
        private $getBlockByIdentifier;

        public function __construct(
            GetBlockByIdentifierInterface $getBlockByIdentifier,
            GetPageByIdentifierInterface $getPageByIdentifier,
            string $name = null
        )
        {
            $this->getBlockByIdentifier = $getBlockByIdentifier;
            $this->getPageByIdentifier = $getPageByIdentifier;
            parent::__construct($name);
        }

        protected function configure()
        {
            $this->setName(self::COMMAND_NAME);
            $this->setDescription('Export CMS object data as a PHP array for use with simple data scripts');
            $this->setDefinition(
                [
                    new InputArgument(
                        self::TYPE_ARGUMENT,
                        InputArgument::REQUIRED
                    ),
                    new InputArgument(
                        self::IDENTIFIER_ARGUMENT,
                        InputArgument::REQUIRED
                    ),
                    new InputArgument(
                        self::STORE_ARGUMENT,
                        InputArgument::OPTIONAL,
                        'Store Id',
                        Store::DEFAULT_STORE_ID
                    )
               ]
            );

            parent::configure();
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $entityType = $input->getArgument(self::TYPE_ARGUMENT);
            $identifier = $input->getArgument(self::IDENTIFIER_ARGUMENT);
            $storeId    = $input->getArgument(self::STORE_ARGUMENT);
            $errors = $this->validate($entityType);
            if ($errors) {
                foreach ($errors as $error) {
                    $output->writeln('<error>'.$error.'</error>');
                }
                return Cli::RETURN_FAILURE;
            }
            try {
                if ($entityType == self::TYPE_BLOCK) {
                    $output->writeln($this->generateBlock($identifier, $storeId));
                }
                if ($entityType == self::TYPE_PAGE) {
                    $output->writeln($this->generatePage($identifier, $storeId));
                }
            } catch (NoSuchEntityException $nse) {
                $output->writeln("No {$entityType} with the identifier {$identifier} was found");
                return Cli::RETURN_FAILURE;
            }
            return Cli::RETURN_SUCCESS;
        }

        private function validate(string $entityType)
        {
            $errors = [];
            if ($entityType != self::TYPE_BLOCK && $entityType != self::TYPE_PAGE) {
                $errors[] = "Invalid value for type must be one of: page, block";
            }
            return $errors;
        }

        /**
         * @param string $identifier
         * @return string
         * @throws NoSuchEntityException
         */
        private function generatePage(string $identifier, int $storeId)
        {
            $page = $this->getPageByIdentifier->execute($identifier, $storeId);
            return sprintf(
                "[
                    'identifier' => '%s',
                    'title'      => '%s',
                    'content'    => <<<CONTENT
                        %s
                    CONTENT,
                ]
                ",
                $identifier,
                $page->getTitle(),
                $page->getContent()
            );
        }

        /**
         * @param string $identifier
         * @return string
         * @throws NoSuchEntityException
         */
        private function generateBlock(string $identifier, int $storeId)
        {
            $block = $this->getBlockByIdentifier->execute($identifier, $storeId);
            return sprintf(
                "[
                    'identifier' => '%s',
                    'title'      => '%s',
                    'content'    => <<<CONTENT
                        %s
                    CONTENT,
                    ]
                ",
                $identifier,
                $block->getTitle(),
                $block->getContent()
            );
        }
    }
