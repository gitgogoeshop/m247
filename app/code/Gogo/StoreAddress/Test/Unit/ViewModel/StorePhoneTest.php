<?php
/**
 * @author Trellis Team
 * @copyright Copyright © 2025 Trellis
 */
declare(strict_types=1);

namespace Gogo\StoreAddress\Test\Unit\ViewModel;

use Gogo\StoreAddress\ViewModel\StorePhone;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Gogo\StoreAddress\ViewModel\StorePhone
 */
class StorePhoneTest extends TestCase
{
    private MockObject|ScopeConfigInterface $scopeConfigMock;

    private StorePhone $viewModel;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->viewModel = new StorePhone($this->scopeConfigMock);
    }

    public function testGetPhoneReturnsConfiguredPhone(): void
    {
        $phone = '(123) 456-7890';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Information::XML_PATH_STORE_INFO_PHONE, ScopeInterface::SCOPE_STORE)
            ->willReturn($phone);

        $this->assertSame($phone, $this->viewModel->getPhone());
    }

    public function testGetPhoneReturnsEmptyStringWhenConfigReturnsNull(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Information::XML_PATH_STORE_INFO_PHONE, ScopeInterface::SCOPE_STORE)
            ->willReturn(null);

        $this->assertSame('', $this->viewModel->getPhone());
    }

    public function testGetPhoneReturnsEmptyStringWhenConfigReturnsEmptyString(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Information::XML_PATH_STORE_INFO_PHONE, ScopeInterface::SCOPE_STORE)
            ->willReturn('');

        $this->assertSame('', $this->viewModel->getPhone());
    }

    public function testGetPhoneCallsGetValueWithCorrectPathAndScope(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Information::XML_PATH_STORE_INFO_PHONE,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn('(123) 456-7890');

        $this->viewModel->getPhone();
    }

    public function testGetPhoneUriStripsNonDigitsFromLocalNumber(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturn('(123) 456-7890');

        $this->assertSame('1234567890', $this->viewModel->getPhoneUri());
    }

    public function testGetPhoneUriPreservesLeadingPlusForInternationalNumber(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturn('+44 20 7946 0958');

        $this->assertSame('+442079460958', $this->viewModel->getPhoneUri());
    }

    public function testGetPhoneUriStripsLettersAndPreservesLeadingPlus(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturn('+1-800-FLOWERS');

        $this->assertSame('+1800', $this->viewModel->getPhoneUri());
    }

    public function testGetPhoneUriReturnsEmptyStringWhenPhoneIsEmpty(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturn('');

        $this->assertSame('', $this->viewModel->getPhoneUri());
    }

    public function testGetPhoneUriLeavesPlainDigitsUnchanged(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturn('5124666492');

        $this->assertSame('5124666492', $this->viewModel->getPhoneUri());
    }
}
