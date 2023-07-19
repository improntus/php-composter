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

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use ErrorException;
use RuntimeException;

/**
 * Class Plugin.
 *
 * This main class activates and sets up the PHP Composter system within the package's .git folder.
 *
 * @since   0.1.0
 *
 * @package PHPComposter\PHPComposter
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{

    /**
     * The name of the current package.
     * Used in error output.
     *
     * @var string
     */
    const PACKAGE_NAME = 'improntus/php-composter';

    /**
     * Instance of the IO interface.
     *
     * @var IOInterface
     *
     * @since 0.1.0
     */
    protected static $io;

    /**
     * Get the event subscriber configuration for this plugin.
     *
     * @return array<string, string|array{0: string, 1?: int}|array<array{0: string, 1?: int}>> The events to listen to, and their associated handlers.
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => [
                ['setCodeSnifferStandards', 1],
                ['persistConfig', 2],
                ['makeHooksExecutable', 3],
                ['makeComposterExecutable', 4],
            ],
            ScriptEvents::POST_UPDATE_CMD  => [
                ['setCodeSnifferStandards', 1],
                ['persistConfig', 2],
                ['makeHooksExecutable', 3],
                ['makeComposterExecutable', 4],
            ],
        ];
    }

    public function setCodeSnifferStandards()
    {
        exec('vendor/bin/phpcs --config-set installed_paths ../../phpcompatibility/php-compatibility,../../magento/magento-coding-standard/');
    }

    public function makeHooksExecutable()
    {
        exec('chmod +x .git/hooks/*');
    }

    public function makeComposterExecutable()
    {
        exec('chmod +x vendor/improntus/php-composter/bin/php-composter');
    }

    /**
     * Persist the stored configuration.
     *
     * @param Event $event Event that was triggered.
     * @since 0.1.0
     *
     */
    public static function persistConfig(Event $event)
    {
        $filesystem = new Filesystem();
        $path = Paths::getPath('git_composter');
        $filesystem->ensureDirectoryExists($path);
        $composterPath = Paths::getPath('git_composter');
        if (static::$io->isVeryVerbose()) {
            static::$io->write(
                sprintf(
                    'Removing previous PHP Composter actions at %1$s',
                    $composterPath
                )
            );
        }
        $filesystem->emptyDirectory($composterPath);
        file_put_contents(Paths::getPath('git_config'), static::buildConfigPhp());
    }

    /**
     * Generate the config file.
     *
     * @return string Generated Config file.
     * @since 0.1.0
     *
     */
    public static function buildConfigPhp()
    {
        $phpEOL = PHP_EOL;
        $output = "<?php$phpEOL";
        $output .= "// PHP Composter configuration file.$phpEOL";
        $output .= "// Do not edit, this file is generated automatically.$phpEOL";
        $timeStamp = date('Y/m/d H:m:s');
        $output .= "// Timestamp: $timeStamp$phpEOL";
        $output .= $phpEOL;
        $output .= "return [$phpEOL";
        $i = '    '; // indent
        foreach (Hook::getSupportedHooks() as $hook) {
            $entries = HookConfig::getEntries($hook);
            $output .= "$i'$hook' => [$phpEOL";
            $i2 = str_repeat($i, 2);
            foreach ($entries as $priority => $methods) {
                $output .= "$i2$priority => [$phpEOL";
                $i3 = str_repeat($i, 3);
                foreach ($methods as $method) {
                    $output .= "$i3'$method',$phpEOL";
                }
                $output .= "$i2],$phpEOL";
            }
            $output .= "$i],$phpEOL";
        }
        $output .= "$i];$phpEOL";
        return $output;
    }

    /**
     * Activate the Composer plugin.
     *
     * @param Composer $composer Reference to the Composer instance.
     * @param IOInterface $io Reference to the IO interface.
     * @since 0.1.0
     *
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        static::$io = $io;
        if (static::$io->isVerbose()) {
            static::$io->write('Activating PHP Composter plugin');
        }
        $installer = new Installer(static::$io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);

        $filesystem = new Filesystem();

        $composterTemplate = Paths::getPath('root_template');
        if (static::$io->isVeryVerbose()) {
            static::$io->write(
                sprintf(
                    'Removing previous PHP Composter code at %1$s',
                    $composterTemplate
                )
            );
        }
        $filesystem->emptyDirectory($composterTemplate);

        $this->linkBootstrapFiles($filesystem);
        $this->createGitHooks($filesystem);
    }

    /**
     * Symlink the bootstrapping code into the .git folder.
     *
     * @param Filesystem $filesystem Reference to the Filesystem instance.
     * @since 0.1.0
     *
     */
    protected function linkBootstrapFiles(Filesystem $filesystem)
    {
        $rootTemplate = Paths::getPath('root_template');
        $composterTemplate = Paths::getPath('git_template');

        $files = [
            'bootstrap.php',
        ];

        $filesystem->ensureDirectoryExists($rootTemplate);

        foreach ($files as $file) {
            if (static::$io->isVeryVerbose()) {
                static::$io->write(
                    sprintf(
                        'Symlinking %1$s to %2$s',
                        $rootTemplate . $file,
                        $composterTemplate . $file
                    )
                );
            }
            $this->createRelativeSymlink($filesystem, $composterTemplate . $file, $rootTemplate . $file);
        }
    }

    /**
     * Symlink each known Git hook to the PHP Composter bootstrapping script.
     *
     * @param Filesystem $filesystem Reference to the Filesystem instance.
     * @since 0.1.0
     *
     */
    protected function createGitHooks(Filesystem $filesystem)
    {

        $hooksPath = Paths::getPath('root_hooks');
        $gitScriptPath = Paths::getPath('git_script');

        $filesystem->ensureDirectoryExists($hooksPath);

        foreach (Hook::getSupportedHooks() as $gitHook) {
            $hookPath = $hooksPath . $gitHook;
            if (is_link($hookPath)) {
                continue;
            }
            if (static::$io->isDebug()) {
                static::$io->write(
                    sprintf(
                        'Symlinking %1$s to %2$s',
                        $hookPath,
                        $gitScriptPath
                    )
                );
            }
            $this->createRelativeSymlink($filesystem, $gitScriptPath, $hookPath);
            exec("chmod +x $hookPath");
            exec("chmod +x $gitScriptPath");
        }
    }

    /**
     * Tries to create a relative symlink with the filesystem. If this fails, try an absolute symlink.
     *
     * @param Filesystem $filesystem
     * @param            $target
     * @param            $link
     * @throws RuntimeException When also the absolute symlink creation fails.
     *
     */
    protected function createRelativeSymlink(Filesystem $filesystem, $target, $link)
    {
        if (!$filesystem->relativeSymlink($target, $link)) {
            static::$io->write(
                'Unable to create relative symlink, try absolute symlink.',
                true,
                IOInterface::VERBOSE
            );

            try {
                symlink($filesystem->normalizePath($target), $filesystem->normalizePath($link));
            } catch (ErrorException $e) {
                // Generate a more explanatory exception instead of the standard symlink messages.
                $explanatoryException = new RuntimeException(
                    sprintf(
                        '%3$s: Failed to create absolute symlink %1$s to %2$s',
                        $filesystem->normalizePath($link),
                        $filesystem->normalizePath($target),
                        static::PACKAGE_NAME
                    ),
                    0,
                    $e
                );

                // If we are on windows and the code of the ErrorException is 1314, you do not have sufficient privilege
                // to perform a symlink.
                if ($e->getMessage() === 'symlink(): Cannot create symlink, error code(1314)'
                    && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Inform the user that it is a privilege issue.
                    throw new RuntimeException(
                        sprintf(
                            '%1$s Failed to create symbolic link: ' .
                            'You do not have sufficient privilege to perform this operation. ' .
                            'Please run this command as administrator.',
                            static::PACKAGE_NAME
                        ),
                        0,
                        $explanatoryException
                    );
                } elseif (file_exists($link)) {
                    // File already exists, issue a warning.
                    static::$io->isVeryVerbose() && static::$io->write(
                        sprintf(
                            '%1$s: Cannot create symlink at %2$s. File already exists.',
                            static::PACKAGE_NAME,
                            $filesystem->normalizePath($link)
                        )
                    );
                } else {
                    throw $explanatoryException;
                }
            }
        }
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // TODO: Implement deactivate() method.
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // TODO: Implement uninstall() method.
    }
}
