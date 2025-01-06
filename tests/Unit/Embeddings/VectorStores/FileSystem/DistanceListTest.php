<?php

namespace Tests\Unit\Chat;

use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\FileSystem\DistanceList;

describe('DistanceList', function () {
    it('can keep a sorted list of the n documents with the lowest distances', function () {
        $distanceList = new DistanceList(2);

        $five = DocumentUtils::document('Five');
        $distanceList->addDistance(5.0, $five);

        expect($distanceList->getDocuments())->toBe([$five]);

        $two = DocumentUtils::document('Two');
        $distanceList->addDistance(2.0, $two);

        expect($distanceList->getDocuments())->toBe([$two, $five]);

        $four = DocumentUtils::document('Four');
        $distanceList->addDistance(4.0, $four);

        expect($distanceList->getDocuments())->toBe([$two, $four]);
    });

});
