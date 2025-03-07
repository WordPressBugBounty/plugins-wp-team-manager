<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5d583c9efdd2dec7399796798cc0da7d
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'DWL\\Wtm\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'DWL\\Wtm\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'DWL\\Wtm\\Classes\\AdminAssets' => __DIR__ . '/../..' . '/includes/Classes/AdminAssets.php',
        'DWL\\Wtm\\Classes\\AdminSettings' => __DIR__ . '/../..' . '/includes/Classes/AdminSettings.php',
        'DWL\\Wtm\\Classes\\Helper' => __DIR__ . '/../..' . '/includes/Classes/Helper.php',
        'DWL\\Wtm\\Classes\\LoadMore' => __DIR__ . '/../..' . '/includes/Classes/LoadMore.php',
        'DWL\\Wtm\\Classes\\PostType' => __DIR__ . '/../..' . '/includes/Classes/PostType.php',
        'DWL\\Wtm\\Classes\\PublicAssets' => __DIR__ . '/../..' . '/includes/Classes/PublicAssets.php',
        'DWL\\Wtm\\Classes\\ShortcodeGenerator' => __DIR__ . '/../..' . '/includes/Classes/ShortcodeGenerator.php',
        'DWL\\Wtm\\Classes\\Shortcodes' => __DIR__ . '/../..' . '/includes/Classes/Shortcodes.php',
        'DWL\\Wtm\\Classes\\TeamMetabox' => __DIR__ . '/../..' . '/includes/Classes/TeamMetabox.php',
        'DWL\\Wtm\\Elementor\\ElementorWidgets' => __DIR__ . '/../..' . '/includes/Elementor/ElementorWidgets.php',
        'DWL\\Wtm\\Elementor\\Widgets\\Team' => __DIR__ . '/../..' . '/includes/Elementor/Widgets/Team.php',
        'DWL\\Wtm\\Elementor\\Widgets\\Team' => __DIR__ . '/../..' . '/includes/Elementor/Widgets/isotope.php',
        'DWL\\Wtm\\Traits\\Singleton' => __DIR__ . '/../..' . '/includes/Traits/Singleton.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5d583c9efdd2dec7399796798cc0da7d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5d583c9efdd2dec7399796798cc0da7d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5d583c9efdd2dec7399796798cc0da7d::$classMap;

        }, null, ClassLoader::class);
    }
}
