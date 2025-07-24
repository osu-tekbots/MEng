<?php
namespace Model;

class DocumentType {
    /** @var int */
    private $id;

    /** @var string */
    private $typeName;

    /**
     * Constructor
     *
     * @param int|null $id Optional ID to initialize the DocumentType.
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
     * Get the value of typeName
     *
     * @return string
     */
    public function getTypeName() {
        return $this->typeName;
    }

    /**
     * Set the value of typeName
     *
     * @param string $typeName
     * @return self
     */
    public function setTypeName($typeName) {
        $this->typeName = $typeName;
        return $this;
    }
}
