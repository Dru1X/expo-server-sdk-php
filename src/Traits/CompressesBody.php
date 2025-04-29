<?php

namespace Dru1x\ExpoPush\Traits;

use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;
use Saloon\Repositories\Body\StringBodyRepository;

trait CompressesBody
{
    private const int COMPRESSION_THRESHOLD = 1024;

    public function bootCompressesBody(PendingRequest $pendingRequest): void
    {
        $pendingRequest->middleware()->onRequest(
            callable: $this->compressBody(...),
            name: 'compressBody',
            order: PipeOrder::LAST
        );
    }

    // Internals ----

    /**
     * @param PendingRequest $pendingRequest
     *
     * @return void
     */
    protected function compressBody(PendingRequest $pendingRequest): void
    {
        $bodyString = (string)$pendingRequest->body();

        if (strlen($bodyString) > self::COMPRESSION_THRESHOLD) {
            $pendingRequest->headers()->add('Content-Encoding', 'gzip');
            $bodyString = gzcompress($bodyString);
        }

        $pendingRequest->setBody(new StringBodyRepository($bodyString));
    }
}