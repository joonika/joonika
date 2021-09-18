<?php

namespace Joonika;

use ZipArchive;

//File System

class FS
{
    public static $instance = null;
    public static $results = [];

    public function __construct()
    {
    }

    private static function catchException(\Exception $e)
    {
        throw new \Exception("Error is :  {$e->getMessage()}  ( code = {$e->getCode()})\n In file : {$e->getFile()} , In line : {$e->getLine()}\n");
    }

    private static function init()
    {
        if (is_null(self::$instance)) {
            new FileSystem();
        }
    }

    public static function isExist($path)
    {
        try {
            if (file_exists($path)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function isFile($path)
    {
        try {
            if (self::isExist($path)) {
                if (is_file($path)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function isWritable($path)
    {
        try {
            if (is_writable($path))
                return true;
            return false;
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function isReadable($path)
    {
        try {
            if (is_readable($path))
                return true;
            return false;
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function isExecutable($path)
    {
        try {
            if (self::isExistIsFile($path)) {
                if (is_executable($path)) {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function isExistIsFileIsWritable($path)
    {
        try {
            if (self::isExist($path) && self::isFile($path) && self::isWritable($path))
                return true;
            return false;
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function isExistIsFileIsReadable($path)
    {
        try {
            if (self::isExist($path) && self::isFile($path) && self::isReadable($path))
                return true;
            return false;
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function isExistIsFile($path)
    {
        try {
            if (self::isExist($path) && self::isFile($path))
                return true;
            return false;
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function fOpen($path, $mode = 'w')
    {
        try {
            return $file = fopen($path, $mode);
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function fClose($file)
    {
        fclose($file);
    }

    public static function createEmptyFile($path, $mode = 'w')
    {
        try {
            $file = self::fOpen($path, $mode);
            self::fClose($file);
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function isDir($path)
    {
        try {
            if (is_dir($path))
                return true;
            return false;
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function rmDir($path)
    {
        try {
            if (self::isDir($path)) {
                rmdir($path);
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function rename($name, $newName)
    {
        try {
            if (self::isExistIsFileIsWritable($name)) {
                rename($name, $newName);
            } elseif (self::isDir($name)) {
                rename($name, $newName);
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function mkDir($path, $mode = 0777, $recursive = true, $log = false)
    {
        try {
            if ($log) {
                if (mkdir($path, $mode, $recursive))
                    return true;
            } else {
                if (@mkdir($path, $mode, $recursive))
                    return true;
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function fileWrite($path, $content = null, $mode = "w")
    {
        try {
            $newFile = self::fOpen($path, $mode);
            if ($content) {
                fwrite($newFile, $content);
            }
            fclose($newFile);
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function filePutContent($path, $content = null, $mode = "w")
    {
        try {
            $newFile = self::fOpen($path, $mode);
            if ($content) {
                file_put_contents($path, $content);
            }
            fclose($newFile);
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function fileGetContent($path, $flag = null, $context = null)
    {
        try {
            if (self::isExistIsFileIsReadable($path)) {
                return file_get_contents($path, $flag, $context);
            } else {
                return '';
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function fileEdit($path, $content = null, $mode = 'w')
    {
        try {
            if (self::isExistIsFileIsWritable($path)) {
                $newFile = self::fOpen($path, $mode);
                if ($content) {
                    file_put_contents($path, $content);
                }
                fclose($newFile);
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function fileRemove($path)
    {
        try {
            if (self::isExistIsFile($path)) {
                unlink($path);
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function pathInfo($path)
    {
        try {
            return pathinfo($path);
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function dirName($path)
    {
        try {
            return self::pathInfo($path)['dirname'];
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function extension($path)
    {
        try {
            return self::pathInfo($path)['extension'];
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function baseName($path, $suffix = null)
    {
        try {
            if ($suffix) {
                return basename(self::pathInfo($path)['basename']);
            }
            return basename(self::pathInfo($path)['basename'], $suffix);
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function stat($path)
    {
        try {
            return stat($path);
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function realPath($path)
    {
        try {
            return realpath($path);
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function readFile($path, $include_path = null, $context = null)
    {
        try {
            if (self::isExistIsFileIsReadable($path)) {
                return readfile($path, $include_path, $context);
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function glob($pattern, $flag)
    {
        try {
            return glob($pattern, $flag);
        } catch (\Exception $e) {

        }
    }

    public static function copy($source, $destination)
    {
        try {
            if (self::isExistIsFile($source)) {
                if (copy($source, $destination)) {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function fileToArray($path, $flag = null, $context = null)
    {
        try {
            return file($path, $flag, $context);
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function createDirectories($dest)
    {
        try {
            if (!self::isExist($dest)) {
                @mkdir($dest, 0777, true);
            }
            if (self::isDir($dest)) {
                $dir = opendir($dest);
                @mkdir($destination, 0777, true);
                while (false !== ($file = readdir($dir))) {
                    if (($file != '.') && ($file != '..')) {
                        if (self::isDir($dest . '/' . $file)) {
                            self::recurse_copy(null, $dest . '/' . $file);
                        }
                    }
                }
                closedir($dir);
            }
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function copyDirectories($source, $destination)
    {
        try {
            if (!self::isExist($source)) {
                @mkdir($source, 0777, true);
            }
            $dir = opendir($source);
            if (!self::isExist($destination)) {
                @mkdir($destination, 0777, true);
            }
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (self::isDir($source . '/' . $file)) {
                        self::recurse_copy($source . '/' . $file, $destination . '/' . $file);
                    } else {
                        self::copy($source . '/' . $file, $destination . '/' . $file);
                    }
                }
            }
            closedir($dir);
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    private static function recurse_copy($source = null, $dest = null)
    {
        try {
            // Check for symlinks
            if ($source) {
                if (is_link($source)) {
                    return symlink(readlink($source), $dest);
                }
                if (self::isFile($source)) {
                    return self::copy($source, $dest);
                }
            }
            // Simple copy for a file
            // Make destination directory
//        if (!is_dir($dest)) {
//            mkdir($dest);
//        }


            if (!self::isDir($dest)) {
                self::mkDir($dest, 0777, true);
            }

            // Loop through the folder
            if ($source) {
                $dir = dir($source);
            } else {
                $dir = dir($dest);
            }

            while (false !== ($entry = $dir->read())) {
                // Skip pointers
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                // Deep copy directories
                self::recurse_copy("{$source}/{$entry}", "{$dest}/{$entry}");
            }
            // Clean up
            $dir->close();
            return true;
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function scanDir($path)
    {
        try {
            return scandir($path);
        } catch (\Exception $e) {
            self::catchException($e);
        }
    }

    public static function getDirectories($path)
    {
        $directoriesNames = [];
        $directories = scandir($path);
        foreach ($directories as $dir) {
            if ($dir != '.' && $dir != "..") {
                $directoriesNames[] = $dir;
            }
        }
        return $directoriesNames;
    }

    private static function emptyDir($dir) {
        if (is_dir($dir)) {
            $scn = scandir($dir);
            foreach ($scn as $files) {
                if ($files !== '.') {
                    if ($files !== '..') {
                        if (!is_dir($dir . '/' . $files)) {
                            unlink($dir . '/' . $files);
                        } else {
                            self::emptyDir($dir . '/' . $files);
                            rmdir($dir . '/' . $files);
                        }
                    }
                }
            }
        }
    }

    private static function deleteDir($dir) {

        foreach(glob($dir . '/' . '*') as $file) {
            if(is_dir($file)){


                self::deleteDir($file);
            } else {

                @unlink($file);
            }
        }
        self::emptyDir($dir);
        @rmdir($dir);
    }

    public static function removeDirectories($dirPath, $selfRemove = true)
    {
        self::deleteDir($dirPath);
    }

    public static function filesList($dir, $ext = 'php')
    {
        if (file_exists($dir)) {
            $files = scandir($dir);
            foreach ($files as $key => $value) {
                $path = realpath($dir . DS() . $value);
                if (!is_dir($path)) {
                    $pathinfo = pathinfo($path);
                    if (strpos($path, '.' . $ext) !== false) {
                        self::$results[] = [
                            'basename' => $pathinfo['basename'],
                            'dirname' => $pathinfo['dirname'],
                            'extension' => $pathinfo['extension'],
                            'filename' => $pathinfo['filename'],
                            'path' => $path,
                            'size' => filesize($path),
                            'type' => mime_content_type($path)
                        ];
                    }
                } else if ($value != "." && $value != "..") {
                    self::filesList($path, $ext);
                }
            }
            return self::$results;
        }
    }

    public static function allFilesListDetails($dir)
    {
        if (file_exists($dir)) {
            $files = scandir($dir);
            foreach ($files as $key => $value) {
                $path = realpath($dir . DS() . $value);
                if (!is_dir($path)) {
                    $pathinfo = pathinfo($path);
                    self::$results[] = [
                        'basename' => $pathinfo['basename'],
                        'dirname' => $pathinfo['dirname'],
                        'extension' => $pathinfo['extension'],
                        'filename' => $pathinfo['filename'],
                        'path' => $path,
                        'size' => filesize($path),
                        'type' => mime_content_type($path)
                    ];
                } else if ($value != "." && $value != "..") {
                    self::allFilesListDetails($path);
                }
            }
            return self::$results;
        }
    }

    public static function allFilesList($dir)
    {
        if (file_exists($dir)) {
            $files = scandir($dir);
            foreach ($files as $key => $value) {
                $path = realpath($dir . DS() . $value);
                if (!is_dir($path)) {
                    $pathinfo = pathinfo($path);
                    self::$results[] = $path;
                } else if ($value != "." && $value != "..") {
                    self::allFilesList($path);
                }
            }
            return self::$results;
        }
    }

    public static function createZipFile($name, $dir, $type, $comment = null, $password = null)
    {
        $zip = new ZipArchive;
        if ($zip->open($name, ZipArchive::CREATE) === TRUE) {

            foreach ($dir as $file) {

                if (!is_null($password)) {
                    $zip->setPassword($password);
                }
                if ($type == "modules") {
                    $localName = trim(substr($file, stripos($file, 'modules') + 8));
                } elseif ($type == "themes") {
                    $localName = trim(substr($file, stripos($file, 'themes') + 7));
                } elseif ($type == "public") {
                    $localName = trim(substr($file, stripos($file, 'public')));
                } elseif ($type == "storage") {
                    $localName = trim(substr($file, stripos($file, 'storage') + 8));
                } else {
                    $localName = null;
                }
                $zip->addFile($file, $localName);

                if (!is_null($password)) {
                    $zip->setEncryptionName($file, ZipArchive::EM_AES_256);
                }
            }
            if (!is_null($comment)) {
                $zip->setArchiveComment($comment);
            }

            $zip->close();
        }
    }

    public static function extractZipFile($fileName, $extractFilePath)
    {
        $zip = new ZipArchive;
        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE) {
            $zip->extractTo($extractFilePath);
            $zip->close();
        }
    }
}