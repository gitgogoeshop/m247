<?php
/**
 * @author Trellis Team
 * @copyright Copyright © 2025 Trellis
 */
declare(strict_types=1);

namespace Gogo\FreeShippingBanner\Test\Unit\Block;

use Gogo\FreeShippingBanner\Block\Banner;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Gogo\FreeShippingBanner\Block\Banner
 */
class BannerTest extends TestCase
{
    private const COOKIE_NAME = 'gogo_fsb_closed';

    private Banner $block;

    private MockObject|ScopeConfigInterface $scopeConfigMock;

    private MockObject|CookieManagerInterface $cookieManagerMock;

    private MockObject|Json $jsonMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->cookieManagerMock = $this->createMock(CookieManagerInterface::class);
        $this->jsonMock = $this->createMock(Json::class);

        $contextMock = $this->createMock(Context::class);

        $this->block = new Banner(
            $contextMock,
            $this->scopeConfigMock,
            $this->cookieManagerMock,
            $this->jsonMock
        );
    }

    public function testIsEnabledReturnsTrueWhenConfigEnabled(): void
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('isSetFlag')
            ->with('gogo_free_shipping_banner/general/enabled', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $this->assertTrue($this->block->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenConfigDisabled(): void
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('isSetFlag')
            ->with('gogo_free_shipping_banner/general/enabled', ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $this->assertFalse($this->block->isEnabled());
    }

    public function testGetBannerContentReturnsConfiguredValue(): void
    {
        $content = '<strong>Free shipping</strong> on orders over <strong>HK$300</strong>.';

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('gogo_free_shipping_banner/general/content', ScopeInterface::SCOPE_STORE)
            ->willReturn($content);

        $this->assertSame($content, $this->block->getBannerContent());
    }

    public function testGetThresholdValueReturnsConfiguredValue(): void
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('gogo_free_shipping_banner/general/threshold_value', ScopeInterface::SCOPE_STORE)
            ->willReturn('300');

        $this->assertSame('300', $this->block->getThresholdValue());
    }

    public function testGetDisplayClosedDaysReturnsConfiguredValue(): void
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('gogo_free_shipping_banner/general/display_closed_days', ScopeInterface::SCOPE_STORE)
            ->willReturn('7');

        $this->assertSame(7, $this->block->getDisplayClosedDays());
    }

    public function testGetBackgroundColorReturnsDefaultWhenEmpty(): void
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('gogo_free_shipping_banner/general/banner_bg_color', ScopeInterface::SCOPE_STORE)
            ->willReturn('');

        $this->assertSame('#F5F5F5', $this->block->getBackgroundColor());
    }

    public function testGetBackgroundColorReturnsConfiguredValue(): void
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('gogo_free_shipping_banner/general/banner_bg_color', ScopeInterface::SCOPE_STORE)
            ->willReturn('#FF0000');

        $this->assertSame('#FF0000', $this->block->getBackgroundColor());
    }

    public function testGetTextColorReturnsDefaultWhenEmpty(): void
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('gogo_free_shipping_banner/general/banner_text_color', ScopeInterface::SCOPE_STORE)
            ->willReturn(null);

        $this->assertSame('#333333', $this->block->getTextColor());
    }

    public function testShowCloseButtonReturnsConfiguredValue(): void
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('isSetFlag')
            ->with('gogo_free_shipping_banner/general/show_close_button', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $this->assertTrue($this->block->showCloseButton());
    }

    public function testIsBannerClosedReturnsTrueWhenCookieSet(): void
    {
        $this->cookieManagerMock
            ->expects($this->once())
            ->method('getCookie')
            ->with(self::COOKIE_NAME)
            ->willReturn('1');

        $this->assertTrue($this->block->isBannerClosed());
    }

    public function testIsBannerClosedReturnsFalseWhenCookieNotSet(): void
    {
        $this->cookieManagerMock
            ->expects($this->once())
            ->method('getCookie')
            ->with(self::COOKIE_NAME)
            ->willReturn(null);

        $this->assertFalse($this->block->isBannerClosed());
    }

    public function testIsBannerClosedReturnsFalseOnException(): void
    {
        $this->cookieManagerMock
            ->expects($this->once())
            ->method('getCookie')
            ->with(self::COOKIE_NAME)
            ->willThrowException(new \RuntimeException('Cookie error'));

        $this->assertFalse($this->block->isBannerClosed());
    }

    public function testGetJsonConfigSerializesExpectedData(): void
    {
        $this->scopeConfigMock
            ->method('isSetFlag')
            ->with('gogo_free_shipping_banner/general/enabled', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $this->scopeConfigMock
            ->method('getValue')
            ->with('gogo_free_shipping_banner/general/display_closed_days', ScopeInterface::SCOPE_STORE)
            ->willReturn('7');

        $expectedConfig = [
            'cookieName' => self::COOKIE_NAME,
            'cookieLifetime' => 604800,
            'storageKey' => 'gogo_fsb_closed',
            'storageTimestampKey' => 'gogo_fsb_closed_ts',
            'isEnabled' => true,
        ];

        $this->jsonMock
            ->expects($this->once())
            ->method('serialize')
            ->with($expectedConfig)
            ->willReturn(
                '{"cookieName":"gogo_fsb_closed","cookieLifetime":604800,'
                . '"storageKey":"gogo_fsb_closed","storageTimestampKey":"gogo_fsb_closed_ts","isEnabled":true}'
            );

        $this->assertSame(
            '{"cookieName":"gogo_fsb_closed","cookieLifetime":604800,'
            . '"storageKey":"gogo_fsb_closed","storageTimestampKey":"gogo_fsb_closed_ts","isEnabled":true}',
            $this->block->getJsonConfig()
        );
    }
}
