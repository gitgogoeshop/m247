<?php
/**
 * @author Trellis Team
 * @copyright Copyright © 2025 Trellis
 */
declare(strict_types=1);

namespace Gogo\CustomerGroup\Test\Unit\ViewModel\Account\Dashboard;

use Gogo\CustomerGroup\ViewModel\Account\Dashboard\CustomerGroup;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Gogo\CustomerGroup\ViewModel\Account\Dashboard\CustomerGroup
 */
class CustomerGroupTest extends TestCase
{
    private MockObject|Session $sessionMock;

    private MockObject|GroupRepositoryInterface $groupRepositoryMock;

    private MockObject|LoggerInterface $loggerMock;

    private CustomerGroup $viewModel;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(Session::class);
        $this->groupRepositoryMock = $this->createMock(GroupRepositoryInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->viewModel = new CustomerGroup(
            $this->sessionMock,
            $this->groupRepositoryMock,
            $this->loggerMock
        );
    }

    public function testGetGroupNameWhenLoggedIn(): void
    {
        $groupId = 1;
        $groupCode = 'Gold Member';

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($groupId);

        $groupMock = $this->createMock(GroupInterface::class);
        $groupMock->expects($this->once())
            ->method('getCode')
            ->willReturn($groupCode);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(42);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerData')
            ->willReturn($customerMock);

        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($groupId)
            ->willReturn($groupMock);

        $this->assertSame($groupCode, $this->viewModel->getGroupName());
    }

    public function testHasGroupWhenValidGroup(): void
    {
        $groupId = 1;

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($groupId);

        $groupMock = $this->createMock(GroupInterface::class);
        $groupMock->method('getCode')
            ->willReturn('Gold Member');

        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(42);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerData')
            ->willReturn($customerMock);

        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($groupId)
            ->willReturn($groupMock);

        $this->assertTrue($this->viewModel->hasGroup());
    }

    public function testGetGroupNameReturnsNullWhenNotLoggedIn(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(null);

        $this->sessionMock->expects($this->never())
            ->method('getCustomerData');

        $this->groupRepositoryMock->expects($this->never())
            ->method('getById');

        $this->assertNull($this->viewModel->getGroupName());
        $this->assertFalse($this->viewModel->hasGroup());
    }

    public function testGetGroupNameReturnsNullWhenNoSuchEntity(): void
    {
        $groupId = 5;

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($groupId);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(10);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerData')
            ->willReturn($customerMock);

        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($groupId)
            ->willThrowException(new NoSuchEntityException(__('No such entity')));

        $this->loggerMock->expects($this->once())
            ->method('error');

        $this->assertNull($this->viewModel->getGroupName());
    }

    public function testGetGroupNameWhenNotLoggedInGroup(): void
    {
        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn(GroupInterface::NOT_LOGGED_IN_ID);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(99);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerData')
            ->willReturn($customerMock);

        $this->groupRepositoryMock->expects($this->never())
            ->method('getById');

        $this->assertNull($this->viewModel->getGroupName());
    }

    public function testHasGroupReturnsFalseWhenGroupCodeIsEmpty(): void
    {
        $groupId = 2;

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($groupId);

        $groupMock = $this->createMock(GroupInterface::class);
        $groupMock->method('getCode')
            ->willReturn('');

        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(7);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerData')
            ->willReturn($customerMock);

        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($groupId)
            ->willReturn($groupMock);

        $this->assertFalse($this->viewModel->hasGroup());
    }

    public function testGetGroupNameReturnsNullWhenCustomerDataIsNull(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(42);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerData')
            ->willReturn(null);

        $this->groupRepositoryMock->expects($this->never())
            ->method('getById');

        $this->assertNull($this->viewModel->getGroupName());
        $this->assertFalse($this->viewModel->hasGroup());
        $this->assertNull($this->viewModel->getGroupId());
    }

    public function testGetGroupIdReturnsNullWhenGroupIdIsNull(): void
    {
        $groupId = 3;

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($groupId);

        $groupMock = $this->createMock(GroupInterface::class);
        $groupMock->method('getId')
            ->willReturn(null);
        $groupMock->method('getCode')
            ->willReturn('Retailer');

        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(55);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerData')
            ->willReturn($customerMock);

        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($groupId)
            ->willReturn($groupMock);

        $this->assertNull($this->viewModel->getGroupId());
    }

    public function testGetGroupIdReturnsIntWhenGroupLoaded(): void
    {
        $groupId = 3;

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($groupId);

        $groupMock = $this->createMock(GroupInterface::class);
        $groupMock->method('getId')
            ->willReturn($groupId);
        $groupMock->method('getCode')
            ->willReturn('Retailer');

        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(55);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerData')
            ->willReturn($customerMock);

        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($groupId)
            ->willReturn($groupMock);

        $this->assertSame($groupId, $this->viewModel->getGroupId());
    }
}
