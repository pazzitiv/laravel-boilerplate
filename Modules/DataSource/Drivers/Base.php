<?php


namespace Modules\DataSource\Drivers;


use Modules\DataSource\Interfaces\Column;

abstract class Base
{
    protected string $driver;

    protected object $provider;

    protected string $table;

    protected array $tables;

    protected array $Data;

    protected array $columns;

    public function __construct(string $DataSourceDriver = null)
    {
        if($DataSourceDriver !== null) $DataSourceDriver = new $DataSourceDriver;

        $this->provider = $DataSourceDriver ?? new $this->driver;
        $this->connect();
        $this->tables();
    }

    /**
     * @return object
     */
    public function getProvider(): object
    {
        return $this->provider;
    }

    /**
     * @param mixed|object|string $provider
     */
    public function setProvider($provider): void
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->Data;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    abstract protected function connect(): Base;

    abstract public function init(string $initTable): Base;

    abstract public function tables(): Base;

    abstract public function columns(): Base;

    abstract public function data(array $dimensions, array $measures, array $group = []): Base;
}
