<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property string $nombre
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ciudad newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ciudad newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ciudad query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ciudad whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ciudad whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ciudad whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ciudad whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Ciudad extends Model
{
    use HasFactory;

    protected $table = 'ciudades';
}
