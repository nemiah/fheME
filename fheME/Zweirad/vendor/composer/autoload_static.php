<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticIniteb4b3930ffbe923df5289ce67a198636
{
    public static $prefixLengthsPsr4 = array (
        'n' => 
        array (
            'nemiah\\phpSilence\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'nemiah\\phpSilence\\' => 
        array (
            0 => __DIR__ . '/..' . '/nemiah/php-silence/src/nemiah/phpSilence',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticIniteb4b3930ffbe923df5289ce67a198636::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticIniteb4b3930ffbe923df5289ce67a198636::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticIniteb4b3930ffbe923df5289ce67a198636::$classMap;

        }, null, ClassLoader::class);
    }
}
