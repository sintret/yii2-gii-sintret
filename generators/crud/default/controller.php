<?php

/**
 * This is the template for generating a CRUD controller class file.
 */
use yii\helpers\Inflector;
use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
    use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
    use yii\data\ActiveDataProvider;
<?php endif; ?>
<?php
$num = 0;
$createDate ="";
$userUpdate = "";
$userCreate = "";
foreach ($generator->getColumnNames() as $attribute) {
    $attr[] = $attribute;
    if ($attribute == 'image') {
        $num = 1;
    }

    if ($attribute == 'createDate') {
        $createDate = '$model->createDate = date("Y-m-d H:i:s");';
    }
    
    if ($attribute == 'userCreate') {
        $userCreate = '$model->userCreate = Yii::$app->user->id;';
    }
    if ($attribute == 'userUpdate') {
        $userUpdate = '$model->userUpdate = Yii::$app->user->id;';
    }
}
if ($num) {
    $loadfile = 'loadWithFiles';
} else {
    $loadfile = 'load';
}
?>

use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use sintret\gii\models\LogUpload;
use sintret\gii\components\Util;


/**
* <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
*/
class <?= $controllerClass ?> extends CController <?= "\n" ?>
{

public function behaviors()
{
return [
'access' => [
'class' => \yii\filters\AccessControl::className(),
'rules' => [
//                    [
//                        'allow' => true,
//                        'actions' => ['index','view','sample','parsing-log','excel'],
//                        'roles' => ['viewer']
//                    ],
//                    [
//                        'allow' => true,
//                        'actions' => ['create','parsing'],
//                        'roles' => ['author']
//                    ],
//                    [
//                        'allow' => true,
//                        'actions' => ['update'],
//                        'roles' => ['editor']
//                    ],
//                    [
//                        'allow' => true,
//                        'actions' => ['delete', 'delete-all'],
//                        'roles' => ['admin']
//                    ],
[
'allow' => true,
'roles' => ['@']
],
],
],
'verbs' => [
'class' => VerbFilter::className(),
'actions' => [
'delete' => ['post'],
],
],
];
}

/**
* Lists all <?= $modelClass ?> models.
* @return mixed
*/
public function actionIndex()
{
$grid = 'grid-'.self::className();
$reset = Yii::$app->getRequest()->getQueryParam('p_reset');
if ($reset) {
\Yii::$app->session->set($grid, "");
} else {
$rememberUrl = Yii::$app->session->get($grid);
$current = Url::current();
if ($rememberUrl != $current && $rememberUrl) {
Yii::$app->session->set($grid, "");
$this->redirect($rememberUrl);
}
if (Yii::$app->getRequest()->getQueryParam('_pjax')) {
\Yii::$app->session->set($grid, "");
\Yii::$app->session->set($grid, Url::current());
}
}

<?php if (!empty($generator->searchModelClass)): ?>
    $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    ]);
<?php else: ?>
    $dataProvider = new ActiveDataProvider([
    'query' => <?= $modelClass ?>::find(),
    ]);

    return $this->render('index', [
    'dataProvider' => $dataProvider,
    ]);
<?php endif; ?>
}

/**
* Displays a single <?= $modelClass ?> model.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return mixed
*/
public function actionView(<?= $actionParams ?>)
{
return $this->render('view', [
'model' => $this->findModel(<?= $actionParams ?>),
]);
}

/**
* Creates a new <?= $modelClass ?> model.
* If creation is successful, the browser will be redirected to the 'view' page.
* @return mixed
*/
public function actionCreate()
{
$model = new <?= $modelClass ?>();
<?= $createDate;?>
<?= $userCreate;?>
<?= $userUpdate;?>
if ($model-><?= $loadfile; ?>(Yii::$app->request->post()) && $model->save()) {
Yii::$app->session->setFlash('success', 'Well done! successfully to save data!  ');
return $this->redirect(['index']);
} else {
return $this->render('create', [
'model' => $model,
]);
}
}

/**
* Updates an existing <?= $modelClass ?> model.
* If update is successful, the browser will be redirected to the 'view' page.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return mixed
*/
public function actionUpdate(<?= $actionParams ?>)
{
$model = $this->findModel(<?= $actionParams ?>);
<?= $userCreate;?>
<?= $userUpdate;?>
if ($model-><?= $loadfile; ?>(Yii::$app->request->post()) && $model->save()) {
Yii::$app->session->setFlash('success', 'Well done! successfully to update data!  ');
return $this->redirect(['index']);
} else {
return $this->render('update', [
'model' => $model,
]);
}
}

/**
* Deletes an existing <?= $modelClass ?> model.
* If deletion is successful, the browser will be redirected to the 'index' page.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return mixed
*/
public function actionDelete(<?= $actionParams ?>)
{
$this->findModel(<?= $actionParams ?>)->delete();
Yii::$app->session->setFlash('success', 'Well done! successfully to deleted data!  ');

return $this->redirect(['index']);
}

/**
* Finds the <?= $modelClass ?> model based on its primary key value.
* If the model is not found, a 404 HTTP exception will be thrown.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return <?= $modelClass ?> the loaded model
* @throws NotFoundHttpException if the model cannot be found
*/
protected function findModel(<?= $actionParams ?>)
{
<?php
if (count($pks) === 1) {
    $condition = '$id';
} else {
    $condition = [];
    foreach ($pks as $pk) {
        $condition[] = "'$pk' => \$$pk";
    }
    $condition = '[' . implode(', ', $condition) . ']';
}
?>
if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
return $model;
} else {
throw new NotFoundHttpException('The requested page does not exist.');
}
}

