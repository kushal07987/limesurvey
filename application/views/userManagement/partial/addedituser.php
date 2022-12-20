<?php
/** @var  User $oUser */

$modalTitle = $oUser->isNewRecord ? gT('Add user') : gT('Edit user');
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => $modalTitle]
);
?>

<?php $form = $this->beginWidget('TbActiveForm', array(
    'id' => 'UserManagement--modalform',
    'action' => App()->createUrl('userManagement/applyedit'),
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
)); ?>

<div class="modal-body">
    <div class="container">

        <?= $form->hiddenField($oUser, 'uid', ['uid' => 'User_Form_users_id']) ?>
        <div class="mb-3" id="UserManagement--errors">

        </div>
        <div class="mb-3">
            <?php echo $form->labelEx($oUser, 'users_name', ['for' => 'User_Form_users_name']); ?>
            <?php
            if ($oUser->isNewRecord) {
                echo $form->textField($oUser, 'users_name', ['id' => 'User_Form_users_name', 'required' => 'required']);
            } else {
                echo '<input class="form-control" type="text" name="usernameshim" value="' . $oUser->users_name . '" disabled="true" />';
            }
            ?>

            <?php echo $form->error($oUser, 'users_name'); ?>
        </div>
        <div class="mb-3">
            <?php echo $form->labelEx($oUser, 'full_name', ['for' => 'User_Form_full_name']); ?>
            <?php echo $form->textField($oUser, 'full_name', ['id' => 'User_Form_full_name']); ?>
            <?php echo $form->error($oUser, 'full_name'); ?>
        </div>
        <div class="mb-3">
            <?php echo $form->labelEx($oUser, 'email', ['for' => 'User_Form_email']); ?>
            <?php echo $form->emailField($oUser, 'email', ['id' => 'User_Form_email', 'required' => 'required']); ?>
            <?php echo $form->error($oUser, 'email'); ?>
        </div>
        <div class="mb-3">
            <label class="form-label" for='expires'><?php eT("Expire date/time:"); ?></label>
            <div class="has-feedback">
                <?php
                Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                    'name' => 'expires',
                    'id' => 'expires',
                    'value' => $oUser->expires ? date(
                        $dateformatdetails['phpdate'] . " H:i",
                        strtotime($oUser->expires)
                    ) : '',
                    'pluginOptions' => [
                        'format' => $dateformatdetails['jsdate'] . " HH:mm",
                        'allowInputToggle' => true,
                        'showClear' => true,
                        'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                    ]
                ));
                ?>
            </div>
            <?php echo $form->error($oUser, 'expires'); ?>
        </div>
        <?php if (!$oUser->isNewRecord): ?>
            <div class="mb-3">
                <?php echo $form->labelEx($oUser, 'last_login', ['for' => 'User_Form_last_login']); ?>
                <input class="form-control" type="text" value="<?= !empty($oUser->last_login) ? convertToGlobalSettingFormat($oUser->last_login, true) : gT("Never") ?>" disabled="true" />
            </div>
            <div class="mb-3">
                <input type="checkbox" id="utility_change_password">
                <label for="utility_change_password"><?= gT("Change password?") ?></label>
            </div>
        <?php else: ?>
            <div class="mb-3" id="utility_set_password">
                <div class="col-6">
                    <label><?= gT("Set password now?") ?></label>
                </div>
                <div class="btn-group col-6" data-bs-toggle="buttons">
                    <input class="btn-check" type="radio" id="utility_set_password_yes" name="preset_password" value="1">
                    <label for="utility_set_password_yes" class="btn btn-outline-secondary col-xs-6">
                        <?= gT("Yes") ?>
                    </label>
                    <input class="btn-check" type="radio" id="utility_set_password_no" checked="checked" name="preset_password" value="0">
                    <label for="utility_set_password_no" class="btn btn-outline-secondary col-xs-6">
                        <?= gT("No") ?>
                    </label>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-none" id="utility_change_password_container">
            <div class="mb-3">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?= gT('If you set a password here, no email will be sent to the new user.') ?>
                </div>
                <?php echo $form->labelEx($oUser, 'password', ['for' => 'User_Form_password']); ?>
                <?php echo $form->passwordField(
                    $oUser,
                    'password',
                    ($oUser->isNewRecord
                        ? ['id' => 'User_Form_password', 'value' => '', 'placeholder' => '********']
                        : ['id' => 'User_Form_password', 'value' => '', 'placeholder' => '********', "disabled" => "disabled"]
                    )
                ); ?>
                <?php echo $form->error($oUser, 'password'); ?>
            </div>
            <div class="mb-3">
                <label for="password_repeat" class="required" required><?= gT("Password safety") ?> <span class="required">*</span></label>
                <input name="password_repeat" placeholder='********' <?= ($oUser->isNewRecord ? '' : 'disabled="disabled"') ?> id="password_repeat" class="form-control" type="password">
            </div>
            <?php if ($oUser->isNewRecord) { ?>
                <div class="mb-3">
                    <label class="form-label">
                        <?= gT('Random password (suggestion):') ?>
                    </label>
                    <input type="text" class="form-control" readonly name="random_example_password" value="<?= htmlspecialchars($randomPassword) ?>"/>
                </div>
            <?php } ?>
        </div>

    </div>
</div>

<div class="modal-footer modal-footer-buttons" style="margin-top: 15px;">
    <button class="btn btn-cancel" id="exitForm" data-bs-dismiss="modal">
        <?= gT('Cancel') ?>
    </button>
    <button class="btn btn-success" id="submitForm">
        <?= gT('Add') ?>
    </button>
</div>
<?php $this->endWidget(); ?>
