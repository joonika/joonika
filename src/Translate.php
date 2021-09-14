<?php

namespace Joonika;


use Medoo\Medoo;
use Symfony\Component\Yaml\Yaml;

class Translate
{

    private static $instance = null;
    //-------------------
    private $results = [];
    private $filesGetResult = [];
    private $dbUpdater = [];
    private $finalResult = [];
    //----
    private $finalSeperateResult = [];
    //----
    public $exportFiles = [];
    private static $translate;
    private $oldDatabaseTranslate = [];
    private static $module = null;
    //-------------
    private $paths;
    private $oldFilesPaths;
    public $added = 0;
    public $updated = 0;
    private static $lang = null;

    //--------------

    private function __construct($module, $details)
    {
        global $translate;
        if ($module) {
            self::$module = $module;
            $translate = [];
            self::$translate = [];
            $type = explode("||", $module)[0];
            $dest = explode("||", $module)[1];

            $entries = Database::query("SELECT SQL_CACHE `id`,`var`,`text`,`dest`,`type` FROM `jk_translate` WHERE `type`='" . $type . "' AND `dest`='" . $dest . "' AND `lang`='" . self::getLang() . "'")->fetchAll(\PDO::FETCH_ASSOC);
            if (checkArraySize($entries)) {
                foreach ($entries as $key => $val) {
                    if ($details) {
                        $translate[$val['type'] . "||" . $val['dest'] . "##" . trim($val['var']) . "##" . $val['id']] = trim($val['text']);
                    } else {
                        $translate[trim($val['var'])] = trim($val['text']);
                    }
                }
            }
            self::$translate = $translate;
        } else {
            $this->initTranslate($details);
        }
    }

