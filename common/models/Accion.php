<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "accion".
 *
 * @property integer $acc_id
 * @property string $acc_nombre
 * @property string $acc_url_accion
 * @property string $acc_tipo
 * @property string $acc_descripcion
 * @property string $acc_lang_file
 * @property string $acc_dir_imagen
 * @property string $acc_estado_activo
 * @property string $acc_fecha_creacion
 * @property string $acc_fecha_modificacion
 * @property string $acc_estado_logico
 *
 * @property ObmoAcci[] $obmoAccis
 */
class Accion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'accion';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['acc_estado_activo', 'acc_estado_logico'], 'required'],
            [['acc_fecha_creacion', 'acc_fecha_modificacion'], 'safe'],
            [['acc_nombre', 'acc_url_accion'], 'string', 'max' => 50],
            [['acc_tipo'], 'string', 'max' => 45],
            [['acc_descripcion'], 'string', 'max' => 250],
            [['acc_lang_file'], 'string', 'max' => 60],
            [['acc_dir_imagen'], 'string', 'max' => 100],
            [['acc_estado_activo', 'acc_estado_logico'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'acc_id' => Yii::t('app', 'Acc ID'),
            'acc_nombre' => Yii::t('app', 'Acc Nombre'),
            'acc_url_accion' => Yii::t('app', 'Acc Url Accion'),
            'acc_tipo' => Yii::t('app', 'Acc Tipo'),
            'acc_descripcion' => Yii::t('app', 'Acc Descripcion'),
            'acc_lang_file' => Yii::t('app', 'Acc Lang File'),
            'acc_dir_imagen' => Yii::t('app', 'Acc Dir Imagen'),
            'acc_estado_activo' => Yii::t('app', 'Acc Estado Activo'),
            'acc_fecha_creacion' => Yii::t('app', 'Acc Fecha Creacion'),
            'acc_fecha_modificacion' => Yii::t('app', 'Acc Fecha Modificacion'),
            'acc_estado_logico' => Yii::t('app', 'Acc Estado Logico'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObmoAccis()
    {
        return $this->hasMany(ObmoAcci::className(), ['acc_id' => 'acc_id']);
    }

    /**
     * @inheritdoc
     * @return AccionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AccionQuery(get_called_class());
    }
    
    /**
     * Función para Obtener el menu de acciones 
     * Los Tipos de botones registrados en el Objeto Modulo son:
     * 0=>Botones normales que ejecutan un accion o entidad
     * 1=>Botones que ejecutan una funcion javascript
     * 
     * Los tipos de objetos modulos son:
     * P=>Principal
     * S=>Secundario
     * A=>Accion
     *
     * @author Eduardo Cueva <ecueva@penblu.com>
     * @access public
     * @param  int        $omod_id    Id del Objeto Modulo
     * @return mixed                  Devuelve un array para construccion de Menus
     */
    public function getAccionesObjModulo($omod_id){
        $con = \Yii::$app->db;
        $sql = "select a.omod_id, a.omod_padre_id, a.omod_nombre,a.omod_entidad,
                        a.omod_lang_file,a.omod_tipo,a.omod_tipo_boton,a.omod_accion,
                        a.omod_function,a.omod_dir_imagen,a.omod_orden,c.acc_id,c.acc_nombre,
                        c.acc_url_accion, c.acc_tipo, c.acc_lang_file, c.acc_dir_imagen
                   from " . $con->dbname . ".objeto_modulo a
                     inner join (" . $con->dbname . ".obmo_acci b
                         inner join " . $con->dbname . ".accion c on b.acc_id=c.acc_id)
                       on a.omod_id=b.omod_id
                 where a.omod_estado_logico=1 and a.omod_id=:omod_id order by a.omod_orden ";
        $comando = $con->createCommand($sql);
        $comando->bindParam(":omod_id", $omod_id, \PDO::PARAM_INT);
        return $comando->queryAll();
    }
    
    public static function generateActions($objmod_id = NULL){
        if(!isset($objmod_id)){
            $session = Yii::$app->session;
            $objmod_id = $session->get('PB_objmodule_id');
        }
        $usu_id    = Yii::$app->session->get('PB_iduser', FALSE);
        $RolId= Yii::$app->session->get('RolId', FALSE);
        $con = \Yii::$app->db;
        $sql = "select b.omod_entidad route
                    from " . $con->dbname . ".omodulo_rol a
                         inner join (" . $con->dbname . ".objeto_modulo b
                           inner join " . $con->dbname . ".modulo c
                             on c.mod_id=b.mod_id and c.mod_estado_logico=1)
                                on a.omod_id=b.omod_id and b.omod_estado_logico=1
                  where a.rol_id=:rolID and a.omrol_est_log=1 
                  and b.omod_padre_id=:objmod_id order by route ";//and b.omod_tipo='A'
        
        $comando = $con->createCommand($sql);
        $comando->bindParam(":objmod_id", $objmod_id, \PDO::PARAM_INT);
        $comando->bindParam(":rolID", $RolId, \PDO::PARAM_INT);
        $result = $comando->queryAll();
        $actions = array();
        $actionsArr = "";
        
        foreach($result as $key => $value){
            $link = $value["route"];
            $arr_link = explode("/",$link);
            $cont = count($arr_link) - 1;
            $actions[] = $arr_link[$cont];
        }
        if(count($actions)>0){
            return [
                "access" => [
                    'class' => \yii\filters\AccessControl::className(),
                    'rules' => [
                        [
                            'allow'   => true,
                            'actions' => $actions,
                            'roles'   => ['@'],
                        ],
                    ],
            ]];
        }else{
            return [
                "access" => [
                    'class' => \yii\filters\AccessControl::className(),
                    'rules' => [
                        [
                            'allow'   => false,
                            'roles'   => ['@'],
                        ],
                    ],
            ]];
        }
        
    }
    
}
