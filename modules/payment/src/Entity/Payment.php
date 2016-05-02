<?php

namespace Drupal\commerce_payment\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Defines the payment entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_payment",
 *   label = @Translation("Payment"),
 *   label_singular = @Translation("Payment"),
 *   label_plural = @Translation("Payments"),
 *   label_count = @PluralTranslation(
 *     singular = "@count payment",
 *     plural = "@count payments",
 *   ),
 *   handlers = {
 *     "event" = "Drupal\commerce_payment\Event\PaymentEvent",
 *     "storage" = "Drupal\commerce\CommerceContentEntityStorage",
 *     "list_builder" = "Drupal\commerce_payment\OrderListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\commerce_payment\Form\OrderForm",
 *       "add" = "Drupal\commerce_payment\Form\OrderForm",
 *       "edit" = "Drupal\commerce_payment\Form\OrderForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_payment",
 *   admin_permission = "administer payments",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "payment_id",
 *     "label" = "payment_number",
 *     "uuid" = "uuid",
 *     "bundle" = "type"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/payments/{commerce_payment}",
 *     "edit-form" = "/admin/commerce/payments/{commerce_payment}/edit",
 *     "delete-form" = "/admin/commerce/payments/{commerce_payment}/delete",
 *     "delete-multiple-form" = "/admin/commerce/payments/delete",
 *     "reassign-form" = "/admin/commerce/payments/{commerce_payment}/reassign",
 *     "collection" = "/admin/commerce/payments"
 *   },
 *   bundle_entity_type = "commerce_payment_type"
 * )
 */
class Payment extends ContentEntityBase implements PaymentInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    if ($this->isNew()) {
      if (!$this->getIpAddress()) {
        $this->setIpAddress(\Drupal::request()->getClientIp());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOrder() {
    return $this->get('order_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderId() {
    return $this->get('order_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteId() {
    return $this->get('remote_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRemoteId($remote_id) {
    $this->set('remote_id', $remote_id);
    return $this;
  }

  /**
   * Gets the total price.
   *
   * @return object
   *   The total price.
   */
  public function getTotalPrice() {
    return $this->get('total_price')->first();
  }


  /**
   * {@inheritdoc}
   */
  public function getState() {
    return $this->get('state')->first();
  }

  /**
   * {@inheritdoc}
   */
  public function getBillingProfile() {
    return $this->get('billing_profile')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setBillingProfile(ProfileInterface $profile) {
    $this->set('billing_profile', $profile->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBillingProfileId() {
    return $this->get('billing_profile')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setBillingProfileId($billingProfileId) {
    $this->set('billing_profile', $billingProfileId);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpAddress() {
    return $this->get('ip_address')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setIpAddress($ip_address) {
    $this->set('ip_address', $ip_address);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmail() {
    return $this->get('mail')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setEmail($mail) {
    $this->set('mail', $mail);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    return $this->get('data')->first()->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setData($data) {
    $this->set('data', [$data]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // The order backreference, populated by Order::postSave().
    $fields['order_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Order'))
      ->setDescription(t('The parent order.'))
      ->setSetting('target_type', 'commerce_order')
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Owner'))
      ->setDescription(t('The payment owner.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\commerce_payment\Entity\Order::getCurrentUserId')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['remote_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Remote ID'))
      ->setDescription(t('The payment remote ID.'))
      ->setDefaultValue('')
      ->setSetting('max_length', 255)
      ->setDisplayConfigurable('view', TRUE);

    $fields['billing_profile'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Billing profile'))
      ->setDescription(t('Billing profile'))
      ->setSetting('target_type', 'profile')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', ['target_bundles' => ['billing']])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 0,
        'settings' => [],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['state'] = BaseFieldDefinition::create('state')
      ->setLabel(t('State'))
      ->setDescription(t('The payment state.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'list_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setSetting('workflow_callback', ['\Drupal\commerce_payment\Entity\Payment', 'getWorkflowId']);

    $fields['total_price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Total price'))
      ->setDescription(t('The total price of the payment.'))
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ip_address'] = BaseFieldDefinition::create('string')
      ->setLabel(t('IP address'))
      ->setDescription(t('The IP address of the payment.'))
      ->setDefaultValue('')
      ->setSetting('max_length', 128)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'hidden',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['data'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Data'))
      ->setDescription(t('A serialized array of additional data.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time when the payment was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the payment was last edited.'));

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * Gets the workflow ID for the state field.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment.
   *
   * @return string
   *   The workflow id.
   */
  public static function getWorkflowId(PaymentInterface $payment) {
    $payment_type_manager = \Drupal::service('plugin.manager.commerce_payment_type');
    /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentType\PaymentTypeInterface $payment_type */
    $payment_type = $payment_type_manager->createInstance($payment->bundle());
    return $payment_type->getWorkflowId();
  }

}
