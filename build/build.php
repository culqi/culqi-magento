<?php

$root = dirname(__DIR__);
$version = get_module_version("$root/registration.php");
$moduleName = get_module_name("$root/registration.php");
$modulePath = str_replace('_', '/', $moduleName);
$dist = "$root/dist";
$temp = "$dist/tmp/$modulePath";
$zipOut = "$dist/culqi-magento-$version.zip";
$excludes = array_filter(
    array_map('trim', file("$root/build/exclude.txt")),
    fn($l) => $l !== '' && !str_starts_with($l, '#')
);

echo "=== Culqi Magento Plugin Build ===\n\n";

if (!class_exists('ZipArchive')) {
    throw new Exception('ZipArchive is not available. Please enable the zip extension.');
}

echo "Version: $version\n";
echo "Module: $moduleName\n";
echo "Cleaning dist...\n";
deleteDir($dist);
mkdir($temp, 0755, true);

echo "Copying files...\n";
copyDir($root, $temp, $excludes);

echo "Removing .DS_Store files...\n";
removeDsStoreFiles($temp);

echo "Creating zip...\n";
$zip = new ZipArchive();
$zip->open($zipOut, ZipArchive::CREATE | ZipArchive::OVERWRITE);
addDirToZip($zip, $temp, $modulePath);
$zip->close();

deleteDir("$dist/tmp");

echo "\n✅ Build generado: dist/culqi-magento-$version.zip\n";
echo 'Tamaño: ' . number_format(filesize($zipOut) / 1024, 2) . " KB\n";

function get_module_version(string $configPath): string
{
    $content = file_get_contents($configPath);
    if (preg_match('/define\s*\(\s*[\'"]PLUGIN_VERSION[\'"]\s*,\s*[\'"]([v\d.]+)[\'"]\s*\)/', $content, $match)) {
        return $match[1];
    }
    throw new Exception('No se pudo leer la versión del módulo desde registration.php');
}

function get_module_name(string $configPath): string
{
    $content = file_get_contents($configPath);
    if (preg_match('/ComponentRegistrar::register\s*\(\s*ComponentRegistrar::MODULE\s*,\s*[\'"]([^\'"]+)[\'"]\s*,/', $content, $match)) {
        return $match[1];
    }
    throw new Exception('No se pudo leer el nombre del módulo desde registration.php');
}

function isExcluded(string $rel, array $excludes): bool
{
    foreach ($excludes as $ex) {
        $ex = rtrim($ex, '/');
        if ($rel === $ex || str_starts_with($rel, "$ex/")) {
            return true;
        }
    }
    return false;
}

function copyDir(string $src, string $dst, array $excludes): void
{
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($items as $item) {
        $rel = ltrim(str_replace($src, '', $item->getPathname()), DIRECTORY_SEPARATOR);
        $rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);

        if (isExcluded($rel, $excludes))
            continue;

        $target = $dst . DIRECTORY_SEPARATOR . $rel;
        if ($item->isDir()) {
            mkdir($target, 0755, true);
        } else {
            copy($item->getPathname(), $target);
        }
    }
}

function addDirToZip(ZipArchive $zip, string $dir, string $prefix): void
{
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($items as $item) {
        if ($item->isFile()) {
            $rel = ltrim(str_replace($dir, '', $item->getPathname()), DIRECTORY_SEPARATOR);
            $zip->addFile($item->getPathname(), $prefix . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $rel));
        }
    }
}

function deleteDir(string $path): void
{
    if (!is_dir($path))
        return;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($path);
}

function removeDsStoreFiles(string $dir): void
{
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($items as $item) {
        if ($item->getFilename() === '.DS_Store') {
            unlink($item->getPathname());
            echo '  Deleted: ' . str_replace($dir . '/', '', $item->getPathname()) . "\n";
        }
    }
}
