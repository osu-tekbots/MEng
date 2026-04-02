<?php
namespace Api;

use DataAccess\FaqDao;
use Model\Faq;

/**
 * Defines the logic for how to handle AJAX requests made to modify FAQ information.
 */
class FaqActionHandler extends ActionHandler {

    /** @var \DataAccess\FaqDao */
    private $faqDao;

    /**
     * Constructs a new instance of the action handler for requests on FAQ resources.
     *
     * @param \DataAccess\DatabaseConnection $dbConn the database connection
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($dbConn, $logger) {
        parent::__construct($logger);
        $this->faqDao = new FaqDao($dbConn, $logger);
    }

    /**
     * Creates a new FAQ entry in the database.
     *
     * @return void
     */
    public function handleCreateFaq() {
        $category = $this->getFromBody('category');
        $question = $this->getFromBody('question');
        $answer = $this->getFromBody('answer');

        $faq = new Faq();
        $faq->setCategory($category)
            ->setQuestion($question)
            ->setAnswer($answer);

        $ok = $this->faqDao->addNewFaq($faq);
        if ($ok) {
            $this->respond(new Response(
                Response::CREATED,
                'FAQ created successfully'
            ));
        } else {
            $this->respond(new Response(
                Response::INTERNAL_SERVER_ERROR,
                'Failed to create FAQ'
            ));
        }
    }

    /**
     * Updates an existing FAQ entry in the database.
     *
     * @return void
     */
    public function handleUpdateFaq() {
        $id = $this->getFromBody('id');
        $category = $this->getFromBody('category');
        $question = $this->getFromBody('question');
        $answer = $this->getFromBody('answer');

        $faq = $this->faqDao->getFaqById($id);
        if (!$faq) {
            $this->respond(new Response(Response::NOT_FOUND, 'FAQ not found'));
            return;
        }

        $faq->setCategory($category)
            ->setQuestion($question)
            ->setAnswer($answer);

        $ok = $this->faqDao->updateFaq($faq);
        if ($ok) {
            $this->respond(new Response(
                Response::OK,
                'FAQ updated successfully'
            ));
        } else {
            $this->respond(new Response(
                Response::INTERNAL_SERVER_ERROR,
                'Failed to update FAQ'
            ));
        }
    }

    /**
     * Deletes a FAQ entry from the database.
     *
     * @return void
     */
    public function handleDeleteFaq() {
        $id = $this->getFromBody('id');

        $faq = $this->faqDao->getFaqById($id);
        if (!$faq) {
            $this->respond(new Response(Response::NOT_FOUND, 'FAQ not found'));
            return;
        }

        $ok = $this->faqDao->deleteFaq($id);
        if ($ok) {
            $this->respond(new Response(
                Response::OK,
                'FAQ deleted successfully'
            ));
        } else {
            $this->respond(new Response(
                Response::INTERNAL_SERVER_ERROR,
                'Failed to delete FAQ'
            ));
        }
    }

    /**
     * Fetches all FAQs for a given category and returns them as JSON content.
     *
     * @return void
     */
    public function handleGetFaqsByCategory() {
        $category = $this->getFromBody('category');

        $faqs = $this->faqDao->getFaqsByCategory($category);
        if ($faqs === false) {
            $this->respond(new Response(
                Response::INTERNAL_SERVER_ERROR,
                'Failed to fetch FAQs'
            ));
            return;
        }

        $faqData = array();
        foreach ($faqs as $faq) {
            $faqData[] = array(
                'id' => $faq->getId(),
                'category' => $faq->getCategory(),
                'question' => $faq->getQuestion(),
                'answer' => $faq->getAnswer()
            );
        }

        $this->respond(new Response(
            Response::OK,
            'FAQs fetched successfully',
            $faqData
        ));
    }

    /**
     * Handles the HTTP request on the API resource.
     * 
     * This effectively will invoke the correct action based on the `action` parameter value in the request body.
     *
     * @return void
     */
    public function handleRequest() {
        // Make sure the action parameter exists
        $this->requireParam('action');

        // Call the correct handler based on the action
        switch ($this->requestBody['action']) {

            case 'getFaqsByCategory':
                $this->handleGetFaqsByCategory();

            case 'createFaq':
                $this->handleCreateFaq();

            case 'updateFaq':
                $this->handleUpdateFaq();

            case 'deleteFaq':
                $this->handleDeleteFaq();

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on FAQ resource'));
        }
    }
}
