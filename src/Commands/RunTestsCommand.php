<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunTestsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('app:phpunit')
            ->setDescription('Resets database and runs unit tests')
            ->addArgument('test', InputArgument::OPTIONAL)
            ->addOption(
                'coverage',
                null,
                InputOption::VALUE_REQUIRED,
                'Should coverage be run?',
                false
            );
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//        get database parameters
        $db_name = 'lig2test';
        $db_user = 'root';
        $db_pass = 'root';
        $output->writeln(
            [
                '',
                'Reseting database and importing fixtures',
                '========================================',
                '',
            ]
        );
//       restart database schema
        $command = $this->getApplication()->find('doctrine:schema:drop');
        $arguments = [
            'command' => 'doctrine:schema:drop',
            '--force' => true,
            '-e' => 'test',
        ];
        $command->run(new ArrayInput($arguments), $output);
        $command = $this->getApplication()->find('doctrine:schema:update');
        $arguments = [
            'command' => 'doctrine:schema:update',
            '--force' => true,
        ];
        $command->run(new ArrayInput($arguments), $output);
//      create folder for test database
        shell_exec('mkdir -m 777 -p var/db');
//      load fixtures
        $command = $this->getApplication()->find('doctrine:fixtures:load');
        $arguments = [
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true,
        ];
        $command->run(new ArrayInput($arguments), $output);
//      export database to test_db.sql file
        shell_exec(
            'mysqldump -u '.$db_user.' -p'.$db_pass.' '.$db_name.' > var/db/test_db.sql --add-drop-table'
        );
        return 0;
//        $output->writeln(
//            [
//                '',
//                'Running phpunit tests',
//                '=====================',
//                '',
//            ]
//        );
////      create phpunit command using arguments from input
//        $phpunitCommand = 'phpunit';
//        if ($input->getArgument('test')) {
//            $phpunitCommand .= ' ' . $input->getArgument('test');
//        }
//        if ($input->getOption('coverage')) {
//            $phpunitCommand .= ' --coverage-html ./coverage';
//        }
////      run phpunit command
//        $this->runShellCommand($phpunitCommand);
    }
    
//    /**
//     * @param $cmd
//     */
//    private function runShellCommand($cmd): void
//    {
//        while (@ ob_end_flush()) {
//            ;
//        } // end all output buffers if any
//        $proc = popen($cmd, 'r');
//        while (!feof($proc)) {
//            echo fread($proc, 4096);
//            @ flush();
//        }
//    }
}
