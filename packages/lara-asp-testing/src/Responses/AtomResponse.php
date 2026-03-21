<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\Body;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\AtomContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Xml\Constraints\XmlMatchesSchema;

class AtomResponse extends Response {
    public function __construct() {
        parent::__construct(
            new Ok(),
            new AtomContentType(),
            new Body(
                new XmlMatchesSchema((new FilePath(__FILE__.'.rng'))->normalized()),
            ),
        );
    }
}
