<?php
/**
 * @author Trellis Team
 * @copyright Copyright © 2025 Trellis
 */
declare(strict_types=1);

namespace Gogo\StoreAddress\Test\Unit\ViewModel;

use Gogo\StoreAddress\ViewModel\StoreEmail;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Gogo\StoreAddress\ViewModel\StoreEmail
 */
class StoreEmailTest extends TestCase
{
    private MockObject|ScopeConfigInterface $scopeConfigMock;

    private StoreEmail $viewModel;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->viewModel = new StoreEmail($this->scopeConfigMock);
    }

    public function testGetEmailReturnsConfiguredEmail(): void
    {
        $email = 'store@example.com';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE)
            ->willReturn($email);

        $this->assertSame($email, $this->viewModel->getEmail());
    }

    public function testGetEmailReturnsEmptyStringWhenConfigReturnsNull(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE)
            ->willReturn(null);

        $this->assertSame('', $this->viewModel->getEmail());
    }

    public function testGetEmailReturnsEmptyStringWhenConfigReturnsEmptyString(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE)
            ->willReturn('');

        $this->assertSame('', $this->viewModel->getEmail());
    }

    public function testGetEmailCallsGetValueWithCorrectPathAndScope(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'trans_email/ident_general/email',
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn('info@example.com');

        $this->viewModel->getEmail();
    }
}
