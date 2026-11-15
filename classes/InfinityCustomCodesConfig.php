<?php
/**
 * Klucze konfiguracji modułu (tabela ps_configuration) i ich wartości domyślne.
 *
 * Wersja demo: tryb debug jest dostępny w pełnej wersji modułu.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

final class InfinityCustomCodesConfig
{
    /** Owijanie kodów komentarzami HTML z ID i powodem pominięcia. */
    public const DEBUG = 'INFINITYCUSTOMCODES_DEBUG';

    public const DEFAULTS = [
        self::DEBUG => 0,
    ];

    public static function isDebug(): bool
    {
        // Pełna wersja.
        return false;
    }
}
