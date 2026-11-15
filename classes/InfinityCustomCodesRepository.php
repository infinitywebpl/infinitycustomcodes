<?php
/**
 * Odczyt aktywnych kodów na froncie: jedno zapytanie na request, wynik w statycznym cache.
 * Zwraca wiersze tablicowe, nie ObjectModel: front potrzebuje tylko kilku kolumn.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

final class InfinityCustomCodesRepository
{
    /** @var array<int, array<string, mixed>>|null ID kodu => wiersz, w kolejności position */
    private static ?array $active = null;

    /**
     * @return array<int, array<string, mixed>> aktywne kody danego miejsca w kolejności position
     */
    public static function findByPlacement(InfinityCustomCodesPlacement $placement): array
    {
        return array_filter(
            self::getActive(),
            static fn (array $code): bool => $code['placement'] === $placement->value
        );
    }

    /**
     * Aktywny kod z {widget … code=…}: liczba = ID, tekst = widget_key (klucz nie może być samą liczbą).
     *
     * @return array<string, mixed>|null
     */
    public static function findByIdOrKey(string $reference): ?array
    {
        $active = self::getActive();

        if (ctype_digit($reference)) {
            return $active[(int) $reference] ?? null;
        }

        foreach ($active as $code) {
            if ($code['widget_key'] === $reference) {
                return $code;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function getActive(): array
    {
        if (self::$active !== null) {
            return self::$active;
        }

        $rows = Db::getInstance()->executeS(
            'SELECT id_infinitycustomcodes_code, name, widget_key, code, placement, pages, once_per_order
            FROM `' . _DB_PREFIX_ . InfinityCustomCode::TABLE . '`
            WHERE active = 1
            ORDER BY position ASC, id_infinitycustomcodes_code ASC'
        );

        self::$active = [];
        foreach ($rows ?: [] as $row) {
            self::$active[(int) $row['id_infinitycustomcodes_code']] = $row;
        }

        return self::$active;
    }
}
