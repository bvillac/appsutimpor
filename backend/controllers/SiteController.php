<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
//        if (!Yii::$app->user->isGuest) {
//            return $this->goHome();
//        }
//
//        $model = new LoginForm();
//        if ($model->load(Yii::$app->request->post()) && $model->login()) {
//            return $this->goBack();
//        } else {
//            return $this->render('login', [
//                'model' => $model,
//            ]);
//        }
        if (!\Yii::$app->user->isGuest) {
            return $this->redirect(Url::base(true).'/site/login');
        }
        $model = new LoginForm();
        
        if(Yii::$app->session->get("PB_isuser", false)){
            // setting default url
            $mod = new Modulo();
            $link =  $mod->getFirstModuleLink();
            $url = Url::base(true) . "/" . $link["url"];
            return $this->goBack($url);
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // setting default url
            $mod = new Modulo();
            $link =  $mod->getFirstModuleLink();
            $url = Url::base(true) . "/" . $link["url"];
            return $this->goBack($url);
        } else {
            if($model->getErrorSession())
                Yii::$app->session->setFlash('loginFormSubmitted');
                return $this->renderFile('@themes/' . \Yii::$app->getView()->theme->themeName . '/layouts/login.php', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
