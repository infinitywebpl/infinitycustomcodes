/**
 * Panel kodów: widoczność pól zależnie od miejsca, „Wszystkie” wykluczające inne strony, kopiowanie snippetu widgetu.
 *
 * Wersja demo: miejsca spoza demo, klucz widgetu, strony, „raz na zamówienie”, podgląd i tryb debug
 * są widoczne, ale nieaktywne. Klik w zmienną pokazuje informację o pełnej wersji.
 */
$(function () {
  var texts = window.infinityCustomCodes || {};
  var demo = texts.demo || { placements: [] };
  var $placement = $('select#placement');
  var $pages = $('input[name^="pages_"]');

  function notice(message) {
    if (typeof showNoticeMessage === 'function') {
      showNoticeMessage(message);
    } else {
      window.alert(message);
    }
  }

  // Wersja demo: nieaktywne opcje formularza.
  $placement.find('option').each(function () {
    if (demo.placements.indexOf(this.value) === -1) {
      $(this).prop('disabled', true);
    }
  });
  $pages.prop('disabled', true);
  $('#widget_key').prop('disabled', true);
  $('input[name="once_per_order"]').prop('disabled', true);
  $('input[name="INFINITYCUSTOMCODES_DEBUG"]').prop('disabled', true);
  $('.icc-preview').find('input, select, textarea').prop('disabled', true);

  function updateVisibility() {
    var placement = $placement.val();

    $('.icc-pages-group').toggle(placement !== 'order_confirmation');
  }

  if ($placement.length) {
    $placement.on('change', updateVisibility);
    updateVisibility();
  }

  // Ściągawka: zmienne są w pełnej wersji.
  $(document).on('click', '.icc-var', function () {
    notice(demo.variables);
  });

  // Podgląd: serwer odpowiada informacją o pełnej wersji.
  function runPreview() {
    var $result = $('#icc-preview-result');
    $.ajax({
      type: 'POST',
      url: texts.previewUrl,
      data: $('#code').closest('form').serialize() + '&ajax=1&action=preview',
      dataType: 'json'
    }).done(function (response) {
      $result.html(response.html);
    }).fail(function () {
      $result.empty().append($('<div class="alert alert-danger">').text(texts.previewError));
    });
  }

  $('#icc-preview-run').on('click', runPreview);

  function copy(text) {
    var done = function () {
      if (texts.copied && typeof showSuccessMessage === 'function') {
        showSuccessMessage(texts.copied);
      }
    };

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(done);
      return;
    }

    var $tmp = $('<textarea>').val(text).css({ position: 'fixed', top: 0, left: 0, opacity: 0 }).appendTo('body');
    $tmp[0].select();
    document.execCommand('copy');
    $tmp.remove();
    done();
  }

  // Komórka listy ze snippetem ma remove_onclick, więc klik nie otwiera edycji.
  $(document).on('click', '.icc-snippet', function () {
    copy($(this).text());
  });
});
