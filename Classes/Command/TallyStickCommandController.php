<?php
declare(strict_types=1);

namespace Sitegeist\TallyStick\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Sitegeist\TallyStick\Domain\BeanCounter;

class TallyStickCommandController extends CommandController
{
    /**
     * @var BeanCounter
     * @Flow\Inject
     */
    protected BeanCounter $beanCounter;


    public function showCommand() {
        $values = $this->beanCounter->getStatSoFar();
        $rows = [];
        foreach ($values as $key => $value) {
            $rows[] = [$key, $value];
        }
        $this->output->outputTable($rows, ['key', 'num']);
    }

    public function resetCommand() {
        $this->beanCounter->reset();
        $this->outputLine("tally stick was resetted");
    }
}
