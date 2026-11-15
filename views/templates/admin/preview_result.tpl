{* Wynik podglądu (ajaxProcessPreview). Wyrenderowany kod pokazujemy escapowany w <pre>, nigdy jako HTML.
   Teksty z trans() ($texts, $skip) są już po htmlspecialchars, więc escape bez ponownego kodowania encji (:false). *}
<div class="icc-preview-result">
  {if $skip}
    <div class="alert alert-warning"><strong>{$texts.skipped|escape:'html':'UTF-8':false}:</strong> {$skip|escape:'html':'UTF-8':false}</div>
  {else}
    <h4>{$texts.rendered|escape:'html':'UTF-8':false}</h4>
    {if $warnings}
      <div class="alert alert-warning">{$texts.warnings|escape:'html':'UTF-8':false} {$warnings|escape:'html':'UTF-8'}</div>
    {/if}
    <pre class="icc-preview-output">{$output|escape:'html':'UTF-8'}</pre>
  {/if}

  <h4>{$texts.variables|escape:'html':'UTF-8':false}</h4>
  <table class="table table-condensed icc-help-table">
    <tbody>
      {foreach $rows as $row}
        <tr>
          <td class="icc-help-tag"><code>{$row.name|escape:'html':'UTF-8'}</code></td>
          <td><pre class="icc-preview-value">{$row.value|escape:'html':'UTF-8'}</pre></td>
        </tr>
      {/foreach}
    </tbody>
  </table>
</div>
