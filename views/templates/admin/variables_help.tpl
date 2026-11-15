{* Ściągawka zmiennych pod polem kodu. Teksty i znaczniki {{…}} przychodzą z kontrolera jako zmienne, nie jako literały szablonu. *}
<div class="icc-help panel">
  <div class="panel-heading"><i class="icon-book"></i> {$texts.title|escape:'html':'UTF-8'}</div>
  <p class="help-block">{$texts.hint|escape:'html':'UTF-8'}</p>

  {foreach $groups as $group}
    <h4 class="icc-help-namespace">
      {$group.namespace|escape:'html':'UTF-8'}.*
      <small>{$texts.source|escape:'html':'UTF-8'}: {$group.source|escape:'html':'UTF-8'}</small>
    </h4>
    <table class="table table-condensed icc-help-table">
      <tbody>
        {foreach $group.vars as $var}
          <tr>
            <td class="icc-help-tag"><code class="icc-var" data-insert="{$var.tag|escape:'html':'UTF-8'}">{$var.tag|escape:'html':'UTF-8'}</code></td>
            <td>{$var.description|escape:'html':'UTF-8'}</td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {/foreach}

  <h4 class="icc-help-namespace">{$texts.modifiers|escape:'html':'UTF-8'}</h4>
  <table class="table table-condensed icc-help-table">
    <tbody>
      {foreach $modifiers as $modifier}
        <tr>
          <td class="icc-help-tag"><code class="icc-var" data-insert="{$modifier.tag|escape:'html':'UTF-8'}">{$modifier.tag|escape:'html':'UTF-8'}</code></td>
          <td>{$modifier.description|escape:'html':'UTF-8'}</td>
        </tr>
      {/foreach}
    </tbody>
  </table>

  <ul class="icc-help-notes">
    {foreach $notes as $note}
      <li>{$note|escape:'html':'UTF-8'}</li>
    {/foreach}
  </ul>
  <p><code class="icc-snippet">{$texts.widget_example|escape:'html':'UTF-8'}</code></p>
</div>
