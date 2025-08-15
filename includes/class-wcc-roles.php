<?php
// includes/class-wcc-roles.php
if (!defined('ABSPATH')) exit;

class WCC_Roles {
  public function init() {}
  public function add_roles() {
    add_role('wcc_agent', 'Support Agent', ['read' => true]);
    add_role('wcc_designer', 'Designer', ['read' => true]);
    $caps = [ 'wcc_start_session' => true, 'wcc_reply_session' => true, 'wcc_view_session' => true ];
    foreach (['wcc_agent','wcc_designer','shop_manager','administrator'] as $role_key) {
      if ($role = get_role($role_key)) { foreach ($caps as $k=>$v) $role->add_cap($k, $v); }
    }
    if ($cust = get_role('customer')) $cust->add_cap('wcc_start_session', true);
    if ($admin = get_role('administrator')) $admin->add_cap('wcc_manage_all_sessions', true);
  }
}
