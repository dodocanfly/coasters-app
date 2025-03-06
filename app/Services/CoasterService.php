<?php

namespace App\Services;

use App\Entities\Coaster;
use App\Entities\Wagon;
use App\Repositories\CoasterRepository;
use App\Repositories\WagonRepository;
use CodeIgniter\Validation\Validation;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Exception;

class CoasterService
{
    public function __construct(
        protected CoasterRepository $coasterRepository,
        protected WagonRepository $wagonRepository,
        protected Validation $validation
    ) {
    }

    /** @throws Exception */
    public function createCoaster(array $data): Coaster
    {
        if (!$this->validation->setRules(Coaster::$createRules)->run($data)) {
            throw new ValidationException($this->getValidationErrorsString());
        }

        $coaster = Coaster::fromArray($data);
        return $this->coasterRepository->save($coaster);
    }

    /** @throws Exception */
    public function updateCoaster(string $coasterId, array $data): Coaster
    {
        $coaster = $this->coasterRepository->findById($coasterId);
        if (is_null($coaster)) {
            throw new Exception('Coaster not found', 404);
        }

        $data[Coaster::KEY_ID] = $coasterId;

        if (!$this->validation->setRules(Coaster::$updateRules)->run($data)) {
            throw new ValidationException($this->getValidationErrorsString());
        }

        $data[Coaster::KEY_LENGTH] = $coaster->getRouteLength();

        $updatedCoaster = Coaster::fromArray($data);

        return $this->coasterRepository->save($updatedCoaster);
    }

    /** @throws Exception */
    public function addWagon(string $coasterId, array $data): Wagon
    {
        if (!$this->coasterRepository->exists($coasterId)) {
            throw new Exception('Coaster not found', 404);
        }

        if (!$this->validation->setRules(Wagon::$createRules)->run($data)) {
            throw new ValidationException($this->getValidationErrorsString());
        }

        $data[Wagon::KEY_COASTER_ID] = $coasterId;
        $wagon = Wagon::fromArray($data);

        return $this->wagonRepository->save($wagon);
    }

    /** @throws Exception */
    public function removeWagon(string $coasterId, string $wagonId): bool
    {
        if (!$this->coasterRepository->exists($coasterId)) {
            throw new Exception('Coaster not found', 404);
        }

        if (!$this->wagonRepository->exists($wagonId) || !$this->wagonRepository->belongsToCoaster($wagonId, $coasterId)) {
            throw new Exception("Wagon not found or doesn't belong to this coaster", 404);
        }

        return $this->wagonRepository->delete($wagonId);
    }

    private function getValidationErrorsString(): string
    {
        return implode("\n", $this->validation->getErrors());
    }
}
