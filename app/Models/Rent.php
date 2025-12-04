<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rent extends Model
{

    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function booted()
    {
        static::deleting(function ($rent) {
            // Обновляем связанные замки, устанавливая rent_id в NULL
            $rent->locks()->update(['rent_id' => null]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function locks()
    {
        return $this->belongsToMany(Lock::class);
    }

    public function children()
    {
        return $this->hasMany(Rent::class, 'rent_id', 'id')->with('children');
    }


    public function parent_rents()
    {
        return $this->hasMany(Rent::class, 'rent_id', 'id')->whereNull('rent_id');
    }


    public function parent()
    {
        return $this->belongsTo(Rent::class, 'rent_id');
    }


    public function groups(): MorphToMany
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    /* static public function getNestedRentsForUser($userId)
    {


        // CTE, которая начинается с корневых записей для конкретного пользователя
        // и рекурсивно поднимает только те дочерние записи, у которых user_id совпадает
        $cteQuery = "
            WITH RECURSIVE rent_tree AS (
                -- Базовый случай: выбираем корневые элементы (где rent_id IS NULL) для конкретного user_id
                SELECT id, name, description, rent_id, user_id, 0 AS level
                FROM rents
                WHERE rent_id IS NULL AND user_id = ? AND deleted_at IS NULL -- Фильтруем по user_id и учитываем soft deletes

                UNION ALL

                -- Рекурсивный случай: добавляем дочерние элементы,
                -- у которых rent_id указывает на id из предыдущего шага (rt.id)
                -- И user_id совпадает с user_id родительской записи (rt.user_id)
                SELECT r.id, r.name, r.description, r.rent_id, r.user_id, rt.level + 1
                FROM rents r
                INNER JOIN rent_tree rt ON r.rent_id = rt.id
                WHERE r.user_id = rt.user_id AND r.deleted_at IS NULL -- Важно: user_id должен совпадать и учитываем soft deletes
            )
            SELECT * FROM rent_tree ORDER BY level, id; -- Сортировка по желанию
        ";

        $bindings = [$userId]; // Привязываем ID пользователя к плейсхолдеру ?

        // Выполняем запрос и получаем результаты как stdClass объекты
        $results = \DB::select($cteQuery, $bindings);

        // Преобразуем результаты в коллекцию Eloquent моделей
        $ids = collect($results)->pluck('id')->toArray();

        // Загружаем модели по ID, убедившись, что учитываются soft deletes,
        // если это важно в вашем контексте. Если нет, можно убрать withTrashed().
        // ВАЖНО: Убедитесь, что вы не загружаете связи, которые могут снова вызвать рекурсию!
        $models = Rent::whereIn('id', $ids)->get();


        return $models;
    }*/
}
