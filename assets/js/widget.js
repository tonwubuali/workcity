(function($){
  function WccChat($root){
    const sessionId = $root.data('session');
    const $msgs = $root.find('#wcc-messages');
    const $input = $root.find('#wcc-input');
    const $typing = $root.find('#wcc-typing');
    const $toggle = $root.find('.wcc-chat__toggle-theme');
    let lastId = 0, timer=null, typingTimer=null;

    function fetch(){
      $.post(WCC.ajax, {action:'wcc_fetch', nonce:WCC.nonce, session_id:sessionId, after_id:lastId})
        .done(res=>{
          if(!res || !res.success) return;
          const ms = res.data.messages || [];
          ms.forEach(m=>{
            lastId = Math.max(lastId, parseInt(m.id,10));
            $msgs.append(renderMessage(m));
          });
          if(ms.length) $msgs.scrollTop($msgs[0].scrollHeight);
          if (res.data.typing) { $typing.removeAttr('hidden'); } else { $typing.attr('hidden', true); }
        });
    }
    function send(){
      const body = ($input.val()||'').trim();
      if(!body) return;
      $.post(WCC.ajax, {action:'wcc_send', nonce:WCC.nonce, session_id:sessionId, body})
       .done(()=>{$input.val(''); fetch(); markRead();});
    }
    function markRead(){ $.post(WCC.ajax, {action:'wcc_read', nonce:WCC.nonce, session_id:sessionId}); }
    function renderMessage(m){
      const mine = parseInt(m.sender_user_id,10)===parseInt(WCC.userId,10);
      return `<div class="wcc-msg ${mine?'is-me':''}">
        <div class="wcc-msg__bubble">${m.body||''}</div>
        <div class="wcc-msg__meta">${m.created_at||''}</div>
      </div>`;
    }
    function pingTyping(){ $.post(WCC.ajax, {action:'wcc_typing', nonce:WCC.nonce, session_id:sessionId}); }
    function loop(){ fetch(); timer = setTimeout(loop, 3000); }
    $root.on('click', '#wcc-send', send);
    $input.on('keydown', function(e){ if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); send(); }});
    $input.on('input', ()=>{ clearTimeout(typingTimer); typingTimer = setTimeout(pingTyping, 200); });
    $toggle.on('click', ()=>{
      document.documentElement.classList.toggle('wcc-dark');
      localStorage.setItem('wcc-theme', document.documentElement.classList.contains('wcc-dark')?'dark':'light');
    });
    if(localStorage.getItem('wcc-theme')==='dark') document.documentElement.classList.add('wcc-dark');
    loop(); markRead();
  }
  $(function(){ $('.wcc-chat').each(function(){ new WccChat($(this)); }); });
})(jQuery);
