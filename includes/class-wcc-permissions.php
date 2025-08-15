<?php
// includes/class-wcc-permissions.php
if (!defined('ABSPATH')) exit;

class WCC_Permissions {
  public static function can_view_session($user_id, $session_id) {
    if (user_can($user_id, 'wcc_manage_all_sessions')) return true;
    $participants = (array) get_post_meta($session_id, '_wcc_participants', true);
    $participants = array_map('intval', $participants);
    return in_array((int)$user_id, $participants, true);
  }
  public static function ensure($expr) {
    if (!$expr) wp_send_json_error(['message'=>'Unauthorized'], 403);
  }
}
