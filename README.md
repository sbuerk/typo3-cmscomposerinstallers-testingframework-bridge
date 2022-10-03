TYPO3 CMS ComposerInstallers TestingFramework Bridge for extension development
==============================================================================

# Mission

This composer plugin has the intermediate mission to keep extension development testing
against TYPO3 v12 working, which recently added enforced CMS Composer Installers 5. This
breaks the way testing works.

Until proper overwork of the whole testing infrastructure is done, this plugin provides
proper symlinked TYPO3 system extension and the extension to the known "legacy paths".
Thus, Unit-, Functional and Acceptance testing works as before without any changes in
extensions which already tests against TYPO3 v12.

> :warning: **This is an unofficial intermediate workaround, which may vanish anytime.**

# Installation and usage

This plugin should be only added as development dependency, so it get not shipped and
installed on installation in a instance.

```shell
$ composer require --dev sbuerk/typo3-cmscomposerinstallers-testingframework-bridge
```

The plugin uses already provided information and configuration from composer and
the CMS Composer Installers, thus no configuration needed for this plugin.
