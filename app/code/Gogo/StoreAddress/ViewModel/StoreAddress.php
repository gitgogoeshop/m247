<?php
/**
 * @author Trellis Team
 * @copyright Copyright © 2025 Trellis
 */
declare(strict_types=1);

namespace Gogo\StoreAddress\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * ViewModel to provide the configured store address for footer display.
 */
class StoreAddress implements ArgumentInterface
{
    private const XML_PATH_STORE_ADDRESS = 'general/store_information/address';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Retrieve the store street address from configuration.
     */
    public function getFormattedAddress(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_STORE_ADDRESS,
            ScopeInterface::SCOPE_STORE
        );
    }
}
