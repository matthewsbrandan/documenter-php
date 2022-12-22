<div class="border border-top-0 bg-light p-3 mt-0 mb-5" id="remote-settings" style="display: none;">
  <h2>Configurar Salvamento Remoto</h2>
  <p class="text-sm text-muted">
    Por padrão a documentação é salva na nossa pasta <span class="badge bg-primary">public</span>, mas você pode adicionar uma url ou caminho para salvar a documentação em um local de sua preferência.
  </p>
  <p class="text-sm text-muted">
    Caso seja uma url, enviaremos os dados em JSON e você deverá implementar o código(criar uma API) para salvar a documentação em algum lugar.
  </p>

  <article class="mt-5">
    <h4>Salvar via API</h4>
    <p class="text-sm text-muted">É bem simples, basta seguir as recomendações e tudo dará certo.</p>

    <p class="text-sm text-muted">
      <b class="text-dark">1. Passo</b> Você deve desenvolver uma api que receba uma requisição <span class="badge bg-success text-white">POST</span> com os parâmetros a <em class="text-danger fw-bold">path</em>(Array contendo o caminho e o nome do arquivo), e <em class="text-danger fw-bold">body</em> (String com o conteúdo do arquivo), como no exemplo abaixo:
    </p>
    <pre class="text-sm text-muted bg-dark px-2 py-3 rounded-2">{<br/>  "path": ['app','Http','Controllers','Controller.php'],<br/>  "body": "{\"hello\": \"word\"}"<br/>}</pre>

    <p class="text-sm text-muted">
      <b class="text-dark">2. Passo</b> Para sua segurança enviamos no header da requisição uma chave chamada <span class="badge bg-danger text-white">documenter-php-secret</span>, para que você possa garantir que a requisição que está recebendo é do lugar certo.
    </p>
    <p class="text-sm text-muted">
      Para configurar essa chave você deve criar uma string aleatória(de preferência com mais o menos 20 caracteres) e guardá-la em um lugar seguro em sua api (como o arquivo .env), e adicionar no arquivo <em>.env</em> desta aplicação também. Exemplo:
    </p>
    <pre class="text-sm text-muted bg-dark px-2 py-3 rounded-2">DOCUMENTER_PHP_SECRET=t7v9yb0nu9ibqrvdsd1n0asfa</pre>
  </article>
  <article class="mt-5">
    <h4>Salvar em um caminho diferente</h4>
    <p class="text-sm text-muted">
      Essa feature ainda não foi implementada, porém não deve gerar nenhuma alteração de código ou coisa do tipo, você deve apenas ter o cuidado de adicionar um caminho existente onde você tenha permissão de escrita.
    </p>
  </article>
</div>