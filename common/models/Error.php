<?php

namespace app\models;

use Yii;

class Error {

    public $type;
    public $title;
    public $message;

    function __construct($type, $title, $message) {
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
    }

    public function getMessageByError() {
        $message = "";
        $title = Yii::t("exception", "Opps, Something went wrong.");
        switch ($this->type) {
            case 400:
                $title = Yii::t("exception", "Opps, Something went wrong.");
                $message = Yii::t("jslang", "Bad Request");
                break;
            case 401:
                $title = Yii::t("exception", "Opps, Something went wrong.");
                $message = Yii::t("jslang", "You must be authorized to view this_page");
                break;
            case 403:
                $title = Yii::t("exception", "Opps, Something went wrong.");
                $message = Yii::t("jslang", "Forbidden");
                break;
            case 404:
                $title = Yii::t("exception", "Opps, Something went wrong.");
                $message = Yii::t("exception", "We can not find the page you are looking for.");
                break;
            case 405:
                $title = Yii::t("exception", "Opps, Something went wrong.");
                $message = Yii::t("jslang", "Method not allowed");
                break;
            case 500:
                $title = Yii::t("exception", "Opps, Something went wrong.");
                $message = Yii::t("jslang", "The server encountered an error processing your request");
                break;
            case 501:
                $title = Yii::t("exception", "Opps, Something went wrong.");
                $message = Yii::t("jslang", "The requested method is not implemented");
                break;
            default :
                $title = Yii::t("exception", "Opps, Something went wrong.");
                $message = Yii::t("jslang", "Bad Request");
                break;
        }
        return self::putMessageContent($this->type, $title, $message);
    }

    public static function putMessageContent($type, $title, $message) {
        $message = "<div class='span5 number'>$type</div><div class='span7 details'><h3>$title</h3><p>$message</p></div>";
        return $message;
    }

}
