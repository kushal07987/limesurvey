<script type="text/javascript">
    var redUrl = "<?php echo $this->createUrl("/admin/participants/sa/displayParticipants"); ?>";
    var surveyId = "<?php echo $survey_id ?>";
    var redirect = "<?php echo $redirect ?>";
    if(redirect == 'on')
    {
        redUrl = "<?php echo $this->createUrl("/admin/tokens/sa/browse/surveyid/{$survey_id}"); ?>";
    }
    var copyUrl = "<?php echo $this->createUrl("/admin/participants/sa/addToTokenattmap"); ?>";

    // Comma separated string of participant ids
    var participant_id = "<?php echo $participant_id; ?>";

    /* SCRIPT TEXT */
    var attributesMappedText = "<?php eT("All the attributes are automatically mapped") ?>";
    var mustPairAttributeText= "<?php eT("You have to pair it with one attribute of the survey participants table") ?>";
    var onlyOneAttributeMappedText="<?php eT("Only one central attribute is mapped with participant attribute") ?>";
    var cannotAcceptTokenAttributesText="<?php eT("This list cannot accept survey participant attributes.") ?>";

</script>

<div class='header ui-widget-header'>
    <div class='pagetitle h3'>
        <?php eT("Map your central participant attributes to existing survey participant attributes or create new ones"); ?>
    </div>
</div>
<?php
    $columncount = 0;
    if (!empty($selectedcentralattribute))
    {
        $columncount = $columncount + 2;
    }
    if (!empty($selectedtokenattribute))
    {
        $columncount++;
    }
    $columnstyle = "attrcol_".$columncount;
?>

<div class='row'>
    <div class='col-sm-4'>
        <div id="centralattribute" class="card <?php echo $columnstyle ?>">
            <div class="card-header bg-primary"><?php eT("Unmapped participant attributes"); ?></div>
            <div class='card-body'>
                <div id="cpdbatt">
                    <?php
                    foreach ($selectedcentralattribute as $key => $value)
                    {
                        ?>
                        <div class='card col-sm-12' id='c_<?php echo $key; ?>'><div class='card-body'><?php echo $value; ?></div></div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class='col-sm-4'>
        <div id="newcreated" class="card <?php echo $columnstyle ?>">
            <div class="card-header bg-primary"><?php eT("Survey participant attributes to create"); ?></div>
            <div class='card-body' style="height:100%;">
                <div class="newcreate" id="sortable" style ="height:100%;">
                </div>
            </div>
        </div>
    </div>
    <div class='col-sm-4'>
        <div id='tokenattribute'>
            <div class='card'>
                <div class="card-header bg-primary">
                    <?php eT("Existing survey participant attributes"); ?>
                </div>
                <div class='card-body'>
                    <div class="tokenatt ui-sortable">
                        <?php foreach ($selectedtokenattribute as $id => $name): ?>
                                <?php if (isset($automaticallyMappedAttributes[$id])): ?>
                                    <?php $autoAttr = $automaticallyMappedAttributes[$id]; // Short-hand... ?>
                                    <div class='tokenatt-container col-sm-12'>
                                        <div class='col-sm-6'>
                                            <div class='card ui-state-disabled token-attribute' id='t_<?php echo $id; ?>'>
                                                <div class='card-body'>
                                                    <?php echo CHtml::encode($name); ?>
                                                    <span class='fa fa-arrows-h tokenatt-arrow'></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-sm-6'>
                                            <div class='card ui-state-disabled cpdb-attribute' id='c_<?php echo $autoAttr['cpdbAttribute']['attribute_id']; ?>'>
                                                <div class='card-body'>
                                                    <?php echo $autoAttr['cpdbAttribute']['attribute_name']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class='tokenatt-container col-sm-12'>
                                        <div class='col-sm-6'>
                                            <div class='card ui-state-disabled token-attribute' id='t_<?php echo $id; ?>'>
                                                <div class='card-body'>
                                                    <?php echo CHtml::encode($name); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <?php if(!empty($selectedtokenattribute)): ?>
                        <div class='explanation row'>
                            <div class='form-group'>
                                <label class='form-label col-sm-10 text-end' for='overwriteman'><?php eT("Overwrite existing participant attribute values if a participant already exists?") ?></label>
                                <div class='col-sm-2'>
                                    <input type='checkbox' id='overwriteman' name='overwriteman' />
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='form-label col-sm-10 text-end' for='createautomap'><?php eT("Make these mappings automatic in future") ?></label>
                                <div class='col-sm-2'>
                                    <input type='checkbox' id='createautomap' name='createautomap' />
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($alreadymappedattributename)): ?>
                        <div class='card-header bg-primary'>
                            <?php eT("Pre-mapped attributes") ?>
                        </div>
                        <div class='card-body'>
                            <div class="notsortable">
                                <?php
                                foreach ($alreadymappedattributename as $key => $value)
                                {
                                    ?>
                                    <div class='card' title='This attribute is already mapped' id=''><div class='card-body'><?php echo $value; ?></div></div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class='explanation'>
                                <input type='checkbox' id='overwrite' name='overwrite' /> <label for='overwrite'><?php eT("Overwrite existing auto mapped attribute values if a participant already exists?") ?></label>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class='card'>
                <div class='card-header bg-primary'><?php eT("Standard participant fields") ?></div>
                <div class='card-body'>
                    <div class="standardfields">
                        <div class='tokenatt-container col-sm-12'>
                            <div class='col-sm-6'>
                                <div class='card ui-state-disabled token-attribute' id='t_token'>
                                    <div class='card-body'>
                                        <?php eT("Participant") ?>
                                        <span class='fa fa-arrows-h tokenatt-arrow'></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='explanation'>
                        <div class='form-group'>
                            <label class='form-label col-sm-10 text-end' for='overwritest'>
                                <?php eT("Overwrite existing standard field values if a participant already exists?") ?>
                            </label>
                            <div class='col-sm-2'>
                                <input type='checkbox' id='overwritest' name='overwritest' />
                            </div>
                        </div>
                        <span class='help-block col-sm-10 text-end'><?php eT("Note: Standard participant fields cannot be automatically mapped") ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class='form-group col-sm-12 text-center'>
    <input class='btn btn-default' type="button" name="goback" onclick="history.back();" id="back" value="<?php eT('Back')?>" />
    <input class='btn btn-default' type='button' name='reset' onClick='window.location.reload();' id='reset' value="<?php eT('Reset') ?>" />
    <input class='btn btn-default' type="button" name="attmap" id="attmap" value="<?php eT('Continue')?>" />
</div>

<?php
$ajaxloader = array(
    'src' => Yii::app()->getConfig('adminimageurl') . '/ajax-loader.gif',
    'alt' => 'Ajax loader',
    'title' => 'Ajax loader'
);
?>
<div id="processing" title="<?php eT("Processing...") ?>" style="display:none">
<?php echo CHtml::image($ajaxloader['src'], $ajaxloader['alt']); ?>
</div>

<div id='attribute-map-participant-modal' class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php eT("Map participant attributes"); ?></h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Close");?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
