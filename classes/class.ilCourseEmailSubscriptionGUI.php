<?php

/*
 * @ilCtrl_isCalledBy ilCourseEmailSubscriptionGUI : ilUIPluginRouterGUI
 * */

class ilCourseEmailSubscriptionGUI{

    protected $courseObject;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    protected $tpl;

    protected $tabs;

    const cmd_show = 'show';
    const cmd_subscribe = 'subscribe';

    function __construct() {
        global $tpl, $ilCtrl, $ilTabs, $ilLocator;
        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->tpl = $tpl;
        $this->courseObject = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
        $this->tpl->getStandardTemplate();
        $this->buildHeader($ilLocator);
    }


    function executeCommand(){
        global $tpl;

        $cmd = $this->ctrl->getCmd('show');
        switch($cmd) {
            case self::cmd_show:
            case self::cmd_subscribe:
                $this->{$cmd}();
                break;
            case 'save':
                $this->save();
                break;
        }
        $tpl->show();

        /*global $tpl, $ilCtrl;

        $tpl->getStandardTemplate();
        $tpl->setContent("hallo world");
        $this->initHeader();
        $tpl->show();
        //echo "hello world";*/
    }

    protected function show() {
        $this->tpl->getStandardTemplate();
        global $ilLocator, $ilAccess;

        if(!$ilAccess->checkAccess('write','', $_GET['ref_id'])){
            ilUtil::sendFailure("Access denied!");
            return;
        }

        $this->buildHeader($ilLocator);

        $form = $this->buildForm();

        $this->tpl->setContent($form->getHTML());

    }

    protected function save(){
        global $tpl;

        $form = $this->buildForm();
        if($form->checkInput()){
            $form->setValuesByPost();

            $emails = $form->getInput('emails');

            require_once"Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CourseEmailSubscription/classes/class.ilEmailSubscriber.php";
            $subscriber = new ilEmailSubscriber($this->courseObject->getRefId());

            $emails = $subscriber->getEmailsFromString($emails);

            foreach($emails as $email){
                $subscriber->subscribeEmail($email);
            }

            //$emailsFound = $subscriber->getEmailsFound();
            //$emailsNotFound = $subscriber->getEmailsNotFound();

            ilUtil::sendSuccess('Folgende Benutzer wurden eingeschrieben: '.(implode(', ', $subscriber->getEmailsFound())), true);
            ilUtil::sendInfo('Folgende E-Mail adressen konnten nicht gefunden werden: '.(implode(', ', $subscriber->getEmailsNotFound())), true);

            $this->ctrl->redirect($this, 'show');

        }else{

            $tpl->setContent($form->getHTML());

        }

    }

    /**
     * @param $ilLocator
     */
    protected function buildHeader($ilLocator)
    {
        $this->tpl->setTitle($this->courseObject->getTitle()); // Der Titel des Kurses
        $this->tpl->setDescription($this->courseObject->getDescription()); // Die Beschreibung des Kurses
        $this->tpl->setTitleIcon(ilObject::_getIcon($this->courseObject->getId(), 'big')); // Das Bild soll des Kurses

        // Wir fügen einen Zurückknopf ein. Dieser soll die Members des Kurses anzeigen
        $this->ctrl->saveParameterByClass('ilObjCourseGUI', 'ref_id'); //Wir müssen die ref_id speichern, damit der Link zum richtigen Kurs zeigt
        $this->tabs->setBackTarget('Zurück', $this->ctrl->getLinkTargetByClass(array(
            'ilRepositoryGUI',
            'ilObjCourseGUI'
        ), 'members'));

        // Wir fügen in den folgenden zwei Zeilen den Locator hinzu. (Breadcrumbs über dem Titel).
        $ilLocator->addRepositoryItems($this->courseObject->getRefId());
        $this->tpl->setLocator($ilLocator->getHTML());
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function buildForm()
    {
        require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setTitle("Mitgliedern Einschreiben");
        $form->setDescription("Bitte geben Sie eine Liste von Emails aus.");

        require_once "Services/Form/classes/class.ilTextAreaInputGUI.php";
        $textarea = new ilTextAreaInputGUI('E-mail Addresse', 'emails');
        $textarea->setRequired(true);
        $textarea->setRows(20);

        $form->addItem($textarea);

        $this->ctrl->saveParameter($this, 'ref_id');
        $form->addCommandButton('save', 'Speichern');
        $form->setFormAction($this->ctrl->getFormAction($this));
        return $form;
    }

}
?>