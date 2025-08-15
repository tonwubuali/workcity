<?php
// includes/class-wcc-cpt.php
if (!defined('ABSPATH')) exit;

class WCC_CPT {
  public function init() { add_action('init', [$this, 'register']); }
  public function register() {
    register_post_type('wcc_chat_session', [
      'label' => 'Chat Sessions',
      'public' => false,
      'show_ui' => true,
      'supports' => ['title', 'custom-fields'],
      'capability_type' => 'post',
      'map_meta_cap' => true,
      'menu_icon' => 'dashicons-format-chat',
    ]);
  }
}
