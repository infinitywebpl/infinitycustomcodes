<?php
/**
 * Wersja demo: miejsca dostępne w demie i adres pełnej wersji.
 *
 * Demo renderuje kody tylko w nagłówku (head) i na końcu body (footer), na wszystkich stronach i bez zmiennych.
 * Wybór stron, widget, zmienne {{…}}, potwierdzenie zamówienia, „raz na zamówienie”, podgląd i tryb debug
 * są w pełnej wersji modułu.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

final class InfinityCustomCodesDemo
{
    /** Kontakt w sprawie pełnej wersji modułu. */
    public const FULL_VERSION_URL = 'https://www.infinityweb.pl/oferta/moduly-prestashop';

    public const CONTACT_EMAIL = 'biuro@infinityweb.pl';

    public const SITE_URL = 'https://www.infinityweb.pl';

    public const GITHUB_URL = 'https://github.com/infinitywebpl';

    /** Miejsca działające w demie. */
    public const PLACEMENTS = [
        InfinityCustomCodesPlacement::Head,
        InfinityCustomCodesPlacement::BodyBottom,
    ];

    public static function isAvailable(InfinityCustomCodesPlacement $placement): bool
    {
        return in_array($placement, self::PLACEMENTS, true);
    }
}
