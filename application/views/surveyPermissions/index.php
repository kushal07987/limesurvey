<?php
/* @var $basePermissions array the base permissions a user could have */
/* @var $userCreatePermission bool true if current user has permission to set survey permission for other users */
/* @var $surveyid int */
/* @var $userList array users that could be added to survey permissions */
/* @var $userGroupList array usergroups that could be added to survey permissions */
/* @var $tableContent CActiveDataProvider dataProvider for the gridview (table) */
/* @var $oSurveyPermissions \LimeSurvey\Models\Services\SurveyPermissions */

?>
<div id='edit-permission' class='side-body  <?= getSideBodyClass(false) ?> "'>
    <?php echo viewHelper::getViewTestTag('surveyPermissions'); ?>
    <h3> <?= gT("Survey permissions") ?> </h3>

    <div class="row">
        <div class="col-lg-12 content-right">
            <?php
            $this->renderPartial('_overview_table', [
                'basePermissions' => $basePermissions,
                'tableContent' => $tableContent,
                'surveyid' => $surveyid,
                'oSurveyPermissions' => $oSurveyPermissions
            ]);

            if ($userCreatePermission) { //only show adduser and addusergroup buttons if permission is set
                echo CHtml::form(
                    array("surveyPermissions/adduser/surveyid/{$surveyid}"),
                    'post',
                    array('class' => "form44")
                ); ?>
                <br/><br/>
                <div class="row justify-content-md-center">
                    <label class='col-sm-1 col-md-offset-2  text-right control-label' for='uidselect'>
                        <?= gT("User") ?>:
                    </label>
                    <div class='col-sm-4'>
                        <select id='uidselect' name='uid' class='form-select'>
                            <?php
                            if (count($userList) > 0) {
                                echo "<option value='-1' selected='selected'>" . gT("Please choose...") . "</option>";
                                foreach ($userList as $selectableUser) {
                                    echo "<option value='{$selectableUser['userid']}'>"
                                        . \CHtml::encode($selectableUser['usersname']) . " "
                                        . \CHtml::encode($selectableUser['fullname']) . "</option>\n";
                                }
                            } else {
                                echo "<option value='-1'>" . gT("None") . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <input style='width: 15em;' class='btn btn-outline-secondary' type='submit' value='<?= gT("Add user") ?>'/>
                    <input type='hidden' name='action' value='addsurveysecurity'/>
                </div>
                </form>
                <br/>
                <?php
                echo CHtml::form(
                    array("surveyPermissions/addusergroup/surveyid/{$surveyid}"),
                    'post',
                    array('class' => "form44")
                ); ?>
                <div class="row justify-content-md-center">
                    <label class='col-sm-1 col-md-offset-2  text-right control-label' for='ugidselect'>
                        <?= gT("User group") ?>:
                    </label>
                    <div class='col-sm-4'>
                        <select id='ugidselect' name='ugid' class='form-select'>
                            <?php
                            if (count($userGroupList) > 0) {
                                echo "<option value='-1' selected='selected'>" . gT("Please choose...") . "</option>";
                                foreach ($userGroupList as $userGroup) {
                                    echo "<option value='{$userGroup['ugid']}'>{$userGroup['name']}</option>";
                                }
                            } else {
                                echo "<option value='-1'>" . gT("None") . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <input style='width: 15em;' class='btn btn-outline-secondary' type='submit'
                           value='<?= gT("Add group users") ?>'/>
                    <input type='hidden' name='action' value='addusergroupsurveysecurity'/>
                </div>
                </form>
            <?php }
            ?>

        </div>
    </div>
</div>
