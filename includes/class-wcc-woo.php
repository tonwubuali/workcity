<?php
// includes/class-wcc-woo.php
if (!defined('ABSPATH')) exit;

class WCC_Woo {
  public function init() { add_action('woocommerce_single_product_summary', [$this,'button'], 35); }
  public function button() {
    if (!is_user_logged_in()) return;
    global $product;
    if (!$product) return;
    echo do_shortcode('[wcc_chat product_id="'.$product->get_id().'" target_role="merchant"]');
  }
}
