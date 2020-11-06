<?php


namespace Extensions\Polymatica;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PolymaticaModel extends Model
{
    public $table;

    /**
     * PolymaticaModel constructor.
     */
    public function __construct(string $table, array $attributes = [], array $columns = [])
    {
        $this->table = 'plm_' . $table;

        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) use ($attributes, $columns) {
                $table->id();
                foreach ($columns as $attr => $attribute) {
                    switch (gettype($attribute))
                    {
                        case 'integer':
                            $table->integer($attr);
                            break;
                        case 'string':
                        default:
                            if(self::isTimestamp($attribute)) {
                                $table->timestamp($attr);
                            } else {
                                $table->string($attr);
                            }
                            break;
                    }
                }
                $table->timestamps();
            });
        }
        parent::__construct($attributes = []);
    }

    public static function isTimestamp(string $string)
    {
        try {
            return Carbon::createFromFormat('d.m.Y, H:i:s', $string);
        } catch (\Exception $exception) {
            return false;
        }
    }
}
