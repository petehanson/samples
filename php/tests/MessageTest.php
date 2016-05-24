<?php


class MessageTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $file1 = null;
    protected $file2 = null;
    protected $simpleFile = null;
    protected $fromObject = null;
    protected $emptyFile = null;
    protected $fileNotFound = null;
    protected $ccbcc = null;
    protected $conditional = null;
    protected $multibody = null;

    protected function _before()
    {
        $dataPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "_data" . DIRECTORY_SEPARATOR;

        $this->file1 = $dataPath . "testemail1.json";
        $this->file2 = $dataPath . "testemail2.json";
        $this->emptyFile = $dataPath . "empty.json";
        $this->simpleFile = $dataPath . "simpletest.json";
        $this->fromObject = $dataPath . "fromobject.json";
        $this->fileNotFound = $dataPath . "doesnotexist.json";
        $this->ccbcc = $dataPath . "testccbcc.json";
        $this->conditional = $dataPath . "conditional.json";
        $this->multibody = $dataPath . "multibody.json";
    }

    protected function _after()
    {
    }

    public function testConditional() {
        $message = new \UAR\Emailer\Message(\UAR\Emailer\MessageConfig::load($this->conditional));
        $this->assertEquals("Conditional subject line",$message->getSubject());
        $message->replace("hasAddress",true);

        $this->assertEquals("conditional body: Yes",$message->getBody());

        $message = new \UAR\Emailer\Message(\UAR\Emailer\MessageConfig::load($this->conditional));
        $this->assertEquals("Conditional subject line",$message->getSubject());
        $message->replace("hasAddress",false);

        $this->assertEquals("conditional body: No",$message->getBody());

    }

    public function testMultiBody() {
        $message = new \UAR\Emailer\Message(\UAR\Emailer\MessageConfig::load($this->multibody));

        $this->assertEquals("this is the html text version",$message->getBody());

        $parts = $message->getChildren();
        $part = $parts[0];
        $this->assertEquals("this is the plain text version",$part->getBody());

    }

    public function testEmail1() {
        $message = new \UAR\Emailer\Message(\UAR\Emailer\MessageConfig::load($this->file1));

        $this->assertEquals("This is test email 1 subject line",$message->getSubject());
        $this->assertEquals("This is the test email 1 body",$message->getBody());

        $from = $message->getFrom();
        $emails = array_keys($from);
        $email = $emails[0];
        $name = $from[$email];

        $this->assertEquals("sender@example.com",$email);
        $this->assertEquals("Sender Name",$name);


        $recipients = $message->getTo();
        $emails= array_keys($recipients);
        $to1 = $emails[0];
        $to2 = $emails[1];

        $this->assertEquals("person1@example.com",$to1);
        $this->assertEquals("person2@example.com",$to2);

    }


    public function testEmail2() {
        $message = new \UAR\Emailer\Message(\UAR\Emailer\MessageConfig::load($this->file2));
        $message->replace("replace1","test1");
        $message->replace("replace2","test2");
        $message->replace("replace3","person2@example.com");
        $message->replace("replace4","sender@example.com");

        $this->assertEquals("This is test email 2 subject line test1",$message->getSubject());
        $this->assertEquals("This is the test email 2 body test2",$message->getBody());


        $recipients = $message->getTo();
        $emails= array_keys($recipients);
        $to1 = $emails[0];
        $to2 = $emails[1];

        $this->assertEquals("person1@example.com",$to1);
        $this->assertEquals("Person One",$recipients[$to1]);
        $this->assertEquals("person2@example.com",$to2);
        $this->assertEquals("Person Two",$recipients[$to2]);

        $from = $message->getFrom();
        $emails = array_keys($from);
        $email = $emails[0];
        $name = $from[$email];

        $this->assertEquals("sender@example.com",$email);
        $this->assertEquals("Sender Name",$name);
    }

    public function testSimpleEmail() {
        $message = new \UAR\Emailer\Message(\UAR\Emailer\MessageConfig::load($this->simpleFile));

        $this->assertEquals("This is simple email subject line",$message->getSubject());
        $this->assertEquals("This is a simple email test",$message->getBody());

        $from = $message->getFrom();
        $emails = array_keys($from);
        $email = $emails[0];

        $this->assertEquals("sender@example.com",$email);

        $recipients = $message->getTo();
        $emails= array_keys($recipients);
        $to = $emails[0];

        $this->assertEquals("recipient@example.com",$to);

    }

    public function testFromObject() {
        $message = new \UAR\Emailer\Message(\UAR\Emailer\MessageConfig::load($this->fromObject));

        $from = $message->getFrom();
        $emails = array_keys($from);
        $email = $emails[0];
        $name = $from[$email];

        $this->assertEquals("sender@example.com",$email);
        $this->assertEquals("Sender Person",$name);

    }

    /**
     * @expectedException Exception
     */
    public function testInvalidJson() {
        $message = new \UAR\Emailer\Message(null);
    }

    public function testCcBcc() {

        $originalJson = \UAR\Emailer\MessageConfig::load($this->ccbcc);

        $message = new \UAR\Emailer\Message($originalJson);

        $message->replace("replace1","test1");
        $message->replace("replace2","test2");
        $message->replace("replace3","person2@example.com");
        $message->replace("replace4","sender@example.com");

        $from = $message->getFrom();
        $emails = array_keys($from);
        $email = $emails[0];
        $name = $from[$email];

        $this->assertEquals("sender@example.com",$email);
        $this->assertEquals("Sender Name",$name);

        $ccs = $message->getCc();
        $emails = array_keys($ccs);
        $email = $emails[0];

        $this->assertEquals("cc1@example.com",$email);

        $bccs = $message->getBcc();
        $emails = array_keys($bccs);
        $email = $emails[0];

        $this->assertEquals("bcc1@example.com",$email);

        // test the CC system with the alternate CC recipient format
        $obj = json_decode($originalJson);
        $cc1 = new stdClass();
        $cc1->email = "cc1@example.com";
        $cc1->name = "CC One";
        $obj->cc = array($cc1);

        $bcc1 = new stdClass();
        $bcc1->email = "bcc1@example.com";
        $bcc1->name = "BCC One";

        $bcc2 = new stdClass();
        $bcc2->email = "bcc2@example.com";
        $bcc2->name = "BCC Two";
        $obj->bcc = array($bcc1,$bcc2);
        $json = json_encode($obj);

        $message = new \UAR\Emailer\Message($json);
        $message->replace("replace1","test1");
        $message->replace("replace2","test2");
        $message->replace("replace3","person2@example.com");
        $message->replace("replace4","sender@example.com");

        $ccs = $message->getCc();
        $emails = array_keys($ccs);
        $email = $emails[0];

        $this->assertEquals("cc1@example.com",$email);
        $this->assertEquals("CC One",$ccs[$email]);

        $bccs = $message->getBcc();
        $emails = array_keys($bccs);
        $email1 = $emails[0];
        $email2 = $emails[1];

        $this->assertEquals("bcc1@example.com",$email1);
        $this->assertEquals("BCC One",$bccs[$email1]);
        $this->assertEquals("bcc2@example.com",$email2);
        $this->assertEquals("BCC Two",$bccs[$email2]);

        // test no CC or BCC fields

        $obj = json_decode($originalJson);
        unset($obj->cc);
        unset($obj->bcc);
        $json = json_encode($obj);

        $message = new \UAR\Emailer\Message($json);
        $message->replace("replace1","test1");
        $message->replace("replace2","test2");
        $message->replace("replace3","person2@example.com");
        $message->replace("replace4","sender@example.com");

        $this->assertNull($message->getCc());
        $this->assertNull($message->getBcc());
    }



}