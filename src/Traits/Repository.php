<?php


namespace Joonika\Traits;


use Joonika\FS;

trait Repository
{
    
    public static function getRequiredModulesForInitSystem($databaseInfo = null)
    {
        $database = $databaseInfo ? \Joonika\Database::connect($databaseInfo) : \Joonika\Database::connect();
        return $database->select("modules", "*",
            [
                "AND" => [
                    'required' => 1,
                    'status' => 'active'
                ],
                "GROUP" => 'name'
            ]
        );
    }

    public static function readComposerConfigsForRecognizeJoonikaVersion($databaseInfo = null)
    {
        $chekComposerFile = self::checkHasComposerConfigAndJoonikaVersion();
        if ($chekComposerFile['code'] == 200) {
            $database = $databaseInfo ? \Joonika\Database::connect($databaseInfo) : \Joonika\Database::connect();
            $versionInfo = $database->get("modules", '*', [
                "AND" => [
                    "name" => "Joonika",
                    "version" => $chekComposerFile['data']['joonikaVersion'],
                    "status" => "active",
                    "public" => "1",
                    "type" => "core"
                ]
            ]);
            if ($versionInfo) {
                $database->insert('module_installed_self', [
                    "moduleId" => $versionInfo['id'],
                    "name" => $versionInfo['name'],
                    "type" => $versionInfo['type'],
                    "date" => now(),
                    "status" => $versionInfo['status'],
                    "tables" => $versionInfo['tables'],
                    "info" => json_encode($versionInfo, JSON_UNESCAPED_UNICODE),
                ]);
                return ["code" => 200, "msg" => __("done")];
            } else {
                return ["code" => 400, "msg" => __("joonika version in composer is not valid")];
            }
        }
    }

    public static function checkHasComposerConfigAndJoonikaVersion($version = null)
    {
        $composerPath = JK_SITE_PATH() . "composer.json";
        if (FS::isExistIsFileIsReadable($composerPath)) {
            $composerInfo = json_decode(FS::fileGetContent($composerPath));
            if (is_object($composerInfo)) {
                $joonikaName = "joonika/joonika";
                $joonikaVersion = (!empty($composerInfo->require) && !empty($composerInfo->require->$joonikaName)) ? $composerInfo->require->$joonikaName : null;
                if ($joonikaVersion) {
                    if ($version) {
                        if ($joonikaVersion == $version) {
                            return ["code" => 200, 'msg' => "ok"];
                        } else {
                            return ["code" => 400, 'msg' => "this version is not valid"];
                        }
                    } else {
                        return ["code" => 200, 'msg' => "ok", "data" => ['joonikaVersion' => $joonikaVersion]];
                    }
                }
            } else {
                return ["code" => 400, 'msg' => "composer file is not valid"];
            }
        } else {
            return ["code" => 400, 'msg' => "composer not found"];
        }
    }

    public static function searchForSingleRepository($repository, $type, $version = null, $databaseInfo = null)
    {
        $database = $databaseInfo ? \Joonika\Database::connect($databaseInfo) : \Joonika\Database::connect();
        $conditions = [
            "AND" => [
                'name' => 1,
                'status' => 'active',
                'type' => $type,
                'public' => 1,
            ],
        ];
        if ($version) {
            $conditions['AND']["version"] = $version;
        }
        return $database->select("modules", "*", $conditions);
    }

    public static function checkRepositoryUrlAndUploadIt($repository)
    {
        $fileUrl = \Joonika\Upload::getLink($repository['fileId']);
        $file_name = JK_SITE_PATH() . "storage" . "/files/tmp/version_{$repository['versionInt']}_" . basename($fileUrl);
        if (pathinfo($file_name)['extension'] == "zip") {
            if (file_put_contents($file_name, file_get_contents($fileUrl))) {
                return ["code" => 200, "msg" => __("success"), "data" => ["fileUrl" => $fileUrl, "file_name" => $file_name]];
            } else {
                return ["code" => 400, "msg" => __("file downloading failed. please try again")];
            }
        } else {
            return ["code" => 400, "msg" => __("invalid file format ,the acceptable format is zip")];
        }
    }

    public static function extractDownloadedFile($repository, $fileName)
    {
        if (FS::isExistIsFile($fileName)) {
            $destPath = JK_SITE_PATH();
            if ($repository['type'] == "module") {
                $destPath .= "modules/";
            } else {
                $destPath .= "themes/";
            }
            FS::extractZipFile($fileName, $destPath);
            return ["code" => 200, 'msg' => __("success")];
        } else {
            return ["code" => 400, 'msg' => __("file not found on your server!")];
        }
    }

    public static function runRepSqlScripts($scripts, $databaseInfo = null)
    {
        $database = $databaseInfo ? \Joonika\Database::connect($databaseInfo) : \Joonika\Database::connect();
        $database->query($scripts)->execute();
    }

