<?php
  session_start();
  #region HANDLE NOTIFY
  $notify = null;
  if(isset($_SESSION['message']) & isset($_SESSION['message']->message)){
    $notify = $_SESSION['message'];
    unset($_SESSION['message']);
  }
  #endregion HANDLE NOTIFY
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documenter PHP</title>
    <link rel="shortcut icon" href="./public/favicon.webp" type="image/x-webp">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>
      pre,[onclick]{ cursor: pointer; }
      pre, pre::after{
        transition: .6s;
      }
      pre:hover{
        transform: scale(1.01);
      }
      pre:hover::after{
        content: "copiar";
        position: absolute;
        top: .5rem;
        right: .5rem;
        border: 1px solid;
        border-radius: .5rem;
        padding: 0 .4rem;
      }
      .nav-link.active{
        --bs-bg-opacity: 1;
        background-color: rgba(var(--bs-light-rgb),var(--bs-bg-opacity)) !important;
        font-weight: 700 !important;
        color: var(--bs-body-color) !important;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <h1 class="mt-4">Documenter PHP!</h1>
      <form method="POST" action="./src/documenter.php" onsubmit="return submitLoad();">
        <?php if($notify): ?>
          <div class="alert alert-auto-dismiss <?php
            echo isset($notify->type) ? 'alert-'.$notify->type : 'alert-light';
          ?> alert-dismissible fade show mt-3 mb-2" role="alert">
            <?php if(isset($notify->title)): ?>
              <h4 class="alert-heading"><?php echo $notify->title; ?></h4>
            <?php endif ?>
            <p><?php echo $notify->message; ?></p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif ?>
        <!-- BEGIN:: NAME AND LOCATION -->
        <div class="row">
          <div class="col-md-4">
            <div class="mb-3">
                <label for="nameapp" class="form-label">Nome da documentação</label>
                <input
                  type="text"
                  class="form-control"
                  id="nameapp"
                  name="nameapp"
                  value="<?php echo $_SESSION['nameapp'] ?? ''; ?>"
                  placeholder="nome-do-app-documentado"
                  required
                >
              </div>
          </div>
          <div class="col-md-8">
            <div class="mb-3">
              <label for="path" class="form-label">Digite a raiz do aplicativo que deseja documentar</label>
              <input
                type="text"
                class="form-control"
                id="path"
                name="path"
                value="<?php echo $_SESSION['path'] ?? ''; ?>"
                placeholder="C://xampp/htdocs/path_to_directory"
                required
              >
            </div>
          </div>
        </div>
        <!-- END:: NAME AND LOCATION | BEGIN:: MAP -->
        <div>
          <div class="form-check">
            <input
              class="form-check-input"
              type="checkbox"
              id="map-all"
              onclick="$('.check-map-option').click()"
            >
            <label class="form-check-label" for="map-all">
            <strong>Mapear</strong>
            </label>
          </div>
          
          <div class="row mb-3">
            <?php
              $map = [
                'controllers' => 'Controladores',
                'repositories' => 'Repositórios',
                'models' => 'Modelos',
                'services' => 'Serviços',
                'commands' => 'Comandos',
                'observers' => 'Observadores',
                // 'routes' => 'Rotas',
                // 'views' => 'Visualizações',
              ];
              foreach($map as $key => $value):
            ?>
              <div class="col-md-4 col-sm-6">
                <div class="form-check">
                  <input
                    class="form-check-input check-map-option"
                    name="map[]"
                    type="checkbox"
                    value="<?php echo $key; ?>"
                    id="map-<?php echo $key; ?>"
                    <?php if(isset($_SESSION['map']) && is_array($_SESSION['map']) && in_array($key, $_SESSION['map'])): ?>
                      checked
                    <?php endif; ?>
                  >
                  <label class="form-check-label" for="map-<?php echo $key; ?>">
                    <?php echo $value; ?>
                  </label>
                </div>
              </div>
            <?php endforeach ?>
          </div>
        </div>
        <!-- END:: MAP | BEGIN:: REMOTE SAVE -->
        <div class="mb-3">
          <div class="form-check form-switch">
            <input
              class="form-check-input"
              type="checkbox"
              role="switch"
              name="switch_remote_save"
              id="switch-remote-save"
              <?php echo isset($_SESSION['remote_address']) ? 'checked' : ''; ?>
              onclick="handleSwitchRemoteSave()"
            >
            <label class="form-check-label" for="switch-remote-save">Salvar Remotamente</label>
          </div>
          <div class="mt-2 mb-3" style="<?php echo isset($_SESSION['remote_address']) ? '' : 'display: none;'; ?>">
            <label for="remote-addrs" class="form-label">Endereço Remoto</label>
            <input
              type="text"
              class="form-control"
              id="remote-address"
              name="remote_address"
              placeholder="http:// ou C://"
              value="<?php echo $_SESSION['remote_address'] ?? ''; ?>"
            >
          </div>
        </div>
        <!-- END:: REMOTE SAVE -->

        <button type="submit" class="btn btn-primary">
          Documentar
        </button>
      </form>

      <ul class="nav nav-tabs mt-5">
        <li class="nav-item">
          <a
            class="nav-link active"
            href="javascript: toggleTabs(false);"
            id="to-default-comments"
          >Padrões de Comentários</a>
        </li>
        <li class="nav-item">
          <a
            class="nav-link"
            href="javascript: toggleTabs(true);"
            id="to-remote-settings"
          >Configurar Salvamento Remoto</a>
        </li>
      </ul>
      <?php include './partials/default_comments.php'; ?>
      <?php include './partials/remote_settings.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <?php include './partials/alerts.php'; ?>
    <?php include './partials/loading.php'; ?>
    <script>
      $(function(){
        if($('.alert-auto-dismiss')[0]) setTimeout(
          () => $('.alert-auto-dismiss .btn-close').click(),
          5 * 1000
        );
        $('pre').on('click', function(){
          handleClipboard($(this).html());
        });
      });

      function handleClipboard(value){
        navigator.clipboard.writeText(value).then(() => {
          alertNotify('success', 'Código copiado');
        });
      }
      function handleSwitchRemoteSave(){
        let sw_remote = $('#switch-remote-save');
        let target = sw_remote.parent().next();
        if(sw_remote.prop('checked')){
          target.show('slow');
          $('#remote-address').focus();
        }
        else{
          target.hide('slow');
          $('#remote-address').val('');
        }        
      }
      function toggleTabs(toRemoteSettings = false){
        if(toRemoteSettings){
          $('#remote-settings').show();
          $('#default-comments').hide();
          $('#to-remote-settings').addClass('active');
          $('#to-default-comments').removeClass('active');
        }else{
          $('#default-comments').show();
          $('#remote-settings').hide();
          $('#to-default-comments').addClass('active');
          $('#to-remote-settings').removeClass('active');
        }
      }
    </script>
  </body>
</html>