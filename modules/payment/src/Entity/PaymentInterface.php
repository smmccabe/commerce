<?php

namespace Drupal\commerce_payment\Entity;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Defines the interface for payments.
 */
interface PaymentInterface extends EntityChangedInterface, ContentEntityInterface, EntityOwnerInterface {

  /**
   * Gets the parent order.
   *
   * @return \Drupal\commerce_order\Entity\OrderInterface|null
   *   The irder entity, or null.
   */
  public function getOrder();

  /**
   * Gets the parent payment id.
   *
   * @return int|null
   *   The payment id, or null.
   */
  public function getOrderId();

  /**
   * Gets the payment remote ID
   *
   * @return string
   *   The payment remote ID.
   */
  public function getRemoteId();

  /**
   * Sets the payment remote ID.
   *
   * @param string $remote_id
   *   The payment remote ID.
   *
   * @return $this
   */
  public function setRemoteId($remote_id);

  /**
   * Gets the total price.
   *
   * @return object
   *   The total price.
   */
  public function getTotalPrice();

  /**
   * Gets the payment state.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The payment state.
   */
  public function getState();

  /**
   * Gets the additional data stored in this payment.
   *
   * @return array
   *   An array of additional data.
   */
  public function getData();

  /**
   * Sets random information related to this payment.
   *
   * @param array $data
   *   An array of additional data.
   *
   * @return $this
   */
  public function setData($data);

  /**
   * Gets the payment IP address.
   *
   * @return string
   *   The IP address.
   */
  public function getIpAddress();

  /**
   * Sets the payment IP address.
   *
   * @param string $ip_address
   *   The IP address.
   *
   * @return $this
   */
  public function setIpAddress($ip_address);

  /**
   * Gets the billing profile.
   *
   * @return \Drupal\profile\Entity\ProfileInterface
   *   The billing profile entity.
   */
  public function getBillingProfile();

  /**
   * Sets the billing profile.
   *
   * @param \Drupal\profile\Entity\ProfileInterface $profile
   *   The billing profile entity.
   *
   * @return $this
   */
  public function setBillingProfile(ProfileInterface $profile);

  /**
   * Gets the billing profile id.
   *
   * @return int
   *   The billing profile id.
   */
  public function getBillingProfileId();

  /**
   * Sets the billing profile id.
   *
   * @param int $billingProfileId
   *   The billing profile id.
   *
   * @return $this
   */
  public function setBillingProfileId($billingProfileId);

  /**
   * Gets the payment creation timestamp.
   *
   * @return int
   *   Creation timestamp of the payment.
   */
  public function getCreatedTime();

  /**
   * Sets the payment creation timestamp.
   *
   * @param int $timestamp
   *   The payment creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

}
