<?php
namespace com\selfcoders\statuspage\objects;

use DateTime;

class Component
{
    /**
     * @var DateTime
     */
    public $createdAt;
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $description;
    /**
     * @var int
     */
    public $position;
    /**
     * @var string
     */
    public $status;
    /**
     * @var DateTime
     */
    public $updatedAt;
    /**
     * @var string|null
     */
    public $groupId;

    /**
     * @param array $data
     * @return static
     */
    public static function createFromArray(array $data)
    {
        $component = new static;

        $component->setFromArray($data);

        return $component;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setFromArray(array $data)
    {
        /**
         * Sample input:
         *
         * "created_at": "2013-03-05T20:50:42Z",
         * "id": "ftgks51sfs2d",
         * "name": "API",
         * "description": "Lorem",
         * "position": 1,
         * "status": "partial_outage",
         * "updated_at": "2013-03-05T22:39:02Z",
         * "group_id": nil
         */

        $this->createdAt = $data["created_at"] === null ? null : new DateTime($data["created_at"]);
        $this->id = $data["id"];
        $this->name = $data["name"];
        $this->description = $data["description"];
        $this->position = $data["position"] === null ? null : (int)$data["position"];
        $this->status = $data["status"];
        $this->updatedAt = $data["updated_at"] === null ? null : new DateTime($data["updated_at"]);
        $this->groupId = $data["group_id"];

        return $this;
    }
}