<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Configurable;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\ConfigurableProduct\Model\Product\VariationHandler;
use Magento\ConfigurableProduct\Test\Unit\Model\Product\ProductExtensionAttributes;
use Magento\Framework\App\Request\Http;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ConfigurableTest
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VariationHandler|MockObject
     */
    private $variationHandler;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var Factory|MockObject
     */
    private $optionFactory;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var Helper|MockObject
     */
    private $subject;

    /**
     * @var Configurable
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->variationHandler = $this->getMockBuilder(VariationHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateSimpleProducts'])
            ->getMock();

        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam', 'getPost'])
            ->getMock();

        $this->optionFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getTypeId', 'setAttributeSetId', 'getExtensionAttributes', 'setNewVariationsAttributeSetId',
                'setCanSaveConfigurableAttributes', 'setExtensionAttributes'
            ])
            ->getMock();

        $this->subject = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new Configurable(
            $this->variationHandler,
            $this->request,
            $this->optionFactory
        );
    }

    /**
     * @covers Configurable::afterInitialize
     */
    public function testAfterInitializeWithAttributesAndVariations()
    {
        $attributes = [
            ['attribute_id' => 90, 'values' => [
                ['value_index' => 12], ['value_index' => 13]
            ]]
        ];
        $valueMap = [
            ['new-variations-attribute-set-id', null, 24],
            ['associated_product_ids', [], []],
            ['product', [], ['configurable_attributes_data' => $attributes]],
        ];
        $simpleProductsIds = [1, 2, 3];
        $simpleProducts = ['simple1', 'simple2', 'simple3'];
        $paramValueMap = [
            ['variations-matrix', [], $simpleProducts],
            ['attributes', null, $attributes],
        ];

        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableProduct::TYPE_CODE);

        $this->request->expects(static::any())
            ->method('getPost')
            ->willReturnMap($valueMap);

        $this->request->expects(static::any())
            ->method('getParam')
            ->willReturnMap($paramValueMap);

        $extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->disableOriginalConstructor()
            ->setMethods(['setConfigurableProductOptions', 'setConfigurableProductLinks'])
            ->getMockForAbstractClass();
        $this->product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->optionFactory->expects(static::once())
            ->method('create')
            ->with($attributes)
            ->willReturn($attributes);

        $extensionAttributes->expects(static::once())
            ->method('setConfigurableProductOptions')
            ->with($attributes);

        $this->variationHandler->expects(static::once())
            ->method('generateSimpleProducts')
            ->with($this->product, $simpleProducts)
            ->willReturn($simpleProductsIds);

        $extensionAttributes->expects(static::once())
            ->method('setConfigurableProductLinks')
            ->with($simpleProductsIds);

        $this->product->expects(static::once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes);

        $this->plugin->afterInitialize($this->subject, $this->product);
    }

    /**
     * @covers Configurable::afterInitialize
     */
    public function testAfterInitializeWithAttributesAndWithoutVariations()
    {
        $attributes = [
            ['attribute_id' => 90, 'values' => [
                ['value_index' => 12], ['value_index' => 13]
            ]]
        ];
        $valueMap = [
            ['new-variations-attribute-set-id', null, 24],
            ['associated_product_ids', [], []],
            ['product', [], ['configurable_attributes_data' => $attributes]],
        ];
        $paramValueMap = [
            ['variations-matrix', [], []],
            ['attributes', null, $attributes],
        ];

        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableProduct::TYPE_CODE);

        $this->request->expects(static::any())
            ->method('getPost')
            ->willReturnMap($valueMap);

        $this->request->expects(static::any())
            ->method('getParam')
            ->willReturnMap($paramValueMap);

        $extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->disableOriginalConstructor()
            ->setMethods(['setConfigurableProductOptions', 'setConfigurableProductLinks'])
            ->getMockForAbstractClass();
        $this->product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->optionFactory->expects(static::once())
            ->method('create')
            ->with($attributes)
            ->willReturn($attributes);

        $extensionAttributes->expects(static::once())
            ->method('setConfigurableProductOptions')
            ->with($attributes);

        $this->variationHandler->expects(static::never())
            ->method('generateSimpleProducts');

        $extensionAttributes->expects(static::once())
            ->method('setConfigurableProductLinks');

        $this->product->expects(static::once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes);

        $this->plugin->afterInitialize($this->subject, $this->product);
    }

    /**
     * @covers Configurable::afterInitialize
     */
    public function testAfterInitializeIfAttributesEmpty()
    {
        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableProduct::TYPE_CODE);
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('attributes')
            ->willReturn([]);
        $this->product->expects(static::never())
            ->method('getExtensionAttributes');
        $this->request->expects(static::once())
            ->method('getPost');
        $this->variationHandler->expects(static::never())
            ->method('generateSimpleProducts');
        $this->plugin->afterInitialize($this->subject, $this->product);
    }

    /**
     * @covers Configurable::afterInitialize
     */
    public function testAfterInitializeForNotConfigurableProduct()
    {
        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn('non-configurable');
        $this->product->expects(static::never())
            ->method('getExtensionAttributes');
        $this->request->expects(static::once())
            ->method('getPost');
        $this->variationHandler->expects(static::never())
            ->method('generateSimpleProducts');
        $this->plugin->afterInitialize($this->subject, $this->product);
    }
}
