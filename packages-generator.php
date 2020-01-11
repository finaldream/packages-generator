<?php

function recur_ksort(&$array)
{
    foreach ($array as &$value) {
        if (is_array($value)) {
            recur_ksort($value);
        }

    }
    return ksort($array);
}

function ensureTrailingSlash($path)
{
    return strrpos($path, DIRECTORY_SEPARATOR) === 0
    ? $path
    : $path . DIRECTORY_SEPARATOR;
}

function getInfo($path)
{

    if (!preg_match('/(.+)@(.+)\.(\w+)$/', $path, $matches)) {
        echo sprintf('Error filename "%s" does not match pattern "name@version.ext"\n', $path);
    }

    try {
        return [
            'path' => $path,
            'name' => $matches[1],
            'version' => $matches[2],
            'ext' => $matches[3],
        ];
    } catch (\Exception $e) {
        echo sprintf('Error matching filename "%s"\n', $path);
        var_export($matches);
    }

}

function getFileInfo($path, $extensions = ['zip'])
{

    $path = ensureTrailingSlash($path);
    $extensions = array_map('strtolower', $extensions);
    $infos = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path)
    );

    foreach ($iterator as $info) {

        $ext = pathinfo($info->getPathname(), PATHINFO_EXTENSION);

        if (in_array(strtolower($ext), $extensions)) {

            $infos[] = getInfo(str_replace($path, '', $info->getPathname()));
        }
    }

    return $infos;

}

function buildStructure($infos, $baseurl = '', $packageType = false)
{

    $result = [];
    $url = ensureTrailingSlash($baseurl);

    foreach ($infos as $info) {
        if (!array_key_exists($info['name'], $result)) {
            $result[$info['name']] = [];
        }

        $package = [
            'name' => $info['name'],
            'version' => $info['version'],
            'dist' => [
                'type' => 'zip',
                'url' => $url . $info['path'],
            ],
        ];

        if ($packageType) {
            $package['type'] = $packageType;
        }

        $result[$info['name']][$info['version']] = $package;
    }

    return [
        'packages' => $result,
    ];

}

function getOptions()
{
    $default = [
        'dir' => getcwd(),
        'ext' => 'zip',
        'baseurl' => '',
        'dry-run' => false,
        'verbose' => false,
        'package-type' => false,
        'help' => false,
    ];

    $options = getopt(
        '',
        [
            'dir::',
            'ext::',
            'baseurl::',
            'package-type::',
            'dry-run',
            'verbose',
            'help',
        ]
    );

    // boolean options are either unset or false, if set.
    $options = array_map(function ($n) {return $n === false ? true : $n;}, $options);

    return array_merge($default, $options);
}

function help()
{
    echo '
    Usage:

    PHP: php packages-generator.php [options]
    Bin: packages-generator [options]


    --dir=<path>            path of repository
    --ext=<extensions>      Default "zip", overrides extensions (comma-separated)
    --baseurl=<url>         Public URL where the repository will be hosted
    --package-type=<type>   Optional, adds "type" field to package (i.e. "wordpress-plugin")
    --dry-run               Don\'t write packages.json
    --verbose               output generated structure
';
}

$options = getOptions();
$path = ensureTrailingSlash($options['dir']);

if ($options['help']) {
    help();
    die();
}

$files = getFileInfo($path, explode(',', $options['ext']));
$packages = buildStructure($files, $options['baseurl'], $options['package-type']);

recur_ksort($packages);
$content = json_encode($packages, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

if ($options['verbose']) {
    print($content);
}

if (!$options['dry-run']) {
    file_put_contents($path . 'packages.json', $content);
}
