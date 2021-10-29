<?php

namespace GhostUnicorns\CrtReports\Ui\Component\Listing\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Date format column
 *
 * @api
 * @since 100.0.2
 */
class Datetime extends Column
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var DataBundle
     */
    private $dataBundle;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param BooleanUtils $booleanUtils
     * @param array $components
     * @param array $data
     * @param ResolverInterface|null $localeResolver
     * @param DataBundle|null $dataBundle
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        BooleanUtils $booleanUtils,
        array $components = [],
        array $data = [],
        ResolverInterface $localeResolver = null,
        DataBundle $dataBundle = null
    ) {
        $this->timezone = $timezone;
        $this->booleanUtils = $booleanUtils;
        $this->localeResolver = $localeResolver ?? ObjectManager::getInstance()->get(ResolverInterface::class);
        $this->locale = $this->localeResolver->getLocale();
        $this->dataBundle = $dataBundle ?? ObjectManager::getInstance()->get(DataBundle::class);
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     * @since 101.1.1
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($config['filter'])) {
            $config['filter'] = [
                'filterType' => 'dateRange',
                'templates' => [
                    'date' => [
                        'options' => [
                            'dateFormat' => 'yyyy-MM-dd',
                            'showsTime' => true,
                            'timeFormat' => 'H:mm:ss',
                        ]
                    ]
                ]
            ];
        }

        $localeData = $this->dataBundle->get($this->locale);
        $monthsData = $localeData['calendar']['gregorian']['monthNames'];
        $months = array_values(iterator_to_array($monthsData['format']['wide']));
        $monthsShort = array_values(
            iterator_to_array(
                null !== $monthsData->get('format')->get('abbreviated')
                    ? $monthsData['format']['abbreviated']
                    : $monthsData['format']['wide']
            )
        );

        $config['storeLocale'] = $this->locale;
        $config['calendarConfig'] = [
            'months' => $months,
            'monthsShort' => $monthsShort,
        ];
        if (!isset($config['dateFormat'])) {
            $config['dateFormat'] = 'yyyy-MM-dd H:mm:ss';
        }
        $config['options']['showsTime'] = true;
        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])
                    && $item[$this->getData('name')] !== "0000-00-00 00:00:00"
                ) {
                    $date = $this->timezone->date(new \DateTime($item[$this->getData('name')]));
                    $item[$this->getData('name')] = $date->format('Y-m-d H:i:s');
                }
            }
        }

        return $dataSource;
    }
}
