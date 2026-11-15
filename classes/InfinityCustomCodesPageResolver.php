<?php
/**
 * Typy stron, na których kod może się renderować (kolumna `pages`), i rozpoznawanie typu bieżącej strony.
 *
 * Wersja demo: wybór stron jest dostępny w pełnej wersji modułu (InfinityCustomCodesDemo::FULL_VERSION_URL).
 * Kody renderują się na wszystkich stronach.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

final class InfinityCustomCodesPageResolver
{
    /** Wartość `pages` oznaczająca wszystkie strony; wyklucza pozostałe. */
    public const ALL = 'all';

    /** Strony nierozpoznane: konto klienta, 404, kontakt, kontrolery modułów (puste php_self) itd. */
    public const OTHER = 'other';

    /** Jedyna lista typów stron: checkboxy w formularzu, etykiety w liście i walidacja. */
    public const PAGES = [
        'index',
        'category',
        'product',
        'cart',
        'checkout',
        'order-confirmation',
        'cms',
        'search',
        'manufacturer',
        self::OTHER,
    ];

    public static function resolve(Context $context): string
    {
        // Pełna wersja.
        return self::OTHER;
    }

    /**
     * Czy kod z daną wartością `pages` renderuje się na stronie typu $pageType.
     */
    public static function matches(string $pages, string $pageType): bool
    {
        // Pełna wersja. W demie wszystkie strony.
        return true;
    }
}
