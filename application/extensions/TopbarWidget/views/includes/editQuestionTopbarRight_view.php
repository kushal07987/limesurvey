<!-- Save -->
<?php if(!empty($showSaveButton)): ?>
    <a
        id="save-button-create-question"
        class="btn btn-default"
        role="button"
        <?php if ($oQuestion->qid !== 0): // Only enable Ajax save for edit question, not create question. ?>
            data-save-with-ajax="true"
        <?php endif; ?>
        onclick="return LS.questionEditor.checkIfSaveIsValid(event, 'editor');"
    >
        <i class="fa fa-check-square"></i>
        <?php eT("Save");?>
    </a>
<?php endif; ?>

<?php /* Ported from previous versions: Pending to adapt to screen own JS for saving (and validations) 
<!-- Save and new group -->
<?php if(!empty($showSaveAndNewGroupButton)): ?>
    <a class="btn btn-default" id='save-and-new-button' role="button">
        <span class="fa fa-plus-square"></span>
        <?php eT("Save and new group"); ?>
    </a>
<?php endif; ?>

<!-- Save and add question -->
<?php if(!empty($showSaveAndNewQuestionButton)): ?>
    <a class="btn btn-default" id='save-and-new-question-button' role="button">
        <span class="fa fa-plus"></span>
        <?php eT("Save and add question"); ?>
    </a>
<?php endif; ?>
*/ ?>

<!-- Save and close -->
<?php if(!empty($showSaveAndCloseButton)): ?>
    <a
        id="save-and-close-button-create-question"
        class="btn btn-default"
        role="button"
        onclick="return LS.questionEditor.checkIfSaveIsValid(event, 'overview');"
    >
        <i class="fa fa-check-square"></i>
        <?php eT("Save and close");?>
    </a>
<?php endif; ?>

<!-- Close -->
<?php if(!empty($showCloseButton)): ?>
    <a class="btn btn-danger" href="<?php echo $closeUrl; ?>" role="button">
        <span class="fa fa-close"></span>
        <?php eT("Close");?>
    </a>
<?php endif;?>
