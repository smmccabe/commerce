# Ensure the right Drupal version is installed.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

# Ensure the module is linked into the codebase.
drupal_ti_ensure_module_linked

drush dl addressfield rules ctools entity views
drush en -y commerce_cart commerce_customer_ui commerce_product_ui commerce_line_item_ui commerce_order_ui
drush en -y commerce_payment commerce_payment_example commerce_tax_ui simpletest
