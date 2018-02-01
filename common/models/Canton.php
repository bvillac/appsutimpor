<?php

namespace app\models;
use Yii;

/**
 * This is the model class for table "canton".
 *
 * @property integer $can_id
 * @property integer $prov_id
 * @property string $can_nombre
 * @property string $can_descripcion
 * @property string $can_estado_activo
 * @property string $can_fecha_creacion
 * @property string $can_fecha_modificacion
 * @property string $can_estado_logico
 *
 * @property Provincia $prov
 * @property Parroquia[] $parroquias
 * @property Persona[] $personas
 */
class Canton extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'canton';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prov_id', 'can_estado_activo', 'can_estado_logico'], 'required'],
            [['prov_id'], 'integer'],
            [['can_fecha_creacion', 'can_fecha_modificacion'], 'safe'],
            [['can_nombre', 'can_descripcion'], 'string', 'max' => 150],
            [['can_estado_activo', 'can_estado_logico'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'can_id' => Yii::t('canton', 'Can ID'),
            'prov_id' => Yii::t('canton', 'Pro ID'),
            'can_nombre' => Yii::t('canton', 'Can Nombre'),
            'can_descripcion' => Yii::t('canton', 'Can Descripcion'),
            'can_estado_activo' => Yii::t('canton', 'Can Estado Activo'),
            'can_fecha_creacion' => Yii::t('canton', 'Can Fecha Creacion'),
            'can_fecha_modificacion' => Yii::t('canton', 'Can Fecha Modificacion'),
            'can_estado_logico' => Yii::t('canton', 'Can Estado Logico'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPro()
    {
        return $this->hasOne(Provincia::className(), ['prov_id' => 'prov_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParroquias()
    {
        return $this->hasMany(Parroquia::className(), ['can_id' => 'can_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonas()
    {
        return $this->hasMany(Persona::className(), ['can_id_nacimiento' => 'can_id']);
    }
    
    public static function findIdentity($id) {
        return static::findOne($id);
    }
    
    public static function getCantones(){
        return static::findAll(["can_estado_activo" => 1, "can_estado_logico" => 1]);
    }
    
    public static function getCantonesByProvincia($pro_id){
        return static::findAll(["can_estado_activo" => 1, "can_estado_logico" => 1, "prov_id" => $pro_id]);
    }
    
    public static function getCantonesByProvinciaID($provincia_id){
        $sql = "SELECT can_id AS Ids, can_nombre AS Nombre FROM canton WHERE prov_id=:provincia_id";
        $comando = Yii::$app->db->createCommand($sql);
        $comando->bindParam(":provincia_id", $provincia_id, \PDO::PARAM_INT);
        return $comando->queryAll();
    }
    
    public static function getCantonProvinciaPaisID($ids){
        $con = \Yii::$app->db;
        $sql="SELECT A.can_nombre Canton,B.prov_nombre Provincia,C.pai_nombre Pais
                    FROM " . $con->dbname . ".canton A
                        INNER JOIN (" . $con->dbname . ".provincia B
                                INNER JOIN " . $con->dbname . ".pais C
                                    ON B.pai_id=C.pai_id)
                            ON A.prov_id=B.prov_id
                WHERE A.can_estado_logico=1 AND A.can_id=:can_id";
        $comando = $con->createCommand($sql);
        $comando->bindParam(":can_id", $ids, \PDO::PARAM_INT);
        return $comando->queryAll();
    }

    
}
