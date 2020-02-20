<?php


namespace App\Messenger\Message;


class CommentMessage
{


    /**
     * @var int
     */
    private $id;
    /**
     * @var array
     */
    private $context;

    public function __construct(int $id, array $context = [])
    {
        $this->id      = $id;
        $this->context = $context;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }


}