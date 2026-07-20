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
use Magento\Store\Model\Information;

/**
 * ViewModel to provide the configured store phone number for footer display.
 *
 * Reads from the existing Magento core config path general/store_information/phone
 * defined in Magento\Store\Model\Information::XML_PATH_STORE_INFO_PHONE.
 */
class StorePhone implements ArgumentInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Retrieve the store phone number from configuration.
     */
    public function getPhone(): string
    {
        return (string) $this->scopeConfig->getValue(
            Information::XML_PATH_STORE_INFO_PHONE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the store phone number formatted for the tel: URI scheme.
     * Strips all non-numeric characters except leading '+' to support international numbers.
     */
    public function getPhoneUri(): string
    {
        $phone = $this->getPhone();
        $startsWithPlus = str_starts_with($phone, '+');
        $digits = preg_replace('/[^0-9]/', '', $phone);

        return $startsWithPlus ? '+' . $digits : (string) $digits;
    }
}
