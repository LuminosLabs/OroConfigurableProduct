<?php

namespace Luminoslabs\OroConfigurableProduct\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\Select2EntityType;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Bundle\ProductBundle\Helper\ProductGrouper\ProductsGrouperFactory;
use Oro\Bundle\ProductBundle\Storage\ProductDataStorage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Event\PreSetDataEvent;

class VariantProductType extends AbstractType
{
    private $parentProduct;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (PreSetDataEvent $event) {
            $form = $event->getForm();
            /** @var Product $parentProduct */
            $parentProduct = $form->getParent()->getData();
            if ($parentProduct->isConfigurable()) {
                $qb = $form->getConfig()->getOption('query_builder');
                $qb->setParameter('productType', Product::TYPE_SIMPLE);
                $qb->setParameter('attributeFamily', $parentProduct->getAttributeFamily()->getId());
            } else {
                $form->getParent()->remove('default_variant_product');
            }
        });
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class'         => Product::class,
            'choice_label'  => 'name',
            'required'      => true,
            'query_builder' => function (ProductRepository $pr) {
                return $pr->createQueryBuilder('p')
                    ->select('p')
                    ->where('p.type = :productType')
                    ->andWhere('p.attributeFamily = :attributeFamily');
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return Select2EntityType::class;
    }


}
