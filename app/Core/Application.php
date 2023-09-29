<?php

declare(strict_types=1);

namespace Ions\app\Core;

/**
 * ------******-----******----
 * Application Class.
 * ------******-----******----
 */

class Application
{
    readonly private string $php_version;

    public function __construct()
    {
        $this->php_version = "8.0";
        $this->configs();
    }

    private function extensions()
    {
        $extensions =
            [
                'gd',
                'pdo_mysql'
            ];

        $not_loaded = [];
        foreach ($extensions as $ext) {
            if (!extension_loaded($ext))
                $not_loaded[] = $ext;
        }

        if (!empty($not_loaded))
            dd("please load the following extensions in your php.ini file: " . implode(",", $not_loaded));
    }

    private function configs(): void
    {
        ini_set('default_charset', 'UTF-8');

        $minPhpVersion = $this->php_version;
        if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
            $message = sprintf(
                'Your PHP version must be a minimum of %s to run Ions Plugins Framework. Current version: %s',
                $minPhpVersion,
                PHP_VERSION
            );

            exit($message);
        }
    }

    public function run()
    {
        do_action('before_controller');
        do_action('controller');
        do_action('after_controller');

        ob_start();
        do_action('before_view');

        $before_content = ob_get_contents();
        do_action('view');
        $after_content = ob_get_contents();

        if (strlen($after_content) == strlen($before_content)) {
            if (page() != '404') {
                redirect('404');
            }
        }

        do_action('after_view');
    }
}
