<?php


namespace Modules\DataSource\Drivers;


use Extensions\Polymatica\Api;
use Modules\DataSource\Interfaces\Column;

class Polymatica extends Base
{
    protected string $driver = Api::class;

    protected function connect(): Polymatica
    {
        $this->provider->authenticate()
            ->Cubes();
        return $this;
    }

    public function init(string $initTable): Polymatica
    {
        $this->setTable($initTable);
        $this->columns();

        return $this;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table): void
    {
        $this->provider->Cube($table, true)
            ->createLayer()
            ->openCube();
        parent::setTable($this->provider->getCube()['name']);
    }

    private function attachColumn(Column $column): void
    {
        $this->columns[] = $column;
    }

    public function tables(): Polymatica
    {
        $this->tables = $this->provider->getCubes();
        return $this;
    }

    public function columns(): Polymatica
    {
        $this->provider->Dims()
            ->Facts();
        foreach ($this->provider->getDims() as $dim) {
            $dataType = current(array_keys(array_filter(SERVERCODES['olap']['olap_data_type'], fn($item) => $item === $dim['olap_type'])));
            $this->attachColumn(clone new Column(
                $dim['name'],
                0,
                $dataType
            ));
        }

        foreach ($this->provider->getFacts() as $fact) {
            $factType = current(array_keys(array_filter(SERVERCODES['olap']['olap_fact_type'], fn($item) => $item === $fact['plm_type'])));
            $this->attachColumn(clone new Column(
                $fact['name'],
                1,
                $factType
            ));
        }

        return $this;
    }

    public function data(array $dimensions, array $measures, array $group = []): Polymatica
    {
        foreach ($dimensions as $dimension) {
            $this->provider->moveDim($this->provider->DimNameToId($dimension), 'left');
        }

        foreach ($group as $groupDimension) {
            $this->provider->moveDim($this->provider->DimNameToId($groupDimension), 'top');
        }

        $this->provider->selectAllFacts();
        foreach ($measures as $measure) {
            $this->provider->toggleSelectedFact($this->provider->FactNameToId($measure), false);
        }

        $this->provider
            ->hideSelectedFacts()
            ->Facts()
            ->render();

        for($i = 0; $i < count($dimensions); $i++) {
            $this->provider->foldAtLevel('left', $i);
        }

        for($i = 0; $i < count($group); $i++) {
            $this->provider->foldAtLevel('top', $i);
        }

        $this->provider->render();

        return $this;
    }

    public function disconnect()
    {
        $this->provider->logout();
    }
}
