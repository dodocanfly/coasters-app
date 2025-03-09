<?php

namespace App\Services;

use App\Libraries\CoasterProcessor;
use App\Repositories\CoasterRepository;
use App\Repositories\WagonRepository;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Log\Logger;
use Config\Redis as RedisConfig;
use Exception;
use React\EventLoop\Loop;
use Clue\React\Redis\Factory as RedisFactory;

class MonitoringService
{
    private string $redisUri;

    public function __construct(
        protected CoasterRepository $coasterRepository,
        protected WagonRepository $wagonRepository,
        protected Logger $logger
    ) {
        $this->redisUri = RedisConfig::getUri();
    }

    public function startMonitoring(): void
    {
        $loop = Loop::get();
        $factory = new RedisFactory($loop);

        $factory->createClient($this->redisUri)->then(function ($client) {
            $client->psubscribe('__keyspace@0__:coaster:*')->then(function () {
                $this->checkAllCoasterStatuses();
            });

            $client->on('pmessage', function () {
                $this->checkAllCoasterStatuses();
            });
        }, function (Exception $e) {
            $message = 'Error connecting Redis command client: ' . $e->getMessage();
            CLI::error($message);
            $this->logger->error($message);
        });

        $loop->run();
    }

    private function checkAllCoasterStatuses(): void
    {
        CLI::clearScreen();

        $coasters = $this->coasterRepository->findAll();

        if (empty($coasters)) {
            CLI::error('No coasters found in the system.');
            return;
        }

        CLI::write(str_pad(' Monitoring... ', 80, '=', STR_PAD_BOTH) . "\n", 'cyan');

        foreach ($coasters as $coaster) {
            $wagons = $this->wagonRepository->findByCoasterId($coaster->getId());

            $processor = new CoasterProcessor($coaster, $wagons);

            CLI::write($processor->getHeader(), 'cyan');
            CLI::write($processor->getCliReport());

            if ($processor->isError()) {
                $this->logger->warning($processor->getLogReport());
            }
        }
    }
}
