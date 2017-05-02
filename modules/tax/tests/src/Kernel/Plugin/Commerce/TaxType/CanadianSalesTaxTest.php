<?php

namespace Drupal\Tests\commerce_tax\Kernel\Plugin\Commerce\TaxType;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_order\Entity\OrderItemType;
use Drupal\commerce_price\Price;
use Drupal\commerce_store\Entity\Store;
use Drupal\commerce_tax\Plugin\Commerce\TaxType\CanadianSalesTax;
use Drupal\commerce_tax\TaxableType;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
use Drupal\profile\Entity\Profile;

/**
 * @coversDefaultClass \Drupal\commerce_tax\Plugin\Commerce\TaxType\EuropeanUnionVat
 * @group commerce
 */
class EuropeanUnionVatTest extends CommerceKernelTestBase {

  /**
   * The tax type plugin.
   *
   * @var \Drupal\commerce_tax\Plugin\Commerce\TaxType\TaxTypeInterface
   */
  protected $plugin;

  /**
   * A sample user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'entity_reference_revisions',
    'profile',
    'state_machine',
    'commerce_product',
    'commerce_order',
    'commerce_tax',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installConfig('commerce_order');

    // Order item types that doesn't need a purchasable entity, for simplicity.
    OrderItemType::create([
      'id' => 'test_physical',
      'label' => 'Test (Physical)',
      'orderType' => 'default',
      'third_party_settings' => [
        'commerce_tax' => ['taxable_type' => TaxableType::PHYSICAL_GOODS],
      ],
    ])->save();

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);

    $configuration = [
      '_entity_id' => 'canadian_sales_tax',
      'display_inclusive' => FALSE,
    ];
    $this->plugin = EuropeanUnionVat::create($this->container, $configuration, 'canadian_sales_tax', ['label' => 'Canadian Sales Tax']);
  }

  /**
   * @covers ::applies
   * @covers ::apply
   */
  public function testApplication() {

    // BC customer, 2 taxes.
    $order = $this->buildOrder('CA', 'BC');
    $this->assertTrue($this->plugin->applies($order));
    $this->plugin->apply($order);
    $adjustments = $order->collectAdjustments();
    $adjustment = reset($adjustments);
    $this->assertCount(2, $adjustments);
    $this->assertEqual('canadian_sales_tax|ca|gst', $adjustment->getSourceId());
    $this->assertEqual('canadian_sales_tax|ca|pst', $adjustment->getSourceId());

    // Alberta customer, GST only.
    $order = $this->buildOrder('CA', 'AB');
    $this->assertTrue($this->plugin->applies($order));
    $this->plugin->apply($order);
    $adjustments = $order->collectAdjustments();
    $adjustment = reset($adjustments);
    $this->assertCount(1, $adjustments);
    $this->assertEqual('canadian_sales_tax|ca|gst', $adjustment->getSourceId());

    // Ontario customer, HST.
    $order = $this->buildOrder('CA', 'ON');
    $this->assertTrue($this->plugin->applies($order));
    $this->plugin->apply($order);
    $adjustments = $order->collectAdjustments();
    $adjustment = reset($adjustments);
    $this->assertCount(1, $adjustments);
    $this->assertEqual('canadian_sales_tax|ca|hst', $adjustment->getSourceId());

    // US store, California.
    $order = $this->buildOrder('US', 'CA');
    $this->assertFalse($this->plugin->applies($order));
  }

  /**
   * Builds an order for testing purposes.
   *
   * @return \Drupal\commerce_order\Entity\OrderInterface
   *   The order.
   */
  protected function buildOrder($customer_country, $customer_province) {
    $store = Store::create([
      'type' => 'default',
      'label' => 'My store',
      'address' => [
        'country_code' => $customer_country,
      ],
      'prices_include_tax' => FALSE,
    ]);
    $store->save();
    $customer_profile = Profile::create([
      'type' => 'customer',
      'uid' => $this->user->id(),
      'address' => [
        'country_code' => $customer_country,
        'administrative_area' => $customer_province,
      ],
    ]);
    $customer_profile->save();
    $order_item = OrderItem::create([
      'type' => 'test_physical',
      'quantity' => 1,
      // Intentionally uneven number, to test rounding.
      'unit_price' => new Price('10.33', 'USD'),
    ]);
    $order_item->save();
    $order = Order::create([
      'type' => 'default',
      'uid' => $this->user->id(),
      'store_id' => $store,
      'billing_profile' => $customer_profile,
      'order_items' => [$order_item],
    ]);
    $order->save();

    return $order;
  }

}
