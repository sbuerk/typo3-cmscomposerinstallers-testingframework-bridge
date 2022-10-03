<?php

declare(strict_types=1);

/*
 * This file is part of the "sbuerk/typo3-cmscomposerinstallers-testingframework-bridge" composer plugin.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace SBUERK\TYPO3CmsComposerInstallersTestingFrameworkBridge\Installer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use SBUERK\TYPO3CmsComposerInstallersTestingFrameworkBridge\Services\PluginService;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // noop
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // noop
    }

    /**
     * @return array<string, string|array{0: string, 1?: int}|array<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_AUTOLOAD_DUMP => ['postAutoloadDump', -1],
        ];
    }

    public function postAutoloadDump(Event $event): void
    {
        (new PluginService($event->getComposer(), $event->getIO()))
            ->handle();
    }
}
