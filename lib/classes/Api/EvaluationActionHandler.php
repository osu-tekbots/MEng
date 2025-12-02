<?php
namespace Api;

use Model\Evaluation;
use Api\Response; 

class EvaluationActionHandler extends ActionHandler {

    /** @var \DataAccess\EvaluationsDao */
    private $evaluationsDao;

    /** @var \DataAccess\RubricsDao */
    private $rubricsDao;

    /** @var \DataAccess\UploadsDao */
    private $uploadsDao;

    public function __construct($evaluationsDao, $rubricsDao, $uploadsDao, $logger) {
        parent::__construct($logger);
        $this->evaluationsDao = $evaluationsDao;
        $this->rubricsDao = $rubricsDao;
        $this->uploadsDao = $uploadsDao;
    }

    public function handleAssignEvaluations() {
        $uploadIds = $this->getFromBody('uploadIds');
        $reviewerIds = $this->getFromBody('reviewerIds');
        $rubricId = $this->getFromBody('rubricId');

        if (empty($uploadIds) || !is_array($uploadIds)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Invalid or missing upload IDs.'));
            return;
        }
        if (empty($reviewerIds) || !is_array($reviewerIds)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Invalid or missing reviewer IDs.'));
            return;
        }
        if (empty($rubricId)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Missing rubric ID.'));
            return;
        }

        $count = 0;
        $errors = [];

        foreach ($uploadIds as $uploadId) {
            try {
                $studentId = $this->uploadsDao->getStudentIdForUpload($uploadId);
            } catch (\Exception $e) {
                $errors[] = "Error fetching student for upload $uploadId: " . $e->getMessage();
                continue; 
            }
            
            if (!$studentId) {
                $errors[] = "Upload ID $uploadId: Student ID not found.";
                continue; 
            }

            foreach ($reviewerIds as $reviewerId) {
                try {
                    $newEval = $this->evaluationsDao->createEvaluation($studentId, $reviewerId, $uploadId);

                    if ($newEval) {
                        $success = $this->rubricsDao->createRubricForEvaluation($newEval->getId(), $rubricId);
                        if ($success) {
                            $count++;
                        } else {
                            $errors[] = "Rubric copy failed for Eval ID: " . $newEval->getId();
                        }
                    }

                } catch (\Exception $e) {
                    $errors[] = "DB Error (Student: $studentId, Reviewer: $reviewerId): " . $e->getMessage();
                }
            }
        }

        if ($count > 0 && empty($errors)) {
            $this->respond(new Response(Response::OK, "Successfully created $count evaluations."));
        } elseif ($count > 0 && !empty($errors)) {
            $this->respond(new Response(Response::OK, "Created $count evaluations with errors: " . implode('; ', $errors)));
        } else {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, "Failed. Errors: " . implode('; ', $errors)));
        }
    }

    public function handleRequest() {
        if(!isset($this->requestBody['action'])){
             $this->respond(new Response(Response::BAD_REQUEST, 'Missing action parameter'));
             return;
        }

        switch ($this->requestBody['action']) {
            case 'assignEvaluations':
                $this->handleAssignEvaluations();
                break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on evaluation resource'));
        }
    }
}