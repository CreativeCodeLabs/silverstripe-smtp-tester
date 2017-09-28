<?php

class SmtpTester extends LeftAndMain implements PermissionProvider {
    private static $menu_title = 'SMTP Tester';
    private static $menu_icon = 'silverstripe-smtp-tester/images/icon-email.png';
    private static $url_segment = 'smtp-tester';

    private static $allowed_actions = array (
        'SmtpTesterForm'
    );

    public function init() {
        parent::init();

        Requirements::css('silverstripe-smtp-tester/css/smtp-tester.css');
    }

    public function providePermissions() {
        return array(
            "CMS_ACCESS_SmtpTester" => array(
                "name" => "Access to SMTP Tester section",
                "category" => "CMS Access",
                "help" => "Allow use of the SMTP Tester"
            )
        );
    }

    public function canView($member = null) {
        $userDomainWhitelist = Config::inst()->get('SmtpTester','user_domain_whitelist');

        if (!is_null($userDomainWhitelist) && $userDomainWhitelist != "" && !is_null($member)) {
            if (strpos($userDomainWhitelist,",") !== false) {
                $userDomainWhitelist = explode(",",$userDomainWhitelist);
            } else {
                $userDomainWhitelist = array($userDomainWhitelist);
            }

            $emailParts = explode("@",$member->Email);

            if (count($emailParts) == 1) {
            	$domain = $emailParts[0];
			} else {
				$domain = $emailParts[1];
			}

			if (!in_array($domain,$userDomainWhitelist)) {
				return false;
			}
        }

        return Permission::check("CMS_ACCESS_SmtpTester");
    }

    public function SmtpTesterForm() {
        $siteName = SiteConfig::current_site_config()->Title;
        $memberEmail = Member::currentUser()->Email;
        $adminEmail = Config::inst()->get('Email', 'admin_email');
        $fieldsArr = array();

        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $adminEmail = "test@".str_replace(Director::protocol(),"",Director::protocolAndHost());
        }

        $fieldsArr[] = TextField::create('EmailTo','To')
            ->setAttribute('placeholder', $memberEmail);
        $fieldsArr[] = TextField::create('EmailFrom','From')
            ->setAttribute('placeholder',$adminEmail);
        $fieldsArr[] = TextField::create('EmailSubject','Subject')
            ->setAttribute('placeholder','Test Email from '.$siteName);
        $fieldsArr[] = TextareaField::create('EmailMessage','Message')
            ->setAttribute('placeholder', 'This is a test email from '.$siteName);

        $fields = new FieldList($fieldsArr);

        $sendTestEmailButton = FormAction::create('send_test_email', 'Send Test Email');

        $actions = new FieldList($sendTestEmailButton);

        $required = new RequiredFields(
            array()
        );

        $form = new Form($this, 'SmtpTesterForm', $fields, $actions, $required);

        $form->setFormMethod('POST', true);

        return $form;
    }

    public function send_test_email($data, Form $form) {
        $siteName = SiteConfig::current_site_config()->Title;
        $memberEmail = Member::currentUser()->Email;
        $errorMessage = "";

        $to = $data['EmailTo'] && $data['EmailTo'] != "" ? $data['EmailTo'] : $memberEmail;
        $from = $data['EmailFrom'] && $data['EmailFrom'] != "" ? $data['EmailFrom'] : null;
        $subject = $data['EmailSubject'] && $data['EmailSubject'] != "" ? $data['EmailSubject'] : "Test Email from {$siteName}";
        $message = $data['EmailMessage'] && $data['EmailMessage'] != "" ? $data['EmailMessage'] : "This is a test email from {$siteName}.";

        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $from = "test@".str_replace(Director::protocol(),"",Director::protocolAndHost());
        }

        try {
            $email = new Email($from,$to,$subject,$message);
            $status = $email->send();
        } catch (Exception $e) {
            $status = false;
            $errorMessage = " Error Message: {$e->getMessage()}";
        }

        if ($status) {
            $form->sessionMessage("Email sent successfully!", 'good');
        } else {
            $form->sessionMessage("Email failed to send.{$errorMessage}", 'bad');
        }

        return $this->redirectBack();
    }
}
