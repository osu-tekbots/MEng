<?php
namespace Model;

class Faq {
    /** @var int */
    private $id;

    /** @var string */
    private $category;

    /** @var string */
    private $question;

    /** @var string */
    private $answer;

    /**
     * Constructor
     *
     * @param int|null $id Optional ID to initialize the Faq.
     */
    public function __construct($id = null) {
        $this->id = $id;
    }

    /**
     * Get the value of id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param int $id
     * @return self
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of category
     *
     * @return string
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * Set the value of category
     *
     * @param string $category
     * @return self
     */
    public function setCategory($category) {
        $this->category = $category;
        return $this;
    }

    /**
     * Get the value of question
     *
     * @return string
     */
    public function getQuestion() {
        return $this->question;
    }

    /**
     * Set the value of question
     *
     * @param string $question
     * @return self
     */
    public function setQuestion($question) {
        $this->question = $question;
        return $this;
    }

    /**
     * Get the value of answer
     *
     * @return string
     */
    public function getAnswer() {
        return $this->answer;
    }

    /**
     * Set the value of answer
     *
     * @param string $answer
     * @return self
     */
    public function setAnswer($answer) {
        $this->answer = $answer;
        return $this;
    }
}
