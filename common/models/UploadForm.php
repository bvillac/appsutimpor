<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\models;
use yii\base\Model;
use yii\web\UploadedFile;
use yii\helpers\Url;

/**
 * Description of UploadForm
 *
 * @author Sistema
 */
class UploadForm extends Model {
    /**
     * @var UploadedFile|Null file attribute
     */
    public $image;
    public function rules()
    {
        return [
            [['image'], 'safe'],
            [['image'], 'file', 'extensions'=>'jpg, gif, png'],
        ];
    }

}