    public static function routeLanguage()
    {
        global $translate;

        $translate = [];
        $domainFileName='language_'.rtrim(JK_DOMAIN_WOP(),'/');
        $langCache=\Joonika\helper\Cache::get($domainFileName);
        if(empty($langCache)){
            $entries = Database::query('SELECT SQL_CACHE `id`,`var`,`text`,`dest`,`type` FROM `jk_translate` WHERE `lang` = \'' . self::getLang() . '\'')->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($entries)) {
                foreach ($entries as $key => $val) {
                    if (!isset($translate[trim($val['var'])]) || $translate[trim($val['var'])] == "") {
                        $translate[trim($val['var'])] = trim($val['text']);
                    }
                }
            }
            \Joonika\helper\Cache::set($domainFileName,$translate,60);
        }else{
            $translate=$langCache;
        }
        self::$translate = $translate;
    }

    public function initTranslate($details = null)
    {

        global $translate;
        $translate = [];
        $destLang = 'main';
        $dL = null;
        $d = 'modules';
        $this->paths = ['themes' => JK_DIR_THEMES(), "modules" => JK_DIR_MODULES(), "vendor" => JK_DIR_JOONIKA()];
        $entries = Database::query('SELECT SQL_CACHE `id`,`var`,`text`,`dest`,`type` FROM `jk_translate` WHERE `lang` = \'' . self::getLang() . '\'')->fetchAll(\PDO::FETCH_ASSOC);
        if (checkArraySize($entries)) {
            foreach ($entries as $key => $val) {
                if ($details) {
                    $translate[$val['type'] . "||" . $val['dest'] . "##" . trim($val['var']) . "##" . $val['id']] = trim($val['text']);
                } else {
                    if (!isset($translate[trim($val['var'])]) || $translate[trim($val['var'])] == "") {
                        $translate[trim($val['var'])] = trim($val['text']);
                    }
                }
            }
        }
        self::$translate = $translate;
        $this->oldDatabaseTranslate = $entries;
    }

    public function goToTranslate()
    {
        foreach ($this->paths as $key => $path) {
            $this->getDirContents_translate($path, $key);
        }
        foreach ($this->results as $dest => $result) {
            if (sizeof($result) >= 1) {
                foreach ($result as $ne) {
                    $this->filesGetResult[$dest][] = $ne;
                }
            }
        }

        foreach ($this->filesGetResult as $dest => $result) {
            $this->preg_match_translate($result, $dest);
        }

        if (!empty($this->finalResult)) {
            foreach ($this->finalResult as $type => $result) {
                if (!empty($result)) {
                    foreach ($result as $dest => $value) {
                        foreach ($value as $k => $v) {
                            $this->insertTranslate($v, $type, $dest, false);
                        }
                    }
                }
            }
        }

        if (property_exists('\Modules\users\inc\Configs::class', 'databaseInfo')) {
            $permissions = Database::select(\Modules\users\inc\Configs::$databaseInfo['permissions'], '*');
            if (checkArraySize($permissions)) {
                foreach ($permissions as $permission) {
                    if ($permission['permName'] != $permission['module']) {
                        if (stripos($permission['permName'], '_') == false) {
                            $oldHasPerm = Database::get('jk_translate', '*', ['var' => $permission['permName']]);
                            $v = $oldHasPerm ? strtolower($oldHasPerm['text']) : "";
                            $this->exportFiles['modules']['permissionsTable'][$permission['permName']] = $v;
                        }
                    }
                }
            }
        }

        if (!empty($this->exportFiles)) {
//            $timeStart=microtime();
            foreach ($this->exportFiles as $type => $result) {
                if (checkArraySize($result)) {
                    foreach ($result as $dest => $value) {
                        $this->updateTranslate($dest, $type, $value);
                    }
                }
            }
        }

        $this->deleteUnExistStrings();
    }

    public function edit($val, $id, $key, $module = null)
    {

        /*   if (FS::isExistIsFileIsReadable(JK_SITE_PATH() . "storage" . DS() . "langs" . DS() . self::getLang() . ".php")) {
               $this->exportFiles = include JK_SITE_PATH() . "storage" . DS() . "langs" . DS() . self::getLang() . ".php";
           }
           if (isset($this->exportFiles[$key])) {
               $this->exportFiles[$key] = $val;
           }*/
        $has = Database::select('jk_translate', '*', ['var' => trim($key)]);
        if ($has) {
            foreach ($has as $word) {
                Database::update('jk_translate', ['text' => trim($val)], ['id' => $word['id']]);
            }
        }
        /*       $this->exportFiles = [];
               if (isset(self::$translate[trim($key)])) {
                   self::$translate[trim($key)] = trim($val);
                   $this->exportFiles = self::$translate;
               }

               $type = explode("||", $module)[0];
               $dest = explode("||", $module)[1];
               $this->updateTranslate($dest, $type);*/
    }

    private function getDirContents_translate($dir, $dest)
    {
        if (FS::isDir($dir)) {
            $files = scandir($dir);
            foreach ($files as $key => $value) {
                $path = realpath($dir . DS() . $value);
                if (!is_dir($path)) {
                    if (strpos($path, '.php') !== false || strpos($path, '.twig') !== false) {
                        $this->results[$dest][] = $path;
                    }
                } else if ($value != "." && $value != "..") {
                    $this->getDirContents_translate($path, $dest);
                }
            }
            return $this;
        }
    }

    private function preg_match_translate($filesGetResult, $dest)
    {
        if (sizeof($filesGetResult) >= 1) {
            foreach ($filesGetResult as $filesget) {
                $filegetin = file_get_contents($filesget);
                $d = 'modules';
                $destLang = 'main';
                $dL = null;
                if (stripos($filesget, '/themes/')) {
                    $d = 'themes';
                } elseif (stripos($filesget, '/modules/')) {
                    $d = 'modules';
                }

                preg_match_all("'(?:__|__e)\(([\",\'].*?[\",\'])\)'s", $filegetin, $matches);
                if (checkArraySize($matches) && isset($matches[1]) && checkArraySize($matches[1])) {
                    foreach ($matches[1] as $matche) {
                        $matche = trim($matche);
                        if ($matche[0] == "'") {
                            $matche = rtrim($matche, "'");
                            $matche = ltrim($matche, "'");
                        } elseif ($matche[0] == '"') {
                            $matche = rtrim($matche, '"');
                            $matche = ltrim($matche, '"');
                        }
                        preg_match_all("/.*\/" . $d . "\/([a-zA-Z0-9_-]*)\/.*/", $filesget, $dL);
                        if (checkArraySize($dL) && isset($dL[1]) && checkArraySize($dL[1]) && isset($dL[1][0]) && $dL[1][0] != '') {
                            $destLang = $dL[1][0];
                        }
                        if (strlen($matche) > 0) {
                            $this->finalResult[$d][$destLang][]['var'] = strtolower($matche);
                        }
                    }
                }
            }
        }
    }

    private function insertTranslate($lang, $type, $dest, $oldTranslate)
    {
        if (is_array($lang)) {
            foreach ($lang as $key => $val) {
                $exKey = $oldTranslate ? trim($key) : strtolower(trim($val));
                $exVal = $oldTranslate ? strtolower(trim($val)) : "";
                if (isset(self::$translate[$exKey]) && self::$translate[$exKey] != "") {
                    $this->exportFiles[$type][$dest][$exKey] = self::$translate[$exKey];
                } else {
                    if ($exVal == '' && self::getLang() == "en") {
                        $this->exportFiles[$type][$dest][$exKey] = $exKey;
                    } else {
                        $this->exportFiles[$type][$dest][$exKey] = $exVal;
                    }
                }
            }
        }
    }

    private function insertNewWordToTranslate()
    {
        foreach ($this->finalResult as $type => $result) {
            if (sizeof($result) >= 1) {
                foreach ($result as $dest => $value) {
                    foreach ($value as $k => $v) {
                        $this->insertTranslate($v, $type, $dest, false);
                    }
                }
            }
        }

        $old = Database::select("jk_translate", '*');
        foreach ($old as $vvv) {
            Database::update('jk_translate', ['var' => strtolower($vvv['var'])], ['id' => $vvv['id']]);
        }
    }

    private function showNotify()
    {
        echo alert([
            "type" => "success",
            "elem" => "span",
            "text" => __("updated") . ' , ' . __("please wait")
        ]);
    }

    public function updateTranslate($dest = null, $type = null, $exportFiles = false)
    {
        $dest = is_null($dest) ? 'main' : $dest;
        $type = is_null($type) ? 'module' : $type;
        $exFiles = $exportFiles ? $exportFiles : $this->exportFiles;
        self::updateTranslateTable($dest, $type, $exFiles);
    }

    public function deleteUnExistStrings()
    {
        $notFound = [];
        if (checkArraySize(self::$translate)) {
            foreach (self::$translate as $translateKey => $translateValue) {
                $found = false;
                foreach ($this->exportFiles as $exportFileKey => $exportFileVal) {
                    if (checkArraySize($exportFileVal)) {
                        foreach ($exportFileVal as $item => $value) {
                            if (checkArraySize($value)) {
                                if (array_key_exists($translateKey, $value)) {
                                    $found = true;
                                    break;
                                }
                            }
                        }
                    }

                    if ($found) {
                        break;
                    }
                }
                if (!$found) {
                    $notFound[] = $translateKey;
                }
            }
        }

        if (checkArraySize($notFound)) {
            foreach ($notFound as $value) {
                Database::delete('jk_translate', ['var' => $value]);
            }
        }
    }

    public static function updateTranslateTable($dest = null, $type = null, $exportFiles = false)
    {
        $dest = is_null($dest) ? 'main' : $dest;
        $type = is_null($type) ? 'module' : $type;
        $database = Database::connect();

        $selectOld = $database->select("jk_translate", ['var', 'text'], [
            "AND" => [
                "lang" => self::getLang(),
                "dest" => $dest,
                "type" => $type,
            ]
        ]);
        $vars = [];
        $texts = [];
        $varTexts = [];
        if (!empty($selectOld)) {
            $vars = array_column($selectOld, 'var');
            $texts = array_column($selectOld, 'text');
            $varTexts = array_combine($vars, $texts);
        }

        foreach ($exportFiles as $key => $val) {
            if (strpos($key, '$') === false) {
                $key = strtolower(trim($key));
                if (!in_array($key, $vars)) {
                    $insert = $database->insert("jk_translate", [
                        "var" => strtolower(trim($key)),
                        "text" => trim($val),
                        "lang" => self::getLang(),
                        "dest" => $dest,
                        "type" => $type,
                    ]);
                } else {
                    if (trim($varTexts[$key]) == "") {
                        $database->update("jk_translate", [
                            "text" => trim($val),
                        ], [
                            "var" => $key,
                            "lang" => self::getLang(),
                            "dest" => $dest,
                            "type" => $type,
                        ]);
                    }
                }
            }
        }
    }


    public static function TR($module = null, $details = false, $lang = null)
    {
        if ($lang) {
            self::setLang($lang);
        }
        if ($module && self::$module != $module) {
            self::$instance = null;
        } elseif (!$module && !is_null(self::$module)) {
            self::$instance = null;
        }
        if ($details) {
            self::$instance = null;
        }
        if (self::$instance == null) {

            self::$instance = new Translate($module, $details);
        }
        return self::$instance;
    }

    public function getTranslate()
    {
        return self::$translate;
    }

    public function createVueTranslateFiles()
    {
        global $translate;
        $out = "export default {\n";
        $this->initTranslate();
        foreach ($translate as $key => $val) {
            $key = str_replace("'", "\'", $key);
            $key = str_replace('"', '\"', $key);
            $val = str_replace('"', '\"', $val);
            $val = str_replace('"', '\"', $val);
            $out .= "\t'" . $key . "': '" . $val . "',\n";
        }
        $out .= "\n}";

        $lanFile = JK_SITE_PATH() . "config" . DS() . "vue" . DS() . "langs" . DS() . self::getLang() . ".js";
        if (FS::isExistIsFileIsReadable($lanFile)) {
            unlink($lanFile);
        }
        FS::filePutContent($lanFile, $out);

        $out = "export default {\n";
        foreach ($translate as $key => $val) {
            $key = str_replace("'", "\'", $key);
            $key = str_replace('"', '\"', $key);
            $out .= "\t'" . $key . "': '" . $key . "',\n";
        }
        $out .= "\n}";

        $enFile = JK_SITE_PATH() . "config" . DS() . "vue" . DS() . "langs" . DS() . "en.js";
        if (FS::isExistIsFileIsReadable($enFile)) {
            unlink($enFile);
        }
        FS::filePutContent($enFile, $out);

    }

    public static function setLang($lang)
    {
        self::$lang = $lang;
    }

    public static function getLang()
    {
        return is_null(self::$lang) ? JK_LANG() : self::$lang;
    }

}