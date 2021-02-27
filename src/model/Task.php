<?php


namespace Bot\model;


class Task extends Base\DomainObject
{
    private $text;
    private $status;
    private $config;

    public function __construct($id, $text, $config, $status = 'new')
    {
        parent::__construct($id);
        $this->text = $text;
        $this->config = $config;
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setText($text): void
    {
        $this->text = $text;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getConfig(): int
    {
        return $this->config;
    }
}