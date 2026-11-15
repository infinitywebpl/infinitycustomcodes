<?php
/**
 * Miejsce, w którym kod trafia na stronę. Wartość to kolumna `placement` w tabeli kodów.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

enum InfinityCustomCodesPlacement: string
{
    case Head = 'head';
    case BodyTop = 'body_top';
    case BodyBottom = 'body_bottom';
    case OrderConfirmation = 'order_confirmation';
    case Widget = 'widget';

    /**
     * Hook renderujący to miejsce; null dla kodów wstawianych tylko przez {widget}.
     */
    public function hookName(): ?string
    {
        return match ($this) {
            self::Head => 'displayHeader',
            self::BodyTop => 'displayAfterBodyOpeningTag',
            self::BodyBottom => 'displayBeforeBodyClosingTag',
            self::OrderConfirmation => 'displayOrderConfirmation',
            self::Widget => null,
        };
    }

    /**
     * @return string[] hooki do zarejestrowania przy instalacji
     */
    public static function hookNames(): array
    {
        return array_values(array_filter(array_map(
            static fn (self $placement): ?string => $placement->hookName(),
            self::cases()
        )));
    }
}
