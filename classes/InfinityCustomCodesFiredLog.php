<?php
/**
 * „Raz na zamówienie”: kod z once_per_order renderuje się tylko przy pierwszym wyświetleniu
 * dla danej referencji zamówienia (wspólnej dla zamówień rozbitych z jednego koszyka).
 *
 * Wersja demo: funkcja jest dostępna w pełnej wersji modułu (InfinityCustomCodesDemo::FULL_VERSION_URL).
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

final class InfinityCustomCodesFiredLog
{
    /**
     * Zapisuje wyrenderowanie i zwraca true, jeśli to pierwsze dla pary (kod, referencja).
     */
    public static function markFired(int $idCode, string $reference): bool
    {
        // Pełna wersja.
        return true;
    }
}
