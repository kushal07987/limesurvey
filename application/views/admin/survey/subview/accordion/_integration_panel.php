<?php
/**
 * @var $this AdminController
 *
* Right accordion, integration panel
* Use datatables, needs surveysettings.js
*/
$yii = Yii::app();
$controller = $yii->getController();
$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyPanelIntegration');
?>
  <!-- Datatable translation-data -->
  <!-- Container -->
  <div id='panelintegration' class=" tab-pane fade show active" >
    <div class="container">
        <div class="row">
            <div class="col-lg-12 ls-flex ls-flex-row">
                <div class="ls-flex-item text-start">
                    <button class="btn btn-success" id="addParameterButton"><?= gT('Add URL parameter') ?></button>
                </div>
                <div class="ls-flex-item justify-content-end row row-cols-lg-auto g-1 align-items-center mb-3">
                    <!-- Search Box -->
                    <div class="col-12">
                        <label class="control-label text-right" for="search_query">Search:</label>
                    </div>
                    <div class="col-12">
                        <input class="form-control" name="search_query" id="search_query" type="text">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success" type="button" id="searchParameterButton"><?= gT('Search', 'unescaped') ?></button>
                        <a href="<?= $updateUrl ?>" class="btn btn-warning"><?= gT('Reset') ?></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row table-responsive">
        <?php
            $this->widget(
                'ext.LimeGridView.LimeGridView',
                [
                    'id' => 'urlparams',
                    'dataProvider'    => $model->search(),
                    'emptyText'       => gT('No parameters defined'),
                    'htmlOptions'     => ['class' => 'table-responsive grid-view-ls'],
                    'template'        => "{items}\n<div id='integrationPanelPager'><div class=\"col-sm-4\" id=\"massive-action-container\"></div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                    'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                        gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            [
                                'class' => 'changePageSize form-control',
                                'style' => 'display: inline; width: auto'
                            ]
                        )
                    ),

                    // Columns to dispplay
                    'columns' => [

                        // Action buttons (defined in model)
                        [
                            'header'      => gT('Action'),
                            'name'        => 'actions',
                            'type'        => 'raw',
                            'value'       => '$data->buttons',
                            'htmlOptions' => ['class' => ''],
                        ],
                        // Parameter
                        [
                            'header' => gT('Parameter'),
                            'name'   => 'parameter',
                            'value'  => '$data->parameter'
                        ],
                        // Target Question
                        [
                            'header' => gT('Target question'),
                            'name'   => 'target_question',
                            'value'  => '$data->questionTitle',
                            'type'=>'raw'
                        ],

                    ],
                    'ajaxUpdate' => 'urlparams',
                    'rowHtmlOptionsExpression' => '["data-id" => $data->id, "data-parameter" => $data->parameter, "data-qid" => $data->targetqid, "data-sqid" => $data->targetsqid]',
                ]
            );
            ?>
        </div>
    </div>
</div>

<?php  
    App()->getClientScript()->registerScript('IntegrationPanel-variables', " 
    window.PanelIntegrationData = ".json_encode($jsData).";
    ", LSYii_ClientScript::POS_BEGIN ); 
?> 

<!-- Modal box to add a parameter -->
<!--div data-copy="submitsurveybutton"></div-->
<?php $this->renderPartial('addPanelIntegrationParameter_view', array('questions' => $questions)); ?>
