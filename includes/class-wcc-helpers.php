<?php
// includes/class-wcc-helpers.php
if (!defined('ABSPATH')) exit;

class WCC_Helpers {
  public static function get_or_create_session($buyer_id, $target_role, $product_id=null) {
    $args = [
      'post_type'=>'wcc_chat_session','post_status'=>'publish','numberposts'=>1,
      'meta_query'=>[
        ['key'=>'_wcc_product_id','value'=>$product_id,'compare'=>'='],
        ['key'=>'_wcc_session_type','value'=>"buyer-$target_role",'compare'=>'='],
        ['key'=>'_wcc_participants','value'=>'\"'.$buyer_id.'\"','compare'=>'LIKE'],
      ]
    ];
    $existing = get_posts($args);
    if ($existing) return $existing[0]->ID;

    $session_id = wp_insert_post([ 'post_type'=>'wcc_chat_session','post_title'=>"Chat: $buyer_id → $target_role #$product_id", 'post_status'=>'publish' ]);
    update_post_meta($session_id,'_wcc_session_type',"buyer-$target_role");
    update_post_meta($session_id,'_wcc_product_id',$product_id);
    $participants = [$buyer_id, self::resolve_target_user_id($target_role, $product_id)];
    update_post_meta($session_id,'_wcc_participants',$participants);
    update_post_meta($session_id,'_wcc_unread_map',[]);
    update_post_meta($session_id,'_wcc_last_message_at', time());
    return $session_id;
  }
  public static function resolve_target_user_id($role, $product_id=null) {
    if ($role==='merchant' && $product_id) {
      $author = (int) get_post_field('post_author', $product_id);
      if ($author) return $author;
    }
    $roles = ($role==='designer') ? ['wcc_designer'] : (($role==='agent') ? ['wcc_agent'] : ['shop_manager']);
    $users = get_users(['role__in'=> $roles, 'number'=>1]);
    return $users ? (int)$users[0]->ID : (int) get_current_user_id();
  }
  public static function increment_unread_for_others($session_id, $sender_id) {
    $map = (array) get_post_meta($session_id,'_wcc_unread_map',true);
    $participants = (array) get_post_meta($session_id,'_wcc_participants',true);
    foreach ($participants as $uid) {
      $uid = (int)$uid; if ($uid === (int)$sender_id) continue;
      $map[$uid] = isset($map[$uid]) ? ((int)$map[$uid] + 1) : 1;
    }
    update_post_meta($session_id,'_wcc_unread_map',$map);
  }
  public static function mark_session_read_for($session_id, $user_id) {
    global $wpdb; $t=$wpdb->prefix.'wcc_chat_messages';
    $wpdb->query($wpdb->prepare("UPDATE $t SET is_read=1, read_at=NOW() WHERE session_id=%d AND sender_user_id<>%d", $session_id, $user_id));
    $map = (array) get_post_meta($session_id,'_wcc_unread_map',true);
    $map[(int)$user_id] = 0; update_post_meta($session_id,'_wcc_unread_map',$map);
  }
}
