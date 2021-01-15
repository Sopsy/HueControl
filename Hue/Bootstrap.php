<?php
declare(strict_types=1);

namespace Hue;

use Hue\Command\CreateConfig;
use Hue\Contract\CommandInterface;
use Hue\Contract\ConfigInterface;
use InvalidArgumentException;
use JsonException;
use function class_exists;

final class Bootstrap
{
    private Bridge $bridge;

    /**
     * @throws JsonException
     */
    public function __construct(
        private ConfigInterface $config,
        private string $command = '',
        string ...$args
    )
    {
        if ($this->command === '') {
            throw new InvalidArgumentException($this->usageHelp());
        }

        if ($this->command !== 'CreateConfig' && !$this->config->isConfigured()) {
            throw new InvalidArgumentException("Configuration missing, please run CreateConfig");
        }

        if ($this->command === 'CreateConfig') {
            (new CreateConfig())->run(...$args);
        } else {
            $this->bridge = new Bridge($this->config);
            $this->dispatch($command, ...$args);
        }
    }

    private function dispatch(string $command, string ...$args): void
    {
        if (!class_exists('\Hue\Command\\' . $command)) {
            throw new InvalidArgumentException("Unknown command '{$command}'");
        }

        /** @var $class CommandInterface */
        $className = '\Hue\Command\\' . $command;
        $class = new $className($this->bridge);
        $class->run(...$args);
    }

    private function usageHelp(): string
    {
        return <<<EOL
        Usage: php cli.php <command> [args]
        
        Available commands:
        CreateConfig [bridge_ip]   Detect the Hue Bridge, create an user and store into config.json.
                                     - bridge_ip: Force Hue Bridge IP, useful when autodetection fails.
        DeleteResourceLinks <id>   Delete resource links by id
        DeleteScene <id>           Delete a scene by id
        DeleteUnusedMemorySensors  Delete memory sensors (boolean and integer flags) that are not used by any rules
        GetGroups                  List all groups (rooms)
        GetLights                  List all lights
        GetResourceLinks           List all resource links
        GetRules                   List all rules
        GetScenes [group_name]     List all scenes (all groups or a single group)
                                     - group_name: List scenes only in this group
        GetSensors                 List sensors (buttons, motion sensors, flags, etc.)
        GetTemp                    List temperature sensors and their values
        ProgramSensor [sensor_id] [group_id] [program_id]
                                   Configure a sensor with a program
                                     - sensor_id: Sensor to configure
                                     - group_id: Group (room) to control
                                     - program_id: Program to configure
        EOL;
    }
}