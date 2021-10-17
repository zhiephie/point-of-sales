<?php

namespace App\Utils;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class JsonResponse implements \JsonSerializable
{
    const STATUS_SUCCESS = true;
    const STATUS_ERROR = false;

    private $message = '';

    private $data = [];

    private $error = '';

    private $success = false;

    public function __construct(string $message = '', $data = [], string $error = '')
    {
        if ($this->shouldBeJson($data)) {
            $this->data = $data;
        }

        $this->message = $message;
        $this->error = $error;
        $this->success = !empty($data);
    }

    public function success($data = [])
    {
        $this->success = true;
        $this->message = $message;
        $this->data = $data;
        $this->error = '';
    }

    public function fail($error = '')
    {
        $this->success = false;
        $this->message = $message;
        $this->error = $error;
        $this->data = [];
    }

    public function jsonSerialize()
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'error' => $this->error,
        ];
    }

    private function shouldBeJson($content): bool
    {
        return $content instanceof Arrayable ||
            $content instanceof Jsonable ||
            $content instanceof \ArrayObject ||
            $content instanceof \JsonSerializable ||
            is_array($content);
    }
}
