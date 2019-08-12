<?php

namespace KlimovPaul\PhpProjectUpdate\Console;

use KlimovPaul\PhpProjectUpdate\ProjectFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ProjectUpdateCommand
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ProjectUpdateCommand extends Command
{
    /**
     * @var array application config.
     */
    protected $config = [];

    /**
     * Constructor.
     *
     * @param array $config application config.
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        parent::__construct('project-update');

        $this->setDescription('Updates specified project');

        $arguments = [
            new InputArgument('project-name', InputArgument::REQUIRED, 'Name of the project to be updated.'),
        ];
        $this->getDefinition()->addArguments($arguments);

        /*$options = [
            new InputOption('interactive', 'i', InputOption::VALUE_OPTIONAL, 'Whether to run the command interactively.')
        ];
        $this->getDefinition()->addOptions($options);*/
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectName = $input->getArgument('project-name');

        $factory = new ProjectFactory($this->config);
        $project = $factory->create($projectName);

        $output->writeln('Updating project "' . $projectName . '"...');

        $project->update();

        return 0;
    }
}
