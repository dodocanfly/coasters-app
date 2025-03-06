<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\CoasterService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Exception;

class CoasterController extends BaseController
{
    use ResponseTrait;

    protected ?CoasterService $coasterService;

    public function __construct()
    {
        $this->coasterService = service('coasterService');
    }

    public function create(): ResponseInterface
    {
        try {
            $data = $this->request->getJSON(true);
            $coaster = $this->coasterService->createCoaster($data);

            return $this->respond([
                'success' => true,
                'message' => 'Coaster created successfully',
                'data' => $coaster->toArray()
            ], 201);
        } catch (ValidationException $e) {
            return $this->failValidationErrors($e->getMessage());
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function update(string $coasterId = ''): ResponseInterface
    {
        try {
            if (empty($coasterId)) {
                return $this->failValidationErrors('Coaster ID is required');
            }

            $data = $this->request->getJSON(true);
            $coaster = $this->coasterService->updateCoaster($coasterId, $data);

            return $this->respond([
                'success' => true,
                'message' => 'Coaster updated successfully',
                'data' => $coaster->toArray()
            ]);

        } catch (ValidationException $e) {
            return $this->failValidationErrors($e->getMessage());
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function addWagon(string $coasterId = null): ResponseInterface
    {
        try {
            if (empty($coasterId)) {
                return $this->failValidationErrors('Coaster ID is required');
            }

            $wagon = $this->coasterService->addWagon($coasterId, $this->request->getJSON(true));

            return $this->respond([
                'success' => true,
                'message' => 'Wagon added successfully',
                'data' => $wagon->toArray()
            ], 201);
        } catch (ValidationException $e) {
            return $this->failValidationErrors($e->getMessage());
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function removeWagon($coasterId = null, $wagonId = null): ResponseInterface
    {
        try {
            if (empty($coasterId) || empty($wagonId)) {
                return $this->failValidationErrors('Coaster ID and Wagon ID are required');
            }

            $result = $this->coasterService->removeWagon($coasterId, $wagonId);

            return $this->respond([
                'success' => $result,
                'message' => 'Wagon removed successfully'
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
