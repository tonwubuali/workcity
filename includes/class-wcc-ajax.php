<?php
// includes/class-wcc-ajax.php
if (!defined('ABSPATH')) exit;

class WCC_Ajax {
  public function init() {
    add_shortcode('wcc_chat', [$this, 'shortcode']);
    add_action('wp_enqueue_scripts', [$this, 'assets']);
    add_action('wp_ajax_wcc_send', [$this, 'send']);
    add_action('wp_ajax_wcc_fetch', [$this, 'fetch']);
    add_action('wp_ajax_wcc_typing', [$this, 'typing']);
    add_action('wp_ajax_wcc_read', [$this, 'mark_read']);
  }
  public function assets() {
    wp_enqueue_style('wcc-widget', WCC_URL.'assets/css/widget.css', [], WCC_VERSION);
    wp_enqueue_script('wcc-widget', WCC_URL.'assets/js/widget.js', ['jquery'], WCC_VERSION, true);
    wp_localize_script('wcc-widget', 'WCC', [
      'ajax' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('wcc_nonce'),
      'userId' => get_current_user_id(),
    ]);
  }
  public function shortcode($atts) {
    if (!is_user_logged_in()) return '<div class="wcc-chat-login">Please log in to chat.</div>';
    $atts = shortcode_atts([ 'product_id' => get_the_ID(), 'target_role' => 'merchant' ], $atts);
    $session_id = WCC_Helpers::get_or_create_session(get_current_user_id(), $atts['target_role'], (int)$atts['product_id']);
    ob_start(); $session_id_local = $session_id; include WCC_PATH.'templates/widget.php'; return ob_get_clean();
  }
  public function send() {
    check_ajax_referer('wcc_nonce','nonce');
    $user_id = get_current_user_id();
    $session_id = (int)($_POST['session_id'] ?? 0);
    $body = wp_kses_post($_POST['body'] ?? '');
    if (!WCC_Permissions::can_view_session($user_id, $session_id)) wp_send_json_error(['message'=>'Unauthorized'], 403);
    global $wpdb; $table = $wpdb->prefix.'wcc_chat_messages';
    $wpdb->insert($table, ['session_id'=>$session_id,'sender_user_id'=>$user_id,'body'=>$body,'is_read'=>0,'created_at'=>current_time('mysql')]);
    update_post_meta($session_id, '_wcc_last_message_at', time());
    WCC_Helpers::increment_unread_for_others($session_id, $user_id);
    if (class_exists('WCC_Notify')) { WCC_Notify::notify_new_message($session_id, $user_id, $body); }
    wp_send_json_success(['message_id'=>$wpdb->insert_id]);
  }
  public function fetch() {
    check_ajax_referer('wcc_nonce','nonce');
    $user_id = get_current_user_id();
    $session_id = (int)($_POST['session_id'] ?? 0);
    $after_id = (int)($_POST['after_id'] ?? 0);
    if (!WCC_Permissions::can_view_session($user_id, $session_id)) wp_send_json_error(['message'=>'Unauthorized'], 403);
    global $wpdb; $t = $wpdb->prefix.'wcc_chat_messages';
    $q = $wpdb->prepare("SELECT * FROM $t WHERE session_id=%d AND id > %d ORDER BY id ASC LIMIT 100", $session_id, $after_id);
    $rows = $wpdb->get_results($q, ARRAY_A);
    $typing = false;
    $participants = (array) get_post_meta($session_id,'_wcc_participants',true);
    foreach ($participants as $p) { if ((int)$p !== (int)$user_id && get_transient("wcc_typing_{$session_id}_{$p}")) { $typing = true; break; } }
    wp_send_json_success(['messages'=>$rows, 'typing'=>$typing]);
  }
  public function typing() {
    check_ajax_referer('wcc_nonce','nonce');
    $session_id = (int)($_POST['session_id'] ?? 0);
    $user_id = get_current_user_id();
    set_transient("wcc_typing_{$session_id}_{$user_id}", 1, 5);
    wp_send_json_success();
  }
  public function mark_read() {
    check_ajax_referer('wcc_nonce','nonce');
    $user_id = get_current_user_id();
    $session_id = (int)($_POST['session_id'] ?? 0);
    if (!WCC_Permissions::can_view_session($user_id, $session_id)) wp_send_json_error(['message'=>'Unauthorized'], 403);
    WCC_Helpers::mark_session_read_for($session_id, $user_id);
    wp_send_json_success();
  }
}
