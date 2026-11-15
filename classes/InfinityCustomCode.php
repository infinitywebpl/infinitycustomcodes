<?php
/**
 * Rekord kodu. Pozycje są globalne: kolejność renderowania w obrębie miejsca wynika z kolejności ogólnej.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class InfinityCustomCode extends ObjectModel
{
    public const TABLE = 'infinitycustomcodes_code';
    public const FIRED_TABLE = 'infinitycustomcodes_fired';
    public const WIDGET_KEY_PATTERN = '/^[a-z0-9_-]{1,64}$/';

    /** @var string */
    public $name;

    /** @var string|null pusty zapisujemy jako NULL, żeby UNIQUE nie blokował wielu kodów bez klucza */
    public $widget_key;

    /** @var string */
    public $code;

    /** @var string wartość InfinityCustomCodesPlacement */
    public $placement = 'head';

    /** @var string lista po przecinku z InfinityCustomCodesPageResolver::PAGES albo `all` */
    public $pages = InfinityCustomCodesPageResolver::ALL;

    /** @var bool */
    public $once_per_order = false;

    /** @var bool */
    public $active = true;

    /** @var int */
    public $position = 0;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    public static $definition = [
        'table' => self::TABLE,
        'primary' => 'id_infinitycustomcodes_code',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 128],
            'widget_key' => ['type' => self::TYPE_STRING, 'size' => 64, 'allow_null' => true],
            // TYPE_HTML bez 'validate' => 'isCleanHtml': inaczej <script> wylatuje (CLAUDE.md, Pułapki).
            'code' => ['type' => self::TYPE_HTML, 'required' => true],
            'placement' => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 32],
            'pages' => ['type' => self::TYPE_STRING, 'size' => 255],
            'once_per_order' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public function add($auto_date = true, $null_values = false)
    {
        $this->normalize();
        $this->position = self::nextPosition();

        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        $this->normalize();

        return parent::update($null_values);
    }

    public function delete()
    {
        $db = Db::getInstance();

        return $db->delete(self::FIRED_TABLE, 'id_infinitycustomcodes_code = ' . (int) $this->id)
            && parent::delete()
            && self::cleanPositions();
    }

    /**
     * @return string[] typy stron albo [`all`]
     */
    public function getPages(): array
    {
        return array_values(array_filter(explode(',', (string) $this->pages)));
    }

    /**
     * Przesunięcie pod drag & drop i strzałki HelperList (AdminController::processPosition).
     * $position to nowa pozycja (od 0), $way: 1 = w dół listy (na wyższą pozycję), 0 = w górę.
     */
    public function updatePosition($way, $position): bool
    {
        $db = Db::getInstance();
        $table = '`' . _DB_PREFIX_ . self::TABLE . '`';
        $current = $db->getValue('SELECT position FROM ' . $table . ' WHERE id_infinitycustomcodes_code = ' . (int) $this->id, false);
        if ($current === false || $current === null) {
            return false;
        }

        $current = (int) $current;
        $position = (int) $position;
        $shift = $way
            ? 'position = position - 1 WHERE position > ' . $current . ' AND position <= ' . $position
            : 'position = position + 1 WHERE position < ' . $current . ' AND position >= ' . $position;

        return $db->execute('UPDATE ' . $table . ' SET ' . $shift)
            && $db->execute('UPDATE ' . $table . ' SET position = ' . $position . ' WHERE id_infinitycustomcodes_code = ' . (int) $this->id);
    }

    /**
     * Przenumerowuje pozycje na 0..n-1, zachowując kolejność (po usunięciu kodu).
     */
    public static function cleanPositions(): bool
    {
        $db = Db::getInstance();
        $rows = $db->executeS(
            'SELECT id_infinitycustomcodes_code FROM `' . _DB_PREFIX_ . self::TABLE . '` ORDER BY position ASC, id_infinitycustomcodes_code ASC'
        );

        foreach ($rows ?: [] as $position => $row) {
            if (!$db->update(self::TABLE, ['position' => (int) $position], 'id_infinitycustomcodes_code = ' . (int) $row['id_infinitycustomcodes_code'])) {
                return false;
            }
        }

        return true;
    }

    public static function isValidPlacement(string $placement): bool
    {
        return InfinityCustomCodesPlacement::tryFrom($placement) !== null;
    }

    /**
     * `all` albo niepusta lista znanych typów stron.
     */
    public static function isValidPages(string $pages): bool
    {
        if ($pages === InfinityCustomCodesPageResolver::ALL) {
            return true;
        }

        $list = explode(',', $pages);

        return $pages !== '' && !array_diff($list, InfinityCustomCodesPageResolver::PAGES);
    }

    /**
     * Sama liczba zderzyłaby się z ID w {widget … code=12}.
     */
    public static function isValidWidgetKey(string $key): bool
    {
        return (bool) preg_match(self::WIDGET_KEY_PATTERN, $key) && !ctype_digit($key);
    }

    public static function widgetKeyExists(string $key, int $excludeId = 0): bool
    {
        return (bool) Db::getInstance()->getValue(
            'SELECT 1 FROM `' . _DB_PREFIX_ . self::TABLE . '`
            WHERE widget_key = \'' . pSQL($key) . '\' AND id_infinitycustomcodes_code != ' . (int) $excludeId,
            false
        );
    }

    private static function nextPosition(): int
    {
        $max = Db::getInstance()->getValue('SELECT MAX(position) FROM `' . _DB_PREFIX_ . self::TABLE . '`', false);

        return $max === null || $max === false ? 0 : (int) $max + 1;
    }

    private function normalize(): void
    {
        $key = trim((string) $this->widget_key);
        $this->widget_key = $key === '' ? null : $key;

        if (in_array(InfinityCustomCodesPageResolver::ALL, $this->getPages(), true) || $this->getPages() === []) {
            $this->pages = InfinityCustomCodesPageResolver::ALL;
        }
    }
}
