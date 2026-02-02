<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Package\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use LastDragon_ru\LaraASP\Eloquent\Concerns\WithoutTimestamps;

/**
 * @internal
 *
 * @property string $id
 * @property string $value
 */
class TestObjectSearchable extends Model {
    /**
     * @use HasFactory<TestObjectSearchableFactory>
     */
    use HasFactory;
    use Searchable;
    use WithoutTimestamps;

    /**
     * @var ?string
     */
    protected $table = 'test_objects';

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var bool
     */
    public $incrementing = false;

    protected static function newFactory(): TestObjectSearchableFactory {
        return TestObjectSearchableFactory::new();
    }
}
