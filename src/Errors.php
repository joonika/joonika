<?php namespace Joonika;


use Joonika\EventListener\builtIn\Events\Error;

class Errors
{

    public static $REQ_BAD = 400;
    public static $REQ_SUCCESS = 200;
    public static $REQ_NO_CONTENT = 204;
    public static $REQ_UNAUTHORIZED = 401;
    public static $REQ_PAYMENT = 402;
    public static $REQ_FORBIDDEN = 403;
    public static $REQ_NOT_FOUND = 404;
    public static $REQ_TIME_OUT = 408;
    public static $REQ_GONE = 410;
    public static $REQ_TEAPOT = 418;
    public static $REQ_UPGRADE_REQUIRED = 426;
    public static $REQ_TOO_MANY_REQS = 429;
    public static $REQ_UNVALIABLE_FOR_LEGAL_RESON = 451;
    public static $REQ_INTERNAL_ERROR = 500;
    public static $REQ_NOT_IMPLEMENTED = 501;
    public static $REQ_BAD_GATEWAY = 502;
    public static $REQ_SERVICE_UNVALIBALE = 503;
    public static $REQ_GATEWAY_TIMAOUT = 504;
    public static $REQ_MOVED_PERMANETLY = 301;
    public static $REQ_FOUND = 302;

    public static function statusCodeMessage($code = 200)
    {
        $text = 'unknown http status code: ' . $code;
        switch ($code) {
            case 100:
                $text = 'Continue';
                break;
            case 101:
                $text = 'Switching Protocols';
                break;
            case 200:
                $text = 'OK';
                break;
            case 201:
                $text = 'Created';
                break;
            case 202:
                $text = 'Accepted';
                break;
            case 203:
                $text = 'Non-Authoritative Information';
                break;
            case 204:
                $text = 'No Content';
                break;
            case 205:
                $text = 'Reset Content';
                break;
            case 206:
                $text = 'Partial Content';
                break;
            case 300:
                $text = 'Multiple Choices';
                break;
            case 301:
                $text = 'Moved Permanently';
                break;
            case 302:
                $text = 'Moved Temporarily';
                break;
            case 303:
                $text = 'See Other';
                break;
            case 304:
                $text = 'Not Modified';
                break;
            case 305:
                $text = 'Use Proxy';
                break;
            case 400:
                $text = 'Bad Request';
                break;
            case 401:
                $text = 'Unauthorized';
                break;
            case 402:
                $text = 'Payment Required';
                break;
            case 403:
                $text = 'Forbidden';
                break;
            case 404:
                $text = 'Not Found';
                break;
            case 405:
                $text = 'Method Not Allowed';
                break;
            case 406:
                $text = 'Not Acceptable';
                break;
            case 407:
                $text = 'Proxy Authentication Required';
                break;
            case 408:
                $text = 'Request Time-out';
                break;
            case 409:
                $text = 'Conflict';
                break;
            case 410:
                $text = 'Gone';
                break;
            case 411:
                $text = 'Length Required';
                break;
            case 412:
                $text = 'Precondition Failed';
                break;
            case 413:
                $text = 'Request Entity Too Large';
                break;
            case 414:
                $text = 'Request-URI Too Large';
                break;
            case 415:
                $text = 'Unsupported Media Type';
                break;
            case 500:
                $text = 'Internal Server Error';
                break;
            case 501:
                $text = 'Not Implemented';
                break;
            case 502:
                $text = 'Bad Gateway';
                break;
            case 503:
                $text = 'Service Unavailable';
                break;
            case 504:
                $text = 'Gateway Time-out';
                break;
            case 505:
                $text = 'HTTP Version not supported';
                break;
            default:
                break;
        }
        return $text;
    }

    public static function setValidCode($code)
    {
        $txt = self::statusCodeMessage($code);
        if ($txt == 'unknown http status code: ' . $code) {
            return 400;
        }
        return $code;
    }


    public static function errorHandler($level, $message, $file, $line, $code = 0)
    {
        if (JK_APP_DEBUG() != true) {
            $log = JK_SITE_PATH() . 'storage/logs/' . date('Y-m-d') . '.txt';
            ini_set('error_log', $log);
            $msg = "===============\n";
            $msg .= "Error with level : {$level} \nFile : {$file} \nLine : {$line}\n";
            $msg .= $message . "\n";
            error_log($msg, 3, $log);
        }
        if (error_reporting() !== 0) {
            if (is_array($code)) {
                $code = 0;
            }
            throw new \ErrorException($message, $code, $level, $file, $line);
        }
    }

