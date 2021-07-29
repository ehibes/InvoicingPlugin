<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\InvoicingPlugin\Provider;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Order\Model\AdjustableInterface;
use Sylius\InvoicingPlugin\Exception\MoreThanOneTaxAdjustment;
use Webmozart\Assert\Assert;

final class TaxRateProvider implements TaxRateProviderInterface
{
    public function provideFromAdjustable(AdjustableInterface $adjustable): ?string
    {
        /** @var Collection|AdjustmentInterface[] $taxAdjustments */
        $taxAdjustments = $adjustable->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT);

        if (count($taxAdjustments) > 1) {
            throw MoreThanOneTaxAdjustment::occur();
        }

        if ($taxAdjustments->isEmpty()) {
            return null;
        }

        return $this->provideFromAdjustment($taxAdjustments->first());
    }

    public function provideFromAdjustment(AdjustmentInterface $adjustment): ?string
    {
        $details = $adjustment->getDetails();

        Assert::keyExists(
            $details,
            'taxRateAmount',
            'There is no tax rate amount in details of this adjustment'
        );

        return $details['taxRateAmount'] * 100 . '%';
    }
}
