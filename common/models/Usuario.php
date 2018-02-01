<?php

namespace app\models;

use Yii;
use yii\base\Security;
use yii\web\IdentityInterface;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\AttributeBehavior;
use app\models\Utilities;
use yii\helpers\Url;

class Usuario extends ActiveRecord implements IdentityInterface {
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'usuario';
    }

    public function rules() {
        return [
            [['usu_username', 'usu_password', 'per_id'], 'required'],
            [['usu_username', 'usu_password'], 'string', 'max' => 200],
            [['usu_link_activo'], 'string'],
            [['per_id', 'usu_est_log', 'usu_estado_activo'], 'integer', 'max' => 20]
        ];
    }

    public function attributeLabels() {
        return [
            'usu_username' => Yii::t('login', 'Username'),
            'usu_password' => Yii::t('login', 'Password'),
            'usu_last_login' => Yii::t('login', 'Last login'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        return $this->usu_sha;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        return static::findOne($id);
    }
    
    public static function findByCondition($condition) {
        return parent::findByCondition($condition);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        return static::findOne(['usu_sha' => $token]);
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username) {
        $user = static::findOne(['usu_username' => $username, 'usu_estado_activo' => 1, 'usu_est_log' => 1]);
        if(isset($user->usu_id))
            return $user;
        else
            return NULL;
    }

    /**
     * Validates password
     *
     * @author Eduardo Cueva <ecueva@penblu.com>
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password) {
        $security = new Security();
        return ($this->usu_sha === $security->decryptByPassword(base64_decode($this->usu_password), $password));
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @author Eduardo Cueva <ecueva@penblu.com>
     * @param string $password
     */
    public function setPassword($password) {
        $security = new Security();
        $hash = (isset($this->usu_sha) ? $this->usu_sha : ($this->generateAuthKey()));
        $this->usu_password = base64_encode($security->encryptByPassword($hash, $password));
    }

    /**
     * Función para generar el Salt o token de clave de manera aleatoria
     *
     * @author Eduardo Cueva <ecueva@penblu.com>
     * @access public
     *
     */
    public function generateAuthKey() {
        $security = new Security();
        $this->usu_sha = $security->generateRandomString();
        return $this->usu_sha;
    }
     /*
     * CREAR LA SESION DEL USUARIO  Y SUS VARIBLES
     */
    public function createSession() {
        $session = Yii::$app->session;
        if ($session->isActive){
            $session->open();
            //$session->close();
            $model_persona  = Persona::findIdentity($this->per_id);
            $arr_nombres    = Utilities::getNombresApellidos($model_persona->per_nombre);
            $arr_apellidos  = Utilities::getNombresApellidos($model_persona->per_apellido);
            $nombre_persona = $arr_nombres[1]." ".$arr_apellidos[1];
            // se busca si el usuario es un licenciatario
            //$mce_reg = MceRegistro::findOne(['usu_id' => $this->usu_id, 'reg_estado' => '1', 'reg_estado_logico' => '1']);
            $tipoUser=  Rol::buscarTipoUser($this->usu_id);
            $session->set('PB_isuser', true);
            $session->set('PB_username', $this->usu_username);
            $session->set('PB_nombres', $nombre_persona);
            $session->set('PB_perid', $this->per_id);
            $session->set('PerId', $this->per_id);
            $session->set('PB_iduser', $this->usu_id);
            //$session->set('PB_idregister', ($mce_reg->reg_id)?($mce_reg->reg_id):0);
            $session->set('PB_yii_lang', Yii::$app->language);
            $session->set('PB_yii_theme', Yii::$app->view->theme->themeName);
            $session->set('PB_client_ip', Utilities::getClientRealIP());
            $session->set('PB_module_id', "");
            $session->set('PB_objmodule_id', "");
            $session->set('RolId',$tipoUser['rolid']);
            $session->set('RolName',$tipoUser['rolName']);
            //RECUPERA VALORES DE PACIENTE Y MEDICO
            if($tipoUser['rolid']==3){//Medico
                $datSes=  Medico::getMedId($this->per_id);
                $session->set('MedId', $datSes['MedId'] );               
            }elseif ($tipoUser['rolid']==4) {//Paciente
                $datSes=Paciente::getPacId($this->per_id);
                $session->set('PacId', $datSes['PacId'] );
            }
        }else{
            Utilities::putMessageLogFile("cerrado");
            $session->destroy();
        }
    }

    public function regenerateSession(){
        $session = Yii::$app->session;
        if ($session->isActive){
            $id = Yii::$app->session->getId();
            Yii::$app->session->regenerateID($id);
        }
    }

    public function destroySession(){
        //$usuario = $this->findIdentity(Yii::$app->session->get("PB_iduser"));
        //$usuario->update(true, array("usu_session"));
        $session = Yii::$app->session;
        $session->close();
        $session->destroy();
    }

    public function crearUsuario($username, $password, $id_persona){
        // se debe verificar de que el usuario no exista
        $this->usu_username = $username;
        $this->generateAuthKey();// generacion de hash
        $this->setPassword($password);
        $this->per_id = $id_persona;
        if($this->save())
            return true;
        return false;
    }
    
    public static function insertarDataUser($con,$username, $password, $id_persona,$link) {
        $security = new Security();
        $usu_sha = $security->generateRandomString();//String de Seguridad
        $usu_password = base64_encode($security->encryptByPassword($usu_sha, $password));//Nuevo Pass con SHA Y 64 BITS        
        $sql = "INSERT INTO " . $con->dbname . ".usuario
            (per_id,usu_username,usu_password,usu_sha,usu_link_activo,usu_estado_activo,usu_est_log)VALUES
            (:per_id,:usu_username,:usu_password,:usu_sha,:usu_link_activo,1,1);";
        $command = $con->createCommand($sql);
        $command->bindParam(":per_id", $id_persona, \PDO::PARAM_INT);//Id Comparacion
        $command->bindParam(":usu_username", $username, \PDO::PARAM_STR);
        $command->bindParam(":usu_password", $usu_password, \PDO::PARAM_STR);
        $command->bindParam(":usu_sha", $usu_sha, \PDO::PARAM_STR);
        $command->bindParam(":usu_link_activo", $link, \PDO::PARAM_STR);
        $command->execute();
    }
    
    /**
     * Función genera un link de acceso para ser enviado por correo
     *
     * @access  public
     * @author  Eduard Cueva <ecueva@penblu.com>
     * @return  string         Link de acceso
     */
    public function generarLinkActivacion(){
        $security = new Security();
        $hash = $security->generateRandomString();
        $sublink = urlencode($hash);
        $sublink = str_replace("/", "", $sublink);
        $sublink = str_replace("+", "", $sublink);
        $sublink = str_replace("-", "", $sublink);
        $sublink = str_replace("_", "", $sublink);
        $sublink = str_replace(" ", "", $sublink);
        $sublink = str_replace("?", "", $sublink);
        $link = Url::base(true)."/site/activation?wg=".$sublink;
        $this->usu_link_activo = $link;
        $this->usu_estado_activo = 0;
        $this->save();
        return $link;
    }
    
    //Solo Genera Link Sin Guardar en la Base
    public static function crearLinkActivacion(){
        $security = new Security();
        $hash = $security->generateRandomString();
        $sublink = urlencode($hash);
        $sublink = str_replace("/", "", $sublink);
        $sublink = str_replace("+", "", $sublink);
        $sublink = str_replace("-", "", $sublink);
        $sublink = str_replace("_", "", $sublink);
        $sublink = str_replace(" ", "", $sublink);
        $sublink = str_replace("?", "", $sublink);
        $link = Url::base(true)."/site/activation?wg=".$sublink;
        return $link;
    }
    
    public function activarLinkCuenta($link){
        $user = static::findOne(['usu_link_activo' => $link]);
        $dbLink = $user->usu_link_activo;
        if(isset($dbLink) && $dbLink != ""){
            if($dbLink == $link){
                $user->usu_link_activo = "";
                $user->usu_estado_activo = 1;
                $id = $user->usu_id;
                $user->update(true, array("usu_link_activo","usu_estado_activo"));
                //EN CASO DE AGREGAR MAS DATOS EN LA ACTIVACION AKI
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
                    ActiveRecord::EVENT_BEFORE_INSERT => ['usu_fec_cre'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['usu_fec_mod'],
                ],
                'value' => new Expression('NOW()'),
            ],
            'integer' => [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['usu_est_log','usu_estado_activo'],
                ],
                'value' => '1',
            ],
        ];
    }
    
    public static function getUserPersona($ids){
        $con = \Yii::$app->db;
        $sql="SELECT A.reg_id Ids,A.usu_id Usu_id,B.usu_username Usuario,C.per_nombres Nombre,CONCAT(C.per_nombres,' ',C.per_apellidos) Nombres
            FROM " . $con->dbname . ".mce_registro A
              INNER JOIN (" . $con->dbname . ".usuario B
                   INNER JOIN " . $con->dbname . ".persona C ON B.per_id=C.per_id)
                ON A.usu_id=B.usu_id AND B.usu_est_log=1
          WHERE A.reg_est_log=1 AND A.reg_id=:reg_id";
        $comando = $con->createCommand($sql);
        $comando->bindParam(":reg_id", $ids, \PDO::PARAM_INT);
        return $comando->queryAll();
    }

}
