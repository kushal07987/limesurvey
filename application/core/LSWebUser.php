<?php
/*
* LSWebUser class file
* Copyright (C) 2007-2019 The LimeSurvey Project Team / Carsten Schmitz
*
*/

/**
 * @inheritDoc
 */

class LSWebUser extends CWebUser
{
    protected $sessionVariable = 'LSWebUser';


    public function __construct()
    {
        Yii::import('application.helpers.Hash', true);
        $this->loginUrl = Yii::app()->createUrl('admin/authentication', array('sa' => 'login'));
    }

    public function checkAccess($operation, $params = array(), $allowCaching = true)
    {
        if ($operation == 'administrator') {
            return Permission::model()->hasGlobalPermission('superadmin', 'read');
        } else {
            return parent::checkAccess($operation, $params, $allowCaching);
        }
    }

    public function getStateKeyPrefix()
    {
        return $this->sessionVariable;
    }


    public function setFlash($key, $value, $defaultValue = null)
    {
        $this->setState("flash.$key", $value, $defaultValue);
    }
    public function hasFlash($key)
    {
        $this->hasState("flash.$key");
    }

    public function getFlashes($delete = true)
    {
        $result = $this->getState('flash', array());
        $this->removeState('flash');
        return $result;
    }

    /**
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed|null
     */
    public function getState($key, $defaultValue = null)
    {
        if (!isset($_SESSION[$this->sessionVariable]) || !Hash::check($_SESSION[$this->sessionVariable], $key)) {
            return $defaultValue;
        } else {
            return Hash::get($_SESSION[$this->sessionVariable], $key);
        }
    }

    /**
     * Removes a state variable.
     * @param string $key
     */
    public function removeState($key)
    {
        $this->setState($key, null);
    }

    public function setState($key, $value, $defaultValue = null)
    {
        $current = isset($_SESSION[$this->sessionVariable]) ? $_SESSION[$this->sessionVariable] : array();
        if ($value === $defaultValue) {
            $_SESSION[$this->sessionVariable] = Hash::remove($current, $key);
        } else {
            $_SESSION[$this->sessionVariable] = Hash::insert($current, $key, $value);
        }
    }

    public function hasState($key)
    {
        return isset($_SESSION[$this->sessionVariable]) && Hash::check($_SESSION[$this->sessionVariable], $key);
    }

    /**
     * Test if a user is in a group
     * @param int $gid
     * @return boolean
     */
    public function isInUserGroup($gid)
    {
        $oUsergroup = UserGroup::model()->findByPk($gid);

        // The group doesn't exist anymore
        if (!is_object($oUsergroup)) {
            return false;
        }

        $users = $oUsergroup->users;
        $aUids = array();
        foreach ($users as $user) {
            $aUids[] = $user->uid;
        }

        if (in_array($this->id, $aUids)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if user are allowed to edit script
     * @return boolean
     */
    public function scriptAllowUpdate()
    {
        if (Yii::app()->getConfig('forcedfilterxss') && !Yii::app()->getConfig('superadminenablescript')) {
            /* TODO : review to empty when save ? */
            /* here , fixed when import and create, but no fix when save existing. */
            return false;
        }
        if (!Yii::app()->getConfig('disablescriptwithxss')) {
            return true;
        }
        if (!Yii::app()->getConfig('filterxsshtml')) {
            return true;
        }
        return !\Permission::model()->hasGlobalPermission('superadmin', 'read');
    }

    /**
     * Check if user are allowed to add script inside text (XSS)
     * @return boolean
     */
    public function xssFiltered()
    {
        if (Yii::app()->getConfig('DBVersion') < 172) {
            // Permission::model exist only after 172 DB version
            return Yii::app()->getConfig('filterxsshtml');
        }
        if (Yii::app()->getConfig('forcedfilterxss')) {
            return true;
        }
        if (Yii::app()->getConfig('filterxsshtml')) {
            return !\Permission::model()->hasGlobalPermission('superadmin', 'read');
        }
        return false;
    }
}
