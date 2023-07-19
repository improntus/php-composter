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

/**
 * Class Paths.
 *
 * This static class generates and distributes all the paths used by PHP Composter.
 *
 * @since   0.1.0
 *
 * @package PHPComposter\PHPComposter
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class Paths
{

    const ACTIONS_FOLDER = 'actions/';
    const BIN_FOLDER = 'bin/';
    const COMPOSTER_FOLDER = 'php-composter/';
    const COMPOSTER_PATH = 'vendor/improntus/php-composter/';
    const CONFIG = 'config.php';
    const EXECUTABLE = 'php-composter';
    const GIT_FOLDER = '.git/';
    const GIT_TEMPLATE_FOLDER = 'includes/';
    const HOOKS_FOLDER = 'hooks/';
    const COMPOSER_CONFIG = 'composer.json';

    /**
     * Internal storage of all required paths.
     *
     * @var array
     *
     * @since 0.1.0
     */
    protected static $paths = [];

    /**
     * Get a specific path by key.
     *
     * @param string $key Key of the path to retrieve.
     *
     * @return string Path associated with the key. Empty string if not found.
     * @since 0.1.0
     *
     */
    public static function getPath($key)
    {
        if (empty(static::$paths)) {
            static::initPaths();
        }

        if (array_key_exists($key, static::$paths)) {
            return static::$paths[$key];
        }

        return '';
    }

    /**
     * Initialize the paths.
     *
     * @since 0.1.0
     */
    protected static function initPaths()
    {
        $pwd = getcwd() . DIRECTORY_SEPARATOR;
        static::$paths = [
            'pwd'              => $pwd,
            'root_git'         => $pwd . self::GIT_FOLDER,
            'root_hooks'       => $pwd . self::GIT_FOLDER . self::HOOKS_FOLDER,
            'vendor_composter' => $pwd . self::COMPOSTER_PATH,
            'git_composter'    => $pwd . self::GIT_FOLDER . self::COMPOSTER_FOLDER,
            'git_script'       => $pwd . self::COMPOSTER_PATH . self::BIN_FOLDER . self::EXECUTABLE,
            'actions'          => $pwd . self::GIT_FOLDER . self::COMPOSTER_FOLDER . self::ACTIONS_FOLDER,
            'git_template'     => $pwd . self::COMPOSTER_PATH . self::GIT_TEMPLATE_FOLDER,
            'root_template'    => $pwd . self::GIT_FOLDER . self::COMPOSTER_FOLDER . self::GIT_TEMPLATE_FOLDER,
            'git_config'       => $pwd . self::GIT_FOLDER . self::COMPOSTER_FOLDER . self::CONFIG,
            'composer_config'  => $pwd . self::COMPOSER_CONFIG,
        ];
    }
}
