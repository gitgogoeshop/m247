<?php
/**
 * @author Trellis Team
 * @copyright Copyright © 2025 Trellis
 */
declare(strict_types=1);

namespace Gogo\StoreAddress\Test\Unit\ViewModel;

use Gogo\StoreAddress\ViewModel\StoreAddress;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Gogo\StoreAddress\ViewModel\StoreAddress
 */
class StoreAddressTest extends TestCase
{
    private MockObject|ScopeConfigInterface $scopeConfigMock;

    private StoreAddress $viewModel;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->viewModel = new StoreAddress($this->scopeConfigMock);
    }

    public function testGetFormattedAddressReturnsConfiguredString(): void
    {
        $address = '123 Main St, Springfield, USA';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('general/store_information/address', ScopeInterface::SCOPE_STORE)
            ->willReturn($address);

        $this->assertSame($address, $this->viewModel->getFormattedAddress());
    }

    public function testGetFormattedAddressReturnsEmptyStringWhenConfigReturnsNull(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('general/store_information/address', ScopeInterface::SCOPE_STORE)
            ->willReturn(null);

        $this->assertSame('', $this->viewModel->getFormattedAddress());
    }

    public function testGetFormattedAddressReturnsEmptyStringWhenConfigReturnsEmptyString(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('general/store_information/address', ScopeInterface::SCOPE_STORE)
            ->willReturn('');

        $this->assertSame('', $this->viewModel->getFormattedAddress());
    }

    public function testGetFormattedAddressCallsGetValueWithCorrectPathAndScope(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'general/store_information/address',
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn('Some Address');

        $this->viewModel->getFormattedAddress();
    }
}
