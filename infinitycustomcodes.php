<?php
/**
 * Własne kody (JS/HTML) wstrzykiwane w hooki strony albo przez {widget}.
 *
 * Klasa modułu tylko deleguje: instalacja → InfinityCustomCodesInstaller, front → InfinityCustomCodesFront.
 *
 * Wersja demo: kody w nagłówku (head) i na końcu body (footer), na wszystkich stronach.
 * Pełna wersja: InfinityCustomCodesDemo::FULL_VERSION_URL.
 */

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/InfinityCustomCodesPlacement.php';
require_once __DIR__ . '/classes/InfinityCustomCodesDemo.php';
require_once __DIR__ . '/classes/InfinityCustomCodesPageResolver.php';
require_once __DIR__ . '/classes/InfinityCustomCode.php';
require_once __DIR__ . '/classes/InfinityCustomCodesConfig.php';
require_once __DIR__ . '/classes/InfinityCustomCodesInstaller.php';
require_once __DIR__ . '/classes/InfinityCustomCodesRepository.php';
require_once __DIR__ . '/classes/InfinityCustomCodesVariables.php';
require_once __DIR__ . '/classes/InfinityCustomCodesRenderer.php';
require_once __DIR__ . '/classes/InfinityCustomCodesFiredLog.php';
require_once __DIR__ . '/classes/InfinityCustomCodesFront.php';

class InfinityCustomCodes extends Module implements WidgetInterface
{
    public const ADMIN_CONTROLLER = 'AdminInfinityCustomCodes';

    private ?InfinityCustomCodesFront $front = null;

    public function __construct()
    {
        $this->name = 'infinitycustomcodes';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'InfinityWeb.pl';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '8.1.0', 'max' => _PS_VERSION_];

        // Ukryty tab: wejście tylko przez „Konfiguruj”, ale ModuleAdminController potrzebuje tabu
        // do routingu, tokenu i uprawnień. PrestaShop instaluje i usuwa go razem z modułem.
        $this->tabs = [
            [
                'class_name' => self::ADMIN_CONTROLLER,
                'parent_class_name' => 'AdminParentModulesSf',
                'name' => 'Własne kody',
                'visible' => false,
            ],
        ];

        parent::__construct();

        $this->displayName = $this->trans('Własne kody na stronie (demo)', [], 'Modules.Infinitycustomcodes.Admin');
        $this->description = $this->trans(
            'Wersja demo: własne kody JS/HTML w nagłówku i stopce strony. Pełna wersja: body, potwierdzenie zamówienia, {widget}, wybór stron i zmienne produktu oraz zamówienia.',
            [],
            'Modules.Infinitycustomcodes.Admin'
        );
        $this->confirmUninstall = $this->trans(
            'Odinstalowanie usunie wszystkie zapisane kody. Najpierw usuń {widget name=\'infinitycustomcodes\'} z szablonów motywu. Kontynuować?',
            [],
            'Modules.Infinitycustomcodes.Admin'
        );
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install(): bool
    {
        return parent::install() && (new InfinityCustomCodesInstaller($this))->install();
    }

    public function uninstall(): bool
    {
        return parent::uninstall() && (new InfinityCustomCodesInstaller($this))->uninstall();
    }

    public function getContent(): string
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(self::ADMIN_CONTROLLER));

        return '';
    }

    public function hookDisplayHeader(array $params): string
    {
        return $this->front()->renderPlacement(InfinityCustomCodesPlacement::Head, $params);
    }

    public function hookDisplayAfterBodyOpeningTag(array $params): string
    {
        // Pełna wersja.
        return '';
    }

    public function hookDisplayBeforeBodyClosingTag(array $params): string
    {
        return $this->front()->renderPlacement(InfinityCustomCodesPlacement::BodyBottom, $params);
    }

    public function hookDisplayOrderConfirmation(array $params): string
    {
        // Pełna wersja.
        return '';
    }

    /**
     * $hookName jest bez typu, bo WidgetInterface go nie ma.
     */
    public function renderWidget($hookName, array $configuration): string
    {
        // Pełna wersja.
        return '';
    }

    public function getWidgetVariables($hookName, array $configuration): array
    {
        return [];
    }

    private function front(): InfinityCustomCodesFront
    {
        return $this->front ??= new InfinityCustomCodesFront($this, $this->context);
    }
}
