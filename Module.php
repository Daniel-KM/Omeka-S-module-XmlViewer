<?php declare(strict_types=1);
/**
 * @author Daniel Berthereau
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 * @copyright Daniel Berthereau, 2018-2023
 *
 * Copyright 2018-2023 Daniel Berthereau
 *
 * This software is governed by the CeCILL license under French law and abiding
 * by the rules of distribution of free software. You can use, modify and/or
 * redistribute the software under the terms of the CeCILL license as circulated
 * by CEA, CNRS and INRIA at the following URL "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and rights to copy, modify
 * and redistribute granted by the license, users are provided only with a
 * limited warranty and the software’s author, the holder of the economic
 * rights, and the successive licensors have only limited liability.
 *
 * In this respect, the user’s attention is drawn to the risks associated with
 * loading, using, modifying and/or developing or reproducing the software by
 * the user in light of its specific status of free software, that may mean that
 * it is complicated to manipulate, and that also therefore means that it is
 * reserved for developers and experienced professionals having in-depth
 * computer knowledge. Users are therefore encouraged to load and test the
 * software’s suitability as regards their requirements in conditions enabling
 * the security of their systems and/or data to be ensured and, more generally,
 * to use and operate it in the same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 */
namespace XmlViewer;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Omeka\Module\Exception\ModuleCannotInstallException;
use Omeka\Stdlib\Message;

class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    public function onBootstrap(MvcEvent $event): void
    {
        parent::onBootstrap($event);
        $this->addAclRules();
    }

    protected function preInstall(): void
    {
        $services = $this->getServiceLocator();
        $t = $services->get('MvcTranslator');

        // Check if xml and xslt reader are available.
        if (!class_exists('XSLTProcessor')) {
            throw new ModuleCannotInstallException(
                $t->translate('The module requires the php extension "xsl".') // @translate
                    . ' ' . $t->translate('See module’s installation documentation.') // @translate
            );
        }
    }

    protected function postInstall(): void
    {
        $this->updateWhitelist();

        $messenger = $this->getServiceLocator()->get('ControllerPluginManager')->get('messenger');
        $message = new Message(
            'To render xml, map each specific xml media-type with a css or xsl in settings and site settings.' // @translate
        );
        $messenger->addSuccess($message);

        if ($this->isModuleActive('BulkEdit')) {
            $message = new Message(
                'To specify a precise xml media-type, for example "application/tei+xml" instead of "application/xml", batch edit them.' // @translate
            );
        } else {
            $message = new Message(
                'To specify a precise xml media-type, for example "application/tei+xml" instead of "application/xml", batch edit them with module %sBulk Edit%s.', // @translate
                '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-BulkEdit">',
                '</a>'
            );
            $message->setEscapeHtml(false);
        }
        $messenger->addWarning($message);
    }

    protected function addAclRules(): void
    {
        /*
         * @var \Omeka\Permissions\Acl $acl
         * @see \Omeka\Service\AclFactory
         */
        $this->getServiceLocator()->get('Omeka\Acl')
            // Everybody can view a xml.
            ->allow(
                null,
                [
                    Controller\IndexController::class,
                ]
            );
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_elements',
            [$this, 'handleMainSettings']
        );
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'handleSiteSettings']
        );
    }

    protected function updateWhitelist(): void
    {
        $settings = $this->getServiceLocator()->get('Omeka\Settings');

        $whitelist = $settings->get('media_type_whitelist', []);
        $mediaTypes = require __DIR__ . '/data/media-types/media-type-identifiers.php';
        // Manage an exception.
        $mediaTypes[] = 'application/vnd.recordare.musicxml';
        sort($mediaTypes);
        $whitelist = array_values(array_unique(array_merge(array_values($whitelist), $mediaTypes)));
        $settings->set('media_type_whitelist', $whitelist);

        $whitelist = $settings->get('extension_whitelist', []);
        $extensions = require __DIR__ . '/data/media-types/media-type-extensions.php';
        sort($extensions);
        $whitelist = array_values(array_unique(array_merge(array_values($whitelist), $extensions)));
        $settings->set('extension_whitelist', $whitelist);
    }
}
