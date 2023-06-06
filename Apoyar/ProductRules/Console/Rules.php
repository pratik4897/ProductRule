<?php
/**
 * 
 * Apoyar
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Apoyar
 * @package    Apoyar_ProductRules
 * @copyright  Copyright (c) 2023 Apoyar (http://www.apoyar.eu/)
 */

namespace Apoyar\ProductRules\Console;

use Symfony\Component\Console\Command\Command; // for parent class
use Symfony\Component\Console\Input\InputInterface; // for InputInterface used in execute method
use Symfony\Component\Console\Output\OutputInterface; // for OutputInterface used in execute method
use Magento\Framework\App\State;
use Apoyar\ProductRules\Model\Cron;

/**
 * class rule for running console commands
 */
class Rules extends Command
{
    protected $state;
    protected $cronModel;

    /**
     * contructor
     * @param State $state
     * @param Cron $cronModel
     */
    public function __construct(
        State $state,
        Cron $cronModel
    ) {
        $this->state = $state;
        $this->cronModel = $cronModel;
        parent::__construct();
    }


    /**
     * Configure set default values
     * @return void
     */
    protected function configure()
    {
        $this->setName('product-rules:cron:run')
        ->setDescription(__('Run Product Rules'));

        parent::configure();
    }

    /**
     * Execute function
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
        try {
            $output->writeln("Products Rule Cron Started");
            //uncomment to execute specific slot
            //$this->cronModel->executeSlot1();
            $output->writeln("Slot 1 Rules executed");
            //uncomment to execute specific slot
            //$this->cronModel->execute();
            $output->writeln("Slot 2 Rules executed");
            //uncomment to execute specific rule which is configured at admin
            $this->cronModel->runRules();  
            $output->writeln("Specific Cron Rules executed");
            $output->writeln("Products Rule Cron Finished");
            return 0;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}
