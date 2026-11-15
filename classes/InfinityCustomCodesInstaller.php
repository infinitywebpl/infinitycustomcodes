<?php
/**
 * Instalacja i deinstalacja: tabele, hooki, konfiguracja. Tab instaluje PrestaShop z Module::$tabs.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

final class InfinityCustomCodesInstaller
{
    public function __construct(
        private readonly Module $module,
    ) {
    }

    public function install(): bool
    {
        return $this->executeSqlFile('install.sql')
            && $this->module->registerHook(InfinityCustomCodesPlacement::hookNames())
            && $this->installConfiguration();
    }

    /**
     * Hooki odpina Module::uninstall(), tu zostają tabele i konfiguracja.
     */
    public function uninstall(): bool
    {
        return $this->executeSqlFile('uninstall.sql')
            && $this->uninstallConfiguration();
    }

    private function installConfiguration(): bool
    {
        foreach (InfinityCustomCodesConfig::DEFAULTS as $key => $value) {
            if (!Configuration::updateValue($key, $value)) {
                return false;
            }
        }

        return true;
    }

    private function uninstallConfiguration(): bool
    {
        foreach (array_keys(InfinityCustomCodesConfig::DEFAULTS) as $key) {
            if (!Configuration::deleteByName($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Wykonuje plik z sql/, podmieniając PREFIX_ i ENGINE_TYPE. Instrukcje rozdzielone średnikiem na końcu linii.
     */
    private function executeSqlFile(string $file): bool
    {
        $sql = Tools::file_get_contents(dirname(__DIR__) . '/sql/' . $file);
        if ($sql === false) {
            return false;
        }

        $sql = str_replace(['PREFIX_', 'ENGINE_TYPE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);
        $db = Db::getInstance();

        foreach (preg_split('/;\s*[\r\n]+/', $sql) as $query) {
            $query = trim($query);
            if ($query !== '' && !$db->execute($query)) {
                return false;
            }
        }

        return true;
    }
}
