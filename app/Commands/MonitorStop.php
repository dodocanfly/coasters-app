<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MonitorStop extends BaseCommand
{
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
    protected $name = 'monitor:stop';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Stop the asynchronous coaster monitoring service';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        CLI::write('Stopping CoastersApp monitoring service...', 'yellow');

        if (!file_exists(MonitorStart::PID_FILE)) {
            CLI::error('Monitor service is not running or PID file not found.');
            return;
        }

        $pid = (int)file_get_contents(MonitorStart::PID_FILE);
        if ($pid <= 0) {
            CLI::error('Invalid PID file content.');
            return;
        }

        $result = posix_kill($pid, SIGTERM);

        if ($result) {
            unlink(MonitorStart::PID_FILE);
            CLI::write('Monitor service stopped successfully.', 'green');
        } else {
            CLI::error('Failed to stop monitor service. It may have already been stopped.');
            if (file_exists(MonitorStart::PID_FILE)) {
                unlink(MonitorStart::PID_FILE);
            }
        }
    }
}
