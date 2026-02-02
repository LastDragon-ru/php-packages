<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package;

use Illuminate\Database\Eloquent\Model;

/**
 * @internal
 *
 * @property string $id
 * @property string $value
 */
class TestObject extends Model {
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
}
