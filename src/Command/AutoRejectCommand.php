<?php

namespace App\Command;

use App\Controller\PreorderController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class AutoRejectCommand extends Command
{
    /**
     * @var $preorderController PreorderController
     */
    private $preorderController;

    public function __construct(PreorderController $preorderController)
    {
        $this->preorderController = $preorderController;
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('preorder:autoreject')->setDescription('Auto reject expired pre orders');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->preorderController->autoRejectExpiredPreorders();
        } catch (\Exception $e) {
            $output->writeln('There was an error while expired preorders are rejected automatically!');
        }

        $output->writeln('Expired pre orders are rejected automatically!');
    }
}