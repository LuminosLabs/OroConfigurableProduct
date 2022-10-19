<?php

namespace Luminoslabs\OroConfigurableProduct\Provider;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductVariantLink;
use Oro\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Bundle\ProductBundle\Event\RestrictProductVariantEvent;
use Oro\Bundle\ProductBundle\ProductVariant\Registry\ProductVariantFieldValueHandlerRegistry;
use Oro\Bundle\ProductBundle\Provider\CustomFieldProvider;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Oro\Bundle\ProductBundle\Provider\ProductVariantAvailabilityProvider;

/**
 * Get product variants by configurable products.
 * Get products fields availability for given configurable products.
 */
class ProductVariantProvider extends ProductVariantAvailabilityProvider
{
    public function __construct(
        ManagerRegistry $doctrine,
        CustomFieldProvider $customFieldProvider,
        PropertyAccessorInterface $propertyAccessor,
        EventDispatcherInterface $eventDispatcher,
        ProductVariantFieldValueHandlerRegistry $fieldValueHandlerRegistry,
        private DoctrineHelper $doctrineHelper
    ) {
        parent::__construct($doctrine, $customFieldProvider, $propertyAccessor, $eventDispatcher, $fieldValueHandlerRegistry);
    }

    public function getSimpleProductByVariantFields(
        Product $configurableProduct,
        array $variantParameters = [],
                $throwException = true
    ) {
        $this->ensureProductTypeIsConfigurable($configurableProduct);
        $simpleProducts = $this->getSimpleProductsByVariantFields($configurableProduct, $variantParameters);

        if ($throwException && count($simpleProducts) !== 1) {
            throw new \InvalidArgumentException('Variant values provided don\'t match exactly one simple product');
        }

        $defaultVariant = $configurableProduct->getDefaultVariantProduct();
        if ($defaultVariant && $this->isAssignedToParent($simpleProducts, $defaultVariant)) {
            return $defaultVariant;
        }

        return $simpleProducts ? reset($simpleProducts) : null;
    }

    /**
     * @return bool
     */
    private function isAssignedToParent($simpleProducts, $product)
    {
        foreach ($simpleProducts as $simple) {
            if ($simple->getId() == $product->getId()) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param Product $product
     *
     * @throws \InvalidArgumentException
     */
    private function ensureProductTypeIsConfigurable(Product $product)
    {
        if (!$product->isConfigurable()) {
            throw new \InvalidArgumentException(
                sprintf('Product with type "%s" expected, "%s" given', Product::TYPE_CONFIGURABLE, $product->getType())
            );
        }
    }


}
