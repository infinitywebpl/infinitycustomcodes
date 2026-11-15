<?php
/**
 * Słownik zmiennych {{przestrzeń.nazwa}}. Budowany z encji, nie z kontrolera,
 * żeby podgląd w adminie i widget z parametrami używały tego samego kodu co hooki.
 *
 * Niezmienny: with() zwraca kopię z dodaną lub nadpisaną przestrzenią.
 *
 * Wersja demo: zmienne są dostępne w pełnej wersji modułu (InfinityCustomCodesDemo::FULL_VERSION_URL).
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

final class InfinityCustomCodesVariables
{
    /**
     * Katalog zmiennych: walidacja przy zapisie i ściągawka. `param` jest dowolne.
     */
    public const NAMES = [
        'shop' => ['id', 'name', 'url'],
        'page' => ['type', 'language', 'currency'],
        'customer' => ['logged', 'id'],
        'product' => ['id', 'id_attribute', 'reference', 'ean13', 'name', 'price', 'price_tax_excl', 'price_regular', 'category', 'manufacturer', 'currency'],
        'category' => ['id', 'name'],
        'cart' => ['id', 'total', 'total_tax_excl', 'products_count', 'products', 'products_ids', 'currency'],
        'order' => [
            'id', 'ids', 'reference', 'total_paid', 'total_paid_tax_excl', 'total_products', 'total_products_tax_excl',
            'total_shipping', 'total_shipping_tax_excl', 'total_discounts', 'total_tax', 'currency', 'payment',
            'items_count', 'products', 'products_ids', 'new_customer', 'email_sha256',
            'customer_firstname', 'customer_email',
        ],
    ];

    /** Zmienne-tablice: renderują się tylko z |json. */
    public const ARRAYS = ['cart.products', 'order.products'];

    /**
     * Typy stron, na których przestrzeń jest dostępna z kontekstu (fromContext()). Przestrzenie spoza listy
     * (shop, page, customer) są zawsze; param tylko w widgecie.
     */
    public const CONTEXT_PAGES = [
        'product' => ['product'],
        'category' => ['category'],
        'cart' => ['cart', 'checkout'],
        'order' => ['order-confirmation'],
    ];

    /**
     * @param array<string, mixed> $values płasko: 'product.id' => 123
     * @param array<string, true> $namespaces dostępne przestrzenie
     */
    private function __construct(
        private readonly array $values,
        private readonly array $namespaces,
    ) {
    }

    public static function empty(): self
    {
        return new self([], []);
    }

    /**
     * Zmienne dostępne z kontekstu strony: zawsze shop/page/customer, reszta zależnie od typu strony.
     */
    public static function fromContext(Context $context, string $pageType): self
    {
        // Pełna wersja.
        return self::empty();
    }

    /**
     * Kopia z przestrzeniami nadpisanymi parametrami {widget}: id_product (+ id_product_attribute),
     * id_category, id_order, param_*.
     *
     * @param array<string, mixed> $configuration parametry z wywołania {widget} (bez `name`)
     *
     * @return array{0: self, 1: array<string, string>} zmienne i problemy: przestrzeń => `product_unavailable`,
     *   `category_unavailable`, `order_unavailable` albo `order_foreign`
     */
    public function withWidgetConfiguration(array $configuration, Context $context): array
    {
        // Pełna wersja.
        return [self::empty(), []];
    }

    /**
     * Zmienne do podglądu w adminie: jak na froncie, ale encje wskazane po ID.
     *
     * @param array{id_product?: int, id_product_attribute?: int, id_category?: int, id_order?: int} $ids
     * @param array<string, string> $params param.xxx => wartość (tylko widget)
     *
     * @return array{0: self, 1: array<string, string>} zmienne i problemy jak w withWidgetConfiguration()
     */
    public static function forPreview(Context $context, string $pageType, bool $widget, array $ids, array $params): array
    {
        // Pełna wersja.
        return [self::empty(), []];
    }

    /**
     * @return array<string, array<string, mixed>> przestrzeń => zmienne
     */
    public static function base(Context $context, string $pageType): array
    {
        // Pełna wersja.
        return [];
    }

    /**
     * $idAttribute spoza produktu albo 0 = kombinacja domyślna (produkt bez kombinacji: 0).
     *
     * @return array<string, mixed>
     */
    public static function forProduct(Product $product, int $idAttribute, Context $context): array
    {
        // Pełna wersja.
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forCategory(Category $category, Context $context): array
    {
        // Pełna wersja.
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forCart(Cart $cart): array
    {
        // Pełna wersja.
        return [];
    }

    /**
     * Zamówienie razem z zamówieniami rozbitymi z tego samego koszyka (wspólna referencja): kwoty i produkty sumowane.
     *
     * @return array<string, mixed>
     */
    public static function forOrder(Order $order): array
    {
        // Pełna wersja.
        return [];
    }

    /**
     * Kopia z przestrzenią $namespace ustawioną na $vars (poprzednie wartości tej przestrzeni znikają).
     *
     * @param array<string, mixed> $vars nazwa bez przestrzeni => wartość
     */
    public function with(string $namespace, array $vars): self
    {
        $prefix = $namespace . '.';
        $values = array_filter(
            $this->values,
            static fn (string $name): bool => !str_starts_with($name, $prefix),
            ARRAY_FILTER_USE_KEY
        );
        foreach ($vars as $name => $value) {
            $values[$prefix . $name] = $value;
        }

        return new self($values, [$namespace => true] + $this->namespaces);
    }

    public function without(string $namespace): self
    {
        $prefix = $namespace . '.';
        $namespaces = $this->namespaces;
        unset($namespaces[$namespace]);

        return new self(
            array_filter($this->values, static fn (string $name): bool => !str_starts_with($name, $prefix), ARRAY_FILTER_USE_KEY),
            $namespaces
        );
    }

    public function hasNamespace(string $namespace): bool
    {
        return isset($this->namespaces[$namespace]);
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }

    public function get(string $name): mixed
    {
        return $this->values[$name] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * Zamówienie widoczne dla odwiedzającego: zalogowany klient jest jego właścicielem albo to zamówienie
     * z bieżącej strony potwierdzenia.
     */
    private static function canSeeOrder(Order $order, Context $context, self $pageVariables): bool
    {
        // Pełna wersja.
        return false;
    }

    /**
     * Przestrzeń zbudowana z encji po ID, zapamiętana na request.
     *
     * @param callable(): ?array<string, mixed> $build
     *
     * @return array<string, mixed>|null
     */
    private static function cached(string $namespace, string $key, callable $build): ?array
    {
        // Pełna wersja.
        return null;
    }

    /**
     * Kwota: float zaokrąglony do 2 miejsc.
     */
    private static function amount(mixed $value): float
    {
        // Pełna wersja.
        return 0.0;
    }

    /**
     * Pierwsze zamówienie klienta.
     */
    private static function isFirstOrder(Order $order, int $firstOrderId): bool
    {
        // Pełna wersja.
        return false;
    }

    /**
     * Pole wielojęzyczne encji: string, gdy encja jest wczytana w jednym języku, tablica w przeciwnym razie.
     */
    private static function langValue(mixed $value, int $idLang): string
    {
        // Pełna wersja.
        return '';
    }
}
