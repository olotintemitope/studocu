<?php


namespace App\Console\Commands\Repositories;


use App\Console\Commands\Contracts\ResponseInterface;
use App\Models\Response;
use Illuminate\Support\Collection;

class ResponseRepository implements ResponseInterface
{

    /**
     * @var Response
     */
    private $model;

    public function __construct(Response $response)
    {
        $this->model = $response;
    }

    /**
     * @param array $data
     */
    public function insert(array $data): void
    {
        $this->model::insert($data);
    }

    /**
     * @param array $data
     * @return array
     */
    public function prepareData(array $data): array
    {
        return [
            'question_id' => $data[0],
            'answer' => $data[1],
            'created_at' => now(),
        ];
    }

    /**
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->model::all();
    }

    /**
     * @return bool|null
     * @throws \Exception
     */
    public function reset()
    {
        return $this->model::truncate();
    }
}