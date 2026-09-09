<?php

declare(strict_types=1);

namespace App\Support\ControlPlane;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Throwable;

final class RuntimeProbeRunner
{
    /**
     * @param list<string> $command
     * @return array{status:string,owner:string,exit_code:?int,timed_out:bool,secrets_included:bool}
     */
    public function run(array $command, string $owner, string $workingDirectory, int $timeoutSeconds = 30): array
    {
        $process = new Process($command, $workingDirectory);
        $process->setTimeout($timeoutSeconds);
        $process->disableOutput();

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            return [
                'status' => 'blocked',
                'owner' => $owner,
                'exit_code' => null,
                'timed_out' => true,
                'secrets_included' => false,
            ];
        } catch (Throwable) {
            return [
                'status' => 'blocked',
                'owner' => $owner,
                'exit_code' => null,
                'timed_out' => false,
                'secrets_included' => false,
            ];
        }

        return [
            'status' => $process->isSuccessful() ? 'ready' : 'blocked',
            'owner' => $owner,
            'exit_code' => $process->getExitCode(),
            'timed_out' => false,
            'secrets_included' => false,
        ];
    }
}
