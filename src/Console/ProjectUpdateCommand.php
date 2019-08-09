<?php

namespace KlimovPaul\PhpProjectUpdate\Console;

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
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
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

        $output->writeln('Updating project "' . $projectName . '"...');

        return 0;
    }
}
