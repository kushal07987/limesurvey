<?php
/**
 * Important functionailites are set in core now, this is just to render the output
 */

 $gid = $gid ?? NULL;
 $qid = $qid ?? NULL;

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyLogicFile');
?>

<<<<<<< HEAD
<div id='edit-survey-text-element' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h1 class="pagetitle h1"><?php eT("Survey logic view");?> </h1>
=======
<div id='edit-survey-text-element' class='side-body'>
    <div class="pagetitle h1"><?php eT("Survey logic view");?> </div>
>>>>>>> master
    <div class="row">
        <?=TbHtml::form(array('admin/expressions/sa/survey_logic_file'), 'post', array('id'=>'survey_logic_file_form', 'target' => '_blank'))?>
            <input name="sid" type="hidden" value="<?=$sid?>" />
            <?php if($gid!==NULL): ?> <input name="gid" type="hidden" value="<?=$gid?>" /> <?php endif; ?>
            <?php if($qid!==NULL): ?> <input name="qid" type="hidden" value="<?=$qid?>" /> <?php endif; ?>
            <?php if($lang!==NULL): ?> <input name="lang" type="hidden" value="<?=$lang?>" /> <?php endif; ?>

            <input name="printable" type="hidden" value="1" />
            <div class="mb-3">
                <input type="submit" name="printablesubmit" value="<?=gT("Open printable view")?>" class="btn btn-outline-secondary" role="link"/>
            </div>
        </form>
    </div>
    <div class="row">
        <div class="col-12 content-right">
            <?php echo $result['html']; ?>
        </div>
    </div>
</div>