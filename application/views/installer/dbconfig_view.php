<?php
/**
 * @var InstallerConfigForm $model
 * @var string $title
 * @var string $descp
 */

Yii::app()->clientScript->registerScript('orgType', "
$( document ).ready(function() {
    checkDbType();
});
$('#InstallerConfigForm_dbtype').change(function(){
    checkDbType();
});

function checkDbType(){
    if($('#InstallerConfigForm_dbtype').val() == '".InstallerConfigForm::DB_TYPE_MYSQL."') {
        $('#InstallerConfigForm_dbengine_row').show();
    } else if($('#InstallerConfigForm_dbtype').val() == '".InstallerConfigForm::DB_TYPE_MYSQLI."') {
        $('#InstallerConfigForm_dbengine_row').show();
    } else {
        $('#InstallerConfigForm_dbengine_row').hide();
    }
}
");
?>

<div class="row">
    <div class="col-md-4">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-md-8">
        <?php echo CHtml::beginForm($this->createUrl('installer/database'), 'post', array('class' => '')); ?>
        <h2><?php echo $title; ?></h2>
        <p><?php echo $descp; ?></p>
        <?php if (CHtml::errorSummary($model, null, null, array('class' => 'errors'))): ?>
            <div class='alert alert-danger'>
                <?php echo CHtml::errorSummary($model, null, null, array('class' => 'errors')); ?>
            </div>
        <?php endif; ?>
            <hr/>
            <p><?php eT("Note: All fields marked with (*) are required."); ?></p>
            <legend><?php eT("Database configuration"); ?></legend>
            <?php
                $rows = array();
                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dbtype'),
                    'control' => CHtml::activeDropDownList($model, 'dbtype', $model->supported_db_types, array('required' => 'required', 'class'=>'form-control', 'autofocus' => 'autofocus')),
                    'description' => gT("The type of your database management system")
                );
                $rows[] = array(
                    'id'=>'InstallerConfigForm_dbengine_row',
                    'label' => CHtml::activeLabelEx($model, 'dbengine'),
                    'control' => CHtml::activeDropDownList($model, 'dbengine', $model->dbEngines, array('prompt'=>gT("Select"), 'autocomplete'=>'off', 'class' => 'form-control')),
                    'description' => '',
                );
                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dblocation'),
                    'control' => CHtml::activeTextField($model, 'dblocation',array('class' => 'form-control')),
                    'description' => gT('Set this to the IP/net location of your database server. In most cases "localhost" will work. You can force Unix socket with complete socket path.').' '.gT('If your database is using a custom port attach it using a colon. Example: db.host.com:5431')
                );
                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dbuser'),
                    'control' => CHtml::activeTextField($model, 'dbuser', array('autocomplete'=>'off', 'class' => 'form-control')),
                    'description' => gT('Your database server user name. In most cases "root" will work.')
                );
                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dbpwd'),
                    'control' => CHtml::activePasswordField($model, 'dbpwd',array('autocomplete'=>'off', 'class' => 'form-control')),
                    'description' => gT("Your database server password.")
                );
                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dbname'),
                    'control' => CHtml::activeTextField($model, 'dbname', array('autocomplete'=>'off', 'class' => 'form-control')),
                    'description' => gT("If the database does not yet exist it will be created (make sure your database user has the necessary permissions). In contrast, if there are existing LimeSurvey tables in that database they will be upgraded automatically after installation.")
                );
                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dbprefix'),
                    'control' => CHtml::activeTextField($model, 'dbprefix', array('value' => 'lime_','autocomplete'=>'off', 'class' => 'form-control')),
                    'description' => gT('If your database is shared, recommended prefix is "lime_" else you can leave this setting blank.')
                );

            foreach ($rows as $row) {
                $idTag = (empty($row['id']) ? [] : ['id'=>$row['id']]);
                echo CHtml::openTag('div', array_merge(array('class' => 'form-group'),$idTag));
                echo $row['label'];
                echo CHtml::tag('div', array('class' => 'controls'), $row['control'] . CHtml::tag('p', array('class' => 'help-block'), $row['description']));
                echo CHtml::closeTag('div');
            }

            ?>


        <br />
        <div class="row">
            <div class="col-md-4" >
                <input id="ls-previous" class="btn btn-default" type="button" value="<?php eT("Previous"); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/precheck"); ?>', '_top')" />
            </div>
            <div class="col-md-4" style="text-align: center;">
            </div>
            <div class="col-md-4" style="text-align: right;">
                <?php echo CHtml::submitButton(gT("Next", "unescaped"), array("class" => "btn btn-default", "id" => "ls-next")); ?>
            </div>
        </div>
        <?php echo CHtml::endForm(); ?>

    </div>
</div>


