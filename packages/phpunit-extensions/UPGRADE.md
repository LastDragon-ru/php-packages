# Upgrade Guide

[include:file]: ../../docs/Shared/Upgrade.md
[//]: # (start: preprocess/aa9fc458898c7c1c)
[//]: # (warning: Generated automatically. Do not edit.)

## Instructions

1. Determine the current version (`composer info ...`)
2. Choose the wanted version
3. Follow the instructions
4. ??????
5. PROFIT

For example, if the current version is `2.x` and you want to migrate to `5.x`, you need to perform all steps in the following order:

* "Upgrade from v2"
* "Upgrade from v3"
* "Upgrade from v4"

Please also see [changelog](https://github.com/LastDragon-ru/php-packages/releases) to find all changes.

## Legend

| 🤝 | Backward-compatible change. Please note that despite you can ignore it now, but it will be mandatory in the future. |
|:--:|:--------------------------------------------------------------------------------------------------------------------|

[//]: # (end: preprocess/aa9fc458898c7c1c)

## Upgrade from v10

* [ ] Extension [`💀\LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare\Extension`][code-links/50cb69b702caae36] renamed to [`\LastDragon_ru\PhpUnit\Extensions\StrictScalarComparator\Extension`][code-links/4ec6b74a682f8155] 🤝

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/4ec6b74a682f8155]: src/Extensions/StrictScalarComparator/Extension.php
    "\LastDragon_ru\PhpUnit\Extensions\StrictScalarComparator\Extension"

[code-links/50cb69b702caae36]: src/Extensions/StrictScalarCompare/Extension.php
    "\LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare\Extension"

[//]: # (end: code-links)
