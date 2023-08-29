<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\GeneratorCommand;

class Filter extends GeneratorCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'make:filter';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new filter class';

	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Filter';

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub(): string
    {
		return base_path('stubs/filter.stub');
	}

	/**
	 * Get the default namespace for the class.
	 *
	 * @param  string $rootNamespace
	 *
	 * @return string
	 */
	protected function getDefaultNamespace($rootNamespace): string
    {
		return $rootNamespace . '\Models\Filters';
	}
}
