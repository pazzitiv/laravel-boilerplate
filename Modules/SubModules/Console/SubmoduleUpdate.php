<?php

namespace Modules\SubModules\Console;

use Illuminate\Console\Command;
use Modules\SubModules\Entities\Submodule;
use Modules\SubModules\Helpers\ModuleSystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SubmoduleUpdate extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'submodule:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление списка муодлей системы.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            $modules = ModuleSystem::getModules();

            foreach ($modules as $module) {
                Submodule::updateOrCreate(['code' => $module->code], [
                    'name' => $module->name,
                    'code' => $module->code,
                    'parent_code' => $module->parentCode,
                ]);
            }

            $this->info('Обновление модулей завершено.');
            return 0;
        } catch (\Exception $exception) {
            $this->getOutput()->writeln("<error>ОШИБКА ОБНОВЛЕНИЯ</error> ({$exception->getCode()}) {$exception->getMessage()}");
            return 1;
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            //['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            //['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
