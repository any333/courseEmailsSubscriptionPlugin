<?php

include_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");

class ilCourseEmailSubscriptionUIHookGUI extends ilUIHookPluginGUI
{

    /*
     * @var ilCtrl
     * */
    protected $ctrl;


    public function __construct()
    {
        global $ilCtrl;
        $this->ctrl = $ilCtrl;


    }

    function modifyGUI($a_comp, $part, $contextElements = array())
    {

        if ($part == "tabs" && $this->isInCourseGUI())
        {
            /** @var ilTabsGUI $tabs */
            $tabs = $contextElements["tabs"];
//            $contextElements["tabs"]->addTab("test", "test", "test");
            $this->ctrl->saveParameterByClass('ilCourseEmailSubscriptionGUI', 'ref_id');
            $tabs->addTab('courseSubscription', 'Mitglieder Einschreiben', $this->ctrl->getLinkTargetByClass(array('ilUIPluginRouterGUI', 'ilCourseEmailSubscriptionGUI'), 'show'));
//            $tabs->setTabActive('tab_view_content');
//            var_dump($tabs->getActiveTab());
        }

        if ($part == "tabs") {
            //var_dump($this->ctrl->getCallHistory()); //!!Fuer den Bereich zu ueberpruefen

            print_r($this->ctrl->getCallHistory());
        }

    }

    protected function isInCourseGUI() {
        foreach($this->ctrl->getCallHistory() as $GUIClassesArray) {
            if($GUIClassesArray['class'] == 'ilObjCourseGUI')
                return true;
        }
        return false;
    }

}


?>