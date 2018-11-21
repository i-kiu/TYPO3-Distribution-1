<?php declare(strict_types = 1);

namespace JosefGlatz\Theme\Signals\Backend;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\EditDocumentController;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Enrich/Customize EditDocumentController via SignalSlot usage
 */
class EditDocumentControllerInitSlot
{
    /**
     * preInitAfter SignalSlot
     *   - restrict creating sys_template records in database
     *
     * @TODO: Use the ServerRequestInterface to interception
     *
     * @param EditDocumentController $editDocumentController
     */
    public function adjustEditDocumentController(EditDocumentController $editDocumentController, ServerRequestInterface $request): void
    {
        $editconf = $editDocumentController->editconf;

        if (isset($editconf['sys_template'])) {
            if (reset(reset($editconf)) === 'new') {
                /**
                 * @var FlashMessage $message Error message to inform the backend user about the barrier
                 */
                $message = GeneralUtility::makeInstance(FlashMessage::class,
                    htmlspecialchars($this->getLanguageService()
                        ->sL('LLL:EXT:theme/Resources/Private/Language/locallang_BackendGeneral.xlf:hooks.dataHandler.prevent.sys_template.description')),
                    htmlspecialchars($this->getLanguageService()
                        ->sL('LLL:EXT:theme/Resources/Private/Language/locallang_BackendGeneral.xlf:hooks.dataHandler.prevent.sys_template.title')),
                    FlashMessage::ERROR,
                    true
                );
                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                $messageQueue->addMessage($message);

                // Return to previous url without saving anything
                $editDocumentController->closeDocument();
            }
        }
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
