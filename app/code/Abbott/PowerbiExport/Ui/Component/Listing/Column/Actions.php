<?php


namespace Abbott\PowerbiExport\Ui\Component\Listing\Column;

class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{

    public const URL_PATH_DELETE = 'powerbi_export/powerbi/delete';
    public const URL_PATH_EDIT = 'powerbi_export/index/edit';
    public const URL_PATH_GENERATE = 'powerbi_export/index/generate';
    public const ENTITY_ID = 'entity_id';

    /**
     * @var urlBuilder
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[self::ENTITY_ID])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    self::ENTITY_ID => $item[self::ENTITY_ID]
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    self::ENTITY_ID => $item[self::ENTITY_ID]
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete'),
                                'message' => __('Are you sure you want to delete this record?')
                            ]
                            ],
                        'generate' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_GENERATE,
                                [
                                    self::ENTITY_ID => $item[self::ENTITY_ID]
                                ]
                            ),
                            'label' => __('Generate'),
                            'confirm' => [
                                'title' => __('Generate'),
                                'message' => __('Are you sure you want to Generate files for this report?')
                            ]
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
