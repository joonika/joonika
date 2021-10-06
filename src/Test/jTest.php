<?php
declare(strict_types=1);

namespace Joonika\Test;

use Joonika\Route;
use PHPUnit\Framework\TestCase;

class jTest extends TestCase
{
    public $Route = null;

    public function __construct(string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->Route = Route::ROUTE(__DIR__ . '/../../../../../', 'dev');
        $this->Route->isApi = 1;
    }
}