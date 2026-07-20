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
 * ViewModel to provide store email for footer display.
 */
class StoreEmail implements ArgumentInterface
{
    private const XML_PATH_STORE_EMAIL = 'trans_email/ident_general/email';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Retrieve the store email address from configuration.
     */
    public function getEmail(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_STORE_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
    }
}