public function actionSample() {

//$objPHPExcel = new \PHPExcel();
$template = Util::templateExcel();
$model = new <?= $modelClass ?>;
$date = date('YmdHis');
$name = $date.'<?= $modelClass ?>';
//$attributes = $model->attributeLabels();
$models = <?= $modelClass ?>::find()->all();
$excelChar = Util::excelChar();
$not = Util::excelNot();

foreach ($model->attributeLabels() as $k=>$v){
if(!in_array($k, $not)){
$attributes[$k]=$v;
}
}

$objReader = \PHPExcel_IOFactory::createReader('Excel5');
$objPHPExcel = $objReader->load(Yii::getAlias($template));

return $this->render('sample', ['models' => $models,'attributes'=>$attributes,'excelChar'=>$excelChar,'not'=>$not,'name'=>$name,'objPHPExcel' => $objPHPExcel]);
}

public function actionParsing() {
$num = 0;
$fields = [];
$values = [];
$log = '';
$route = '';
$model = new LogUpload;

$date = date('Ymdhis') . Yii::$app->user->identity->id;

if (Yii::$app->request->isPost) {
$model->fileori = UploadedFile::getInstance($model, 'fileori');

if ($model->validate()) {
$fileOri = Yii::getAlias(LogUpload::$imagePath) . $model->fileori->baseName . '.' . $model->fileori->extension;
$filename = Yii::getAlias(LogUpload::$imagePath) . $date . '.' . $model->fileori->extension;
$model->fileori->saveAs($filename);
}
$params = Util::excelParsing(Yii::getAlias($filename));
$model->params = \yii\helpers\Json::encode($params);
$model->title = 'parsing <?= $modelClass ?>';
$model->fileori = $fileOri;
$model->filename = $filename;


if ($params)
foreach ($params as $k => $v) {
foreach ($v as $key => $val) {
if ($num == 0) {
$fields[$key] = $val;
$max = $key;
}

if ($num >= 3) {
$values[$num][$fields[$key]] = $val;
}
}
$num++;
}
if (in_array('id', $fields)) {
$model->type = LogUpload::TYPE_UPDATE;
} else {
$model->type = LogUpload::TYPE_INSERT;
}
$model->keys = \yii\helpers\Json::encode($fields);
$model->values = \yii\helpers\Json::encode($values);
if ($model->save()) {
$log = 'log_<?= $modelClass ?>'. Yii::$app->user->id;
Yii::$app->session->setFlash('success', 'Well done! successfully to Parsing data, see log on log upload menu! Please Waiting for processing indicator if available...  ');
Yii::$app->session->set($log, $model->id);
$notification = new \sintret\gii\models\Notification;
$notification->title = 'parsing';
$notification->message = Yii::$app->user->identity->username . ' parsing <?= $modelClass ?> ';
$notification->params = \yii\helpers\Json::encode(['model' => '<?= $modelClass ?>', 'id' => $model->id]);
$notification->save();
}
}
$route = '<?= strtolower($modelClass) ?>/parsing-log';

return $this->render('parsing', ['model' => $model,'log'=>$log,'route'=>$route]);
}

public function actionParsingLog($id) {
$mod = LogUpload::findOne($id);
$type = $mod->type;
$keys = \yii\helpers\Json::decode($mod->keys);
$values = \yii\helpers\Json::decode($mod->values);
$modelAttribute = new <?= $modelClass ?>;
$not = Util::excelNot();

foreach ($values as $value) {
if ($type == LogUpload::TYPE_INSERT)
$model = new <?= $modelClass ?>;
else
$model = <?= $modelClass ?>::findOne($value['id']);

foreach ($keys as $v) {
$model->$v = $value[$v];
}

$e = 0;
if ($model->save()) {
$model = NULL;
$pos = NULL;
} else {
$error[] = \yii\helpers\Json::encode($model->getErrors());
$e = 1;
}
}

if ($error) {
foreach ($error as $err) {
if ($err) {
$er[] = $err;
$e+=1;
}
}
if ($e) {
$mod->warning = \yii\helpers\Json::encode($er);
$mod->save();
echo '<pre>';
                print_r($er);
            }
        }
    }

    public function actionExcel() {
        $searchModel = new <?= $modelClass ?>Search;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $modelAttribute = new <?= $modelClass ?>;
        $not = Util::excelNot();
        foreach ($modelAttribute->attributeLabels() as $k=>$v){
            if(!in_array($k, $not)){
                $attributes[$k] = $v;
            }
        }

        $models = $dataProvider->getModels();
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objPHPExcel = $objReader->load(Yii::getAlias(Util::templateExcel()));
        $excelChar = Util::excelChar();
        return $this->render('_excel', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'attributes' => $attributes,
                    'models' => $models,
                    'objReader' => $objReader,
                    'objPHPExcel' => $objPHPExcel,
                    'excelChar' => $excelChar
        ]);
    }
    public function actionDeleteAll() {
        $pk = Yii::$app->request->post('pk'); // Array or selected records primary keys
        $explode = explode(",", $pk);
        if ($explode)
            foreach ($explode as $v) {
                if($v)
                $this->findModel($v)->delete();
            }
        echo 1;
    }
}
