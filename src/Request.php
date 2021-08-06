<?php

namespace Joonika;

use Joonika\Validator\validator;

class Request extends RequestScaffold
{
    public $validateAlert = [];

    public function Input($input, $safe = true)
    {
        return isset($this->post[$input]) ? is_array($this->post[$input]) ? $this->post[$input] : htmlspecialchars(stripslashes(trim($this->post[$input]))) : null;
    }

    public function all()
    {
        return $this->post;
    }

    public function queryStrings($field = null)
    {
        if (!$field) {
            return $this->get;
        } else {
            return isset($this->get[$field]) ? is_array($this->get[$field]) ? $this->get[$field] : htmlspecialchars(stripslashes(trim($this->get[$field]))) : null;
        }
    }

    public function hasQueryString($field)
    {
        try {
            if (is_array($this->get)) {
                if (array_key_exists($field, $this->get)) {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Not variable send.');
        }

    }

    public function fillQueryString($field)
    {
        if (is_array($this->get)) {
            if (array_key_exists($field, $this->get) && isset($this->get[$field]) && !is_null($this->get[$field])) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new \Exception('Not variable send.');
        }

    }

    public function requestMethod()
    {

        return $this->request_method;
    }

    public function isMethod($method)
    {
        if ($this->requestMethod() == strtoupper($method)) {
            return true;
        } else {
            return false;
        }
    }

    public function has($field)
    {
        if (is_array($this->post)) {
            if (array_key_exists($field, $this->post)) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new \Exception('Not variable send.');
        }
    }

    public function fill($field)
    {
        if (is_array($this->post)) {
            if (array_key_exists($field, $this->post) && isset($this->post[$field]) && !is_null($this->post[$field])) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new \Exception('Not variable send.');
        }
    }

    public function ip()
    {
        $ip = new RemoteAddress();
        return $ip->getIpAddress();
    }

    public function scriptName()
    {
        return $this->get('SCRIPT_NAME');
    }

    public function scriptFilename()
    {
        return $this->get('SCRIPT_FILENAME');
    }

    public function documentRoot()
    {
        return $this->get('DOCUMENT_ROOT');
    }

    public function remotePort()
    {
        return $this->get('REMOTE_PORT');
    }

    public function url()
    {
        return trim(explode('?', substr(trim($this->get('REQUEST_URI'), '/'), 3))[0], '/');
        return rtrim(preg_replace('/\?.*/', '', $this->getUrl(false, false, false)), '/');
    }

    public function fullUrl()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUrl(false, false, true)), '/');
    }

    public function urlWithProtocolAndQueryStrings()
    {
        return rtrim($this->getUrl(true, true, true));
    }

    public function urlWithProtocol()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUrl(true, false, true)), '/');
    }

    public function fullUrlWithQueryStrings()
    {
        return rtrim($this->getUrl(false, true, true));
    }

    public function protocol()
    {
        return strtolower(explode('/', $this->get('SERVER_PROTOCOL'))[0]);
    }

    public function serverName()
    {
        return strtolower($this->get('SERVER_NAME'));
    }

    public function getUrl($protocol, $queryString, $uri)
    {

        $url = '';
        if ($protocol) {
            $url .= $this->protocol() . "://";
        }

        $url .= $this->get('HTTP_HOST') . "/";
        if ($uri) {
            $request_uri = preg_replace('/^\//', '', explode('?', $this->get('REQUEST_URI'))[0]);
            $url .= $request_uri;
        }

        if ($queryString) {
            $queryExplode = explode('?', $this->server['REQUEST_URI']);
            if (array_key_exists('REQUEST_URI', $this->server) && sizeof($queryExplode) == 2) {
                $url .= "?" . $queryExplode[1];
            }
        }

        return $url;
    }

    public function get($input)
    {
        if ($this->isset($input)) {
            return $this->server[$input];
        }
    }

    public function isset($input)
    {
        if (isset($this->server[$input])) {
            return true;
        } else {
            return false;
        }
    }


    public function segments()
    {
        $s = [];
        $segments = explode("/", $_SERVER['REQUEST_URI']);
        foreach ($segments as $segment) {
            if (stripos($segment, '?') == false) {
                $s[] = $segment;
            } else {
                $part = explode('?', $segment);
                $s[] = $part[0];
                $queries = explode('&', $part[1]);
                if (sizeof($queries) > 0) {
                    foreach ($queries as $query) {
                        $query = explode('=', $query);
                        if (isset($query[1])) {
                            $s['queries'][$query[0]] = $query[1];
                        }
                    }
                }

            }
        }
        return $s;
    }

    public function segment($i)
    {
        if (isset($this->segments()[$i])) {
            return $this->segments()[$i];
        }
    }

    public function isAjax()
    {
        return $this->isset('HTTP_X_REQUESTED_WITH') && $this->get('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    public function connection()
    {
        return $this->get('HTTP_CONNECTION');
    }

    public function headers($headerName = null, $key = null)
    {
        if ($headerName) {
            foreach ($this->headers as $k => $header) {
                if (strtolower($k) == strtolower($headerName)) {
                    return $header;
                };
            }
            switch ($headerName) {
                case "Cookie":
                    $result = [];
                    $headers = explode(';', $this->headers[$headerName]);
                    foreach ($headers as $header) {
                        $h = explode('=', $header);
                        $result[trim($h[0])] = trim($h[1]);
                    }
                    if ($key) {
                        return $result[$key];
                    } else {
                        return $result;
                    }
                    break;
                case "Accept-Encoding":
                    $headers = explode(',', $this->headers[$headerName]);
                    return $headers;
                    break;
                case "Accept-Language":
                    $headers = explode(',', $this->headers[$headerName]);
                    return $headers;
                    break;
                case "Accept":
                    $headers = explode(',', $this->headers[$headerName]);
                    return $headers;
                    break;
                case "User-Agent":
                    return $this->headers[$headerName];
                    break;
                case "Upgrade-Insecure-Requests":
                    return $this->headers[$headerName];
                    break;
                case "Cache-Control":
                    $result = [];
                    $headers = explode(';', $this->headers[$headerName]);
                    foreach ($headers as $header) {
                        $h = explode('=', $header);
                        $result[trim($h[0])] = trim($h[1]);
                    }
                    if ($key) {
                        return $result[$key];
                    } else {
                        return $result;
                    }
                    break;
                case "Connection":
                    return $this->headers[$headerName];
                    break;
                case "Host":
                    return $this->headers[$headerName];
                    break;
                default :
                    $result = [];
                    if (isset($this->headers[$headerName])) {
                        $headers = explode(';', $this->headers[$headerName]);
                        if (sizeof($headers) > 1) {
                            foreach ($headers as $header) {
                                $h = explode('=', $header);
                                $result[trim($h[0])] = trim($h[1]);
                            }
                            if ($key) {
                                return $result[$key];
                            } else {
                                return $result;
                            }
                        } else {
                            $h = explode('=', $headers[0]);
                            if (sizeof($h) > 1) {
                                if ($key) {
                                    $res[$h[0]] = $h[1];
                                    return $res[$key];
                                }
                                return implode('=', $h);
                            } else {
                                return $h[0];
                            }
                        }
                    }
                    break;
            }
            return;
        } else {
            return $this->headers;
        }
    }

    public function hasHeader($headerName)
    {
        $status = false;
        foreach ($this->headers as $k => $header) {
            if (strtolower($k) == strtolower($headerName)) {
                $status = true;
            };
        }
        return $status;
    }

    public function back()
    {
        $url = $_SERVER['HTTP_REFERER'];
        redirect_to($url);
    }

    public function hasFile($name)
    {
        if (checkArraySize($this->files) && isset($this->files[$name])) {
            return true;
        } else {
            return false;
        }
    }

    public function files($name)
    {
        if ($this->hasFile($name)) {
            return $this->files[$name];
        } else {
            return false;
        }
    }

    private function uploadSingleFile($file, $option)
    {
        if ($this->hasFile($file)) {
            $fileInfo = $this->files($file);
            $allowMimeTypes = is_null($option['mimeTypes']) ? $this->allowMimeType() : $option['mimeTypes'];
            $allowSize = is_null($option['size']) ? $this->allowFileSize() : $option['size'];
            $fileSize = ($fileInfo['size'] / 1024) / 1024;
            if ($fileSize > $allowSize) {
                $array = [
                    "status" => 'failed',
                    'name' => $fileInfo['name'],
                    'description' => __("maximum size of file is :") . " " . $allowSize . " " . __("MB") . " " . __("your file size is :") . " " . round($fileSize, 4) . " " . __("MB"),
                ];
                $this->uploadFilesResult[] = $array;
                return;
            }

            if (!in_array($fileInfo['type'], $allowMimeTypes)) {
                $array = [
                    "status" => 'failed',
                    'name' => $fileInfo['name'],
                    'description' => __("mime type is not allow"),
                ];
                $this->uploadFilesResult[] = $array;
                return;
            }

            $inerted_id = 0;
            if ($option['module'] != "") {
                $folder = $option['module'];
            } else {
                $folder = null;
            }
            if ($option['date'] == true) {
                $date = true;
            } else {
                $date = false;
            }
            if ($option['module'] != "") {
                $module = $option['module'];
            } else {
                $module = null;
            }

            $upload_dir = Upload::upload_folder($folder, $date);

            $uploadPath = JK_SITE_PATH() . $upload_dir . DS();

            $mainFile = $uploadPath . $fileInfo['name'];
            $handle = new \Joonika\Uploads($fileInfo, 'fa_IR');
            $handle->png_compression = '80';
            $handle->jpeg_quality = '80';
            if ($handle->uploaded) {
                $handle->Process($uploadPath);
                if ($handle->processed) {
                    $rowup = $upload_dir . $handle->file_dst_name;
                    Database::connect()->insert('jk_uploads', [
                        "file" => $rowup,
                        "name" => $handle->file_dst_name,
                        "creatorID" => $option['creatorID'],
                        "folder" => $upload_dir,
                        "datetime" => now(),
                        "mime" => $handle->file_src_mime,
                        "parent" => 0,
                        "source" => $option['source'],
                        "module" => $module,
                    ]);
                    $inerted_id = Database::connect()->id();
                    $thMaker = true;
                    if ($option['thMaker'] == 0) {
                        $thMaker = false;
                    }
                    if ($handle->file_is_image && $thMaker) {
                        $ths = Upload::getThumbnails();
                        if (sizeof($ths) >= 1) {
                            $irp = 1;
                            foreach ($ths as $th) {
                                $image_x = $th['w'];
                                $image_y = $th['h'];
                                $handle_th = new \Joonika\Upload\upload($fileInfo, JK_LANG_LOCALE());
                                $handle_th->mime_check = true;
                                $handle_th->allowed = array('image/*');
                                $handle_th->image_resize = true;
                                $handle_th->image_x = $image_x;
                                $handle_th->image_y = $image_y;
                                $handle_th->file_auto_rename = true;
                                $handle_th->file_name_body_add = '_th_' . $th['id'];
                                if ($handle_th->uploaded) {
                                    $handle_th->Process($uploadPath);
                                    if ($handle_th->processed) {
                                        $rowup2 = $upload_dir . $handle_th->file_dst_name;
                                        $handleInsert2 = Database::connect()->insert('jk_uploads', [
                                            "file" => $rowup2,
                                            "name" => $handle_th->file_dst_name,
                                            "creatorID" => $option['creatorID'],
                                            "folder" => $upload_dir,
                                            "datetime" => now(),
                                            "mime" => $handle_th->file_src_mime,
                                            "parent" => $inerted_id,
                                            "source" => $th['name'],
                                            "module" => $module,
                                        ]);
                                    }
                                }
                                $irp += 1;
                            }
                        }
                    }
                }
            }
            $retunparam = $inerted_id;
            $array = [
                "status" => 'success',
                'fileid' => $retunparam,
                'description' => __("file upload successful"),
            ];
            if ($option['return']) {
                if ($option['return'] == 'file') {
                    $array = [
                        'filename' => $rowup
                    ];
                } elseif ($option['return'] == 'both') {
                    $array = [
                        "status" => 'success',
                        'filename' => $rowup,
                        'fileid' => $retunparam,
                        'description' => __("file upload successful"),
                    ];
                }
            }
            $this->uploadFilesResult[] = $array;
        }
    }

    public function uploadFile($files, $options = [])
    {
        $option = [
            "module" => '',
            "size" => null,
            "mimeTypes" => null,
            "date" => true,
            "thMaker" => null,
            "return" => false,
            "creatorID" => $this->userId,
            "source" => "original",
        ];

        if (sizeof($option) >= 1) {
            foreach ($option as $key => $opt) {
                if (isset($options[$key])) {
                    $option[$key] = $options[$key];
                }
            }
        }
        if (is_array($files)) {
            foreach ($files as $file) {
                $this->uploadSingleFile($file, $option);
            }
        } else {
            $this->uploadSingleFile($files, $option);
        }
        if (checkArraySize($this->uploadFilesResult)) {
            if (sizeof($this->uploadFilesResult) == 1) {
                return $this->uploadFilesResult[0];
            } else {
                return $this->uploadFilesResult;
            }
        } else {
            return false;
        }
    }


    final public function validate(array $inputs, array $msg = null)
    {
        $validator = new validator($inputs);
        dd($validator);
    }
}