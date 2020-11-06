<?php


namespace Modules\DataSource\Interfaces;


class Column
{
    public string $name;
    public int $type;
    public string $dataType;

    /**
     * Column constructor.
     * @param string $name
     * @param int $type
     * @param string $dataType
     */
    public function __construct(string $name, int $type, string $dataType)
    {
        $this->name = $name;
        $this->type = $type;
        $this->dataType = $dataType;
    }
}
