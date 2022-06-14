<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb00fffd2a9cedc0a58a9f7a83bf95a1c
{
    public static $prefixLengthsPsr4 = array (
        'K' => 
        array (
            'Khartnett\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Khartnett\\' => 
        array (
            0 => __DIR__ . '/..' . '/khartnett/address-normalization/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb00fffd2a9cedc0a58a9f7a83bf95a1c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb00fffd2a9cedc0a58a9f7a83bf95a1c::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
