<?php
/**
 * Git Hooks Management through Composer.
 *
 * @package   PHPComposter\PHPComposter
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   MIT
 * @link      http://www.brightnucleus.com/
 * @copyright 2016 Alain Schlesser, Bright Nucleus
 */

namespace Improntus\PHPComposter;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use InvalidArgumentException;

/**
 * Class Installer.
 *
 * The Installer class tells Composer where to install each package of type `php-composter-action`.
 *
 * @since   0.1.0
 *
 * @package PHPComposter\PHPComposter
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class Installer extends LibraryInstaller
{

    const EXTRA_KEY = 'php-composter-hooks';
    const PREFIX = 'php-composter-';
    const TYPE = 'php-composter-action';

    /**
     * Install the package.
     *
     * @param InstalledRepositoryInterface $repo The repository from where the package was fetched.
     * @param PackageInterface $package The package to install.
     *
     * @throws InvalidArgumentException If the package name does not match the required pattern.
     * @since 0.1.0
     *
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $path = $this->getInstallPath($package);
        if ($this->io->isVerbose()) {
            $this->io->write(
                sprintf(
                    'Symlinking PHP Composter action %1$s',
                    $path
                )
            );
        }

        parent::install($repo, $package);

        foreach ($this->getHooks($package) as $prioritizedHook => $method) {
            $array = explode('.', $prioritizedHook);
            if (count($array) > 1) {
                [$priority, $hook] = $array;
            } else {
                $hook = $array[0];
                $priority = 10;
            }

            if ($this->io->isVeryVerbose()) {
                $this->io->write(
                    sprintf(
                        'Adding method "%1$s" to hook "%2$s" with priority %3$s',
                        $method,
                        $hook,
                        $priority
                    )
                );
            }
            HookConfig::addEntry($hook, $method, $priority);
        }
    }

    /**
     * Check whether the package is already installed.
     *
     * @param InstalledRepositoryInterface $repo
     * @param PackageInterface $package
     *
     * @return bool
     * @since 0.1.0
     *
     * @todo  This should be made smarter to not always reinstall from scratch.
     *
     */
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        // Always reinstall all PHP Composter actions.
        return false;
    }

    /**
     * Whether the installer supports a given package type.
     *
     * @param $packageType
     *
     * @return bool
     * @since 0.1.0
     *
     */
    public function supports($packageType)
    {
        return self::TYPE === $packageType;
    }

    /**
     * Get the package name suffix.
     *
     * @param PackageInterface $package Package to inspect.
     *
     * @return string Suffix of the package name.
     * @throws InvalidArgumentException If the package name does not match the required pattern.
     * @since 0.1.0
     *
     */
    protected function getSuffix(PackageInterface $package)
    {
        $result = explode('/', $package->getPrettyName());
        if (count($result) !== 2) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unable to install PHP Composter action, could '
                    . 'not extract action name from package "%1$s"',
                    $package->getPrettyName()
                )
            );
        }

        [$vendor, $name] = $result;
        $prefixLength = mb_strlen(self::PREFIX);
        $prefix = mb_substr($name, 0, $prefixLength);

        if (self::PREFIX === $prefix) {
            return mb_substr($name, $prefixLength);
        }

        return $name;
    }

    /**
     * Get the hooks configuration from package extra data.
     *
     * @param PackageInterface $package Package to inspect.
     *
     * @return array Array of prioritized hooks.
     * @since 0.2.0
     *
     */
    protected function getHooks(PackageInterface $package)
    {
        $extra = $package->getExtra();
        if (!array_key_exists(self::EXTRA_KEY, $extra)) {
            return [];
        }

        return $extra[self::EXTRA_KEY];
    }
}
