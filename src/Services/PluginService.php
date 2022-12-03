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

namespace SBUERK\TYPO3CmsComposerInstallersTestingFrameworkBridge\Services;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;

final class PluginService
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->filesystem = new Filesystem(new ProcessExecutor($io));
    }

    public function handle(): void
    {
        if (!$this->isProcessable()) {
            $this->io->info('>> TYPO3 CMS ComposerInstallers TestingFramework Bridge - nothing to do');
            return;
        }

        // core monorepo - should never happen, but better be safe than sorry.
        $rootPackage = $this->rootPackage();
        if ($this->packageIsOfType($rootPackage, 'typo3-cms-core')) {
            return;
        }

        if ($this->packageIsExtension($rootPackage)) {
            $this->handleRootTypo3Extension();
            return;
        }

        if ($this->packageIsOfType($rootPackage, 'project')) {
            $this->handleRootProject();
            return;
        }
    }

    private function handleRootTypo3Extension(): void
    {
        $this->linkRootExtensionToVendorFolder();
        $this->buildLegacySysExtMirror();
        $this->buildLegacyExtMirror();
        $this->buildRootExtesionLegacyExtMirror();
    }

    private function handleRootProject(): void
    {
        $this->buildLegacySysExtMirror();
        $this->buildLegacyExtMirror();
    }

    private function buildLegacySysExtMirror(): void
    {
        $publicPath = $this->filesystem->normalizePath($this->getPublicPath());
        $legacySysExtPath = $this->filesystem->normalizePath($publicPath . '/typo3/sysext');
        $this->filesystem->emptyDirectory($legacySysExtPath, true);
        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getPackages();
        $processed = [];
        foreach ($packages as $package) {
            if (!$this->packageIsCoreExtension($package)) {
                continue;
            }
            $packageName = $package->getName();
            if (in_array($packageName, $processed, true)) {
                continue;
            }
            $extensionKey = $this->getPackageExtensionKey($package);
            $installationSource = $this->composer->getInstallationManager()->getInstallPath($package);
            $legacyPackagePath = $legacySysExtPath . '/' . $extensionKey;
            $relativeVendor = $this->filesystem->findShortestPath($this->rootPath(), $installationSource);
            $relativeSysext = $this->filesystem->findShortestPath($this->rootPath(), $legacyPackagePath);
            $linked = $this->filesystem->relativeSymlink($installationSource, $legacyPackagePath);
            if ($linked) {
                $this->io->info(sprintf('>> Linked system extension "%s" from "%s" to "%s"', $package->getName(), $relativeVendor, $relativeSysext));
            } else {
                $this->io->error(sprintf('>> Failed to link system extension "%s" from "%s" to "%s"', $package->getName(), $relativeVendor, $relativeSysext));
            }
            $processed[] = $packageName;
        }
    }

    private function buildLegacyExtMirror(): void
    {
        $publicPath = $this->filesystem->normalizePath($this->getPublicPath());
        $legacyExtPath = $this->filesystem->normalizePath($publicPath . '/typo3conf/ext');
        $this->filesystem->emptyDirectory($legacyExtPath, true);
        $rootPackage = $this->rootPackage();
        $rootPackageIsExtension = $this->packageIsExtension($rootPackage);
        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getPackages();
        $processed = [];
        foreach ($packages as $package) {
            $packageName = $package->getName();
            if (in_array($packageName, $processed, true)
                || !$this->packageIsExtension($package)
                || $this->packageIsCoreExtension($package)
                || ($rootPackageIsExtension && $rootPackage->getName() === $package->getName())
            ) {
                continue;
            }
            $extensionKey = $this->getPackageExtensionKey($package);
            $installationSource = $this->composer->getInstallationManager()->getInstallPath($package);
            $legacyPackagePath = $legacyExtPath . '/' . $extensionKey;
            $relativeVendor = $this->filesystem->findShortestPath($this->rootPath(), $installationSource);
            $relativeExt = $this->filesystem->findShortestPath($this->rootPath(), $legacyPackagePath);

            $linked = $this->filesystem->relativeSymlink($installationSource, $legacyPackagePath);
            if ($linked) {
                $this->io->info(sprintf('>> Linked extension "%s" from "%s" to "%s"', $package->getName(), $relativeVendor, $relativeExt));
            } else {
                $this->io->error(sprintf('>> Failed to link extension "%s" from "%s" to "%s"', $package->getName(), $relativeVendor, $relativeExt));
            }
            $processed[] = $packageName;
        }
    }

    private function buildRootExtesionLegacyExtMirror(): void
    {
        $publicPath = $this->filesystem->normalizePath($this->getPublicPath());
        $legacyExtPath = $this->filesystem->normalizePath($publicPath . '/typo3conf/ext');
        $this->filesystem->emptyDirectory($legacyExtPath, true);
        $package = $this->rootPackage();
        $packageName = $package->getName();
        $extensionKey = $this->getPackageExtensionKey($package);
        $installationSource = $this->filesystem->normalizePath($this->extractBaseDir($this->composer->getConfig()));
        $legacyPackagePath = $legacyExtPath . '/' . $extensionKey;
        $relativeLegacy = $this->filesystem->findShortestPath($this->rootPath(), $legacyPackagePath);
        $linked = $this->filesystem->relativeSymlink($installationSource, $legacyPackagePath);
        if ($linked) {
            $this->io->info(sprintf('>> Linked root extension "%s" to "%s"', $package->getName(), $relativeLegacy));
        } else {
            $this->io->error(sprintf('>> Failed to link root extension "%s" "%s"', $package->getName(), $relativeLegacy));
        }
    }

    private function getPublicPath(): string
    {
        $typo3WebPath = getenv('TYPO3_PATH_WEB');
        if (!is_string($typo3WebPath) || $typo3WebPath === '' || !is_dir($typo3WebPath)) {
            throw new \RuntimeException('Public path could not be determined.', 1664805208);
        }
        return $typo3WebPath;
    }

    private function rootPackage(): RootPackageInterface
    {
        return $this->composer->getPackage();
    }

    private function packageIsCoreExtension(PackageInterface $package): bool
    {
        return $this->packageIsOfType($package, 'typo3-cms-framework');
    }

    private function packageIsExtension(PackageInterface $package): bool
    {
        return $this->packageIsOfType($package, 'typo3-cms-extension');
    }

    private function packageIsOfType(PackageInterface $package, string $type): bool
    {
        return $package->getType() === $type;
    }

    protected function linkRootExtensionToVendorFolder(): void
    {
        $package = $this->rootPackage();
        $packageName = $package->getName();
        $extensionKey = $this->getPackageExtensionKey($package);
        if (trim($extensionKey) === '') {
            throw new \RuntimeException(
                sprintf(
                    'RootPackage "%s" is of type TYPO3 Extension, but extension-key configuration is missing.',
                    $package
                ),
                1664803234
            );
        }

        $rootPackagePath = $this->filesystem->normalizePath($this->extractBaseDir($this->composer->getConfig()));
        $packageVendorPath = $this->filesystem->normalizePath($this->getVendorPath() . '/' . $packageName);
        $this->filesystem->ensureDirectoryExists(dirname($packageVendorPath));
        if ($this->filesystem->isSymlinkedDirectory($packageVendorPath)) {
            $this->filesystem->unlink($packageVendorPath);
        }
        $success = $this->filesystem->relativeSymlink($rootPackagePath, $packageVendorPath);
        $this->io->info(sprintf('>> Symlinked extension root to vendor folder "%s"', $packageVendorPath));
    }

    private function rootPath(): string
    {
        return $this->extractBaseDir($this->composer->getConfig());
    }

    private function extractBaseDir(Config $config): string
    {
        $reflectionClass = new \ReflectionClass($config);
        $reflectionProperty = $reflectionClass->getProperty('baseDir');
        $reflectionProperty->setAccessible(true);
        $value = $reflectionProperty->getValue($config);
        return is_string($value) ? $value : '';
    }

    private function getVendorPath(): string
    {
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        return is_string($vendorDir) ? $vendorDir : '';
    }

    private function getPackageExtensionKey(PackageInterface $package): string
    {
        /** @var array{"typo3/cms"?: array{"extension-key"?: mixed}} $extra */
        $extra = $package->getExtra();
        $extensionKey = $extra['typo3/cms']['extension-key'] ?? '';
        if (!is_string($extensionKey)) {
            $extensionKey = '';
        }
        $extensionKey = trim($extensionKey, '');
        if ($extensionKey === '' && str_starts_with($package->getType(), 'typo3-cms-')) {
            throw new \RuntimeException(sprintf('Extension with package name "%s" does not define an extension key.', $package->getName()), 1501195043);
        }
        return $extensionKey !== '' ? $extensionKey : $package->getName();
    }

    private function isProcessable(): bool
    {
        $cmsComposerInstallersPackage = $this->getInstalledCmsComposerInstallersPackage();
        if ($cmsComposerInstallersPackage instanceof PackageInterface) {
            $versionParts = explode('.', trim($cmsComposerInstallersPackage->getVersion(), 'v'));
            $major = (int)($versionParts[0] ?? 0);
            if ($major === 4 || $major >= 5) {
                $rootPackage = $this->rootPackage();
                return
                    $this->packageIsExtension($rootPackage)
                    || $this->packageIsOfType($rootPackage, 'project');
            }
        }
        return false;
    }

    private function getInstalledCmsComposerInstallersPackage(): ?PackageInterface
    {
        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getPackages();
        foreach ($packages as $package) {
            if ($package->getType() === 'composer-plugin' && $package->getName() === 'typo3/cms-composer-installers') {
                return $package;
            }
        }
        return null;
    }
}
