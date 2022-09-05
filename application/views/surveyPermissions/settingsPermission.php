<?php

/* @var $surveyid int */
/* @var $aPermissions array has all permissions in */
/* @var $isUserGroup bool indicates that permissions for a user group should be set */
/* @var $id int */
/* @var $name string */

/**
 * This page shows the permissions that could be set for a user or a user group.
 */

?>

<div id='edit-permission' class='side-body  <?= getSideBodyClass(false) ?> "'>
    <h3>
        <?php
        if ($isUserGroup) {
            echo sprintf(gT("Edit survey permissions for user %s"), "<em>" . \CHtml::encode($userGroupName) . "</em>");
        } else {
            echo sprintf(gT("Edit survey permissions for user %s"), "<em>" . \CHtml::encode($userName) . "</em>");
        }
        ?>
    </h3>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php echo CHtml::form(
                array("surveyPermissions/savePermissions/surveyid/{$surveyid}")
            );
            echo App()->getController()->widget(
                'ext.UserPermissionsWidget.UserPermissionsWidget',
                ['aPermissions' => $aPermissions],
                true
            );?>
            <input class='btn btn-default hidden'  type='submit' value='<?=gT("Save Now") ?>' />"
            <input type='hidden' name='action' value='surveyrights' />
            <?php
            if ($isUserGroup) { ?>
                    <input type='hidden' name='ugid' value="<?= $userGroupId?>>" />
                <?php
            } else {?>
                    <input type='hidden' name='uid' value="<?= $userId?>" />
            <?php }
            ?>
            <?php echo CHtml::endForm(); ?>
        </div>
    </div>
</div>
