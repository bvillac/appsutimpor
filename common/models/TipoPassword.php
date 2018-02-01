<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tipo_password".
 *
 * @property integer $tpas_id
 * @property string $tpas_tipo
 * @property string $tpas_validacion
 * @property string $tpas_descripcion
 * @property string $tpas_estado_activo
 * @property string $tpas_fecha_creacion
 * @property string $tpas_fecha_modificacion
 * @property string $tpas_estado_logico
 *
 * @property Grupo[] $grupos
 */
class TipoPassword extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tipo_password';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tpas_estado_activo', 'tpas_estado_logico'], 'required'],
            [['tpas_fecha_creacion', 'tpas_fecha_modificacion'], 'safe'],
            [['tpas_tipo'], 'string', 'max' => 50],
            [['tpas_validacion'], 'string', 'max' => 200],
            [['tpas_descripcion'], 'string', 'max' => 300],
            [['tpas_estado_activo', 'tpas_estado_logico'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tpas_id' => Yii::t('tipo_password', 'Tpas ID'),
            'tpas_tipo' => Yii::t('tipo_password', 'Tpas Tipo'),
            'tpas_validacion' => Yii::t('tipo_password', 'Tpas Validacion'),
            'tpas_descripcion' => Yii::t('tipo_password', 'Tpas Descripcion'),
            'tpas_estado_activo' => Yii::t('tipo_password', 'Tpas Estado Activo'),
            'tpas_fecha_creacion' => Yii::t('tipo_password', 'Tpas Fecha Creacion'),
            'tpas_fecha_modificacion' => Yii::t('tipo_password', 'Tpas Fecha Modificacion'),
            'tpas_estado_logico' => Yii::t('tipo_password', 'Tpas Estado Logico'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGrupos()
    {
        return $this->hasMany(Grupo::className(), ['tpas_id' => 'tpas_id']);
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        return static::findOne($id);
    }
}