    public static function installedRepositoryAlready($repository, $databaseInfo = null)
    {
        $database = $databaseInfo ? \Joonika\Database::connect($databaseInfo) : \Joonika\Database::connect();
        $has = $database->get('module_installed_self', '*', [
            "AND" => [
                'name' => $repository['name'],
                'type' => $repository['type'],
            ]
        ]);
        if ($has) {
            $has['info'] = json_decode($has['info'], true);
            return $has;
        } else {
            return false;
        }
    }

    public static function updateSingleRepository($installed, $repository, $databaseInfo = null)
    {
        self::runRepSqlScripts($repository['sqlScript'], $databaseInfo);
        $status = ($installed['status'] == "active") ? "active" : "deActive";

        if ($installed['info']['required']) {
            $status = "active";
        }

        $tables = $repository['tables'] ? $repository['tables'] : null;
        if ($tables && is_json($tables)) {
            $tables = json_decode($tables, true);
            if (checkArraySize($tables)) {
                $oldTables = $installed['tables'] ? $installed['tables'] : null;
                if ($oldTables && is_json($oldTables)) {
                    $oldTables = json_decode($oldTables, true);
                    foreach ($oldTables as $table) {
                        $tables[] = $table;
                    }
                }
            }
            $tables = json_encode($tables, JSON_UNESCAPED_UNICODE);
        }
        $database = $databaseInfo ? \Joonika\Database::connect($databaseInfo) : \Joonika\Database::connect();
        $database->update('module_installed_self', [
            "moduleId" => $repository['id'],
            "name" => $repository['name'],
            "date" => now(),
            "status" => $status,
            "type" => $repository['type'],
            "tables" => $tables,
            "info" => json_encode($repository, JSON_UNESCAPED_UNICODE),
        ], ['id' => $installed['id']]);
        return [
            'code' => 200,
            'msg' => __("success"),
            'data' => [
                'continueText' => ($status != "active") ? __("please active it") : ""
            ]
        ];
    }

    public static function installSingleRepository($repository, $databaseInfo = null)
    {
        self::runRepSqlScripts($repository['sqlScript'], $databaseInfo);
        $status = $repository['required'] ? "active" : "deActive";
        $database = $databaseInfo ? \Joonika\Database::connect($databaseInfo) : \Joonika\Database::connect();
        $database->insert('module_installed_self', [
            "moduleId" => $repository['id'],
            "name" => $repository['name'],
            "type" => $repository['type'],
            "date" => now(),
            "status" => $status,
            "tables" => $repository['tables'],
            "info" => json_encode($repository, JSON_UNESCAPED_UNICODE),
        ]);
        return [
            'code' => 200,
            'msg' => __("success"),
            'data' => [
                'continueText' => ($status != "active") ? __("please active it") : ""
            ]
        ];
    }

    public static function installRepository($repository, $databaseInfo = null)
    {
        if ($repository) {
            $continueText = null;
            $continue = true;
            $text = __("module installed successfully");

            $checkRep = self::checkRepositoryUrlAndUploadIt($repository);
            if ($checkRep['code'] == 200) {
                $extractFile = self::extractDownloadedFile($repository, $checkRep['data']['file_name']);
                if ($extractFile['code'] == 200) {
                    $installedAlready = self::installedRepositoryAlready($repository, $databaseInfo);
                    if ($installedAlready) {
                        $update = self::updateSingleRepository($installedAlready, $repository, $databaseInfo);
                        if ($update['code'] == 200) {
                            $continueText = $update['data']['continueText'];
                        }
                    } else {
                        $install = self::installSingleRepository($repository, $databaseInfo);
                        if ($install['code'] == 200) {
                            $continueText = $install['data']['continueText'];
                        }
                    }
                } else {
                    $text = $extractFile['msg'];
                    $continue = false;
                }
            } else {
                $text = $checkRep['msg'];
                $continue = false;
            }
            if ($continueText) {
                $text .= "<br>" . $continueText;
            }
            return ["code" => $continue ? 200 : 400, 'msg' => $text];
        }
    }

