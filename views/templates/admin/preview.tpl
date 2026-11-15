{* Panel podglądu w formularzu. Pola mają prefiks icc_preview_, więc copyFromPost() ich nie zapisuje. data-pages: typy stron, dla których pole ma sens (w widgecie wszystkie).
   Teksty z trans() są już po htmlspecialchars, więc escape bez ponownego kodowania encji (:false). *}
<div class="icc-preview panel">
  <div class="panel-heading"><i class="icon-eye"></i> {$texts.title|escape:'html':'UTF-8':false}</div>
  <p class="help-block">{$texts.intro|escape:'html':'UTF-8':false}</p>

  <div class="row">
    <div class="col-lg-3 form-group icc-preview-field icc-preview-page">
      <label for="icc_preview_page">{$texts.page|escape:'html':'UTF-8':false}</label>
      <select name="icc_preview_page" id="icc_preview_page" class="form-control">
        {foreach $pages as $page => $label}
          <option value="{$page|escape:'html':'UTF-8'}">{$label|escape:'html':'UTF-8':false}</option>
        {/foreach}
      </select>
    </div>
    <div class="col-lg-2 form-group icc-preview-field" data-pages="product">
      <label for="icc_preview_id_product">{$texts.id_product|escape:'html':'UTF-8':false}</label>
      <input type="number" min="0" name="icc_preview_id_product" id="icc_preview_id_product" class="form-control">
    </div>
    <div class="col-lg-2 form-group icc-preview-field" data-pages="product">
      <label for="icc_preview_id_product_attribute">{$texts.id_product_attribute|escape:'html':'UTF-8':false}</label>
      <input type="number" min="0" name="icc_preview_id_product_attribute" id="icc_preview_id_product_attribute" class="form-control">
    </div>
    <div class="col-lg-2 form-group icc-preview-field" data-pages="category">
      <label for="icc_preview_id_category">{$texts.id_category|escape:'html':'UTF-8':false}</label>
      <input type="number" min="0" name="icc_preview_id_category" id="icc_preview_id_category" class="form-control">
    </div>
    <div class="col-lg-2 form-group icc-preview-field" data-pages="order-confirmation">
      <label for="icc_preview_id_order">{$texts.id_order|escape:'html':'UTF-8':false}</label>
      <input type="number" min="0" name="icc_preview_id_order" id="icc_preview_id_order" class="form-control">
    </div>
  </div>

  <div class="form-group icc-preview-field" data-pages="">
    <label for="icc_preview_params">{$texts.params|escape:'html':'UTF-8':false}</label>
    <textarea name="icc_preview_params" id="icc_preview_params" rows="3" class="form-control icc-code" placeholder="list=Polecane"></textarea>
  </div>

  <p class="help-block">{$texts.note|escape:'html':'UTF-8':false}</p>
  <button type="button" class="btn btn-default" id="icc-preview-run"><i class="icon-eye"></i> {$texts.run|escape:'html':'UTF-8':false}</button>
  <div id="icc-preview-result"></div>
</div>
