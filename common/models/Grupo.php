<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "grupo".
 *
 * @property integer $gru_id
 * @property integer $cseg_id
 * @property string $gru_nombre
 * @property string $gru_color
 * @property string $gru_descripcion
 * @property string $gru_estado_activo
 * @property string $gru_fecha_creacion
 * @property string $gru_fecha_modificacion
 * @property string $gru_estado_logico
 *
 * @property GrupObmo[] $grupObmos
 * @property ConfiguracionSeguridad $cseg
 * @property GrupoRol[] $grupoRols
 */
class Grupo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'grupo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cseg_id', 'gru_estado_activo', 'gru_estado_logico'], 'required'],
            [['cseg_id'], 'integer'],
            [['gru_fecha_creacion', 'gru_fecha_modificacion'], 'safe'],
            [['gru_nombre'], 'string', 'max' => 50],
            [['gru_color'], 'string', 'max' => 10],
            [['gru_descripcion'], 'string', 'max' => 200],
            [['gru_estado_activo', 'gru_estado_logico'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'gru_id' => Yii::t('app', 'Gru ID'),
            'cseg_id' => Yii::t('app', 'Cseg ID'),
            'gru_nombre' => Yii::t('app', 'Gru Nombre'),
            'gru_color' => Yii::t('app', 'Gru Color'),
            'gru_descripcion' => Yii::t('app', 'Gru Descripcion'),
            'gru_estado_activo' => Yii::t('app', 'Gru Estado Activo'),
            'gru_fecha_creacion' => Yii::t('app', 'Gru Fecha Creacion'),
            'gru_fecha_modificacion' => Yii::t('app', 'Gru Fecha Modificacion'),
            'gru_estado_logico' => Yii::t('app', 'Gru Estado Logico'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGrupObmos()
    {
        return $this->hasMany(GrupObmo::className(), ['gru_id' => 'gru_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCseg()
    {
        return $this->hasOne(ConfiguracionSeguridad::className(), ['cseg_id' => 'cseg_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGrupoRols()
    {
        return $this->hasMany(GrupoRol::className(), ['gru_id' => 'gru_id']);
    }
    
    public function getMainGrupo($username){
        $user = Usuario::findByUsername($username);
        $con = Yii::$app->db;
        $trans = $con->getTransaction();
        if ($trans !== null) {
            $trans = null; // si existe la transacción entonces no se crea una
        } else {
            $trans = $con->beginTransaction();
        }
        $sql = "SELECT 
                    g.gru_id AS id,
                    p.per_cedula AS dni
                FROM 
                    usuario AS u 
                    INNER JOIN grupo_rol AS gr ON gr.usu_id = u.usu_id 
                    INNER JOIN grupo AS g ON g.gru_id = gr.gru_id 
                    INNER JOIN persona AS p ON p.per_id = u.per_id 
                WHERE 
                    u.usu_username = :user AND 
                    gr.grol_estado_logico=1 AND 
                    gr.grol_estado_activo=1 AND 
                    p.per_estado_logico=1 AND 
                    p.per_estado_activo=1 AND 
                    g.gru_estado_activo = 1 AND 
                    g.gru_estado_logico=1 AND 
                    u.usu_estado_activo=1 AND 
                    u.usu_estado_logico=1";
        $comando = $con->createCommand($sql);
        $comando->bindParam(":user", $username, \PDO::PARAM_STR);
        $result = $comando->queryOne();
        return $result;
    }
    
}
