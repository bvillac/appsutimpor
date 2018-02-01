<?php
/**
 * This is the model class for table "user_passreset".
 *
 * @property integer $upas_id
 * @property integer $usu_id
 * @property string $upas_token
 * @property string $upas_remote_ip_inactivo
 * @property string $upas_remote_ip_activo
 * @property string $upas_link
 * @property string $upas_fecha_inicio
 * @property string $upas_fecha_fin
 * @property string $upas_estado_activo
 * @property string $upas_fecha_creacion
 * @property string $upas_fecha_modificacion
 * @property string $upas_estado_logico
 */
namespace app\models;

use yii\helpers\Html;
use Yii;
use yii\base\Security;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Url;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\AttributeBehavior;

class UserPassreset extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_passreset';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['usu_id'], 'required'],
            [['usu_id'], 'integer'],
            [['upas_fecha_inicio', 'upas_fecha_fin', 'upas_fecha_creacion', 'upas_fecha_modificacion'], 'safe'],
            [['upas_link'], 'string', 'max' => 500],
            [['upas_remote_ip_inactivo', 'upas_remote_ip_activo'], 'string', 'max' => 20],
            [['upas_estado_activo', 'upas_estado_logico'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'upas_id' => Yii::t('user_passreset', 'Upas ID'),
            'usu_id' => Yii::t('user_passreset', 'Usu ID'),
            'upas_remote_ip_inactivo' => Yii::t('user_passreset', 'Upas Remote Ip Inactivo'),
            'upas_remote_ip_activo' => Yii::t('user_passreset', 'Upas Remote Ip Activo'),
            'upas_link' => Yii::t('user_passreset', 'Upas Link'),
            'upas_fecha_inicio' => Yii::t('user_passreset', 'Upas Fecha Inicio'),
            'upas_fecha_fin' => Yii::t('user_passreset', 'Upas Fecha Fin'),
            'upas_estado_activo' => Yii::t('user_passreset', 'Upas Estado Activo'),
            'upas_fecha_creacion' => Yii::t('user_passreset', 'Upas Fecha Creacion'),
            'upas_fecha_modificacion' => Yii::t('user_passreset', 'Upas Fecha Modificacion'),
            'upas_estado_logico' => Yii::t('user_passreset', 'Upas Estado Logico'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        return static::findOne($id);
    }
    
    /**
     * Función genera un link de clave para ser enviado por correo
     *
     * @access  public
     * @author  Eduard Cueva <ecueva@penblu.com>
     * @return  string         Link de acceso
     */
    public function generarLinkCambioClave($usuId){
        $security = new Security();
        $hash = $security->generateRandomString();
        $sublink = urlencode($hash);
        $sublink = str_replace("/", "", $sublink);
        $sublink = str_replace("+", "", $sublink);
        $sublink = str_replace("-", "", $sublink);
        $sublink = str_replace("_", "", $sublink);
        $sublink = str_replace(" ", "", $sublink);
        $sublink = str_replace("?", "", $sublink);
        $link = Url::base(true)."/site/updatepass?wg=".$sublink;
        $this->usu_id = $usuId;
        $this->upas_remote_ip_inactivo = Utilities::getClientRealIP();
        //$this->upas_remote_ip_activo = "";
        $this->upas_link = $link;
        $this->upas_fecha_inicio = date("Y-m-d H:i:s");
        //$this->upas_fecha_fin = "";
	$status = $this->save();
        if($status){
            return $link;
        }
        return false;
    }
    
    public function verificarLinkCambioClave($link){
        $userpass = static::findOne(['upas_link' => $link]);
        $dbLink = $userpass->upas_link;
        if(isset($dbLink) && $dbLink != "" && $userpass->upas_estado_activo == 1 && $userpass->upas_estado_logico == 1){
            if($dbLink == $link){
                return true;
            }
        }
        return false;
    }
    
    public function behaviors() {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['upas_fecha_creacion'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['upas_fecha_modificacion'],
                ],
                'value' => new Expression('NOW()'),
            ],
            'integer' => [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['upas_estado_activo','upas_estado_logico'],
                ],
                'value' => '1',
            ],
        ];
    }
}