    public static function requestIsApi()
    {
        $api = false;
        $api = [];
        $url = null;
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        }
        if (checkArraySize($url) && isset($url[1]) && $url[1] == "api") {
            return true;
        } else {
            return false;
        }
    }

    public static function exceptionHandler($exception)
    {
        $isApi = self::requestIsApi();
        $code = $exception->getCode();
        if ($code == 0) {
            $code = 400;
        }
        $code = self::setValidCode($code);
        $request = [
            'url' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null,
            'body' => $_POST,
            'headers' => getallheaders()
        ];
        $data = [
            "class" => get_class($exception),
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine(),
            "trace" => $exception->getTraceAsString(),
            "code" => $exception->getCode(),
            "request" => $request
        ];
        if (!empty(JK_WEBSITE()['database']))
            $database = Database::connect();
        $errorDbId = '';

        if (!empty($database) && !in_array($code, [404, 403]) && $exception->getMessage() != 'file not found') {

            $file = $exception->getFile();
            $line = $exception->getLine();
            $message = $exception->getMessage();
            $datetime = now();
            $dupFound = $database->get("logs.errors_log", 'id', [
                "file" => $file,
                "line" => $line,
                "message" => $message,
                "status" => "new",
                "date" => date("Y-m-d"),
            ]);
            if ($dupFound) {
                $database->update('logs.errors_log', [
                    "lastOccurred" => $datetime,
                ], [
                    "id" => $dupFound
                ]);
                $errorDbId = $dupFound;
            } else {
                $database->insert('logs.errors_log', [
                    "datetime" => $datetime,
                    "date" => date("Y-m-d"),
                    "file" => $file,
                    "line" => $line,
                    "message" => $message,
                    "trace" => $exception->getTraceAsString(),
                    "userID" => JK_USERID(),
                    "lastOccurred" => $datetime,
                    "request" => json_encode($request, JSON_UNESCAPED_UNICODE),
                    "website" => trim(JK_DOMAIN_WOP(), '/'),
                ]);
                $errorDbId = $database->id();
            }

            $data['errorID'] = $errorDbId;
//            $errorEvent = new Error(['id' => $errorDbId, 'lastOccurred' => $datetime]);
//            $errorEvent->fire();
        }

        if ($isApi) {
            if (JK_APP_DEBUG() == true) {
                header('HTTP/1.1 ' . $code);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'errors' => [[
                        'message' => __("bad request"),
                        "data" => $data
                    ]],
                ], 256 | 128);
            } else {
                header('HTTP/1.1 ' . $code);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'errors' => [[
                        'message' => sprintf(__("your problem registered successfully,your problem number is %s"), $errorDbId),
                        "data" => $data
                    ]],
                ], 256 | 128);
            }
        } else {
            $title = __("error occurred");
            $message = '';
            if (!empty($exception->getMessage())) {
                $message = $exception->getMessage();
            }
            if (!in_array($code, [403, 404])) {
                $message .= '<div style="text-align: left">';
                $message .= "<p>" . __("error class name") . " : '" . get_class($exception) . "'</p>\n";
                $message .= "<p>" . __("error message") . " : '" . $exception->getMessage() . "'</p>\n";
                $message .= "<p>" . __("error in") . " : '" . $exception->getFile() . "'</p>\n";
                $message .= "<p>" . __("error line") . " : '" . $exception->getLine() . "'</p>\n";
                $r = self::jTraceEx($exception);
                $message .= "<p>" . __("error trace") . " : <div style='text-align: left;direction: ltr;word-wrap: break-word!important'>" . $r . "</div></p>\n";
                $message .= '</div>';
            }
            if (JK_APP_DEBUG() == true) {
//                ob_clean();
//                flush();
                templateRenderSimpleAlert($code, $title, $message);
                exit;
            } else {
                http_response_code($code);
                $log = JK_SITE_PATH() . 'storage/logs/' . date('Y-m-d') . '.txt';
                ini_set('error_log', $log);
                error_log($message);


                $msg = '';
                if (!empty($exception->getMessage())) {
                    $msg = $exception->getMessage();
                }
                $errorCode = '';
                if (!empty($data['errorID'])) {
                    $msg = sprintf(__("error occured, your problem registered with tracking number is %s"), $errorDbId);
                    $errorCode = $errorDbId;
                }
                ob_clean();
                flush();
                Redirect::code($code, $msg, $errorCode);
                exit;
            }
        }
    }

    static function jTraceEx($e, $seen = null)
    {
        $result = array();
        if (!$seen) $seen = array();
        $trace = $e->getTrace();
        $prev = $e->getPrevious();
        $file = $e->getFile();
        $line = $e->getLine();
        $i = 1;
        while (true) {
            $current = "$file:$line";
            $result[] = sprintf("#" . $i . " %s%s%s\n%s%s%s\n",
                    count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                    count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
                    count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
                    $line === null ? $file : "\n" . $file,
                    $line === null ? '' : ':',
                    $line === null ? '' : $line) . "<br/><br/>";
            if (is_array($seen))
                $seen[] = "$file:$line";
            if (!count($trace))
                break;
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
            $i += 1;
        }
        $result = join("\n", $result);
        if ($prev)
            $result .= "\n\n" . self::jTraceEx($prev, $seen);

        return $result;
    }

    public static function exceptionString(\Exception $exception, $trace = false)
    {
        $message = "FE: ";
        $message .= get_class($exception);
        $message .= " : " . $exception->getMessage();
        if ($trace) {
            $message .= " : trace: " . $exception->getTraceAsString();
        }
        $message .= " : in file '" . $exception->getFile() . "' on line " . $exception->getLine() . "";

        return $message;
    }

    public static function errorInfo($id, $column = '*')
    {
        $database = Database::connect();
        return $database->get("logs.errors_log", $column, [
            "id" => $id
        ]);
    }


}