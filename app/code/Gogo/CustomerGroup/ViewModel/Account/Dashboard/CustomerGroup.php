<?php
/**
 * @author Trellis Team
 * @copyright Copyright © 2025 Trellis
 */
declare(strict_types=1);

namespace Gogo\CustomerGroup\ViewModel\Account\Dashboard;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

/**
 * ViewModel to provide customer group info for the My Account dashboard.
 */
class CustomerGroup implements ArgumentInterface
{
    private ?GroupInterface $group = null;

    private bool $groupResolved = false;

    public function __construct(
        private readonly Session $customerSession,
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Get current customer's group display name.
     *
     * @return string|null Returns null if customer not logged in or group not found
     */
    public function getGroupName(): ?string
    {
        $group = $this->getGroup();

        return $group ? $group->getCode() : null;
    }

    /**
     * Get current customer's group ID.
     */
    public function getGroupId(): ?int
    {
        $group = $this->getGroup();
        if ($group === null) {
            return null;
        }

        $id = $group->getId();

        return $id !== null ? (int) $id : null;
    }

    /**
     * Check if customer has a valid displayable group.
     */
    public function hasGroup(): bool
    {
        $name = $this->getGroupName();

        return $name !== null && $name !== '';
    }

    /**
     * Retrieve the customer group.
     */
    private function getGroup(): ?GroupInterface
    {
        if ($this->groupResolved) {
            return $this->group;
        }

        $this->groupResolved = true;

        try {
            $customerId = $this->customerSession->getCustomerId();
            if (!$customerId) {
                return null;
            }

            $customer = $this->customerSession->getCustomerData();
            if ($customer === null) {
                return null;
            }

            $groupId = $customer->getGroupId();

            if ($groupId === null || (int) $groupId === GroupInterface::NOT_LOGGED_IN_ID) {
                return null;
            }

            $this->group = $this->groupRepository->getById((int) $groupId);
        } catch (NoSuchEntityException | LocalizedException $e) {
            $this->logger->error('Failed to retrieve customer group: ' . $e->getMessage());
            return null;
        }

        return $this->group;
    }
}
