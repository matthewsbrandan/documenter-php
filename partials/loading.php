<style>
  #loading{
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    z-index: 9999999;
    display:none;
    background: rgba(0,0,20,.4);
  }
  #loading .content{
    display: flex;
    flex-direction: column;
    gap: .3rem;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    flex: 1;
  }
  #loading .additional{ color: white; }
</style>
<div id="loading">
  <div class="content">
    <div class="spinner-border text-light" role="status">
      <span class="sr-only">Loading...</span>
    </div>
    <div class="additional"></div>
  </div>
</div>
<script>
  function runLoad(url, additional = ''){
    $('#loading').show();
    $('#loading .additional').html(additional);
    window.location.href = url;
  }
  function stopLoad(){
    $('#loading').hide();
    $('#loading .additional').html('');
  }
  function submitLoad(additional = ''){
    $('#loading').show();
    $('#loading .additional').html(additional);
    return true;
  }
</script>