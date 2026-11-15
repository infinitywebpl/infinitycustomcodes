<?php
/**
 * Panel kodów: lista z pozycjami, formularz, duplikowanie, ściągawka zmiennych i podgląd.
 *
 * Wersja demo: działają kody w nagłówku i stopce. Pozostałe opcje formularza są widoczne, ale nieaktywne,
 * a podgląd i zmienne prowadzą do pełnej wersji (InfinityCustomCodesDemo). Widok „Licencja” w wariancie demo:
 * bez klucza i bez paska „Wersja niezarejestrowana”.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Klasy modułu są potrzebne już w konstruktorze, przed parent::__construct(), a ModuleAdminController
// wczytuje moduł (Module::getInstanceByName()) dopiero w swoim konstruktorze.
require_once dirname(__DIR__, 2) . '/infinitycustomcodes.php';

class AdminInfinityCustomCodesController extends ModuleAdminController
{
    /** Wartość w base64: Cookie::__set() odrzuca znak `|`, który występuje w ostrzeżeniach (np. `|json`). */
    private const WARNINGS_COOKIE = 'infinitycustomcodes_warnings';

    /** Widok „Licencja”: parametr w URL i wartość $this->display. */
    private const LICENSE_PARAM = 'license';

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = InfinityCustomCode::TABLE;
        $this->className = InfinityCustomCode::class;
        $this->identifier = 'id_infinitycustomcodes_code';
        $this->position_identifier = 'id_infinitycustomcodes_code';
        $this->lang = false;
        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';

        parent::__construct();

        $this->fields_list = [
            'id_infinitycustomcodes_code' => [
                'title' => $this->trans('ID', [], 'Modules.Infinitycustomcodes.Admin'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title' => $this->trans('Nazwa', [], 'Modules.Infinitycustomcodes.Admin'),
                'filter_key' => 'a!name',
            ],
            'placement' => [
                'title' => $this->trans('Miejsce', [], 'Modules.Infinitycustomcodes.Admin'),
                'type' => 'select',
                'list' => $this->getPlacementLabels(),
                'filter_key' => 'a!placement',
                'callback' => 'renderPlacementLabel',
            ],
            'pages' => [
                'title' => $this->trans('Strony', [], 'Modules.Infinitycustomcodes.Admin'),
                'search' => false,
                'orderby' => false,
                'callback' => 'renderPagesLabel',
            ],
            'widget_key' => [
                'title' => $this->trans('Widget', [], 'Modules.Infinitycustomcodes.Admin'),
                'filter_key' => 'a!widget_key',
                'callback' => 'renderWidgetSnippet',
                // Klik w komórce kopiuje snippet zamiast otwierać edycję.
                'remove_onclick' => true,
            ],
            'once_per_order' => [
                'title' => $this->trans('Raz/zamówienie', [], 'Modules.Infinitycustomcodes.Admin'),
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!once_per_order',
                'callback' => 'renderOncePerOrder',
            ],
            'position' => [
                'title' => $this->trans('Pozycja', [], 'Modules.Infinitycustomcodes.Admin'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-sm',
                'search' => false,
            ],
            'active' => [
                'title' => $this->trans('Aktywny', [], 'Modules.Infinitycustomcodes.Admin'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!active',
            ],
            'date_upd' => [
                'title' => $this->trans('Modyfikacja', [], 'Modules.Infinitycustomcodes.Admin'),
                'type' => 'datetime',
                'search' => false,
            ],
        ];

        // Renderowane pod listą i zapisywane przez AdminController::processUpdateOptions().
        $this->fields_options = [
            'debug' => [
                'title' => $this->trans('Diagnostyka', [], 'Modules.Infinitycustomcodes.Admin'),
                'icon' => 'icon-bug',
                'fields' => [
                    InfinityCustomCodesConfig::DEBUG => [
                        'title' => $this->trans('Tryb debug', [], 'Modules.Infinitycustomcodes.Admin'),
                        'desc' => $this->trans('Owija każdy kod w źródle strony komentarzem HTML z ID i nazwą, a pominięte kody zostawiają komentarz z powodem. Widoczne dla wszystkich odwiedzających, więc włączaj tylko na czas diagnozy.', [], 'Modules.Infinitycustomcodes.Admin')
                            . ' ' . $this->trans('Dostępne w pełnej wersji modułu.', [], 'Modules.Infinitycustomcodes.Admin'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'cast' => 'intval',
                    ],
                ],
                'submit' => ['title' => $this->trans('Zapisz', [], 'Modules.Infinitycustomcodes.Admin')],
            ],
        ];

        $this->bulk_actions = [
            'enableSelection' => [
                'text' => $this->trans('Włącz zaznaczone', [], 'Modules.Infinitycustomcodes.Admin'),
                'icon' => 'icon-power-off text-success',
            ],
            'disableSelection' => [
                'text' => $this->trans('Wyłącz zaznaczone', [], 'Modules.Infinitycustomcodes.Admin'),
                'icon' => 'icon-power-off text-danger',
            ],
            'delete' => [
                'text' => $this->trans('Usuń zaznaczone', [], 'Modules.Infinitycustomcodes.Admin'),
                'confirm' => $this->trans('Usunąć zaznaczone kody?', [], 'Modules.Infinitycustomcodes.Admin'),
                'icon' => 'icon-trash',
            ],
        ];
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS($this->module->getPathUri() . 'views/css/admin.css');
        $this->addJS($this->module->getPathUri() . 'views/js/admin.js');
        Media::addJsDef([
            'infinityCustomCodes' => [
                'copied' => $this->trans('Skopiowano do schowka.', [], 'Modules.Infinitycustomcodes.Admin'),
                'previewUrl' => $this->context->link->getAdminLink(InfinityCustomCodes::ADMIN_CONTROLLER),
                'previewError' => $this->trans('Nie udało się wykonać podglądu.', [], 'Modules.Infinitycustomcodes.Admin'),
                'demo' => [
                    'placements' => array_map(static fn (InfinityCustomCodesPlacement $placement): string => $placement->value, InfinityCustomCodesDemo::PLACEMENTS),
                    'variables' => self::plain($this->trans('Zmienne są dostępne w pełnej wersji modułu: %url%', ['%url%' => InfinityCustomCodesDemo::FULL_VERSION_URL], 'Modules.Infinitycustomcodes.Admin')),
                ],
            ],
        ]);
    }

    /**
     * Ostrzeżenia z zapisu przechodzą przez przekierowanie w cookie (processSave()).
     */
    public function initContent()
    {
        $cookie = $this->context->cookie;
        if (isset($cookie->{self::WARNINGS_COOKIE})) {
            $warnings = json_decode((string) base64_decode((string) $cookie->{self::WARNINGS_COOKIE}), true);
            unset($cookie->{self::WARNINGS_COOKIE});
            if (is_array($warnings)) {
                $this->warnings = array_merge($this->warnings, array_map('strval', $warnings));
            }
        }

        // AdminController::initContent() nie zna tego widoku i wyrenderowałby listę. Bez uprawnienia
        // do podglądu parent::initContent() sam zgłasza błąd i niczego nie renderuje.
        if ($this->display === self::LICENSE_PARAM && $this->viewAccess()) {
            $this->content .= $this->renderLicense();
            $this->context->smarty->assign(['content' => $this->content]);

            return;
        }

        // Wersja demo: zamiast paska „Wersja niezarejestrowana” komunikat o pełnej wersji.
        if (!$this->ajax) {
            $this->informations[] = $this->trans('Wersja demo: kody w nagłówku (head) i stopce (footer) na wszystkich stronach. Początek body, potwierdzenie zamówienia, widget, wybór stron, zmienne, „raz na zamówienie”, podgląd i tryb debug są w pełnej wersji: %url%, %email%', ['%url%' => InfinityCustomCodesDemo::FULL_VERSION_URL, '%email%' => InfinityCustomCodesDemo::CONTACT_EMAIL], 'Modules.Infinitycustomcodes.Admin');
        }

        parent::initContent();
    }

    public function initToolbarTitle()
    {
        parent::initToolbarTitle();

        if ($this->display === self::LICENSE_PARAM) {
            $title = $this->trans('Licencja', [], 'Modules.Infinitycustomcodes.Admin');
            $this->toolbar_title[] = $title;
            $this->addMetaTitle($title);
        }
    }

    public function initPageHeaderToolbar()
    {
        if ($this->display === self::LICENSE_PARAM) {
            $this->page_header_toolbar_btn['back'] = [
                'href' => self::$currentIndex . '&token=' . $this->token,
                'desc' => $this->trans('Wróć do listy', [], 'Modules.Infinitycustomcodes.Admin'),
            ];
        } else {
            if (empty($this->display) || $this->display === 'list') {
                $this->page_header_toolbar_btn['new'] = [
                    'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                    'desc' => $this->trans('Dodaj kod', [], 'Modules.Infinitycustomcodes.Admin'),
                    'icon' => 'process-icon-new',
                ];
            }
            $this->page_header_toolbar_btn['license'] = [
                'href' => $this->getLicenseUrl(),
                'desc' => $this->trans('Licencja', [], 'Modules.Infinitycustomcodes.Admin'),
                'icon' => 'process-icon-flag',
            ];
        }

        parent::initPageHeaderToolbar();
    }

    public function initProcess()
    {
        parent::initProcess();

        if (Tools::getIsset(self::LICENSE_PARAM)) {
            // Wersja demo: widok bez klucza, więc bez akcji zapisu.
            $this->display = self::LICENSE_PARAM;

            return;
        }

        if (Tools::getIsset('duplicate' . $this->table)) {
            if ($this->access('add')) {
                $this->action = 'duplicate';
            } else {
                $this->errors[] = $this->trans('Brak uprawnień do dodawania kodów.', [], 'Modules.Infinitycustomcodes.Admin');
            }
        }
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('duplicate');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function renderForm()
    {
        /** @var InfinityCustomCode $code */
        $code = $this->loadObject(true);
        if (!$code) {
            return '';
        }

        $pageOptions = [];
        foreach ($this->getPageLabels() as $page => $label) {
            $pageOptions[] = ['id' => $page, 'name' => $label];
        }

        $placementOptions = [];
        $fullVersion = $this->trans('(pełna wersja)', [], 'Modules.Infinitycustomcodes.Admin');
        foreach ($this->getPlacementLabels() as $placement => $label) {
            $available = InfinityCustomCodesDemo::isAvailable(InfinityCustomCodesPlacement::from($placement));
            $placementOptions[] = ['id' => $placement, 'name' => $available ? $label : $label . ' ' . $fullVersion];
        }
        $onlyFull = $this->trans('Dostępne w pełnej wersji modułu.', [], 'Modules.Infinitycustomcodes.Admin');

        $yesNo = static fn (string $name, string $yes, string $no): array => [
            ['id' => $name . '_on', 'value' => 1, 'label' => $yes],
            ['id' => $name . '_off', 'value' => 0, 'label' => $no],
        ];
        $yes = $this->trans('Tak', [], 'Modules.Infinitycustomcodes.Admin');
        $no = $this->trans('Nie', [], 'Modules.Infinitycustomcodes.Admin');

        $this->fields_form = [
            'legend' => [
                'title' => $code->id
                    ? $this->trans('Edycja kodu', [], 'Modules.Infinitycustomcodes.Admin')
                    : $this->trans('Nowy kod', [], 'Modules.Infinitycustomcodes.Admin'),
                'icon' => 'icon-code',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->trans('Nazwa', [], 'Modules.Infinitycustomcodes.Admin'),
                    'name' => 'name',
                    'required' => true,
                    'maxlength' => 128,
                    'hint' => $this->trans('Widoczna tylko w panelu.', [], 'Modules.Infinitycustomcodes.Admin'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->trans('Miejsce', [], 'Modules.Infinitycustomcodes.Admin'),
                    'name' => 'placement',
                    'required' => true,
                    'options' => ['query' => $placementOptions, 'id' => 'id', 'name' => 'name'],
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Klucz widgetu', [], 'Modules.Infinitycustomcodes.Admin'),
                    'name' => 'widget_key',
                    'maxlength' => 64,
                    'desc' => $this->getWidgetKeyHelp($code) . '<br>' . $this->escapeTranslated($onlyFull),
                ],
                [
                    'type' => 'checkbox',
                    'label' => $this->trans('Strony', [], 'Modules.Infinitycustomcodes.Admin'),
                    'name' => 'pages',
                    'values' => ['query' => $pageOptions, 'id' => 'id', 'name' => 'name'],
                    'form_group_class' => 'icc-pages-group',
                    'desc' => $this->trans('„Wszystkie” wyklucza pozostałe. Dla potwierdzenia zamówienia pole jest ignorowane.', [], 'Modules.Infinitycustomcodes.Admin') . ' ' . $onlyFull,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->trans('Kod', [], 'Modules.Infinitycustomcodes.Admin'),
                    'name' => 'code',
                    'required' => true,
                    'rows' => 20,
                    'autoload_rte' => false,
                    'class' => 'icc-code',
                    'desc' => $this->trans('Surowy HTML/JS. Trafia na stronę bez zmian. Zmienne {{…}} działają w pełnej wersji.', [], 'Modules.Infinitycustomcodes.Admin'),
                ],
                [
                    'type' => 'html',
                    'name' => 'icc_help',
                    'label' => $this->trans('Zmienne i podgląd', [], 'Modules.Infinitycustomcodes.Admin'),
                    'html_content' => $this->renderVariablesHelp() . $this->renderPreviewPanel(),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Raz na zamówienie', [], 'Modules.Infinitycustomcodes.Admin'),
                    'name' => 'once_per_order',
                    'is_bool' => true,
                    'values' => $yesNo('once_per_order', $yes, $no),
                    'form_group_class' => 'icc-once-group',
                    'desc' => $this->trans('Kod konwersji nie wykona się ponownie po odświeżeniu strony potwierdzenia. Działa tylko, gdy kod ma dostęp do zamówienia.', [], 'Modules.Infinitycustomcodes.Admin') . ' ' . $onlyFull,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Aktywny', [], 'Modules.Infinitycustomcodes.Admin'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => $yesNo('active', $yes, $no),
                ],
            ],
            'submit' => [
                'title' => $this->trans('Zapisz', [], 'Modules.Infinitycustomcodes.Admin'),
            ],
            'buttons' => [
                [
                    'type' => 'submit',
                    'title' => $this->trans('Zapisz i zostań', [], 'Modules.Infinitycustomcodes.Admin'),
                    'icon' => 'process-icon-save',
                    'name' => 'submitAdd' . $this->table . 'AndStay',
                    'class' => 'pull-right',
                ],
            ],
        ];

        // Checkboxy HelperForm czytają wartości z kluczy pages_<typ>. Po błędzie zapisu bierzemy je z POST
        // (processSave() składa tam `pages`).
        $selected = explode(',', (string) Tools::getValue('pages', $code->pages));
        foreach (array_keys($this->getPageLabels()) as $page) {
            $this->fields_value['pages_' . $page] = in_array($page, $selected, true);
        }

        return parent::renderForm();
    }

    /**
     * Składa `pages` z checkboxów i normalizuje klucz widgetu przed walidacją i copyFromPost().
     * Ostrzeżenia nie blokują zapisu: po udanym zapisie idą przez przekierowanie w cookie,
     * przy błędzie formularz renderuje się od razu, więc wystarczy $this->warnings.
     */
    public function processSave()
    {
        $_POST['pages'] = $this->getPagesFromPost();
        $_POST['widget_key'] = trim((string) Tools::getValue('widget_key'));

        $warnings = $this->getSaveWarnings(
            (string) ($_POST['code'] ?? ''),
            (string) Tools::getValue('placement'),
            (string) $_POST['pages'],
            (bool) Tools::getValue('once_per_order')
        );

        $result = parent::processSave();

        if ($warnings) {
            if ($result && !$this->errors) {
                $this->context->cookie->{self::WARNINGS_COOKIE} = base64_encode((string) json_encode($warnings));
            } else {
                $this->warnings = array_merge($this->warnings, $warnings);
            }
        }

        return $result;
    }

    /**
     * Podgląd renderowania bieżącej treści formularza, bez zapisu. Odpowiedź: {"html": "…"}.
     */
    public function ajaxProcessPreview()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->access('edit') && !$this->access('add')) {
            $html = '<div class="alert alert-danger">' . $this->escapeTranslated($this->trans('Brak uprawnień do podglądu.', [], 'Modules.Infinitycustomcodes.Admin')) . '</div>';
        } else {
            try {
                // Pełna wersja.
                $html = '<div class="alert alert-info">' . $this->escapeTranslated($this->trans('Podgląd renderowania jest dostępny w pełnej wersji modułu: %url%', ['%url%' => InfinityCustomCodesDemo::FULL_VERSION_URL], 'Modules.Infinitycustomcodes.Admin')) . '</div>';
            } catch (\Throwable $e) {
                $html = '<div class="alert alert-danger">' . $this->escape($e->getMessage()) . '</div>';
            }
        }

        $this->ajaxRender((string) json_encode(['html' => $html]));
        exit;
    }

    /**
     * Kopia z active = 0, dopiskiem „(kopia)” i bez klucza widgetu; potem od razu jej edycja.
     */
    public function processDuplicate()
    {
        /** @var InfinityCustomCode|false $source */
        $source = $this->loadObject();
        if (!$source) {
            return false;
        }

        $copy = new InfinityCustomCode();
        $copy->name = Tools::substr($source->name . ' ' . $this->trans('(kopia)', [], 'Modules.Infinitycustomcodes.Admin'), 0, 128);
        $copy->code = $source->code;
        $copy->placement = $source->placement;
        $copy->pages = $source->pages;
        $copy->once_per_order = $source->once_per_order;
        $copy->active = false;
        $copy->widget_key = null;

        if (!$copy->add()) {
            $this->errors[] = $this->trans('Nie udało się skopiować kodu.', [], 'Modules.Infinitycustomcodes.Admin');

            return false;
        }

        $this->redirect_after = self::$currentIndex . '&' . $this->identifier . '=' . (int) $copy->id
            . '&update' . $this->table . '&conf=3&token=' . $this->token;

        return $copy;
    }

    public function ajaxProcessUpdatePositions()
    {
        $way = (int) Tools::getValue('way');
        $id = (int) Tools::getValue('id');
        $positions = Tools::getValue($this->table);
        // dnd.js wysyła kolejność tylko z bieżącej strony listy.
        $page = (int) Tools::getValue('page');
        $offset = $page > 1 ? ($page - 1) * (int) Tools::getValue('selected_pagination') : 0;

        foreach (is_array($positions) ? $positions : [] as $index => $row) {
            // Wiersze listy mają id tr_<grupa>_<id>_<pozycja>.
            $parts = explode('_', (string) $row);
            if (!isset($parts[2]) || (int) $parts[2] !== $id) {
                continue;
            }

            $position = $offset + (int) $index;
            $code = new InfinityCustomCode($id);
            if (Validate::isLoadedObject($code) && $code->updatePosition($way, $position)) {
                exit('ok position ' . $position . ' for code ' . $id);
            }

            exit('{"hasError" : true, "errors" : "Can not update code ' . $id . ' to position ' . $position . '"}');
        }

        exit('{"hasError" : true, "errors" : "Code ' . $id . ' not found in positions"}');
    }

    /**
     * Zastępuje HelperList::displayDuplicateLink(), który pyta o kopiowanie zdjęć i dokleja &noimage=1.
     * Sam AdminController nie obsługuje duplikowania, stąd initProcess() i processDuplicate().
     */
    public function displayDuplicateLink($token, $id, $name = null): string
    {
        $href = self::$currentIndex . '&' . $this->identifier . '=' . (int) $id
            . '&duplicate' . $this->table . '&token=' . ($token ?: $this->token);

        return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
            . '<i class="icon-copy"></i> ' . $this->escapeTranslated($this->trans('Duplikuj', [], 'Modules.Infinitycustomcodes.Admin'))
            . '</a>';
    }

    public function renderPlacementLabel($placement, array $row): string
    {
        $labels = $this->getPlacementLabels();

        return isset($labels[$placement]) ? $this->escapeTranslated($labels[$placement]) : $this->escape((string) $placement);
    }

    public function renderPagesLabel($pages, array $row): string
    {
        $labels = $this->getPageLabels();
        $names = array_map(
            fn (string $page): string => isset($labels[$page]) ? $this->escapeTranslated($labels[$page]) : $this->escape($page),
            array_filter(explode(',', (string) $pages))
        );

        return implode(', ', $names);
    }

    public function renderWidgetSnippet($widgetKey, array $row): string
    {
        // Pełna wersja.
        return '<span class="text-muted">' . $this->escapeTranslated($this->trans('pełna wersja', [], 'Modules.Infinitycustomcodes.Admin')) . '</span>';
    }

    public function renderOncePerOrder($value, array $row): string
    {
        return $value ? '<i class="icon-check text-success"></i>' : '<i class="icon-minus text-muted"></i>';
    }

    protected function _childValidation()
    {
        $placement = (string) Tools::getValue('placement');
        if (!InfinityCustomCode::isValidPlacement($placement)) {
            $this->errors[] = $this->trans('Nieznane miejsce.', [], 'Modules.Infinitycustomcodes.Admin');
        }

        $pages = (string) Tools::getValue('pages');
        if ($pages === '') {
            $this->errors[] = $this->trans('Zaznacz co najmniej jedną stronę albo „Wszystkie”.', [], 'Modules.Infinitycustomcodes.Admin');
        } elseif (!InfinityCustomCode::isValidPages($pages)) {
            $this->errors[] = $this->trans('Nieznany typ strony.', [], 'Modules.Infinitycustomcodes.Admin');
        }

        $widgetKey = (string) Tools::getValue('widget_key');
        if ($widgetKey !== '') {
            if (!InfinityCustomCode::isValidWidgetKey($widgetKey)) {
                $this->errors[] = $this->trans('Klucz widgetu może zawierać tylko małe litery, cyfry, „-” i „_” (do 64 znaków) i nie może składać się z samych cyfr.', [], 'Modules.Infinitycustomcodes.Admin');
            } elseif (InfinityCustomCode::widgetKeyExists($widgetKey, (int) Tools::getValue($this->identifier))) {
                $this->errors[] = $this->trans('Klucz widgetu „%key%” jest już używany przez inny kod.', ['%key%' => $widgetKey], 'Modules.Infinitycustomcodes.Admin');
            }
        }
    }

    /**
     * Ostrzeżenia przy zapisie: literówki w zmiennych, przestrzenie niedostępne na części stron,
     * param.* poza widgetem, „raz na zamówienie” bez dostępu do zamówienia.
     *
     * @return string[]
     */
    private function getSaveWarnings(string $code, string $placementValue, string $pages, bool $oncePerOrder): array
    {
        // Pełna wersja: literówki w zmiennych, przestrzenie niedostępne na części stron, param.* poza widgetem.
        if (!str_contains($code, '{{')) {
            return [];
        }

        return [$this->trans('Zmienne {{…}} działają w pełnej wersji modułu. W demie trafią na stronę dosłownie: %url%', ['%url%' => InfinityCustomCodesDemo::FULL_VERSION_URL], 'Modules.Infinitycustomcodes.Admin')];
    }

    /**
     * Ściągawka zmiennych pod polem kodu. Klik w zmienną wstawia ją w miejscu kursora (admin.js).
     */
    private function renderVariablesHelp(): string
    {
        // Każde wywołanie z literałem domeny: ekstraktor tłumaczeń PS czyta domenę tylko z literału (TranslationToolsBundle).
        $descriptions = [
            'shop' => [
                'id' => $this->trans('ID sklepu', [], 'Modules.Infinitycustomcodes.Admin'),
                'name' => $this->trans('nazwa sklepu', [], 'Modules.Infinitycustomcodes.Admin'),
                'url' => $this->trans('adres sklepu', [], 'Modules.Infinitycustomcodes.Admin'),
            ],
            'page' => [
                'type' => $this->trans('typ strony: index, category, product, cart, checkout, order-confirmation, cms, search, manufacturer, other', [], 'Modules.Infinitycustomcodes.Admin'),
                'language' => $this->trans('kod ISO języka', [], 'Modules.Infinitycustomcodes.Admin'),
                'currency' => $this->trans('kod ISO waluty', [], 'Modules.Infinitycustomcodes.Admin'),
            ],
            'customer' => [
                'logged' => $this->trans('1, gdy klient jest zalogowany, inaczej 0', [], 'Modules.Infinitycustomcodes.Admin'),
                'id' => $this->trans('ID klienta, 0 dla niezalogowanego', [], 'Modules.Infinitycustomcodes.Admin'),
            ],
            'product' => [
                'id' => $this->trans('ID produktu', [], 'Modules.Infinitycustomcodes.Admin'),
                'id_attribute' => $this->trans('ID kombinacji (0 bez kombinacji)', [], 'Modules.Infinitycustomcodes.Admin'),
                'reference' => $this->trans('indeks (kombinacji, jeśli ma własny)', [], 'Modules.Infinitycustomcodes.Admin'),
                'ean13' => $this->trans('EAN13 (kombinacji, jeśli ma własny)', [], 'Modules.Infinitycustomcodes.Admin'),
                'name' => $this->trans('nazwa', [], 'Modules.Infinitycustomcodes.Admin'),
                'price' => $this->trans('cena brutto po rabatach', [], 'Modules.Infinitycustomcodes.Admin'),
                'price_tax_excl' => $this->trans('cena netto po rabatach', [], 'Modules.Infinitycustomcodes.Admin'),
                'price_regular' => $this->trans('cena brutto bez rabatów', [], 'Modules.Infinitycustomcodes.Admin'),
                'category' => $this->trans('nazwa kategorii domyślnej', [], 'Modules.Infinitycustomcodes.Admin'),
                'manufacturer' => $this->trans('nazwa producenta', [], 'Modules.Infinitycustomcodes.Admin'),
                'currency' => $this->trans('kod ISO waluty', [], 'Modules.Infinitycustomcodes.Admin'),
            ],
            'category' => [
                'id' => $this->trans('ID kategorii', [], 'Modules.Infinitycustomcodes.Admin'),
                'name' => $this->trans('nazwa kategorii', [], 'Modules.Infinitycustomcodes.Admin'),
            ],
            'cart' => [
                'id' => $this->trans('ID koszyka', [], 'Modules.Infinitycustomcodes.Admin'),
                'total' => $this->trans('suma brutto', [], 'Modules.Infinitycustomcodes.Admin'),
                'total_tax_excl' => $this->trans('suma netto', [], 'Modules.Infinitycustomcodes.Admin'),
                'products_count' => $this->trans('liczba sztuk', [], 'Modules.Infinitycustomcodes.Admin'),
                'products' => $this->trans('tablica produktów: id, id_attribute, reference, ean13, name, quantity, price, price_tax_excl (tylko z |json)', [], 'Modules.Infinitycustomcodes.Admin'),
                'products_ids' => $this->trans('ID produktów po przecinku', [], 'Modules.Infinitycustomcodes.Admin'),
                'currency' => $this->trans('kod ISO waluty', [], 'Modules.Infinitycustomcodes.Admin'),
            ],
            'order' => [
                'id' => $this->trans('ID zamówienia (przy rozbiciu koszyka: pierwsze)', [], 'Modules.Infinitycustomcodes.Admin'),
                'ids' => $this->trans('wszystkie ID zamówień z koszyka po przecinku', [], 'Modules.Infinitycustomcodes.Admin'),
                'reference' => $this->trans('referencja zamówienia', [], 'Modules.Infinitycustomcodes.Admin'),
                'total_paid' => $this->trans('zapłacono brutto (suma zamówień z koszyka)', [], 'Modules.Infinitycustomcodes.Admin'),
                'total_paid_tax_excl' => $this->trans('zapłacono netto', [], 'Modules.Infinitycustomcodes.Admin'),
                'total_products' => $this->trans('produkty brutto', [], 'Modules.Infinitycustomcodes.Admin'),
                'total_products_tax_excl' => $this->trans('produkty netto', [], 'Modules.Infinitycustomcodes.Admin'),
                'total_shipping' => $this->trans('dostawa brutto', [], 'Modules.Infinitycustomcodes.Admin'),
                'total_shipping_tax_excl' => $this->trans('dostawa netto', [], 'Modules.Infinitycustomcodes.Admin'),
                'total_discounts' => $this->trans('rabaty brutto', [], 'Modules.Infinitycustomcodes.Admin'),
                'total_tax' => $this->trans('podatek (brutto minus netto)', [], 'Modules.Infinitycustomcodes.Admin'),
                'currency' => $this->trans('kod ISO waluty zamówienia', [], 'Modules.Infinitycustomcodes.Admin'),
                'payment' => $this->trans('nazwa płatności', [], 'Modules.Infinitycustomcodes.Admin'),
                'items_count' => $this->trans('liczba sztuk', [], 'Modules.Infinitycustomcodes.Admin'),
                'products' => $this->trans('tablica produktów: id, id_attribute, reference, ean13, name, quantity, price, price_tax_excl (tylko z |json)', [], 'Modules.Infinitycustomcodes.Admin'),
                'products_ids' => $this->trans('ID produktów po przecinku', [], 'Modules.Infinitycustomcodes.Admin'),
                'new_customer' => $this->trans('1, gdy to pierwsze zamówienie klienta', [], 'Modules.Infinitycustomcodes.Admin'),
                'email_sha256' => $this->trans('SHA-256 e-maila klienta (enhanced conversions). Dane osobowe: za zgodę odpowiada wpinający kod.', [], 'Modules.Infinitycustomcodes.Admin'),
                'customer_firstname' => $this->trans('imię klienta. Dane osobowe: za zgodę odpowiada wpinający kod.', [], 'Modules.Infinitycustomcodes.Admin'),
                'customer_email' => $this->trans('e-mail klienta (jawny). Dane osobowe: za zgodę odpowiada wpinający kod.', [], 'Modules.Infinitycustomcodes.Admin'),            ],
        ];

        $always = $this->trans('zawsze', [], 'Modules.Infinitycustomcodes.Admin');
        $sources = [
            'shop' => $always,
            'page' => $always,
            'customer' => $always,
            'product' => $this->trans('karta produktu; w widgecie parametr id_product (opcjonalnie id_product_attribute)', [], 'Modules.Infinitycustomcodes.Admin'),
            'category' => $this->trans('strona kategorii; w widgecie parametr id_category', [], 'Modules.Infinitycustomcodes.Admin'),
            'cart' => $this->trans('koszyk i checkout, gdy koszyk nie jest pusty; w widgecie na każdej stronie', [], 'Modules.Infinitycustomcodes.Admin'),
            'order' => $this->trans('potwierdzenie zamówienia (także head/body tej strony); w widgecie parametr id_order, tylko zamówienie zalogowanego klienta', [], 'Modules.Infinitycustomcodes.Admin'),
        ];

        $groups = [];
        foreach (InfinityCustomCodesVariables::NAMES as $namespace => $names) {
            $vars = [];
            foreach ($names as $name) {
                $vars[] = ['tag' => '{{' . $namespace . '.' . $name . '}}', 'description' => $descriptions[$namespace][$name] ?? ''];
            }
            $groups[] = ['namespace' => $namespace, 'source' => $sources[$namespace], 'vars' => $vars];
        }
        $groups[] = [
            'namespace' => 'param',
            'source' => $this->trans('tylko widget', [], 'Modules.Infinitycustomcodes.Admin'),
            'vars' => [[
                'tag' => '{{param.xxx}}',
                'description' => $this->trans('wartość parametru param_xxx=… z wywołania widgetu; bez parametru kod jest pomijany', [], 'Modules.Infinitycustomcodes.Admin'),
            ]],
        ];

        $modifiers = [
            ['tag' => '{{product.name}}', 'description' => $this->trans('domyślnie js: bezpieczne wewnątrz cudzysłowów w JS i w <script>; liczby wprost', [], 'Modules.Infinitycustomcodes.Admin')],
            ['tag' => '{{order.products|json}}', 'description' => $this->trans('json: pełny literał JSON (z cudzysłowami); jedyny sposób na tablice', [], 'Modules.Infinitycustomcodes.Admin')],
            ['tag' => '{{product.name|html}}', 'description' => $this->trans('html: do atrybutów i treści HTML, np. noscript img', [], 'Modules.Infinitycustomcodes.Admin')],
            ['tag' => '{{order.reference|url}}', 'description' => $this->trans('url: do parametrów w adresach pikseli', [], 'Modules.Infinitycustomcodes.Admin')],
            ['tag' => '{{product.id|raw}}', 'description' => $this->trans('raw: bez zmian, tylko dla wartości na pewno bezpiecznych', [], 'Modules.Infinitycustomcodes.Admin')],
        ];

        $notes = [
            $this->trans('Kod, który używa przestrzeni niedostępnej na danej stronie albo nieprzekazanego param.xxx, jest pomijany w całości, żeby nie psuć składni JS.', [], 'Modules.Infinitycustomcodes.Admin'),
            $this->trans('Kwoty: dwa miejsca po przecinku, kropka dziesiętna, bez waluty (waluta osobno jako kod ISO). Inne {{…}} niż powyższe przestrzenie zostają nietknięte.', [], 'Modules.Infinitycustomcodes.Admin'),
            $this->trans('product.* odpowiada kombinacji z pierwszego wczytania strony; zmiana kombinacji bez przeładowania nie aktualizuje zmiennych.', [], 'Modules.Infinitycustomcodes.Admin'),
            $this->trans('Widget działa tylko w plikach .tpl (nie w treściach CMS ani opisach). W szablonach modułów z własnym cache (np. ps_featuredproducts) wyrenderowany kod trafia do ich cache: nie używaj tam customer.*, cart.* ani „raz na zamówienie”. To samo dotyczy stron z full-page cache.', [], 'Modules.Infinitycustomcodes.Admin'),
        ];

        $tpl = $this->createTemplate('variables_help.tpl');
        $tpl->assign([
            'groups' => $groups,
            'modifiers' => $modifiers,
            'notes' => $notes,
            'texts' => [
                'title' => $this->trans('Zmienne', [], 'Modules.Infinitycustomcodes.Admin'),
                'hint' => $this->trans('Kliknij zmienną, aby wstawić ją w miejscu kursora.', [], 'Modules.Infinitycustomcodes.Admin'),
                'source' => $this->trans('Skąd dane', [], 'Modules.Infinitycustomcodes.Admin'),
                'modifiers' => $this->trans('Modyfikatory', [], 'Modules.Infinitycustomcodes.Admin'),
                'widget_example' => "{widget name='infinitycustomcodes' code='klucz' id_product=\$product.id_product param_list='Polecane'}",
            ],
        ]);

        return $tpl->fetch();
    }

    /**
     * Panel podglądu w formularzu: typ strony, ID encji, parametry widgetu.
     */
    private function renderPreviewPanel(): string
    {
        $pages = $this->getPageLabels();
        unset($pages[InfinityCustomCodesPageResolver::ALL]);

        $tpl = $this->createTemplate('preview.tpl');
        $tpl->assign([
            'pages' => $pages,
            'texts' => [
                'title' => $this->trans('Podgląd renderowania', [], 'Modules.Infinitycustomcodes.Admin'),
                'intro' => $this->trans('Renderuje bieżącą treść pola kodu (bez zapisu) dla wskazanej strony i encji.', [], 'Modules.Infinitycustomcodes.Admin'),
                'page' => $this->trans('Typ strony', [], 'Modules.Infinitycustomcodes.Admin'),
                'id_product' => $this->trans('ID produktu', [], 'Modules.Infinitycustomcodes.Admin'),
                'id_product_attribute' => $this->trans('ID kombinacji', [], 'Modules.Infinitycustomcodes.Admin'),
                'id_category' => $this->trans('ID kategorii', [], 'Modules.Infinitycustomcodes.Admin'),
                'id_order' => $this->trans('ID zamówienia', [], 'Modules.Infinitycustomcodes.Admin'),
                'params' => $this->trans('Parametry widgetu (nazwa=wartość, jeden w wierszu)', [], 'Modules.Infinitycustomcodes.Admin'),
                'run' => $this->trans('Podgląd', [], 'Modules.Infinitycustomcodes.Admin'),
                'note' => $this->trans('Ceny liczone w panelu mogą różnić się od frontu (grupa klienta, reguły cenowe). Podgląd nie ma koszyka (cart.*), nie sprawdza właściciela zamówienia i nie zapisuje „raz na zamówienie”.', [], 'Modules.Infinitycustomcodes.Admin'),
            ],
        ]);

        return $tpl->fetch();
    }

    private function renderPreviewResult(): string
    {
        // Pełna wersja.
        return '';
    }

    /**
     * Widok „Licencja”, wariant demo (docs licencji InfinityWeb.pl, „Wersja demo”): bez klucza i bez statusu.
     * Szablon dostaje gotowy HTML z licenseHtml().
     */
    private function renderLicense(): string
    {
        $email = InfinityCustomCodesDemo::CONTACT_EMAIL;
        $mailto = '<a href="mailto:' . $this->escape($email) . '"><strong>' . $this->escape($email) . '</strong></a>';

        $features = [
            $this->trans('kody na początku body i na stronie potwierdzenia zamówienia', [], 'Modules.Infinitycustomcodes.Admin'),
            $this->trans('{widget} w dowolnym szablonie .tpl', [], 'Modules.Infinitycustomcodes.Admin'),
            $this->trans('wybór typów stron (główna, kategoria, produkt, koszyk, checkout, CMS…)', [], 'Modules.Infinitycustomcodes.Admin'),
            $this->trans('zmienne produktu, koszyka i zamówienia ({{product.price}}, {{order.total_paid}}…)', [], 'Modules.Infinitycustomcodes.Admin'),
            $this->trans('„raz na zamówienie” dla kodów konwersji', [], 'Modules.Infinitycustomcodes.Admin'),
            $this->trans('podgląd renderowania i tryb debug', [], 'Modules.Infinitycustomcodes.Admin'),
        ];

        $tpl = $this->createTemplate('license.tpl');
        $tpl->assign([
            'back_url' => self::$currentIndex . '&token=' . $this->token,
            'github_url' => InfinityCustomCodesDemo::GITHUB_URL,
            'site_url' => InfinityCustomCodesDemo::SITE_URL,
            'features' => array_map(fn (string $feature): string => $this->escapeTranslated($feature), $features),
            'html' => [
                'header' => $this->licenseHtml(
                    $this->trans('%name% · wersja %version% · Wersja demo · autor InfinityWeb.pl', [], 'Modules.Infinitycustomcodes.Admin'),
                    ['%name%' => (string) $this->module->displayName, '%version%' => (string) $this->module->version]
                ),
                'demo' => '<strong>' . $this->trans('To jest wersja demo.', [], 'Modules.Infinitycustomcodes.Admin') . '</strong> '
                    . $this->trans('Działa w niej część funkcji modułu. Pozostałe widać w panelu i w kodzie, ale działają tylko w wersji pełnej.', [], 'Modules.Infinitycustomcodes.Admin'),
                'full' => $this->trans('Wersja pełna:', [], 'Modules.Infinitycustomcodes.Admin'),
                'contact' => $this->licenseHtml(
                    $this->trans('Chcesz wersję pełną? Napisz na %email% albo zajrzyj na %url%', [], 'Modules.Infinitycustomcodes.Admin'),
                    [],
                    ['%email%' => $mailto, '%url%' => $this->externalLink(InfinityCustomCodesDemo::FULL_VERSION_URL)]
                ),
                'back' => $this->trans('Wróć do listy', [], 'Modules.Infinitycustomcodes.Admin'),
                'more_title' => $this->trans('Więcej modułów PrestaShop od InfinityWeb.pl', [], 'Modules.Infinitycustomcodes.Admin'),
                'more_github' => $this->licenseHtml(
                    $this->trans('Dema innych modułów znajdziesz na GitHubie: %url%', [], 'Modules.Infinitycustomcodes.Admin'),
                    [],
                    ['%url%' => $this->externalLink(InfinityCustomCodesDemo::GITHUB_URL)]
                ),
                'more_site' => $this->licenseHtml(
                    $this->trans('Opisy, przykłady użycia i kontakt: %url%', [], 'Modules.Infinitycustomcodes.Admin'),
                    [],
                    ['%url%' => $this->externalLink(InfinityCustomCodesDemo::SITE_URL)]
                ),
                'more_github_button' => $this->trans('Moduły na GitHubie', [], 'Modules.Infinitycustomcodes.Admin'),
                'footer' => $this->trans('Wersja demo nie sprawdza licencji i nie wysyła żadnych danych do autora.', [], 'Modules.Infinitycustomcodes.Admin'),
            ],
        ]);

        return $tpl->fetch();
    }

    /**
     * Wynik trans() z kontrolera jest już po htmlspecialchars(ENT_NOQUOTES) (Controller::trans() w 8.1),
     * więc tu nie escapujemy go drugi raz. Znaczniki %…% podmieniamy na escapowane wartości ($values)
     * albo gotowe fragmenty HTML ($html). Tłumaczenia nie zawierają HTML.
     *
     * @param array<string, string> $values
     * @param array<string, string> $html
     */
    private function licenseHtml(string $translated, array $values = [], array $html = []): string
    {
        return strtr($translated, array_map(fn (string $value): string => $this->escape($value), $values) + $html);
    }

    private function externalLink(string $url): string
    {
        return '<a href="' . $this->escape($url) . '" target="_blank" rel="noopener">' . $this->escape($url) . '</a>';
    }

    private function getLicenseUrl(): string
    {
        return self::$currentIndex . '&' . self::LICENSE_PARAM . '=1&token=' . $this->token;
    }

    private function describePreviewProblem(string $problem): string
    {
        // Pełna wersja.
        return '';
    }

    /**
     * Wiersze `nazwa=wartość` (z prefiksem param_ albo bez) → param.nazwa.
     *
     * @return array<string, string>
     */
    private function parsePreviewParams(string $text): array
    {
        // Pełna wersja.
        return [];
    }

    private static function displayValue(mixed $value): string
    {
        // Pełna wersja.
        return '';
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escapowanie wyniku $this->trans(), który Controller::trans() zwraca już po htmlspecialchars(ENT_NOQUOTES).
     * Bez ponownego kodowania encji (`&lt;` zostaje `&lt;`), ale z cudzysłowami, więc nadaje się też do atrybutów.
     * W szablonach odpowiednik to `|escape:'html':'UTF-8':false`.
     */
    private function escapeTranslated(string $translated): string
    {
        return htmlspecialchars($translated, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Wynik $this->trans() jako zwykły tekst, gdy trafia jako parametr do kolejnego trans(), który escapuje całość.
     */
    private static function plain(string $translated): string
    {
        return htmlspecialchars_decode($translated, ENT_NOQUOTES);
    }

    /**
     * `all`, lista zaznaczonych typów stron albo '' (nic nie zaznaczono).
     */
    private function getPagesFromPost(): string
    {
        // Wybór stron: pełna wersja. Nieaktywne checkboxy nie są wysyłane, więc zawsze „Wszystkie”.
        return InfinityCustomCodesPageResolver::ALL;
    }

    private function getWidgetKeyHelp(InfinityCustomCode $code): string
    {
        $snippet = $code->id
            ? self::widgetSnippet((int) $code->id, $code->widget_key)
            : self::widgetSnippet(0, 'klucz');
        $example = "{widget name='infinitycustomcodes' code='klucz' id_product=\$product.id_product param_list='Polecane'}";

        return $this->escapeTranslated($this->trans('Opcjonalny: małe litery, cyfry, „-” i „_”, nie same cyfry. Wstawienie w szablonie .tpl:', [], 'Modules.Infinitycustomcodes.Admin'))
            . '<br><code class="icc-snippet">' . htmlspecialchars($snippet, ENT_QUOTES, 'UTF-8') . '</code>'
            . '<br>' . $this->escapeTranslated($this->trans('Z parametrami (nadpisują kontekst strony):', [], 'Modules.Infinitycustomcodes.Admin'))
            . '<br><code class="icc-snippet">' . htmlspecialchars($example, ENT_QUOTES, 'UTF-8') . '</code>';
    }

    private static function widgetSnippet(int $id, ?string $widgetKey): string
    {
        $code = $widgetKey !== null && $widgetKey !== '' ? "'" . $widgetKey . "'" : (string) $id;

        return "{widget name='infinitycustomcodes' code=" . $code . '}';
    }

    /**
     * @return array<string, string> wartość placement => etykieta
     */
    private function getPlacementLabels(): array
    {
        return [
            InfinityCustomCodesPlacement::Head->value => $this->trans('Nagłówek strony (head)', [], 'Modules.Infinitycustomcodes.Admin'),
            InfinityCustomCodesPlacement::BodyTop->value => $this->trans('Początek body', [], 'Modules.Infinitycustomcodes.Admin'),
            InfinityCustomCodesPlacement::BodyBottom->value => $this->trans('Koniec body (footer)', [], 'Modules.Infinitycustomcodes.Admin'),
            InfinityCustomCodesPlacement::OrderConfirmation->value => $this->trans('Potwierdzenie zamówienia', [], 'Modules.Infinitycustomcodes.Admin'),
            InfinityCustomCodesPlacement::Widget->value => $this->trans('Tylko widget w szablonie', [], 'Modules.Infinitycustomcodes.Admin'),
        ];
    }

    /**
     * @return array<string, string> wartość pages => etykieta, z `all` na początku
     */
    private function getPageLabels(): array
    {
        return [
            InfinityCustomCodesPageResolver::ALL => $this->trans('Wszystkie', [], 'Modules.Infinitycustomcodes.Admin'),
            'index' => $this->trans('Strona główna', [], 'Modules.Infinitycustomcodes.Admin'),
            'category' => $this->trans('Kategoria', [], 'Modules.Infinitycustomcodes.Admin'),
            'product' => $this->trans('Produkt', [], 'Modules.Infinitycustomcodes.Admin'),
            'cart' => $this->trans('Koszyk', [], 'Modules.Infinitycustomcodes.Admin'),
            'checkout' => $this->trans('Checkout (zamówienie)', [], 'Modules.Infinitycustomcodes.Admin'),
            'order-confirmation' => $this->trans('Potwierdzenie zamówienia', [], 'Modules.Infinitycustomcodes.Admin'),
            'cms' => $this->trans('CMS', [], 'Modules.Infinitycustomcodes.Admin'),
            'search' => $this->trans('Wyszukiwarka', [], 'Modules.Infinitycustomcodes.Admin'),
            'manufacturer' => $this->trans('Producent', [], 'Modules.Infinitycustomcodes.Admin'),
            'other' => $this->trans('Inne', [], 'Modules.Infinitycustomcodes.Admin'),
        ];
    }
}
