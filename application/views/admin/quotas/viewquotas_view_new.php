
<script>
    function addStaticRaw(){
        alert("kjdf");
        $('#party-ledger-grid tbody tr:first').before("<tr><td>Your static raw blah blah</td></tr>");
    }
</script>
<?php

/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var Quota[] $aQuotas */
/* @var CActiveDataProvider $oDataProvider */
/* @var string $editUrl */
/* @var string $deleteUrl */

?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'active'=> gT("Survey quotas"))); ?>
            <h3>
                <?php eT("Survey quotas");?>
            </h3>

            <?php if( isset($sShowError) ):?>
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong><?php eT("Quota could not be added!", 'js'); ?></strong><br/> <?php eT("It is missing a quota message for the following languages:", 'js'); ?><br/><?php echo $sShowError; ?>
                </div>
            <?php endif; ?>


            <!-- Grid -->
            <div class="row">
                <div class="col-sm-12 content-right">
                    <?php
                    $surveyGrid = $this->widget('bootstrap.widgets.TbGridView', array(
                        'dataProvider' => $oDataProvider,

                        // Number of row per page selection
                        'id' => 'survey-grid',
                        'emptyText'=>gT('No quotas'),

                        'columns' => array(

                            array(
                                'id'=>'id',
                                'class'=>'CCheckBoxColumn',
                                'selectableRows' => '100',
                            ),
                            array(
                                'name'=>'name',
                                'value'=>'$data->name',
                            ),
                            array(
                                'name'=>'active',
                                'type'=>'raw',
                                'value'=>function($oQuota){
                                    if($oQuota->active==1){
                                        return '<font color="#48B150">'.gT("Active").'</font>';
                                    }else{
                                        return '<font color="#B73838">'.gT("Not active").'</font>';
                                    }
                                },
                            ),
                            array(
                                'name'=>'completed',
                                'type'=>'raw',
                                'value'=>function($oQuota)use($oSurvey){
                                    return getQuotaCompletedCount($oSurvey->sid, $oQuota->id);
                                },
                            ),
                            'qlimit',
                            array(
                                'name'=>'action',
                                'value'=>function($oQuota){
                                    if($oQuota->action==1){
                                        return gT("Terminate survey");
                                    }elseif ($oQuota->action==1){
                                        return gT("Terminate survey with warning");
                                    }
                                },
                            ),
                            array(
                                'header'=>gT("Action"),
                                'value'=>function($oQuota)use($oSurvey,$editUrl,$deleteUrl){
                                    /** @var Quota $oQuota */
                                    $this->renderPartial('/admin/quotas/viewquotas_quota_actions',
                                        array(
                                            'oSurvey'=>$oSurvey,
                                            'oQuota'=>$oQuota,
                                            'editUrl'=>$editUrl,
                                            'deleteUrl'=>$deleteUrl,

                                        ));
                                },
                                'headerHtmlOptions'=>array(
                                    'style'=>'text-align:right;',
                                ),
                                'htmlOptions'=>array(
                                    'align'=>'right',
                                ),
                            ),
                            array(
                                'type'=>'raw',
                                'value'=>function($oQuota)use($oSurvey){
                                    /** @var Quota $oQuota */
                                    $this->renderPartial('/admin/quotas/viewquotas_quota_items',
                                        array(
                                            'oSurvey'=>$oSurvey,
                                            'oQuota'=>$oQuota,
                                        ));
                                },
                            ),

                        ),
                        'itemsCssClass' =>'table-striped',
                    ));
                    ?>
                </div>
            </div>


        </div>
    </div>
</div>