    public static function updateRepository($repository, $databaseInfo = null, $mode = 'update')
    {
        $continue = true;
        $isJoonika = $repository['type'] == "core" ? 1 : 0;
        $updateByComposer = ($isJoonika && !empty($_POST['c']) && $_POST['c']) ? 1 : 0;
        if ($repository) {
            $database = $databaseInfo ? \Joonika\Database::connect($databaseInfo) : \Joonika\Database::connect();
            $repositoies = $database->select('modules', '*', [
                'name' => $repository['name']
            ]);
            $continueText = null;
            $type = $repository['type'] == "core" ? "Joonika" : $repository['type'];
            $text = sprintf(__("%s update successfully"), $type);
            $installedAlready = self::installedRepositoryAlready($repository, $databaseInfo);
            if (checkArraySize($repositoies) && sizeof($repositoies) > 1) {
                $repositoies = $database->select('modules', '*', [
                    "AND" => [
                        'name' => $repository['name'],
                        'status' => "active",
                        'public' => "1",
                        'type' => "module",
                        "versionInt[<>]" => [$installedAlready['info']['versionInt'], $repository['versionInt']]
                    ]
                ]);
                for ($i = 0; $i < sizeof($repositoies); $i++) {
                    if ($installedAlready['info']['versionInt'] == $repositoies[$i]['versionInt']) {
                        unset($repositoies[$i]);
                        break;
                    }
                }
                $repositoies = array_values($repositoies);

                for ($i = 0; $i < sizeof($repositoies); $i++) {
                    if ($repositoies[$i]['updateRequired'] && $repositoies[$i]['id'] != $repository['id']) {
                        $continue = false;
                        $text = sprintf(__("you want to update module to version %s but for this update version %s required.please update to version %s first"), $repository['version'], $repositoies[$i]['version'], $repositoies[$i]['version']);
                        break;
                    }
                }
            }

            if ($continue) {
                if ($updateByComposer && $repository['updateByComposer']) {
                    $editComposerFile = self::editComposerFile($repository);
                    if ($editComposerFile['code'] == 200) {
                        $composerUpdate = self::runComposerUpdate($repository);
                        if ($composerUpdate['code'] == 200) {
                            if ($installedAlready) {
                                $update = self::updateSingleRepository($installedAlready, $repository, $databaseInfo);
                            } else {
                                $install = self::installSingleRepository($repository, $databaseInfo);
                            }
                            $composerUpdate['data']['mode'] = $mode;
                            return ["code" => 200, 'msg' => $composerUpdate['msg'], 'data' => $composerUpdate['data']];
                        } else {
                            $composerUpdate['data']['mode'] = $mode;
                            return ["code" => 400, 'msg' => $composerUpdate['msg'], 'data' => $composerUpdate['data']];
                        }
                    } else {
                        return ["code" => 400, 'msg' => $editComposerFile['msg']];
                    }
                } else {
                    $checkRep = self::checkRepositoryUrlAndUploadIt($repository);
                    if ($checkRep['code'] == 200) {
                        if ($repository['type'] == "core") {
                            $versionName = basename($checkRep['data']['file_name']);
                            $versionPath = JK_SITE_PATH() . "storage/files/tmp";
                            $destPath = JK_SITE_PATH() . "vendor";

                            if (FS::isExistIsFile($versionPath . DS() . $versionName)) {
                                ob_start();
                                $command = "unzip -o " . $versionPath . DS() . $versionName . " -d " . $destPath . " ;";
                                $command .= ' 2>&1';
                                $result = shell_exec($command);
                                $result_captured = ob_get_contents();
                                ob_end_clean();

                                if ($installedAlready) {
                                    $r = self::updateSingleRepository($installedAlready, $repository, $databaseInfo);
                                } else {
                                    $r = self::installSingleRepository($repository, $databaseInfo);
                                }
                            } else {
                                $continueText = __("error,please try again");
                                $continue = false;
                            }
                        } else {
                            $extractFile = self::extractDownloadedFile($repository, $checkRep['data']['file_name']);
                            if ($extractFile['code'] == 200) {
                                $update = self::updateSingleRepository($installedAlready, $repository, $databaseInfo);
                                if ($update['code'] == 200) {
                                    $continueText = $update['data']['continueText'];
                                }
                            } else {
                                $text = $extractFile['msg'];
                                $continue = false;
                            }
                        }
                    } else {
                        $text = $checkRep['msg'];
                        $continue = false;
                    }

                    if ($continueText) {
                        $text .= "<br>" . $continueText;
                    }
                    return ["code" => $continue ? 200 : 400, 'msg' => $text];
                }
            } else {
                return ["code" => 400, 'msg' => $text];
            }
        }
    }

    public static function refreshRepository($repository, $databaseInfo = null)
    {
        return self::updateRepository($repository, $databaseInfo, 'refresh');
    }

    public static function downgradeRepository($repository, $databaseInfo = null)
    {
        if ($repository) {
            $continue = true;
            $continueText = null;
            $text = __("module downgrade successfully");
            $installedAlready = self::installedRepositoryAlready($repository, $databaseInfo);
            if ($continue) {
                if ($installedAlready) {
                    $checkRep = self::checkRepositoryUrlAndUploadIt($repository);
                    if ($checkRep['code'] == 200) {
                        $extractFile = self::extractDownloadedFile($repository, $checkRep['data']['file_name']);
                        if ($extractFile['code'] == 200) {
                            $update = self::updateSingleRepository($installedAlready, $repository, $databaseInfo);
                            if ($update['code'] == 200) {
                                $continueText = $update['data']['continueText'];
                            }
                        } else {
                            $text = $extractFile['msg'];
                            $continue = false;
                        }
                    } else {
                        $text = $checkRep['msg'];
                        $continue = false;
                    }
                }
                if ($continueText) {
                    $text .= "<br>" . $continueText;
                }
                return ["code" => $continue ? 200 : 400, 'msg' => $text];
            } else {
                return ["code" => 400, 'msg' => $text];
            }
        }
    }

