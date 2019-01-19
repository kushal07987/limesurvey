<?php
/**
 * Set question group and position modal body (parsed to massive action widget)
 */

/** @var AdminController $this */
/** @var Question $model */
?>

<?php eT("Set question group for those question"); ?>
<form class="custom-modal-datas">

    <!-- select group -->
    <div class="form-group">
        <label class="control-label col-sm-4" for="group_gid"><?php et('Group:'); ?></label>
        <div class="col-sm-8">
            <select name="group_gid" class="form-control custom-data" id="gid">
                <?php foreach($model->survey->groups as $group): ?>
                    <option value="<?php echo $group->gid;?>">
                        <?php echo flattenText($group->questionGroupL10ns[$model->survey->language]->group_name);?>
                    </option>
                <?php endforeach?>
            </select>
        </div>

    </div>

    <br/><br/>

    <!-- Position widget -->
    <?php $this->widget('ext.admin.survey.question.PositionWidget.PositionWidget', array(
                'display' => 'ajax_form_group',
                'oSurvey' => $model->survey,
                'classes' => 'custom-data'
        ));
    ?>
</form>
