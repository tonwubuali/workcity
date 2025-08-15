<?php
// templates/widget.php
if (!defined('ABSPATH')) exit;
$session_id = isset($session_id_local) ? (int)$session_id_local : 0;
?>
<div class="wcc-chat" data-session="<?php echo esc_attr($session_id); ?>">
  <div class="wcc-chat__header">
    <div class="wcc-chat__title"><?php echo esc_html(get_the_title($session_id)); ?></div>
    <button class="wcc-chat__toggle-theme" aria-label="Toggle theme">🌓</button>
  </div>
  <div class="wcc-chat__messages" id="wcc-messages"></div>
  <div class="wcc-chat__composer">
    <textarea id="wcc-input" rows="1" placeholder="Type a message…"></textarea>
    <input type="file" id="wcc-file" hidden />
    <button id="wcc-attach" title="Attach file" type="button">📎</button>
    <button id="wcc-send" class="wcc-btn" type="button">Send</button>
  </div>
  <div class="wcc-chat__typing" id="wcc-typing" hidden>Typing…</div>
</div>
