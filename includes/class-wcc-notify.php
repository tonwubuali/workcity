<?php
// includes/class-wcc-notify.php
if (!defined('ABSPATH')) exit;

class WCC_Notify {
  public static function notify_new_message($session_id, $sender_id, $body) {
    $participants = (array) get_post_meta($session_id,'_wcc_participants',true);
    foreach ($participants as $uid) {
      $uid = (int)$uid; if ($uid === (int)$sender_id) continue;
      $user = get_user_by('id', $uid); if (!$user) continue;
      $subject = sprintf('[Workcity Chat] New message in session #%d', $session_id);
      $link = get_edit_post_link($session_id);
      $msg = sprintf("You have a new message:\n\n%s\n\nView session: %s", wp_strip_all_tags($body), $link);
      wp_mail($user->user_email, $subject, $msg);
    }
  }
}
