<?php
/**
 * @package		Spir
 * @date	    16/7/4
 * @author		Irvin
 */

/*
 *---------------------------------------------------------------
 *  PHP版本检测
 *---------------------------------------------------------------
 */
if (version_compare(PHP_VERSION, '7.0', '<'))
{
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Old PHP version detected, please update your PHP to PHP 7.0 or higher.';
    exit(1);
}

/*
 *---------------------------------------------------------------
 * 设置应用运行环境: development、testing、production
 *---------------------------------------------------------------
 */
define('ENVIRONMENT', $_SERVER['SP_ENV'] ?? 'development');

/*
 *---------------------------------------------------------------
 * 错误报告: 开启错误报告等级
 *---------------------------------------------------------------
 */
switch (ENVIRONMENT)
{
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'testing':
    case 'production':
        ini_set('display_errors', 0);
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'The application environment is not set correctly.';
        exit(1);
}

/*
 *---------------------------------------------------------------
 * 框架系统目录名
 *---------------------------------------------------------------
 */
	$system_path = 'system';

/*
 *---------------------------------------------------------------
 * 框架应用目录名
 *---------------------------------------------------------------
 */
	$application_folder = 'application';

/*
 *---------------------------------------------------------------
 * 应用目录中的视图目录名
 *---------------------------------------------------------------
 */
	$view_folder = '';


/*
 * --------------------------------------------------------------------
 *  框架系统目录的绝对路径
 * --------------------------------------------------------------------
 */
	if (defined('STDIN'))
    {
        chdir(dirname(__FILE__));
    }

	if (($_temp = realpath($system_path)) !== FALSE)
    {
        $system_path = $_temp.DIRECTORY_SEPARATOR;
    }
    else
    {
        $system_path = strtr(
                rtrim($system_path, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            ).DIRECTORY_SEPARATOR;
    }

	if ( ! is_dir($system_path))
    {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
        exit(3); // EXIT_CONFIG
    }

/*
 * -------------------------------------------------------------------
 *  全局路径常量
 * -------------------------------------------------------------------
 */
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	define('BASEPATH', $system_path);

	define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

	define('SYSDIR', basename(BASEPATH));

	if (is_dir($application_folder))
    {
        if (($_temp = realpath($application_folder)) !== FALSE)
        {
            $application_folder = $_temp;
        }
        else
        {
            $application_folder = strtr(
                rtrim($application_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            );
        }
    }
    elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
    {
        $application_folder = BASEPATH.strtr(
                trim($application_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            );
    }
    else
    {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
        exit(3); // EXIT_CONFIG
    }

	define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);

	if ( ! isset($view_folder[0]) && is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
    {
        $view_folder = APPPATH.'views';
    }
    elseif (is_dir($view_folder))
    {
        if (($_temp = realpath($view_folder)) !== FALSE)
        {
            $view_folder = $_temp;
        }
        else
        {
            $view_folder = strtr(
                rtrim($view_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            );
        }
    }
    elseif (is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
    {
        $view_folder = APPPATH.strtr(
                trim($view_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            );
    }
    else
    {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
        exit(3); // EXIT_CONFIG
    }

	define('VIEWPATH', $view_folder.DIRECTORY_SEPARATOR);

/*
 * --------------------------------------------------------------------
 *  加载引导文件
 * --------------------------------------------------------------------
 */
require_once BASEPATH.'core/Spir.php';
