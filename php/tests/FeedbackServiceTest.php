<?php


use App\Model\Enums\EmailEntryStatus;
use App\Model\Enums\FeedbackType;
use App\Model\Feedback;
use App\Model\FeedbackCode;
use App\Services\FeedbackCodeService;
use App\Services\FeedbackService;
use App\Services\MessageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FeedbackServiceTest extends TestCase {

    use DatabaseTransactions;

    use TestDataCustomers,
        TestDataRecipients,
        TestDataMessages,
        TestDataEmailEntries,
        TestDataFeedbackCodes,
        TestDataFeedback;

    /** @var FeedbackService */
    private $feedbackService;

    public function __construct()
    {
        parent::__construct();
        $this->feedbackService = new FeedbackService(
            new MessageService(),
            new FeedbackCodeService()
        );
    }

    public function setUp()
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
        $this->createCustomer("customer1")
            ->createRecipient("customer1", "bob")
            ->createMessages()
            ->createEmailEntry(1, EmailEntryStatus::SENT, "bob")
            ->createFeedbackCodes(1, [1, 2, 3]);
    }

    public function testProcessNewFeedback_happy_flow_first_click()
    {
        /** @var FeedbackCode $feedbackCode */
        $feedbackCode = FeedbackCode::where("email_entry_id", 1)->where("feedback_type", FeedbackType::UNHAPPY)->first();

        // Check if the feedback is processed correctly when the customer clicks
        // on the link for the first time (a feedback record is created)
        $feedback1 = $this->feedbackService->processFeedbackCode($feedbackCode->code);
        $this->assertNotNull($feedback1);
        $this->assertEquals(EmailEntryStatus::CONFIRMED, $feedbackCode->emailEntry->status);
    }

    public function testProcessNewFeedback_happy_flow_multiple_clicks()
    {
        /** @var FeedbackCode $feedbackCode */
        $feedbackCode = FeedbackCode::where("email_entry_id", 1)->where("feedback_type", FeedbackType::UNHAPPY)->first();
        $feedback1 = $this->feedbackService->processFeedbackCode($feedbackCode->code);

        // Check if the feedback is processed correctly when the customer clicks
        // multiple times on the same link (only the initial feedback record persists)
        $feedback2 = $this->feedbackService->processFeedbackCode($feedbackCode->code);
        $this->assertNotNull($feedback2);
        $this->assertEquals($feedback1->id, $feedback2->id);
        $this->assertEquals(EmailEntryStatus::CONFIRMED, $feedbackCode->emailEntry->status);
    }

    public function testProcessNewFeedback_happy_flow_mail_not_sent()
    {
        /** @var FeedbackCode $feedbackCode */
        $feedbackCode = FeedbackCode::where("email_entry_id", 1)->where("feedback_type", FeedbackType::UNHAPPY)->first();

        // Links should not be accepted if the email wasn't even sent
        $feedbackCode->emailEntry()->update(["status" => EmailEntryStatus::INITIAL]);
        $feedback = $this->feedbackService->processFeedbackCode($feedbackCode->code);
        $this->assertNull($feedback);

        $feedbackCode->emailEntry()->update(["status" => EmailEntryStatus::SENDING_ERROR]);
        $feedback = $this->feedbackService->processFeedbackCode($feedbackCode->code);
        $this->assertNull($feedback);
    }

    public function testProcessNewFeedback_happy_flow_cannot_use_different_feedback_mood_after_one_is_already_clicked()
    {
        /** @var FeedbackCode $feedbackCodeUnhappy */
        $feedbackCodeUnhappy = FeedbackCode::where("email_entry_id", 1)->where("feedback_type", FeedbackType::UNHAPPY)->first();
        /** @var FeedbackCode $feedbackCodeNeutral */
        $feedbackCodeNeutral = FeedbackCode::where("email_entry_id", 1)->where("feedback_type", FeedbackType::NEUTRAL)->first();

        $feedback1 = $this->feedbackService->processFeedbackCode($feedbackCodeUnhappy->code);
        $this->assertNotNull($feedback1);
        $this->assertEquals(EmailEntryStatus::CONFIRMED, $feedbackCodeUnhappy->emailEntry->status);

        $feedback2 = $this->feedbackService->processFeedbackCode($feedbackCodeNeutral->code);
        $this->assertNull($feedback2);
        $this->assertEquals(EmailEntryStatus::CONFIRMED, $feedbackCodeNeutral->emailEntry->status);
    }

    public function testUpdate_happy_flow_valid_content()
    {
        $this->createFeedback(1, 1, 1, 13);

        $result = $this->feedbackService->update(1, ["content" => "valid feedback"]);
        /** @var Feedback $feedback */
        $feedback = Feedback::whereId(1)->first();

        $this->assertEquals($feedback->id, $result->id);
        $this->assertEquals("valid feedback", $result->content);
        $this->assertEquals("valid feedback", $feedback->content);
    }

    public function testUpdate_unhappy_flow_empty_content()
    {
        $this->createFeedback(1, 1, 1, 13);

        $result = $this->feedbackService->update(1, []);
        /** @var Feedback $feedback */
        $feedback = Feedback::whereId(1)->first();

        $this->assertNull($result);
        $this->assertEquals(null, $feedback->content);
    }

    public function testUpdate_unhappy_flow_null_content()
    {
        $this->createFeedback(1, 1, 1, 13);

        $result = $this->feedbackService->update(1, null);
        /** @var Feedback $feedback */
        $feedback = Feedback::whereId(1)->first();

        $this->assertNull($result);
        $this->assertEquals(null, $feedback->content);
    }

    public function testUpdate_unhappy_flow_invalid_feedback_id()
    {
        $this->createFeedback(1, 1, 1, 13);

        $result = $this->feedbackService->update(42, ["content" => "valid feedback"]);
        /** @var Feedback $feedback */
        $feedback = Feedback::whereId(1)->first();

        $this->assertNull($result);
        $this->assertEquals(null, $feedback->content);
    }
}