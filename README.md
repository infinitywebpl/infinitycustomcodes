# Własne kody na stronie (infinitycustomcodes) — wersja demo

Moduł PrestaShop 8.1+ (PHP ≥ 8.1). Wstawia własne fragmenty HTML/JS (piksele, GTM, czaty, weryfikacje) bez edycji motywu.

**To wersja demo.** Działa wstawianie kodów w nagłówku i stopce strony. Pozostałe funkcje widać w panelu i w kodzie, ale nie działają. Są w pełnej wersji. Kontakt w sprawie pełnej wersji: https://www.infinityweb.pl/oferta/moduly-prestashop, biuro@infinityweb.pl

Panel: **Moduły → Własne kody na stronie (demo) → Konfiguruj**.

## Co działa w demie

| funkcja | demo | pełna wersja |
|---|---|---|
| Nagłówek strony (`<head>`, hook `displayHeader`) | ✓ | ✓ |
| Koniec body / footer (przed `</body>`, hook `displayBeforeBodyClosingTag`) | ✓ | ✓ |
| Lista kodów, kolejność przeciąganiem, duplikowanie, włączanie i wyłączanie | ✓ | ✓ |
| Początek body (zaraz po `<body>`) | — | ✓ |
| Strona potwierdzenia zamówienia | — | ✓ |
| `{widget name='infinitycustomcodes' code=…}` w dowolnym szablonie `.tpl` | — | ✓ |
| Wybór typów stron (główna, kategoria, produkt, koszyk, checkout, CMS…) | — | ✓ |
| Zmienne `{{product.price}}`, `{{order.total_paid}}`, `{{order.products\|json}}`… | — | ✓ |
| „Raz na zamówienie” dla kodów konwersji | — | ✓ |
| Podgląd renderowania dla wskazanego produktu, kategorii lub zamówienia | — | ✓ |
| Tryb debug | — | ✓ |

W demie kod trafia na **każdą stronę bez zmian**. Znaczniki `{{…}}` nie są podstawiane.

## Pełna wersja: zmienne

```js
gtag('event', 'purchase', {
  transaction_id: '{{order.reference}}',
  value: {{order.total_paid}},
  currency: '{{order.currency}}',
  items: {{order.products|json}}
});
```

Przestrzenie `shop`, `page`, `customer`, `product`, `category`, `cart`, `order` i `param` (widget), modyfikatory `js`, `json`, `html`, `url` i `raw`. Kod używający danych niedostępnych na danej stronie jest pomijany w całości.

## Uprawnienia

Dostęp do panelu modułu oznacza **możliwość wstrzyknięcia dowolnego JavaScriptu na front sklepu**. Uprawnienia profili pracowników ustawiaj świadomie.

## Instalacja i przejście na pełną wersję

- Instalacja tworzy tabele `PREFIX_infinitycustomcodes_code` i `PREFIX_infinitycustomcodes_fired`, rejestruje hooki i ukrytą zakładkę panelu.
- **Odinstalowanie usuwa wszystkie kody.**
- Przejście na pełną wersję: wgraj pliki pełnej wersji w miejsce dema. Zapisane kody zostają.

## Licencja

Warunki korzystania z wersji demo: [LICENSE.md](LICENSE.md). Przycisk **„Licencja”** w nagłówku panelu pokazuje, co jest w wersji pełnej, i kontakt. Wersja demo jest bezpłatna, bez kluczy i bez sprawdzania licencji. Wersja pełna wymaga licencji na domenę sklepu: [biuro@infinityweb.pl](mailto:biuro@infinityweb.pl).

## Tłumaczenia

Teksty są po polsku w kodzie, w domenach `Modules.Infinitycustomcodes.Admin` i `Modules.Infinitycustomcodes.Shop`. Inny język dodaje się w „Tłumaczeniach” PrestaShop (typ: moduły).
