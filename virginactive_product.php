
<?php

/**
* iabaduu
* nei prodotti abbonamento abilitiamo opzione inizia subito
*smart open 12  ->  729, 723, 716, 711, 624, 623
*open 12  -> 724, 718, 712, 705, 428, 252
*flexi 3 -> 727, 721, 714, 709, 625, 253
* woo-price_to_date-iabaduu
*/


// Add custom field to product page
add_action( 'woocommerce_before_add_to_cart_button', 'add_datepicker_field_to_product' );
function add_datepicker_field_to_product() {
  global $product;

  $specific_product_ids = array(729, 723, 716, 711, 624, 623, 724, 718, 712, 705, 428, 252, 727, 721, 714, 709, 625, 253, 749); // replace with your specific product IDs
  if ( ! in_array( $product->get_id(), $specific_product_ids ) || ! $product->is_type( 'simple' ) ) {
      return;
  }

      echo '<div id="datepicker_field" style="background-color:white;">';
      echo '<img src="https://shop.sol-ide.com/wp-content/uploads/2023/02/Timer_inizia-subito-logo-alpha.png" width="150px" align="center"';
      woocommerce_form_field( 'enable_delivery_date', array(
        'type'        => 'checkbox',
        'class'       => array( 'form-row-wide'),
        'label'       => __( '<b>Inizia Subito</b>', 'woocommerce' ),
      ), '' );
      echo '<div id="delivery_date_field" style="display:none;">';
      echo '<p>Puoi fare partire il tuo abbonamento già questo mese, aspettando solo i 3 giorni di attivazione previsti dall\'acquisto! <br><br>
            Al prezzo totale, verranno aggiunte le quote (il “Periodo Pro-rata”), relative ai giorni che mancano alla fine del mese in corso oltre all’abbonamento richiesto.</p>';
      woocommerce_form_field( 'delivery_date', array(
        'type'        => 'text',
        'class'       => array( 'form-row-wide' ),
        'placeholder' => __( 'DD/MM/YYYY', 'woocommerce' ),
      ), '' );
      echo '</div>';
      echo '</div>';
      //echo '<hr>';
      ?>
      <script>
        jQuery(document).ready(function($){
          $("#delivery_date").datepicker( {dateFormat: 'dd/mm/yy'} );
          $("#enable_delivery_date").change(function() {
            if ($(this).is(':checked')) {
              $("#delivery_date_field").show();
              // data iniziale a 3gg da oggi
              var threeDaysFromToday = new Date();
              threeDaysFromToday.setDate(threeDaysFromToday.getDate() + 3);
              $("#delivery_date").datepicker("option", "minDate", threeDaysFromToday);
              //data finale = fine mese corrente
              var endOfMonth = new Date();
              endOfMonth.setMonth(endOfMonth.getMonth() + 1);
              endOfMonth.setDate(0);
              $("#delivery_date").datepicker("option", "maxDate", endOfMonth);
              // -- OK fin qui ---
            } else {
              $("#delivery_date_field").val('');
              $("#delivery_date_field").hide();
            }
          });
        });
      </script>
      <?php

  }

  /**
  * iabaduu - update price
  * Hook into the "woocommerce_before_calculate_totals" action
  * formula prezzo
  * iabaduu_modify_product_bydate
  */

  add_action('woocommerce_before_calculate_totals', 'iabaduu_modify_product_bydate', 10, 1);

  function iabaduu_modify_product_bydate($cart) {

    if (is_admin() && !defined('DOING_AJAX')) {
      return;
    }

    //calcolo giorni da pagare solo se positivi
    // products array
   $targetProductIds12 = array( 729, 723, 716, 711, 624, 623, 724, 718, 712, 705, 428, 252,749);
   $targetProductIds3 = array( 727, 721, 714, 709, 625, 253); //749
   // Loop through the cart items
   foreach($cart->get_cart() as $cart_item) {
       // Get the product object
       $product = $cart_item['data'];
       $product_id = $product->get_id();

       if ( isset( $cart_item['delivery_date']) && $cart_item['delivery_date'] != '' ) {

              $date_str = $cart_item['delivery_date'];
              //
              $months = array('Gennaio'=>'January',
                          'Febbraio'=>'February',
                          'Marzo'=>'March',
                          'Aprile'=>'April',
                          'Maggio'=>'May',
                          'Giugno'=>'June',
                          'Luglio'=>'July',
                          'Agosto'=>'August',
                          'Settembre'=>'September',
                          'Ottobre'=>'October',
                          'Novembre'=>'November',
                          'Dicembre'=>'December'
            );
            foreach(array_keys($months) as $month){
                $date_str = str_replace($month, $months[$month], $date_str);
            }
            //
            $datetime1 = DateTime::createFromFormat("d/m/Y", $date_str);
            // Do something with the selected date
            //echo "<p>la data selezionate è: $selected_date</p>";
            //
            // Create a DateTime object for the first day of the next month
            $nextMonth = new DateTime();
            $nextMonth->modify('first day of next month');

            // Calculate the difference between the two dates
            //$datetime1 = DateTime::createFromFormat("d/m/Y", $selected_date);
            $datetime2 =  $nextMonth;
            $interval = $datetime1->diff($datetime2);

            //date interval in integer
            $days = intval($interval->format('%a'));
            // echo "The difference between the two dates is $days days.";
            if (in_array($product_id, $targetProductIds12)) {
                // Calculate the new price 12 month
                $newPrice = $product->get_price() + (($product->get_price()/360)*$days);
                }
            elseif (in_array($product_id, $targetProductIds3)) {
              // Calculate the new price 3 month
                $newPrice = $product->get_price() + (($product->get_price()/90)*$days);
            }
            // do nothing
            else $newPrice = $product->get_price();

            // Update the product price
            $product->set_price($newPrice);
          }
    }
  }