    public static function removeRepository($repository, $databaseInfo = null)
    {
        if ($repository) {
            $database = $databaseInfo ? \Joonika\Database::connect($databaseInfo) : \Joonika\Database::connect();
            $installedAlready = $database->get('module_installed_self', '*', ['moduleId' => $repository['id']]);
            if ($installedAlready) {
                $database->delete('module_installed_self', ['id' => $installedAlready['id']]);
                return ["code" => 200, 'msg' => __("module removed successfully")];
            }
        }
    }

    public static function activeRepository($repository, $databaseInfo = null)
    {
        if ($repository) {
            $database = $databaseInfo ? \Joonika\Database::connect($databaseInfo) : \Joonika\Database::connect();
            $installedAlready = $database->get('module_installed_self', '*', ['name' => $repository['name']]);
            if ($installedAlready) {
                $database->update('module_installed_self', [
                    "status" => "active"
                ], ['id' => $installedAlready['id']]);
                return ["code" => 200, 'msg' => __("module active successfully")];
            }
        }
    }

    public static function deActiveRepository($repository, $databaseInfo = null)
    {
        if ($repository) {
            $database = $databaseInfo ? \Joonika\Database::connect($databaseInfo) : \Joonika\Database::connect();
            $installedAlready = \Joonika\Database::get('module_installed_self', '*', ['name' => $repository['name']]);
            if ($installedAlready) {
                $database->update('module_installed_self', [
                    "status" => "deActive"
                ], ['id' => $installedAlready['id']]);
                return ["code" => 200, 'msg' => __("module disable successfully")];
            }
        }
    }

    public static function editComposerFile($repository)
    {
        $composerPath = JK_SITE_PATH() . "composer.json";
        if (FS::isExistIsFileIsReadable($composerPath)) {
            $composer = json_decode(FS::fileGetContent($composerPath));
            if (is_object($composer)) {
                $joonika = "joonika/joonika";
                if (isset($composer->require->$joonika)) {
//                    $composer->require->$joonika = $repository['version'];
                }
                $string = json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                FS::filePutContent($composerPath, $string);
                return ['code' => 200, 'msg' => __("composer file edited successfully")];
            } else {
                return ['code' => 400, 'msg' => __("composer file is not valid")];
            }
        } else {
            return ['code' => 400, 'msg' => __("composer file not found")];
        }
    }

    public static function runComposerUpdate($repository)
    {

        ob_start();
        $command = "cd ..;";
        $command .= "chmod 777  composer.lock ;";
        if (isset($_POST['ua']) && $_POST['ua']) {
            $command .= "composer update joonika/joonika --no-interaction --ignore-platform-reqs ";
        } else {
            $command .= "composer update joonika/joonika --no-interaction";
        }
        $command .= ' 2>&1';
        $output = shell_exec($command);
        $output_captured = ob_get_contents();
        ob_end_clean();

        $phpWarnings = [];
        preg_match_all("/.*Warning.*/", $output, $phpWarnings);

        $problems = [];
        preg_match_all("/.*- .*/", $output, $problems);


        $updatedMatches = [];
        preg_match_all("/Lock file operations:.*install,.*update/", $output, $updatedMatches);
        $updated = checkArraySize($updatedMatches[0]) ? true : false;


        $updatedJoonikaMatches = [];
        preg_match_all("/.*-.?Upgrading joonika\/joonika.*/", $output, $updatedJoonikaMatches);
        $updatedJoonika = checkArraySize($updatedJoonikaMatches[0]) ? true : false;

        if ($updatedJoonika && $updated) {
            return [
                'code' => 200,
                'msg' => __('joonika updated successfully') . " :)",
                'data' => [
                    'output' => $output,
                    'repository' => $repository,
                    'update' => $updated,
                    'updatedJoonika' => $updatedJoonika,
                    'updatedJoonikaMsg' => $updatedJoonikaMatches[0][0],
                    'updateByComposer' => true
                ]
            ];
        } else {
            return [
                'code' => 400,
                'msg' => __('update failed'),
                'data' => [
                    'problems' => $problems,
                    'warnings' => $phpWarnings,
                    'output' => $output,
                    'repository' => $repository,
                    'updateByComposer' => true
                ]
            ];
        }
    }
}