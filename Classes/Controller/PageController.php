<?php
namespace FluidTYPO3\Fluidpages\Controller;

/*
 * This file is part of the FluidTYPO3/Fluidpages project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Fluidpages\Service\ConfigurationService;
use FluidTYPO3\Fluidpages\Service\PageService;
use FluidTYPO3\Flux\Controller\AbstractFluxController;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Response;

/**
 * Page Controller
 *
 * @route off
 */
class PageController extends AbstractFluxController implements PageControllerInterface
{

    /**
     * @var string
     */
    protected $fluxRecordField = 'tx_fed_page_flexform';

    /**
     * @var string
     */
    protected $fluxTableName = 'pages';

    /**
     * @var PageService
     */
    protected $pageService;

    /**
     * @var ConfigurationService
     */
    protected $pageConfigurationService;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param PageService $pageService
     */
    public function injectPageService(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    /**
     * @param ConfigurationService $pageConfigurationService
     * @return void
     */
    public function injectPageConfigurationService(ConfigurationService $pageConfigurationService)
    {
        $this->pageConfigurationService = $pageConfigurationService;
    }

    /**
     * @throws \RuntimeException
     * @return void
     */
    protected function initializeProvider()
    {
        $this->provider = $this->pageConfigurationService->resolvePageProvider($this->getRecord());
    }

    /**
     * @return void
     */
    public function initializeViewVariables() {

        $config = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if (\is_array($config['variables'])) {
            $this->view->assignMultiple($config['variables']);
        }
        parent::initializeViewVariables();
    }

    /**
     * @return string
     */
    public function rawAction()
    {
        $record = $this->getRecord();
        $templateFileReference = $record['tx_fluidpages_templatefile'];
        $templatePathAndFilename = $this->pageConfigurationService->convertFileReferenceToTemplatePathAndFilename(
            $templateFileReference
        );
        $paths = $this->pageConfigurationService->getViewConfigurationByFileReference($templateFileReference);
        $this->provider->setTemplatePathAndFilename($templatePathAndFilename);
        $this->view->setTemplatePathAndFilename($templatePathAndFilename);
        $this->view->setTemplateRootPaths((array) $paths['templateRootPaths']);
        $this->view->setPartialRootPaths((array) $paths['partialRootPaths']);
        $this->view->setLayoutRootPaths((array) $paths['layoutRootPaths']);
    }

    /**
     * @return array|null
     */
    public function getRecord()
    {
        return $GLOBALS['TSFE']->page ?? null;
    }
}
