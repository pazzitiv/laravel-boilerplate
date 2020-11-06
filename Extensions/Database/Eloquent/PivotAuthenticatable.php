<?php


namespace Extensions\Database\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PivotAuthenticatable extends Authenticatable
{
    /**
     * @param string $related
     * @param string|null $table
     * @param string|null $foreignPivotKey
     * @param string|null $relatedPivotKey
     * @param string|null $parentKey
     * @param string|null $relatedKey
     * @param string|null $relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null): BelongsToMany
    {
        $Pivot = new $related;
        if($Pivot instanceof Pivot)
        {
            $table = $table ?? $related;
            $foreignPivotKey = $foreignPivotKey ?? $Pivot->getForeignKey();
            $relatedPivotKey = $relatedPivotKey ?? $Pivot->getRelatedKey();
            $related = $this instanceof $Pivot->pivotTarget ? $Pivot->pivotParent : $Pivot->pivotTarget;
        }

        return parent::belongsToMany($related, $table, $foreignPivotKey, $relatedPivotKey,
            $parentKey, $relatedKey, $relation);
    }
}