// Save the custom field value to the cart item
add_filter( 'woocommerce_add_cart_item_data', 'save_datepicker_field_to_cart_item', 10, 2 );
function save_datepicker_field_to_cart_item( $cart_item_data, $product_id ) {
  if ( isset( $_POST['delivery_date'] ) ) {
    $cart_item_data['delivery_date'] = sanitize_text_field( $_POST['delivery_date'] );
  }
  return $cart_item_data;
}

// Display the custom field value on the cart page and checkout page OK
add_filter( 'woocommerce_get_item_data', 'get_item_data' , 25, 2 );
function get_item_data ( $cart_data, $cart_item ) {
    $allowed_product_ids = array(729, 723, 716, 711, 624, 623, 724, 718, 712, 705, 428, 252, 727, 721, 714, 709, 625, 253, 749); // Array of allowed product IDs

    if ( !in_array( $cart_item['product_id'], $allowed_product_ids ) ) {
      return $cart_data; // Return the original cart data if the current product ID is not in the array of allowed product IDs
    }

    if( ! empty( $cart_item['delivery_date'] ) ){

        $cart_data[] = array(
            'name'    => __( "<b>Inizia Subito</b>", "woocommerce"),
            'value' => $cart_item['delivery_date'],
        );
    }

    return $cart_data;
}
//add custom details entered by customers as extra details for order line items OK
add_action( 'woocommerce_checkout_create_order_line_item', 'iaba_add_custom_order_line_item_meta',10,4 );

function iaba_add_custom_order_line_item_meta($item, $cart_item_key, $values, $order)
{

    if(array_key_exists('delivery_date', $values))
    {
        $item->add_meta_data('_inizia_subito',$values['delivery_date']);
    }
}

//add custom details in thank you and email OK

add_action('woocommerce_order_item_meta_end', 'email_confirmation_display_order_items', 10, 4);

function email_confirmation_display_order_items($item_id, $item, $order, $plain_text) {

    echo '<div><br><b>Inizia Subito:</b> '. wc_get_order_item_meta( $item_id, '_inizia_subito') .'</div>';
}


// ------------------- add on per data inizio abbonamento -------------------------------//

add_action('woocommerce_add_order_item_meta', 'add_inizio_abbonamento_to_order_item', 10, 3);
function add_inizio_abbonamento_to_order_item($item_id, $values, $cart_item_key) {
  // Define an array of product IDs that should have the custom meta
  $product_ids = array(729, 723, 716, 711, 624, 623, 724, 718, 712, 705, 428, 252, 727, 721, 714, 709, 625, 253, 749); // Replace with your own product IDs

  // Check if the product belongs to one of the specified product IDs
  $product_id = $values['product_id'];
  if (in_array($product_id, $product_ids)) {
    // Create a DateTime object for the first day of the next month
    $today = new DateTime();
    $nextMonth = new DateTime();
    $nextMonth->modify('first day of next month');
    $date = $nextMonth;
    $inizio_abbonamento = $date->format('d/m/Y');

    // Add the inizio_abbonamento meta data to the order item
    wc_add_order_item_meta($item_id, 'inizio_abbonamento', $inizio_abbonamento);
  }
}


//add custom details in thank you and email OK
add_action('woocommerce_order_item_meta_end', 'email_confirmation_display_order_data', 10, 4);
function email_confirmation_display_order_data($item_id, $item, $order, $plain_text) {
    echo '<div><b>Attivazione Abbonamento:</b> '. wc_get_order_item_meta( $item_id, 'inizio_abbonamento') .'</div>';
}
