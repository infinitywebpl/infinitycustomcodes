{* Widok „Licencja”, wariant demo: bez klucza i bez statusu licencji. Wartości z $html i $features to gotowy HTML
   z kontrolera (renderLicense(): tłumaczenia po htmlspecialchars), więc idą bez filtra. Escapujemy tu tylko adresy. *}
<div class="panel icc-license">
  <div class="panel-heading"><i class="icon-certificate"></i> {$html.header nofilter}</div>

  <p>{$html.demo nofilter}</p>

  <p><strong>{$html.full nofilter}</strong></p>
  <ul>
    {foreach $features as $feature}
      <li>{$feature nofilter}</li>
    {/foreach}
  </ul>

  <p>{$html.contact nofilter}</p>

  <p class="icc-license-form">
    <a href="{$back_url|escape:'html':'UTF-8'}" class="btn btn-default">
      <i class="icon-arrow-left"></i> {$html.back nofilter}
    </a>
  </p>

  <div class="alert alert-info icc-license-more">
    <p><strong>{$html.more_title nofilter}</strong></p>
    <p>{$html.more_github nofilter}<br>{$html.more_site nofilter}</p>
    <p>
      <a href="{$github_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener" class="btn btn-default">{$html.more_github_button nofilter}</a>
      <a href="{$site_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener" class="btn btn-default">infinityweb.pl</a>
    </p>
  </div>

  <p class="help-block">{$html.footer nofilter}</p>
</div>
