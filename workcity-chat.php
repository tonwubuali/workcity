<?php
/**
 * Plugin Name: Workcity Chat
 * Description: Role-aware WooCommerce chat (buyer↔merchant/designer/agent) with CPT sessions, REST, AJAX, and product-context.
 * Version: 1.0.1
 * Author: Thankgod
 * Text Domain: workcity-chat
 */

if (!defined('ABSPATH')) exit;

define('WCC_VERSION', '1.0.1');
define('WCC_PATH', plugin_dir_path(__FILE__));
define('WCC_URL', plugin_dir_url(__FILE__));

require_once WCC_PATH.'includes/class-wcc-activator.php';
require_once WCC_PATH.'includes/class-wcc-deactivator.php';
require_once WCC_PATH.'includes/class-wcc-cpt.php';
require_once WCC_PATH.'includes/class-wcc-rest.php';
require_once WCC_PATH.'includes/class-wcc-ajax.php';
require_once WCC_PATH.'includes/class-wcc-roles.php';
require_once WCC_PATH.'includes/class-wcc-woo.php';
require_once WCC_PATH.'includes/class-wcc-permissions.php';
require_once WCC_PATH.'includes/class-wcc-helpers.php';
require_once WCC_PATH.'includes/class-wcc-notify.php';

register_activation_hook(__FILE__, ['WCC_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['WCC_Deactivator', 'deactivate']);

add_action('plugins_loaded', function () {
  (new WCC_CPT())->init();
  (new WCC_Roles())->init();
  (new WCC_Ajax())->init();
  (new WCC_REST())->init();
  (new WCC_Woo())->init();
});
