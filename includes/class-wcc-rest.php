<?php
// includes/class-wcc-rest.php
if (!defined('ABSPATH')) exit;

class WCC_REST {
  public function init() {
    add_action('rest_api_init', function() {
      register_rest_route('wcc/v1', '/sessions', [
        'methods'=>'GET',
        'callback'=>[$this,'list_sessions'],
        'permission_callback'=>function(){ return is_user_logged_in(); }
      ]);
      register_rest_route('wcc/v1', '/sessions/(?P<id>\d+)/messages', [
        'methods'=>'GET',
        'callback'=>[$this,'get_messages'],
        'permission_callback'=>[$this,'can_view']
      ]);
      register_rest_route('wcc/v1', '/sessions/(?P<id>\d+)/messages', [
        'methods'=>'POST',
        'callback'=>[$this,'post_message'],
        'permission_callback'=>[$this,'can_view']
      ]);
    });
  }
  public function can_view($req) {
    return WCC_Permissions::can_view_session(get_current_user_id(), (int)$req['id']);
  }
  public function list_sessions($req) {
    $user_id = get_current_user_id();
    $q = new WP_Query([
      'post_type'=>'wcc_chat_session','posts_per_page'=>50,
      'meta_query'=>[['key'=>'_wcc_participants','value'=>'\"'.$user_id.'\"','compare'=>'LIKE']],
      'orderby'=>'meta_value_num','meta_key'=>'_wcc_last_message_at','order'=>'DESC'
    ]);
    $out = array_map(function($p){
      return [
        'id'=>$p->ID,
        'title'=>$p->post_title,
        'product_id'=> (int) get_post_meta($p->ID,'_wcc_product_id',true),
        'session_type'=> get_post_meta($p->ID,'_wcc_session_type',true),
        'participants'=> (array) get_post_meta($p->ID,'_wcc_participants',true),
        'unread_map'=> (array) get_post_meta($p->ID,'_wcc_unread_map',true),
      ];
    }, $q->posts);
    return rest_ensure_response($out);
  }
  public function get_messages($req) {
    global $wpdb; $t=$wpdb->prefix.'wcc_chat_messages';
    $after_id = (int)($req->get_param('after') ?? 0);
    $sid = (int)$req['id'];
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE session_id=%d AND id>%d ORDER BY id ASC LIMIT 200",$sid,$after_id), ARRAY_A);
    return rest_ensure_response($rows);
  }
  public function post_message($req) {
    global $wpdb; $t=$wpdb->prefix.'wcc_chat_messages';
    $sid = (int)$req['id'];
    $body = wp_kses_post($req->get_param('body') ?? '');
    $uid = get_current_user_id();
    if (!WCC_Permissions::can_view_session($uid,$sid)) {
      return new WP_Error('forbidden', 'Unauthorized', ['status'=>403]);
    }
    $wpdb->insert($t, ['session_id'=>$sid,'sender_user_id'=>$uid,'body'=>$body,'is_read'=>0,'created_at'=>current_time('mysql')]);
    update_post_meta($sid,'_wcc_last_message_at', time());
    WCC_Helpers::increment_unread_for_others($sid,$uid);
    return rest_ensure_response(['id'=>$wpdb->insert_id]);
  }
}
