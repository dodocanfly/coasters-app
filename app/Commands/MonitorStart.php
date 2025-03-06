<?php

namespace App\Commands;

use App\Services\MonitoringService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MonitorStart extends BaseCommand
{
    public const PID_FILE = WRITEPATH . 'monitor.pid';

    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'CoastersApp';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'monitor:start';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Start the asynchronous coaster monitoring service';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        if (file_exists(self::PID_FILE)) {
            $pid = (int)file_get_contents(self::PID_FILE);
            $running = false;

            if ($pid > 0) {
                $running = posix_kill($pid, 0);
            }

            if ($running) {
                CLI::error('Monitor service is already running. Use "php spark monitor:stop" to stop it first.');
                return;
            } else {
                unlink(self::PID_FILE);
            }
        }

        file_put_contents(self::PID_FILE, getmypid());

        pcntl_signal(SIGINT, function () {
            CLI::write('Received interrupt signal, shutting down...', 'yellow');

            if (file_exists(self::PID_FILE)) {
                unlink(self::PID_FILE);
            }

            exit(0);
        });

        CLI::write('Starting CoastersApp monitoring service...', 'green');
        CLI::write('Press Ctrl+C to stop or run "php spark monitor:stop" from another terminal', 'yellow');
//        CLI::wait(5, true);

        /** @var MonitoringService $monitoringService */
        $monitoringService = service('monitoringService');
        $monitoringService->startMonitoring();
    }
}
