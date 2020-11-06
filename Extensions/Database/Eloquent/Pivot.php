<?php


namespace Extensions\Database\Eloquent;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\Pivot as OriginalPivot;

class Pivot extends OriginalPivot
{
    public Model $pivotTarget;

    protected string $pivotParentClassname;
    protected string $pivotTargetClassname;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setPivotParent();
        $this->setPivotTarget();
    }

    public function setPivotParent(): void
    {
        $this->pivotParent = new $this->pivotParentClassname;
    }

    public function setPivotTarget(): void
    {
        $this->pivotTarget = new $this->pivotTargetClassname;
    }
}
