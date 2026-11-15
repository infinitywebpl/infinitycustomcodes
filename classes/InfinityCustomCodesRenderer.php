<?php
/**
 * Podstawianie {{przestrzeń.nazwa|modyfikator}} w kodzie użytkownika.
 *
 * Podmienia tylko znane przestrzenie; inne {{…}} (np. Vue, Handlebars) zostają nietknięte.
 * Gdy kod używa przestrzeni niedostępnej na stronie albo nieprzekazanego param.xxx, cały kod jest pomijany,
 * żeby `value: {{product.price}},` nie dawało błędu składni JS.
 *
 * Wersja demo: zmienne są dostępne w pełnej wersji modułu (InfinityCustomCodesDemo::FULL_VERSION_URL).
 * Kod trafia na stronę bez zmian.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

final class InfinityCustomCodesRenderer
{
    public const NAMESPACES = ['shop', 'page', 'customer', 'product', 'category', 'cart', 'order', 'param'];
    public const MODIFIERS = ['js', 'json', 'html', 'url', 'raw'];

    /**
     * Zmienne ze znanych przestrzeni użyte w kodzie (walidacja przy zapisie), bez powtórzeń.
     *
     * @return array<int, array{namespace: string, name: string, modifier: string}> modifier '' = domyślny
     */
    public function usedVariables(string $code): array
    {
        // Pełna wersja.
        return [];
    }

    /**
     * @return array{output: string|null, missing: string[], warnings: string[]}
     *   output null = kod pominięty; missing: brakujące przestrzenie (`product`) albo parametry (`param.list`);
     *   warnings: zmienne użyte niepoprawnie (tablica bez |json, nieznany modyfikator), np. `order.products|js`
     */
    public function render(string $code, InfinityCustomCodesVariables $variables): array
    {
        // Pełna wersja. W demie kod bez zmian.
        return ['output' => $code, 'missing' => [], 'warnings' => []];
    }

    private static function format(mixed $value, string $modifier): string
    {
        // Pełna wersja.
        return '';
    }

    /**
     * Kwoty (float) z 2 miejscami i kropką, bool jako 1/0, null jako pusty string.
     */
    private static function scalar(mixed $value): string
    {
        // Pełna wersja.
        return '';
    }
}
