<?php
/**
 * @author Trellis Team
 * @copyright Copyright © 2025 Trellis
 */
declare(strict_types=1);

namespace Gogo\FreeShippingBanner\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Banner extends Template
{
    private const XML_PATH_ENABLED = 'gogo_free_shipping_banner/general/enabled';
    private const XML_PATH_CONTENT = 'gogo_free_shipping_banner/general/content';
    private const XML_PATH_THRESHOLD = 'gogo_free_shipping_banner/general/threshold_value';
    private const XML_PATH_CLOSED_DAYS = 'gogo_free_shipping_banner/general/display_closed_days';
    private const XML_PATH_BG_COLOR = 'gogo_free_shipping_banner/general/banner_bg_color';
    private const XML_PATH_TEXT_COLOR = 'gogo_free_shipping_banner/general/banner_text_color';
    private const XML_PATH_CLOSE_BUTTON = 'gogo_free_shipping_banner/general/show_close_button';
    private const COOKIE_NAME = 'gogo_fsb_closed';
    private const STORAGE_KEY = 'gogo_fsb_closed';
    private const STORAGE_TIMESTAMP_KEY = 'gogo_fsb_closed_ts';

    public function __construct(
        Template\Context $context,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly CookieManagerInterface $cookieManager,
        private readonly Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getBannerContent(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CONTENT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getThresholdValue(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_THRESHOLD,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDisplayClosedDays(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_CLOSED_DAYS,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getBackgroundColor(): string
    {
        $color = (string) $this->scopeConfig->getValue(
            self::XML_PATH_BG_COLOR,
            ScopeInterface::SCOPE_STORE
        );

        return $color ?: '#F5F5F5';
    }

    public function getTextColor(): string
    {
        $color = (string) $this->scopeConfig->getValue(
            self::XML_PATH_TEXT_COLOR,
            ScopeInterface::SCOPE_STORE
        );

        return $color ?: '#333333';
    }

    public function showCloseButton(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CLOSE_BUTTON,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isBannerClosed(): bool
    {
        try {
            $value = $this->cookieManager->getCookie(self::COOKIE_NAME);

            return $value === '1';
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getJsonConfig(): string
    {
        return $this->json->serialize([
            'cookieName' => self::COOKIE_NAME,
            'cookieLifetime' => $this->getDisplayClosedDays() * 86400,
            'storageKey' => self::STORAGE_KEY,
            'storageTimestampKey' => self::STORAGE_TIMESTAMP_KEY,
            'isEnabled' => $this->isEnabled(),
        ]);
    }
}
