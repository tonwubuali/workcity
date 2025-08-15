<?php
// includes/class-wcc-activator.php
if (!defined('ABSPATH')) exit;

class WCC_Activator {
  public static function activate() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $table = $wpdb->prefix . 'wcc_chat_messages';
    $sql = "CREATE TABLE $table (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      session_id BIGINT UNSIGNED NOT NULL,
      sender_user_id BIGINT UNSIGNED NOT NULL,
      recipient_user_id BIGINT UNSIGNED NULL,
      body LONGTEXT NULL,
      attachments LONGTEXT NULL,
      is_read TINYINT(1) DEFAULT 0,
      read_at DATETIME NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      KEY session_idx (session_id),
      KEY created_idx (created_at),
      KEY read_idx (is_read)
    ) $charset;";
    require_once ABSPATH.'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    if (class_exists('WCC_Roles')) {
      (new WCC_Roles())->add_roles();
    }
  }
}
