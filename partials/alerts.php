<script>
  const classesAlerts = {
    primary: "alert alert-primary",
    secondary: "alert alert-secondary",
    success: "alert alert-success",
    danger: "alert alert-danger",
    warning: "alert alert-warning",
    info: "alert alert-info",
    light: "alert alert-light",
    dark: "alert alert-dark",
  };
  function alertNotify(type, text, timeout = 6000){
    let notify_id = `${type}_${$('#container-alerts .alert').length}`;
    $('#container-alerts').prepend(`
      <div
        class="${classesAlerts[type]}"
        role="alert"
        id="${notify_id}"
        style="display: none; min-width: 8rem; max-width: 90vw;"
        onclick="$(this).hide('slow');"
      >${text}</div>
    `);
    $(`#${notify_id}`).show('slow');

    if(timeout) setTimeout(() => { $(`#${notify_id}`).hide('slow'); }, timeout);
  }
  $(function(){
    $('body').prepend(`
      <div class="d-flex flex-column justify-content-end" style="
        position: fixed;
        top: 0;
        right: 0;
        padding: 1rem;
        z-index: 9999;
      " id="container-alerts"></div>
    `);
  });
</script>