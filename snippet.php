/**
* Hook into the "woocommerce_before_calculate_totals" action
* moltiplichiamo il costo del prodotto x i giorni a fine mese (-3)
* woo-price_to_date_iabaduu
*/
add_action('woocommerce_before_calculate_totals', 'iabaduu_modify_product_prices', 10, 1);

function iabaduu_modify_product_prices($cart) {
  if (is_admin() && !defined('DOING_AJAX')) {
    return;
  }

  // Create a DateTime object for the current date and time
  $now = new DateTime();

  // Create a DateTime object for the first day of the next month
  $nextMonth = new DateTime();
  $nextMonth->modify('first day of next month');

  // Calculate the difference between the two dates
  $diff = $now->diff($nextMonth);

  // Format the difference as a string
  $daysUntilNextMonth = $diff->format('%a');

  // Loop through the cart items
  foreach($cart->get_cart() as $cart_item) {
					// Find the product by ID
					if ( 544 === $cart_item['product_id'] ) {
							    // Get the product object
							    $product = $cart_item['data'];

							    // Calculate the new price
							    $newPrice = $product->get_price() * ($daysUntilNextMonth -3);

							    // Update the product price
							    $product->set_price($newPrice);
  				}
			}
}
