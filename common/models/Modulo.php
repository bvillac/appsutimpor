<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "modulo".
 *
 * @property integer $mod_id
 * @property string $mod_nombre
 * @property string $mod_dir_imagen
 * @property string $mod_url
 * @property integer $mod_orden
 * @property string $mod_lang_file
 * @property string $mod_estado_activo
 * @property string $mod_fecha_creacion
 * @property string $mod_fecha_modificacion
 * @property string $mod_estado_logico
 *
 * @property Aplicacion $apl
 * @property ObjetoModulo[] $objetoModulos
 */
class Modulo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'modulo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mod_estado_activo', 'mod_estado_logico'], 'required'],
            [['mod_orden'], 'integer'],
            [['mod_fecha_creacion', 'mod_fecha_modificacion'], 'safe'],
            [['mod_nombre'], 'string', 'max' => 50],
            [['mod_dir_imagen', 'mod_url'], 'string', 'max' => 100],
            [['mod_lang_file'], 'string', 'max' => 60],
            [['mod_estado_activo', 'mod_estado_logico'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mod_id' => Yii::t('app', 'Mod ID'),
            'mod_nombre' => Yii::t('app', 'Mod Nombre'),
            'mod_dir_imagen' => Yii::t('app', 'Mod Dir Imagen'),
            'mod_url' => Yii::t('app', 'Mod Url'),
            'mod_orden' => Yii::t('app', 'Mod Orden'),
            'mod_lang_file' => Yii::t('app', 'Mod Lang File'),
            'mod_estado_activo' => Yii::t('app', 'Mod Estado Activo'),
            'mod_fecha_creacion' => Yii::t('app', 'Mod Fecha Creacion'),
            'mod_fecha_modificacion' => Yii::t('app', 'Mod Fecha Modificacion'),
            'mod_estado_logico' => Yii::t('app', 'Mod Estado Logico'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObjetoModulos()
    {
        return $this->hasMany(ObjetoModulo::className(), ['mod_id' => 'mod_id']);
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        return static::findOne($id);
    }
    
    /**
     * Funcion que retorna los modulos que puede visualizar un usuario
     *
     * @access public
     * @return mixed $menu      Arreglos de Modulos
     */
    public function getModulos() {
        $RolId= Yii::$app->session->get('RolId', FALSE);
        $con = \Yii::$app->db;
        $sql = "select distinct(a.mod_id),a.*
                    from " . $con->dbname . ".modulo a
                      inner join (" . $con->dbname . ".objeto_modulo b
                          inner join (" . $con->dbname . ".omodulo_rol c
                              inner join " . $con->dbname . ".rol d on c.rol_id=d.rol_id)
                          on b.omod_id=c.omod_id)
                      on b.mod_id=a.mod_id
                  where a.mod_estado_logico=1 and d.rol_id=:rol_id order by a.mod_orden ";      
        $comando = $con->createCommand($sql);
        $comando->bindParam(":rol_id", $RolId, \PDO::PARAM_INT);
        return $comando->queryAll();
    }
    
    /**
     * Funcion que retorna el link del primer modulo
     *
     * @access public
     * @return mixed $menu      Arreglos de Modulos
     */
    function getFirstModuleLink(){
        //$iduser    = Yii::$app->session->get('PB_iduser', FALSE);
        $RolId= Yii::$app->session->get('RolId', FALSE);
        $con = \Yii::$app->db;
        $sql = "select a.mod_url url
                    from " . $con->dbname . ".modulo a
                      inner join (" . $con->dbname . ".objeto_modulo b
                          inner join (" . $con->dbname . ".omodulo_rol c
                              inner join " . $con->dbname . ".rol d on c.rol_id=d.rol_id)
                          on b.omod_id=c.omod_id)
                      on b.mod_id=a.mod_id
                  where a.mod_estado_logico=1 and d.rol_id=:rol_id ";      
        $comando = $con->createCommand($sql);
        $comando->bindParam(":rol_id", $RolId, \PDO::PARAM_INT);
        return $comando->queryOne();
    }
}
