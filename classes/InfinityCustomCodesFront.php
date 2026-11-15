<?php
/**
 * Renderowanie kodów na froncie: wspólne wejście dla hooków i {widget}.
 *
 * Wersja demo: kody z miejsc InfinityCustomCodesDemo::PLACEMENTS trafiają na każdą stronę bez zmian.
 * Wybór stron, zmienne, widget, „raz na zamówienie” i tryb debug są w pełnej wersji modułu.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

final class InfinityCustomCodesFront
{
    private ?string $pageType = null;

    private ?InfinityCustomCodesVariables $contextVariables = null;

    private ?InfinityCustomCodesRenderer $renderer = null;

    public function __construct(
        private readonly Module $module,
        private readonly Context $context,
    ) {
    }

    /**
     * Wszystkie aktywne kody danego miejsca. Błąd jednego kodu nie blokuje pozostałych ani strony:
     * zwracamy to, co udało się wyrenderować.
     */
    public function renderPlacement(InfinityCustomCodesPlacement $placement, array $params): string
    {
        if (!InfinityCustomCodesDemo::isAvailable($placement)) {
            return '';
        }

        try {
            $codes = InfinityCustomCodesRepository::findByPlacement($placement);
        } catch (\Throwable $e) {
            $this->logError($e);

            return '';
        }

        $output = '';
        foreach ($codes as $code) {
            try {
                $output .= $this->renderCode($code);
            } catch (\Throwable $e) {
                $this->logError($e, (int) $code['id_infinitycustomcodes_code']);
            }
        }

        return $output;
    }

    /**
     * Jeden kod wskazany parametrem `code` w {widget name='infinitycustomcodes' …}.
     */
    public function renderWidget(?string $hookName, array $configuration): string
    {
        // Pełna wersja.
        return '';
    }

    /**
     * Wspólny render jednego kodu dla hooków i widgetu: filtr stron, zmienne, „raz na zamówienie”, tryb debug.
     *
     * @param array<string, mixed> $code wiersz z InfinityCustomCodesRepository
     * @param InfinityCustomCodesVariables|null $variables null = zmienne z kontekstu strony (hooki), budowane leniwie
     * @param array<string, string> $problems przestrzeń => powód niedostępności z parametrów widgetu
     */
    private function renderCode(array $code, ?InfinityCustomCodesVariables $variables = null, array $problems = []): string
    {
        // W demie kod bez zmian. Filtr stron, zmienne i „raz na zamówienie”: pełna wersja.
        return (string) $code['code'];
    }

    private function describeProblem(string $problem): string
    {
        // Pełna wersja.
        return '';
    }

    private function getContextVariables(): InfinityCustomCodesVariables
    {
        // Pełna wersja.
        return InfinityCustomCodesVariables::empty();
    }

    private function getRenderer(): InfinityCustomCodesRenderer
    {
        return $this->renderer ??= new InfinityCustomCodesRenderer();
    }

    /**
     * Komentarz z powodem pominięcia, tylko w trybie debug.
     *
     * @param array<string, mixed> $code
     */
    private function debugSkipped(array $code, string $reason): string
    {
        // Pełna wersja.
        return '';
    }

    /**
     * Komentarz diagnostyczny niezwiązany z konkretnym kodem (np. widget z nieznanym `code`), tylko w trybie debug.
     */
    private function debugComment(string $message): string
    {
        // Pełna wersja.
        return '';
    }

    private function getPageType(): string
    {
        // Pełna wersja.
        return $this->pageType ??= InfinityCustomCodesPageResolver::OTHER;
    }

    private function logError(\Throwable $e, ?int $codeId = null): void
    {
        try {
            PrestaShopLogger::addLog(
                'infinitycustomcodes: ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')',
                3,
                null,
                'InfinityCustomCode',
                $codeId
            );
        } catch (\Throwable) {
            // Log niedostępny (np. błąd bazy): strona sklepu ma się wyrenderować mimo to.
        }
    }

    /**
     * Module::trans() jest chroniona. Wywołania podają domenę literałem, bo ekstraktor tłumaczeń PS
     * czyta ją tylko z literału w trzecim argumencie trans().
     */
    private function translator(): \Symfony\Contracts\Translation\TranslatorInterface
    {
        return $this->module->getTranslator();
    }

    /**
     * Tekst bezpieczny wewnątrz komentarza HTML: bez `--` (które mogłoby go zamknąć) i bez nowych linii.
     */
    private static function commentSafe(mixed $text): string
    {
        return str_replace(['--', "\r", "\n"], ['- -', ' ', ' '], (string) $text);
    }
}
