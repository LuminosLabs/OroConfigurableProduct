<?php

namespace Luminoslabs\OroConfigurableProduct\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteSelectType;
use Luminoslabs\OroConfigurableProduct\Form\Type\SimpleProductsOptionType;
use Luminoslabs\OroConfigurableProduct\Form\Type\VariantProductType;

class AddProductDefaultVariantField implements Migration, ExtendExtensionAwareInterface
{
    private ExtendExtension $extendExtension;

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        $this->extendExtension->addManyToOneRelation(
            $schema,
            'oro_product',
            'default_variant_product',
            'oro_product',
            'name',
            [
                'extend' => [
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'is_extend' => true
                ],
                'form' => [
                    'is_enabled' => true,
                    'form_type' => VariantProductType::class,
                ]
            ]
        );
    }
}
